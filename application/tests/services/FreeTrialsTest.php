<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend_Config as Config;
use Zend_Validate_EmailAddress as EmailValidator;
use Zend_Db_Table_Row_Abstract as TableRow;
use Application_Service_FreeTrials as FreeTrialsService;
use Application_Service_Licenses as LicensesService;
use Application_Service_LicenseSubscriptions as LicenseSubscriptionsService;
use Application_Service_Osoby as PersonsService;
use Application_Service_Authorization as AuthorizationService;
use Application_Model_FreeTrial as FreeTrialRepository;
use Application_Service_Exception_DuplicateException as DuplicateException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_FreeTrials_DTO_CreateResult as CreateResult;
use Application_Service_FreeTrials_DTO_ConfirmResult as ConfirmResult;

class Services_FreeTrialsTest extends TestCase
{
    const API_SALT = 'some-salt';

    /** @var Config|MockObject */
    protected $configMock;

    /** @var LicensesService|MockObject */
    protected $licensesServiceMock;

    /** @var LicenseSubscriptionsService|MockObject */
    protected $licenseSubscriptionsServiceMock;

    /** @var PersonsService|MockObject */
    protected $personsServiceMock;

    /** @var AuthorizationService|MockObject */
    protected $authorizationServiceMock;

    /** @var FreeTrialRepository|MockObject */
    protected $freeTrialRepositoryMock;

    /** @var EmailValidator|MockObject */
    protected $emailValidatorMock;

