<?php

use Application_Model_LicenseSubscription as SubscriptionRepository;
use Application_Model_LicenseSubscriptionActivity as ActivityRepository;
use Application_Service_Utilities as Utilities;
use Application_Service_Osoby as PersonsService;
use Application_Service_Licenses as LicensesService;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Subscription_DTO_Subscription as Subscription;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_DTO_Customer as Customer;
use Zend_Db_Table_Row_Abstract as DbRow;
use Zend_Controller_Request_Http as HttpRequest;

class Application_Service_LicenseSubscriptions
{
    /** @var self */
    protected static $_instance;

    /** @var PersonsService */
    protected $personsService;

    /** @var LicensesService */
    protected $licensesService;

    /** @var SubscriptionAdapter */
    protected $subscriptionAdapter;

    /** @var SubscriptionRepository */
    protected $subscriptionRepository;

    /** @var ActivityRepository */
    protected $activityRepository;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance() {
        if (!self::$_instance) {
            /** @var SubscriptionRepository $subscriptionRepository */
            $subscriptionRepository = Utilities::getModel('LicenseSubscription');
            /** @var ActivityRepository $activityRepository */
            $activityRepository = Utilities::getModel('LicenseSubscriptionActivity');
            $subscriptionFactory = Application_Service_Subscription_Factory::getInstance();
            self::$_instance = new self(
                $subscriptionRepository,
                $activityRepository,
                LicensesService::getInstance(),
                PersonsService::getInstance(),
                $subscriptionFactory->createAdapter()
            );
        }
        return self::$_instance;
    }

    /**
     * @param SubscriptionRepository $subscriptionRepository
     * @param ActivityRepository $activityRepository
     * @param LicensesService $licensesService
     * @param PersonsService $personsService
     * @param SubscriptionAdapter $subscriptionAdapter
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        ActivityRepository $activityRepository,
        LicensesService $licensesService,
        PersonsService $personsService,
        SubscriptionAdapter $subscriptionAdapter
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->activityRepository = $activityRepository;
        $this->licensesService = $licensesService;
        $this->personsService = $personsService;
        $this->subscriptionAdapter = $subscriptionAdapter;
    }

    /**
     * @param int $licenseId
     * @param $personId
     * @param $endDate
     * @param int $status
     * @return DbRow|object
     */
    public function create($licenseId, $personId, $endDate, $status = SubscriptionRepository::STATUS_INACTIVE, $arrTypeCounts = null)
    {
        if(empty($arrTypeCounts))
        {
            $arrTypeCounts = array(
                        'expert_count' => 0,
                        'pro_count' => 0,
                        'mini_count' => 0
                    );
        }
        $license = $this->licensesService->get($licenseId);
        /** @var DbRow|object $subscription */
        $subscription = $this->subscriptionRepository->createRow([
            'license_id' => $licenseId,
            'osoby_id' => $personId,
            'created_at' => date('Y-m-d H:i:s'),
            'end_date' => $endDate,
            'status' => $status,
            'expert_count'=>$license->expert_count + $arrTypeCounts['expert_count'],
            'pro_count'=>$license->pro_count + $arrTypeCounts['pro_count'],
            'mini_count'=>$license->mini_count + $arrTypeCounts['mini_count'],
            'subscription_price' => $license->price
        ]);

        //$this->save($subscription);
        $this->addActivity($licenseId, ActivityRepository::TYPE_CREATE);
        return $subscription;
    }

    /**
     * @param $id
     * @return DbRow|object
     * @throws NotFoundException
     * @throws Exception
     */
    public function get($id) {
        if ($license = $this->subscriptionRepository->getOne($id)) {
            return $license;
        }
        throw new NotFoundException('License subscription not found: '.$id);
    }

    /**
     * @param null $personId
     * @param null [$status]
     * @return DbRow|object
     */
    public function getList($personId = null, $status = null) {
        return $this->subscriptionRepository->getList(array_filter([
            'osoby_id' => $personId,
            'status' => $status,
        ]));
    }


   

