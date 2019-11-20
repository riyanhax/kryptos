<?php

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend_Http_Client as HttpClient;
use Zend_Config as Config;
use Zend_Http_Response as HttpResponse;
use Zend_Json as Json;
use Application_Service_Subscription_Adapter_ChargebeeAdapter as ChargebeeAdapter;
use Application_Service_Subscription_Adapter_AdapterInterface as SubscriptionAdapter;
use Application_Service_Subscription_DTO_Customer as Customer;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_DTO_Subscription as Subscription;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Model_License as License;

class Services_SubscriptionApapters_ChargebeeAdapterTest extends TestCase
{
    const API_URL = 'http://some-api-url.com';

    const API_KEY = 'some-key';

    /** @var SubscriptionAdapter */
    protected $service;

    /** @var Config|MockObject */
    protected $configMock;

    /** @var HttpClient|MockObject */
    protected $httpClientMock;

    public function setUp()
    {
        parent::setUp();
        $this->configMock = new Config([
            'url' => self::API_URL,
            'api_key' => self::API_KEY,
        ]);
        $this->httpClientMock = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'resetParameters',
                'setUri',
                'setAuth',
                'setMethod',
                'setEncType',
                'setParameterGet',
                'setParameterPost',
                'request'
            ])->getMock();
        $this->service = new ChargebeeAdapter(
            $this->httpClientMock,
            $this->configMock
        );
    }

    /**
     * @param $id
     * @param array $plan
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider getPlanProvider
     */
    public function testGetPlan($id, $plan)
    {
        $this->mockRequest(
            'plans/' . $id,
            HttpClient::GET,
            Json::encode([
                'plan' => $plan,
            ])
        );
        $plan = $this->service->getPlan($id);
        $this->assertInstanceOf(SubscriptionPlan::class, $plan);
        $this->assertEquals($id, $plan->getExternalId());
    }

    /**
     * @return array
     */
    public function getPlanProvider()
    {
        return [
            [
                123, $this->mockSubscriptionPlanData(123),
            ],
        ];
    }

    /**
     * @param array $expectedResult
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider getPlansListProvider
     */
    public function testGetPlansList($expectedResult)
    {
        $this->mockRequest(
            'plans',
            HttpClient::GET,
            Json::encode([
                'plans' => $expectedResult,
            ])
        );
        $result = $this->service->getPlansList();
        $this->assertInternalType('array', $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getPlansListProvider()
    {
        return [
            [
                [],
            ],
        ];
    }


    /**
     * @param $plans
     * @param $cbPlans
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider synchronizePlansListProvider
     */
    public function testSynchronizePlansList($plans, $cbPlans)
    {
        $this->mockRequest(
            'plans',
            HttpClient::GET,
            Json::encode([
                'plans' => $cbPlans,
            ])
        );
        $this->service->synchronizePlansList($plans);
    }

    /**
     * @return array
     */
    public function synchronizePlansListProvider()
    {
        return [
            [
                [],
                [],
            ],
        ];
    }

    /**
     * @param $planData
     * @param $cbPlanData
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider synchronizePlanProvider
     */
    public function testSynchronizePlanWithEquals($planData, $cbPlanData)
    {
        $this->mockRequest(
            'plans/' . $planData['id'],
            HttpClient::POST,
            Json::encode([])
        );
        $this->service->synchronizePlan(
            $this->generateSubscriptionPlanMock($planData),
            $this->generateSubscriptionPlanMock($cbPlanData)
        );
    }

    /**
     * @return array
     */
    public function synchronizePlanProvider()
    {
        return [
            [
                $this->mockSubscriptionPlanData(123),
                $this->mockSubscriptionPlanData(456),
            ],
        ];
    }

    /**
     * @param $planData
     * @param $customerData
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @dataProvider createProvider
     */
    public function testCreate($planData, $customerData)
    {
        $this->mockRequest(
            'subscriptions',
            HttpClient::POST,
            Json::encode([])
        );
        $this->service->create(
            $this->generateSubscriptionMock(
                $this->generateSubscriptionPlanMock($planData),
                $this->generateCustomerMock($customerData)
            )
        );
    }

    public function createProvider()
    {
        return [
            [
                $this->mockSubscriptionPlanData(),
                $this->mockCustomerData(),
            ],
        ];
    }

    /**
     * @param $plan
     * @param $customer
     * @return Subscription|MockObject
     */
    protected function generateSubscriptionMock($plan, $customer)
    {
        return $this->generateDtoMock(Subscription::class, [
            'getPlan' => $plan,
            'getCustomer' => $customer,
        ]);
    }

    /**
     * @param int|string $id
     * @param string $name
     * @param string $description
     * @param int|null $period
     * @param int|null $period_unit
     * @param int|null $trial_period
     * @param int|null $trial_period_unit
     * @param int $price
     * @param string $currency_code
     * @param int $status
     * @return array
     */
    protected function mockSubscriptionPlanData(
        $id = 'subscription-id',
        $name = 'subscription name',
        $description = 'subscription description',
        $period = 1,
        $period_unit = License::PERIOD_MONTH,
        $trial_period = null,
        $trial_period_unit = null,
        $price = 0,
        $currency_code = '',
        $status = License::STATUS_INACTIVE
    ) {
        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'period' => $period,
            'period_unit' => $period_unit,
            'trial_period' => $trial_period,
            'trial_period_unit' => $trial_period_unit,
            'price' => $price,
            'currency_code' => $currency_code,
            'status' => $status,
        ];
    }

    protected function mockCustomerData(
        $id = 'customer-id',
        $email = 'customer-email',
        $phone = 'customer-phone',
        $first_name = 'customer-first-name',
        $last_name = 'customer-last-name'
    ) {
        return [
            'id' => $id,
            'email' => $email,
            'phone' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ];
    }

    /**
     * @param array $data
     * @return SubscriptionPlan|MockObject
     */
    protected function generateSubscriptionPlanMock(array $data)
    {
        return $this->generateDtoMock(SubscriptionPlan::class, [
            'getExternalId' => $data['id'],
            'getName' => $data['name'],
            'getDescription' => $data['description'],
            'getPeriod' => $data['period'],
            'getPeriodUnit' => $data['period_unit'],
            'getTrialPeriod' => $data['trial_period'],
            'getTrialPeriodUnit' => $data['trial_period_unit'],
            'getPrice' => $data['price'],
            'getCurrency' => $data['currency_code'],
            'getStatus' => $data['status'],
        ]);
    }

    /**
     * @param array $data
     * @return SubscriptionPlan|MockObject
     */
    protected function generateCustomerMock(array $data)
    {
        return $this->generateDtoMock(Customer::class, [
            'getId' => $data['id'],
            'getEmail' => $data['email'],
            'getPhone' => $data['phone'],
            'getFirstName' => $data['first_name'],
            'getLastName' => $data['last_name'],
        ]);
    }

    /**
     * @param $url
     * @param $method
     * @param $responseBody
     * @param int $status
     * @param null $message
     * @param bool $isError
     */
    protected function mockRequest($url, $method, $responseBody, $status = 200, $message = null, $isError = false)
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('resetParameters')
            ->willReturnSelf();
        $this->httpClientMock
            ->expects($this->once())
            ->method('setUri')
            ->with(self::API_URL . 'api/' . ChargebeeAdapter::API_VERSION . '/' . $url)
            ->willReturnSelf();
        $this->httpClientMock
            ->expects($this->once())
            ->method('setAuth')
            ->with(self::API_KEY)
            ->willReturnSelf();
        $this->httpClientMock
            ->expects($this->once())
            ->method('setMethod')
            ->with($method)
            ->willReturnSelf();
        $this->httpClientMock
            ->expects($this->once())
            ->method('setEncType')
            ->with(HttpClient::ENC_URLENCODED)
            ->willReturnSelf();
        $this->httpClientMock
            ->expects($this->any())
            ->method($method === HttpClient::GET?'setParameterGet':'setParameterPost')
            ->willReturnSelf();

        $response = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['isError', 'getStatus', 'getMessage', 'getBody'])
            ->getMock();
        $response
            ->expects($this->once())
            ->method('isError')
            ->willReturn($isError);
        $response
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);
        $response
            ->expects($this->any())
            ->method('getMessage')
            ->willReturn($message);
        $response
            ->expects($this->any())
            ->method('getBody')
            ->willReturn($responseBody);
        $this->httpClientMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);
    }

    /**
     * @param string $className
     * @param array $methods
     * @return MockObject
     */
    protected function generateDtoMock($className, array $methods = [])
    {
        $dtoMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(array_keys($methods))
            ->getMock();
        foreach ($methods as $method => $value) {
            $dtoMock->method($method)->willReturn($value);
        }
        return $dtoMock;
    }
}
