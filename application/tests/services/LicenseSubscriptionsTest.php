<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend_Db_Table_Row_Abstract as TableRow;
use Application_Service_LicenseSubscriptions as SubscriptionsService;
use Application_Model_LicenseSubscription as SubscriptionRepository;
use Application_Model_LicenseSubscriptionActivity as ActivityRepository;
use Application_Service_Osoby as PersonsService;
use Application_Service_Licenses as LicensesService;
use Application_Service_Subscription_Adapter_DummyAdapter as DummyAdapter;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;

class Services_LicenseSubscriptionsTest extends TestCase
{
    /** @var SubscriptionsService */
    protected $service;

    /** @var SubscriptionRepository|MockObject */
    protected $subscriptionRepositoryMock;

    /** @var ActivityRepository|MockObject */
    protected $activityRepositoryMock;

    /** @var LicensesService|MockObject */
    protected $licensesServiceMock;

    /** @var PersonsService|MockObject */
    protected $personsServiceMock;

    /** @var SubscriptionAdapter|MockObject */
    protected $subscriptionAdapterMock;


    public function setUp()
    {
        parent::setUp();
        $this->subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOne', 'createRow'])
            ->getMock();
        $this->activityRepositoryMock = $this->getMockBuilder(ActivityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['createRow'])
            ->getMock();
        $this->licensesServiceMock = $this->getMockBuilder(LicensesService::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->personsServiceMock = $this->getMockBuilder(PersonsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->subscriptionAdapterMock = $this->getMockBuilder(DummyAdapter::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['getPlan'])
            ->getMock();
        $this->service = new SubscriptionsService(
            $this->subscriptionRepositoryMock,
            $this->activityRepositoryMock,
            $this->licensesServiceMock,
            $this->personsServiceMock,
            $this->subscriptionAdapterMock
        );
    }

    /**
     * @param int $id
     * @param object $subscription
     * @throws NotFoundException
     * @dataProvider getProvider
     */
    public function testGet($id, $subscription)
    {
        $this->subscriptionRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('getOne')
            ->with($id)
            ->willReturn($subscription);
        $this->assertEquals($subscription, $this->service->get($id));
    }

    /**
     * @param int $id
     * @throws NotFoundException
     * @dataProvider getProvider
     * @expectedException Application_Service_Exception_NotFoundException
     */
    public function testGetWithException($id)
    {
        $this->subscriptionRepositoryMock
            ->expects($this->once())
            ->method('getOne')
            ->with($id)
            ->willReturn(null);
        $this->service->get($id);
    }

    /**
     * @return array
     */
    public function getProvider()
    {
        return [
            [
                1,
                true
            ],
        ];
    }

    /**
     * @param $licenseId
     * @param $personId
     * @param $subscriptionId
     * @param $endDate
     * @dataProvider createProvider
     */
    public function testCreate($licenseId, $personId, $subscriptionId, $endDate)
    {
        $subscription = $this->getMockBuilder(TableRow::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', '__get', '__set'])
            ->getMock();
        $subscription
            ->expects($this->once())
            ->method('save');
        $subscription
            ->expects($this->once())
            ->method('__get')
            ->willReturn($subscriptionId);
        $subscription
            ->expects($this->once())
            ->method('__set');
        $this->subscriptionRepositoryMock
            ->expects($this->once())
            ->method('createRow')
            ->willReturn($subscription);
        $this->testAddActivity($licenseId, ActivityRepository::TYPE_CREATE);
        $this->assertEquals(
            $subscription,
            $this->service->create($licenseId, $personId, $endDate)
        );
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        return [
            [
                'license-id',
                'person-id',
                'subscription-id',
                'some-date'
            ],
        ];
    }

    /**
     * @param int $subscriptionId
     * @param object $license
     * @param object $person
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider approveProvider
     */
    public function testApprove($subscriptionId, $license, $person)
    {
        /** @var TableRow|MockObject|object $subscription */
        $subscription = $this->getMockBuilder(TableRow::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', '__get', '__set'])
            ->getMock();
        $subscription
            ->expects($this->atLeastOnce())
            ->method('__get')
            ->willReturnMap([
                ['id', $subscriptionId],
                ['license_id', $license->id],
                ['osoby_id', $person->id],
            ]);
        $subscription
            ->expects($this->atLeastOnce())
            ->method('__set')
            ->withConsecutive(
                ['status', SubscriptionRepository::STATUS_ACTIVATED],
                ['updated_at', $this->anything()]
            );
        $subscription
            ->expects($this->once())
            ->method('save');
        $this->testGet($subscriptionId, $subscription);
        $this->licensesServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($license->id)
            ->willReturn($license);
        $this->personsServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($person->id)
            ->willReturn($person);
        $subscriptionPlanMock = $this->getMockBuilder(SubscriptionPlan::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->subscriptionAdapterMock
            ->expects($this->once())
            ->method('getPlan')
            ->with($license->external_id)
            ->willReturn($subscriptionPlanMock);
        $this->testAddActivity($license->id, ActivityRepository::TYPE_CREATE);
        $this->service->approve($subscription);
    }

    /**
     * @return array
     */
    public function approveProvider()
    {
        return [
            [
                'some-subscription-id',
                (object)[
                    'id' => 'some-license-id',
                    'external_id' => 'some-external-id',
                ],
                (object)[
                    'id' => 'some-person-id',
                    'email' => 'person-email',
                    'telefon_stacjonarny' => 'person-phone',
                    'telefon_komorkowy' => 'person-phone',
                    'imie' => 'person-first-name',
                    'nazwisko' => 'person-last-name',
                ],
            ],
        ];
    }
    /**
     * @param $subscriptionId
     * @param $type
     * @dataProvider addActivityProvider
     */
    public function testAddActivity($subscriptionId, $type)
    {
        $activity = $this->getMockBuilder(TableRow::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $activity
            ->expects($this->atLeastOnce())
            ->method('save');
        $this->activityRepositoryMock
            ->expects($this->atLeastOnce())
            ->method('createRow')
            ->willReturn($activity);
        $this->service->addActivity($subscriptionId, $type);
    }

    /**
     * @return array
     */
    public function addActivityProvider()
    {
        return [
            [
                'subscription-id',
                'event-type',
            ],
        ];
    }
}
