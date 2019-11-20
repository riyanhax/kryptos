<?php

use Application_Service_Licenses as LicensesService;
use Application_Service_LicenseSubscriptions as LicenseSubscriptionsService;
use Application_Model_FreeTrial as FreeTrialRepository;
use Application_Model_Osoby as PersonsModel;
use Application_Service_Authorization as AuthorizationService;
use Application_Service_Osoby as PersonsService;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Exception_DuplicateException as DuplicateException;
use Application_Service_Exception_OsobyException as PersonException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_FreeTrials_DTO_CreateResult as CreateResult;
use Application_Service_FreeTrials_DTO_ConfirmResult as ConfirmResult;
use Zend_Validate_EmailAddress as EmailValidator;

class Application_Service_FreeTrials
{
    /** @var self */
    protected static $_instance = null;

    /** @var string */
    protected $apiSalt;

    /** @var FreeTrialRepository */
    protected $freeTrialsRepository;

    /** @var LicensesService */
    protected $licensesService;

    /** @var LicenseSubscriptionsService */
    protected $licensesSubscriptionService;

    /** @var AuthorizationService */
    protected $authorizationService;

    /** @var PersonsService */
    protected $personsService;

    /** @var EmailValidator */
    protected $emailValidator;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance() {
        if (!self::$_instance) {
            $config = new Zend_Config_Ini(__DIR__ . '/../configs/subscriptions.ini');
            /** @var FreeTrialRepository $freeTrialRepository */
            $freeTrialRepository = Application_Service_Utilities::getModel('FreeTrial');
            self::$_instance = new self(
                $config->get('free_trials'),
                $freeTrialRepository,
                LicensesService::getInstance(),
                LicenseSubscriptionsService::getInstance(),
                PersonsService::getInstance(),
                AuthorizationService::getInstance(),
                new EmailValidator()
            );
        }
        return self::$_instance;
    }

    /**
     * @param Zend_Config $config
     * @param FreeTrialRepository $freeTrialsRepository
     * @param LicensesService $licensesService
     * @param LicenseSubscriptionsService $licensesSubscriptionService
     * @param PersonsService $personsService
     * @param AuthorizationService $authorizationService
     * @param EmailValidator $emailValidator
     */
    public function __construct(
        Zend_Config $config,
        FreeTrialRepository $freeTrialsRepository,
        LicensesService $licensesService,
        LicenseSubscriptionsService $licensesSubscriptionService,
        PersonsService $personsService,
        AuthorizationService $authorizationService,
        EmailValidator $emailValidator
    ) {
        $this->apiSalt = $config->get('api_salt');
        $this->freeTrialsRepository = $freeTrialsRepository;
        $this->licensesService = $licensesService;
        $this->licensesSubscriptionService = $licensesSubscriptionService;
        $this->personsService = $personsService;
        $this->authorizationService = $authorizationService;
        $this->emailValidator = $emailValidator;
    }

    /**
     * @param string $sign
     * @param array $args
     * @return bool
     */
    public function checkSign($sign, array $args)
    {
        $calculatedSign = hash('sha256', join('', $args) . $this->apiSalt);

        return $calculatedSign === $sign;
    }

    /**
     * @param string $email
     * @param string $phone
     * @return CreateResult
     * @throws DuplicateException
     * @throws BadRequestException
     */
    public function create($email, $phone)
    {

        if (!$this->emailValidator->isValid($email)) {
            throw new BadRequestException('Email is not valid: '.var_export($email, true));
        }

        /** @var object $freeTrial */
        if (!$freeTrial = $this->freeTrialsRepository->findByEmail($email)) {
            $freeTrial = $this->freeTrialsRepository->createRow([
                'email' => $email,
                'phone' => $phone,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($freeTrial->status == FreeTrialRepository::STATUS_ACTIVATED) {
            throw new DuplicateException("Free trial for $email is already exists");
        }
        $freeTrial->confirmation_code = $this->generateRandomString();
        $this->freeTrialsRepository->updateStatus($freeTrial, FreeTrialRepository::STATUS_PENDING);
        return new CreateResult($freeTrial->id, $freeTrial->confirmation_code);
    }

    /**
     * @param string $email
     * @param string $code
     * @return ConfirmResult
     * @throws NotFoundException
     * @throws DuplicateException
     * @throws BadRequestException
     * @throws Exception
     */
    public function confirm($email, $code)
    {
        $freeTrial = $this->freeTrialsRepository->findByEmail($email);
        /** @var object $freeTrial */
        if (!$freeTrial || $freeTrial->confirmation_code !== $code) {
            throw new NotFoundException("Free trial for $email not found");
        }
        if ($freeTrial->status == FreeTrialRepository::STATUS_ACTIVATED) {
            throw new DuplicateException("Free trial for $email is already activated");
        }
        $subscription = $this->licensesSubscriptionService->create(
            $licenseId = $this->licensesService->getDefaultLicenseId(),
            $personId = $this->getPersonId(
                $freeTrial->email,
                $freeTrial->phone,
                $password = $this->authorizationService->generateRandomPassword()
            ),
            $this->licensesService->getTrialEndDate($licenseId)
        );
        $this->licensesSubscriptionService->approve($subscription);
        $freeTrial->license_subscription_id = $subscription->id;
        $this->freeTrialsRepository->updateStatus($freeTrial, FreeTrialRepository::STATUS_ACTIVATED);
        return new ConfirmResult($freeTrial->id, $this->personsService->get($personId)->login_do_systemu, $password);
    }

    /**
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length = 20) {
        return substr(
            str_shuffle(
                str_repeat(
                    $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    ceil($length / strlen ($x))
                )
            ),1, $length
        );
    }

    /**
     * @param string $email
     * @param string $phone
     * @param string [$password]
     * @return int
     * @throws PersonException
     */
    protected function getPersonId($email, $phone, $password = null)
    {
        /** @var Zend_Db_Table_Row|object|null $person */
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $request->setParams([
                'email' => $email,
                'phone' => $phone,
                'status' => PersonsModel::STATUS_ACTIVE,
                'type' => PersonsModel::TYPE_EMPLOYEE,
                'type_of_user' => PersonsModel::USER_TYPE_PRO,
                'proposal_role' => 'create',
                'page_name' => 'trial',
            ]);
        if (!$person = $this->personsService->getByEmail($email)) {
            $person = $this->personsService->save($request);
        }
        $this->personsService->setPassword($person, $password);
        return $person->id;
    }
}
