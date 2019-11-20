<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend_Http_Client as HttpClient;
use Zend_Config as Config;
use Application_Service_Subscription_Adapter_DummyAdapter as DummyAdapter;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_DTO_Subscription as Subscription;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Exception_NotFoundException as NotFoundException;

class Services_SubscriptionApapters_DummyAdapterTest extends TestCase
{
    /** @var SubscriptionAdapter */
    protected $service;

    /** @var Config|MockObject */
    protected $configMock;

    /** @var HttpClient|MockObject */
    protected $httpClientMock;

    public function setUp()
    {
        parent::setUp();
        $this->configMock = new Config([]);
        $this->httpClientMock = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->service = new DummyAdapter(
            $this->httpClientMock,
            $this->configMock
        );
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     */
    public function testGetPlan()
    {
        $this->service->getPlan(123);
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     */
    public function testGetPlansList()
    {
        $this->service->getPlansList();
    }


    /**
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     */
    public function testSynchronizePlansList()
    {
        $this->service->synchronizePlansList([]);
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     */
    public function testSynchronizePlan()
    {
        $this->service->synchronizePlan(
            $this->generateSubscriptionPlanMock(),
            $this->generateSubscriptionPlanMock()
        );
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     */
    public function testCreate()
    {
        $this->service->create(
            $this->generateSubscriptionMock()
        );
    }

    /**
     * @return Subscription|MockObject
     */
    protected function generateSubscriptionMock()
    {
        return $this->generateDtoMock(Subscription::class);
    }

    /**
     * @return SubscriptionPlan|MockObject
     */
    protected function generateSubscriptionPlanMock()
    {
        return $this->generateDtoMock(SubscriptionPlan::class);
    }

    protected function generateDtoMock($className)
    {
        $dtoMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        return $dtoMock;
    }
}
