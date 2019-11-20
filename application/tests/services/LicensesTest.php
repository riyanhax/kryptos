<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Application_Model_License as LicenseRepository;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Licenses as LicenseService;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_SubscriptionOverLimitException as LimitException;

class Services_LicensesTest extends TestCase
{
    /** @var LicenseRepository|MockObject */
    protected $licenseRepositoryMock;

    /** @var SubscriptionAdapter|MockObject */
    protected $subscriptionAdapterMock;

    /** @var LicenseService */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->licenseRepositoryMock = $this->getMockBuilder(LicenseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriptionAdapterMock = $this->getMockBuilder(SubscriptionAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new LicenseService(
            $this->licenseRepositoryMock,
            $this->subscriptionAdapterMock
        );
    }

    /**
     * @param $list
     * @dataProvider getListProvider
     */
    public function testGetList($list)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->willReturn($list);
        $this->assertEquals($list, $this->service->getList());
    }

    /**
     * @return array
     */
    public function getListProvider()
    {
        return [
            [
                [],
            ],
            [
                [1,2,3],
            ],
        ];
    }

    /**
     * @param $data
     * @dataProvider createProvider
     */
    public function testCreate($data)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('createRow')
            ->willReturn($data);
        $this->assertEquals($data, $this->service->create([]));
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        return [
            [
                [],
            ],
            [
                [1,2,3],
            ],
        ];
    }

    /**
     * @param $id
     * @param $result
     * @throws NotFoundException
     * @dataProvider getProvider
     */
    public function testGet($id, $result)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('getOne')
            ->with($id)
            ->willReturn($result);
        $this->assertEquals($result, $this->service->get($id));
    }

    /**
     * @param $id
     * @throws NotFoundException
     * @expectedException Application_Service_Exception_NotFoundException
     * @dataProvider getProvider
     */
    public function testGetWithException($id)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('getOne')
            ->with($id)
            ->willReturn(false);
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
     * @param $data
     * @param $result
     * @throws LimitException
     * @dataProvider saveProvider
     */
    public function testSave($data, $result)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($data)
            ->willReturn($result);
        $this->assertEquals($result, $this->service->save($data));
    }

    /**
     * @return array
     */
    public function saveProvider()
    {
        return [
            [
                [1, 2, 3],
                [4, 5, 6],
            ],
            [
                [],
                false,
            ],
        ];
    }

    /**
     * @param $id
     * @param $result
     * @throws Exception
     * @dataProvider removeProvider
     */
    public function testRemove($id, $result)
    {
        $this->licenseRepositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with($id)
            ->willReturn($result);
        $this->assertEquals($result, $this->service->remove($id));
    }

    /**
     * @return array
     */
    public function removeProvider()
    {
        return [
            [
                1,
                true,
            ],
            [
                null,
                false,
            ],
        ];
    }

    public function testGetListMethods()
    {
        $this->assertInternalType('array', $this->service->getPeriodsUnits());
        $this->assertInternalType('array', $this->service->getTrialPeriodsUnits());
        $this->assertInternalType('array', $this->service->getCurrencies());
    }
}