    /**
     * @param null $personId
     * @param null [$status]
     * @return boolean
     */
    public function checkPersonLicense($personId) {
        if (!$this->subscriptionRepository->getCountByOsobaId($personId)) {
            // person created without any licenses
            return true;
        }
        foreach ($this->getList($personId, SubscriptionRepository::STATUS_ACTIVATED) as $subscription) {
            
            //echo $subscription->end_date.'----'.strtotime($subscription->end_date).'----'.time().'<br/>';die;
            if (strtotime($subscription->end_date) < time()) {
                $this->deactivate($subscription, ActivityRepository::TYPE_EXPIRE);
                return true;
            }
            //return true;
        }
        return false;
    }

    /**
     * @param DbRow $subscription
     * @throws NotFoundException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws BadRequestException
     */
    public function approve(DbRow $subscription)
    {
        /** @var DbRow|object $subscription */
        $license = $this->licensesService->get($subscription->license_id);
        /*$this->subscriptionAdapter->create(
            $this->subscription(
                $this->subscriptionAdapter->getPlan($license->external_id),
                $this->customer($this->personsService->get($subscription->osoby_id))
            )
        );*/
        $subscription->status = SubscriptionRepository::STATUS_ACTIVATED;
        $this->save($subscription);
        $this->addActivity($subscription->id, ActivityRepository::TYPE_APPROVE);
    }

    /**
     * @param DbRow $subscription
     * @param int $activityType
     */
    public function deactivate(DbRow $subscription, $activityType = ActivityRepository::TYPE_CANCEL)
    {
        /** @var DbRow|object $subscription */
        $subscription->status = SubscriptionRepository::STATUS_INACTIVE;
        $this->save($subscription);
        $this->addActivity($subscription->id, $activityType);
    }

    /**
     * @param int $subscriptionId
     * @param int $type
     * @return DbRow|object
     */
    public function addActivity($subscriptionId, $type)
    {
        $activity = $this->activityRepository->createRow([
            'license_subscription_id' => $subscriptionId,
            'event_type' => $type,
            'event_time' => date('Y-m-d H:i:s'),
        ]);
        $activity->save();
        return $activity;
    }

    /**
     * @return array
     */
    public function getActivityTypes()
    {
        return [
            ActivityRepository::TYPE_CREATE => 'created',
            ActivityRepository::TYPE_APPROVE => 'approved',
            ActivityRepository::TYPE_EXPIRE => 'expired',
            ActivityRepository::TYPE_CANCEL => 'canceled',
        ];
    }

    /**
     * @param int $subscriptionId
     * @return DbRow|object
     */
    public function getActivity($subscriptionId)
    {
        return $this->activityRepository->getList([
            'license_subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * @param HttpRequest $request
     */
    public function subscriptionChangeCallback(HttpRequest $request)
    {

    }

    /**
     * @param object $person
     * @return Customer
     */
    protected function customer($person)
    {
        return new Customer(
            (int) $person->id,
            $person->email,
            $person->telefon_stacjonarny
                ?: $person->telefon_komorkowy,
            $person->imie ?: '',
            $person->nazwisko ?: ''
        );
    }

    /**
     * @param SubscriptionPlan $plan
     * @param Customer $customer
     * @return Subscription
     */
    protected function subscription(SubscriptionPlan $plan, Customer $customer)
    {
        return new Subscription($plan, $customer);
    }

    /**
     * @param DbRow|object $subscription
     */
    protected function save($subscription)
    {
        $subscription->updated_at = date('Y-m-d H:i:s');

        $subscription->save();
    }

    public function getActive($personId = null, $status = null) {

        $where['status'] = $status;
        if($personId)
            $where['osoby_id'] = $personId;

        return $this->subscriptionRepository->getOne($where);
    }
}
