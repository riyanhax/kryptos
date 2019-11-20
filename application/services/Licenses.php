<?php

use Application_Model_License as LicenseRepository;
use Application_Model_Osoby as PersonRepository;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_SubscriptionOverLimitException as SubscriptionOverLimitException;

class Application_Service_Licenses
{
    /** @var self */
    protected static $_instance = null;

    /** @var LicenseRepository */
    protected $licenseRepository;

    /** @var SubscriptionAdapter */
    protected $subscriptionAdapter;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance() {
        if (!self::$_instance) {
            /** @var LicenseRepository $licenseModel */
            $licenseModel = Application_Service_Utilities::getModel('License');
            $subscriptionFactory = Application_Service_Subscription_Factory::getInstance();
            self::$_instance = new self($licenseModel, $subscriptionFactory->createAdapter());
        }
        return self::$_instance;
    }

    /**
     * @param LicenseRepository $licenseModel
     * @param SubscriptionAdapter $subscriptionAdapter
     */
    public function __construct(LicenseRepository $licenseModel, SubscriptionAdapter $subscriptionAdapter)
    {
        $this->licenseRepository = $licenseModel;
        $this->subscriptionAdapter = $subscriptionAdapter;
    }

    /**
     * @return mixed
     */
    public function getList() {
        return $this->licenseRepository->getList([
            'status <> ?' => LicenseRepository::STATUS_DELETED,
        ]);
    }

    /**
     * @param array $params
     * @return Zend_Db_Table_Row_Abstract
     */
    public function create(array $params = []) {
        return $this->licenseRepository->createRow($params + [
                'period' => 1,
                'period_unit' => LicenseRepository::PERIOD_MONTH,
                'trial_period' => 14,
                'trial_period_unit' => LicenseRepository::PERIOD_DAY,
                'user_type' => PersonRepository::USER_TYPE_PRO,
                'status' => LicenseRepository::STATUS_INACTIVE,
                'price' => 0,
            ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundException
     * @throws Exception
     */
    public function get($id) {
        if ($license = $this->licenseRepository->getOne($id)) {
            return $license;
        }
        throw new NotFoundException('License not found: '.$id);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws SubscriptionOverLimitException
     */
    public function save(array $params) {
        return $this->licenseRepository->save($params);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function remove($id) {
        return $this->licenseRepository->remove($id);
    }

    /**
     * @return array
     */
    public function getPeriodsUnits() {
        return [
            LicenseRepository::PERIOD_MONTH => 'month',
            LicenseRepository::PERIOD_YEAR => 'year',
        ];
    }

    /**
     * @return array
     */
    public function getTrialPeriodsUnits() {
        return [
            LicenseRepository::PERIOD_DAY => 'day',
            LicenseRepository::PERIOD_MONTH => 'month',
        ];
    }

    /**
     * @return array
     */
    public function getCurrencies() {
        return [
            LicenseRepository::CURRENCY_USD => 'USD (in cents)',
        ];
    }

    /**
     * @param int $id
     * @param int [$time]
     * @return string
     * @throws NotFoundException
     */
    public function getEndDate($id, $time = null)
    {
        $license = $this->get($id);
        return $this->calculateDate($time ?: time(), $license['period_unit'], $license['period']);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundException
     */
    public function getTrialEndDate($id)
    {
        $license = $this->get($id);
        return $this->calculateDate(time(), $license['trial_period_unit'], $license['trial_period']);
    }

    /**
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function synchronizeAll()
    {
        $this->subscriptionAdapter->synchronizePlansList(array_map(function ($license){
            return $this->subscriptionPLan($license);
        }, $this->getList()));
    }

    /**
     * @param int $id
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function synchronize($id)
    {
        $license = $this->subscriptionPLan($this->get($id));
        try {
            $cbLicense = $this->subscriptionAdapter->getPlan(
                $license->getExternalId()
            );
        } catch (Application_Service_Exception_NotFoundException $exception) {
            $cbLicense = null;
        }
        $this->subscriptionAdapter->synchronizePlan($license, $cbLicense);
    }

    /**
     * @param object $license
     * @return SubscriptionPlan
     */
    protected function subscriptionPLan($license)
    {
        return new SubscriptionPlan(
            $license->name,
            $license->description,
            (int) $license->period,
            (int) $license->period_unit,
            (int) $license->trial_period,
            (int) $license->trial_period_unit,
            (int) $license->price,
            $license->currency,
            $license->status == LicenseRepository::STATUS_ACTIVATED
                ? LicenseRepository::STATUS_ACTIVATED
                : LicenseRepository::STATUS_INACTIVE,
            /* Vipin code starts */
            (int) $license->is_trial,
            /* Vipin code end */
            $license->external_id
        );
    }

    /**
     * @param int $time
     * @param int $periodUnit
     * @param int $periodValue
     * @return false|string
     */
    protected function calculateDate($time, $periodUnit, $periodValue)
    {
        if (!$periodValue) {
            return date('Y-m-d H:i:s', $time);
        }
        switch ($periodUnit) {
            case LicenseRepository::PERIOD_YEAR:
                $endTime = strtotime("+$periodValue year", $time);
                break;
            case LicenseRepository::PERIOD_MONTH:
                $endTime = strtotime("+$periodValue month", $time);
                break;
            case LicenseRepository::PERIOD_WEEK:
                $endTime = strtotime("+$periodValue week", $time);
                break;
            case LicenseRepository::PERIOD_DAY:
            default:
                $endTime = strtotime("+$periodValue day", $time);
        }
        return date('Y-m-d', $endTime) . ' 23:59:59';
    }

    /**
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function getLicense() {
        return $this->licenseRepository->getLicense();
    }


    /**
     * @return bool
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function hasLicense() {
        $license = $this->getLicense();
        return !empty($license);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getDefaultLicenseId()
    {
        $license = $this->licenseRepository->findOneBy([
            'status = ?' => LicenseRepository::STATUS_ACTIVATED,
            'user_type = ?' => PersonRepository::USER_TYPE_PRO,
        ]);
        if (!$license) {
            throw new Exception('License with "pro" user type not found');
        }
        return $license['id'];
    }

    /**
     * @param array $data
     * @return mixed
     * @deprecated
     */
    public function createLicense(array $data) {
        return $this->licenseRepository->insert($data);
    }

    /**
     * @param $usersCount
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function increaseUserLimit($usersCount) {
        $this->licenseRepository->increaseUserLimit($usersCount);
    }

    public function upgradeVersion($version) {
        $this->licenseRepository->setVersion($version);
    }

}