    /** @var FreeTrialsService */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->configMock = new Config(['api_salt' => self::API_SALT]);
        $this->freeTrialRepositoryMock = $this->getMockBuilder(FreeTrialRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByEmail', 'createRow', 'updateStatus'])
            ->getMock();
        $this->licensesServiceMock = $this->getMockBuilder(LicensesService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->licenseSubscriptionsServiceMock = $this->getMockBuilder(LicenseSubscriptionsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'approve'])
            ->getMock();
        $this->personsServiceMock = $this->getMockBuilder(PersonsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getByEmail', 'setPassword'])
            ->getMock();
        $this->authorizationServiceMock = $this->getMockBuilder(AuthorizationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateRandomPassword'])
            ->getMock();
        $this->emailValidatorMock = $this->getMockBuilder(EmailValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValid'])
            ->getMock();
        $this->service = new FreeTrialsService(
            $this->configMock,
            $this->freeTrialRepositoryMock,
            $this->licensesServiceMock,
            $this->licenseSubscriptionsServiceMock,
            $this->personsServiceMock,
            $this->authorizationServiceMock,
            $this->emailValidatorMock
        );
    }

    /**
     * @param string $sign
     * @param array $args
     * @param boolean $expectedResult
     * @dataProvider checkSignProvider
     */
    public function testCheckSign($sign, $args, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->service->checkSign($sign, $args));
    }

    /**
     * @return array
     */
    public function checkSignProvider()
    {
        return [
            [
                'dbadc9ceaa945c5baed560080f12ef9af206ebe4118a5791f6f30628b30afd4a',
                [1, 2, 3],
                true,
            ],
            [
                'some-incorrect-salt',
                [4, 5, 6],
                false,
            ],
        ];
    }

    /**
     * @param string $email
     * @param string $phone
     * @param object $trial
     * @throws DuplicateException
     * @throws BadRequestException
     * @dataProvider createProvider
     */
    public function testCreate($email, $phone, $trial)
    {
        $this->emailValidatorMock
            ->expects($this->once())
            ->method('isValid')
            ->with($email)
            ->willReturn(true);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($trial);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('updateStatus')
            ->with($trial);
        $result = $this->service->create($email, $phone);
        $this->assertInstanceOf(CreateResult::class, $result);
        $this->assertNotEmpty($result->getConfirmationCode());
        $this->assertNotEmpty($result->getTrialId());
        $this->assertEquals($trial->id, $result->getTrialId());
    }

    /**
     * @param string $email
     * @param string $phone
     * @param object $trial
     * @throws DuplicateException
     * @throws BadRequestException
     * @dataProvider createProvider
     */
    public function testCreateFirst($email, $phone, $trial)
    {
        $this->emailValidatorMock
            ->expects($this->once())
            ->method('isValid')
            ->with($email)
            ->willReturn(true);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('createRow')
            ->willReturn($trial);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('updateStatus')
            ->with($trial);
        $result = $this->service->create($email, $phone);
        $this->assertInstanceOf(CreateResult::class, $result);
        $this->assertNotEmpty($result->getConfirmationCode());
        $this->assertNotEmpty($result->getTrialId());
        $this->assertEquals($trial->id, $result->getTrialId());
    }

    /**
     * @param string $email
     * @param string $phone
     * @param object $trial
     * @throws DuplicateException
     * @throws BadRequestException
     * @dataProvider createProvider
     * @expectedException Application_Service_Exception_DuplicateException
     */
    public function testCreateWithDuplicate($email, $phone, $trial)
    {
        $trial->status = FreeTrialRepository::STATUS_ACTIVATED;
        $this->emailValidatorMock
            ->expects($this->once())
            ->method('isValid')
            ->with($email)
            ->willReturn(true);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($trial);
        $this->service->create($email, $phone);
    }

    /**
     * @param string $email
     * @param string $phone
     * @throws DuplicateException
     * @throws BadRequestException
     * @dataProvider createProvider
     * @expectedException Application_Service_Subsription_Exception_BadRequestException
     */
    public function testCreateWithInvalidEmail($email, $phone)
    {
        $this->emailValidatorMock
            ->expects($this->once())
            ->method('isValid')
            ->with($email)
            ->willReturn(false);
        $this->service->create($email, $phone);
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        return [
            [
                'some-email',
                'some-phone',
                (object)[
                    'id' => 123,
                    'status' => FreeTrialRepository::STATUS_PENDING,
                ],
            ],
        ];
    }

    /**
     * @param object $trial
     * @param object $person
     * @param object $subscriptionData
     * @param string $password
     * @throws DuplicateException
     * @throws NotFoundException
     * @throws BadRequestException
     * @dataProvider confirmProvider
     */
    public function testConfirm($trial, $person, $subscriptionData, $password)
    {
        $subscription = $this->getMockBuilder(TableRow::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', '__get', '__set'])
            ->getMock();
        $subscription
            ->expects($this->any())
            ->method('__get')
            ->willReturnMap([
                ['id', $subscriptionData->id],
            ]);
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($trial->email)
            ->willReturn($trial);
        $this->personsServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($person->id)
            ->willReturn($person);
        $this->personsServiceMock
            ->expects($this->once())
            ->method('getByEmail')
            ->with($trial->email)
            ->willReturn($person);
        $this->personsServiceMock
            ->expects($this->once())
            ->method('setPassword')
            ->with($person, $password)
            ->willReturn($person);
        $this->licenseSubscriptionsServiceMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($subscription);
        $this->licenseSubscriptionsServiceMock
            ->expects($this->once())
            ->method('approve')
            ->with($subscription);
        $this->authorizationServiceMock
            ->expects($this->once())
            ->method('generateRandomPassword')
            ->willReturn($password);
        $result = $this->service->confirm($trial->email, $trial->confirmation_code);
        $this->assertInstanceOf(ConfirmResult::class, $result);
        $this->assertNotEmpty($result->getTrialId());
        $this->assertEquals($trial->id, $result->getTrialId());
        $this->assertNotEmpty($result->getLogin());
        $this->assertEquals($person->login_do_systemu, $result->getLogin());
        $this->assertNotEmpty($result->getPassword());
        $this->assertEquals($password, $result->getPassword());
    }

    /**
     * @param object $trial
     * @throws DuplicateException
     * @throws NotFoundException
     * @throws BadRequestException
     * @dataProvider confirmProvider
     * @expectedException Application_Service_Exception_NotFoundException
     */
    public function testConfirmWithNotFound($trial)
    {
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($trial->email)
            ->willReturn(false);
        $this->service->confirm($trial->email, $trial->confirmation_code);
    }

    /**
     * @param object $trial
     * @throws DuplicateException
     * @throws NotFoundException
     * @throws BadRequestException
     * @dataProvider confirmProvider
     * @expectedException Application_Service_Exception_DuplicateException
     */
    public function testConfirmWithDuplicate($trial)
    {
        $trial->status = FreeTrialRepository::STATUS_ACTIVATED;
        $this->freeTrialRepositoryMock
            ->expects($this->once())
            ->method('findByEmail')
            ->with($trial->email)
            ->willReturn($trial);
        $this->service->confirm($trial->email, $trial->confirmation_code);
    }

    /**
     * @return array
     */
    public function confirmProvider()
    {
        return [
            [
                (object)[
                    'id' => 123,
                    'status' => FreeTrialRepository::STATUS_PENDING,
                    'email' => 'some-email',
                    'phone' => 'some-phone',
                    'confirmation_code' => 'some-code',
                ],
                (object)[
                    'id' => 456,
                    'login_do_systemu' => 'user1',
                    'password' => 'password',
                ],
                (object)[
                    'id' => 789,
                ],
                'some-password',
            ],
        ];
    }
}
