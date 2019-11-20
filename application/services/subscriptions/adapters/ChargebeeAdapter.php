<?php

use Application_Service_Subscription_Adapter_AdapterInterface as AdapterInterface;
use Application_Service_Subsription_Exception_ParsingErrorException as ParsingErrorException;
use Application_Service_Subsription_Exception_ServerErrorException as ServerErrorException;
use Application_Service_Subsription_Exception_PermissionDeniedException as PermissionDeniedException;
use Application_Service_Subsription_Exception_BadRequestException as BadRequestException;
use Application_Service_Exception_NotFoundException as NotFoundException;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_DTO_Subscription as Subscription;
use Application_Model_License as License;
use Zend_Http_Client as HttpClient;
use Zend_Json as Json;

class Application_Service_Subscription_Adapter_ChargebeeAdapter implements AdapterInterface
{
    const API_VERSION = 'v2';

    /** @var Zend_Http_Client */
    protected $httpClient;

    /** @var string */
    protected $url;

    /** @var string */
    protected $apiKey;

    protected $statusesMatrix = [
        License::STATUS_INACTIVE => 'archived',
        License::STATUS_ACTIVATED => 'active',
    ];

    protected $periodUnitsMatrix = [
        License::PERIOD_YEAR => 'year',
        License::PERIOD_MONTH => 'month',
        License::PERIOD_WEEK => 'week',
        License::PERIOD_DAY => 'day',
    ];

    /**
     * @inheritdoc
     */
    public function __construct(
        Zend_Http_Client $httpClient,
        Zend_Config $config
    ) {
        $this->httpClient = $httpClient;
        $this->url = $config->get('url');
        $this->apiKey = $config->get('api_key');
    }

    /**
     * @inheritdoc
     */
    public function getPlan($id)
    {
        return $this->composeSubscriptionPlanDTO($this->request('plans/' . $id));
    }

    /**
     * @inheritdoc
     */
    public function getPlansList()
    {
        $data = $this->request('plans');
        if (empty($data['list'])) {
            return [];
        }
        return array_filter(array_map(function($subscription){
            return  $this->composeSubscriptionPlanDTO($subscription);
        }, $data['list']));
    }

    /**
     * @inheritdoc
     */
    public function synchronizePlansList(array $plans)
    {
        $cbPlans = $this->getPlansList();
        foreach ($plans as $plan) {
            foreach ($cbPlans as $cbPlan) {
                if ($cbPlan->getExternalId() === $plan->getExternalId()) {
                    if ($cbPlan->toArray() !== $plan->toArray()) {
                        $this->synchronizePlan($plan, $cbPlan); //update
                    }
                    continue(2);
                }
            }
            $this->synchronizePlan($plan); // add
        }
        foreach ($cbPlans as $cbPlan) {
            if ($cbPlan->getStatus() !== License::STATUS_ACTIVATED) {
                continue;
            }
            foreach ($plans as $plan) {
                if ($cbPlan->getExternalId() === $plan->getExternalId()) {
                    continue(2);
                }
            }
            $this->deactivatePlan($cbPlan); // remove
        }
    }

    /**
     * @inheritdoc
     */
    public function synchronizePlan(SubscriptionPlan $newPlan, SubscriptionPlan $oldPlan = null)
    {
        if (!$oldPlan) {
            $this->createPlan($newPlan);
            return;
        }
        $this->updatePlan($newPlan);
        if ($newPlan->getStatus() !== $oldPlan->getStatus()) {
            ($newPlan->getStatus() === License::STATUS_ACTIVATED)
                ? $this->activatePlan($newPlan)
                : $this->deactivatePlan($newPlan);
        }
    }

    /**
     * @inheritdoc
     */
    public function create(Subscription $subscription)
    {
        
        $this->request(
            'subscriptions',
            [
                'plan_id' => $subscription->getPlan()->getExternalId(),
                'customer' => [
                    'id' => 'kr_dev_' . $subscription->getCustomer()->getId(),
                    'email' => $subscription->getCustomer()->getEmail(),
                    'phone' => $subscription->getCustomer()->getPhone(),
                    'first_name' => $subscription->getCustomer()->getFirstName(),
                    'last_name' => $subscription->getCustomer()->getLastName(),
                ],
            ],
            HttpClient::POST
        );
    }

    /**
     * @param SubscriptionPlan $plan
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    protected function createPlan(SubscriptionPlan $plan)
    {
        if ($plan->getStatus() !== License::STATUS_ACTIVATED) {
            return;
        }
        $this->request(
            'plans',
            [
                'id' => $plan->getExternalId(),
            ] + $this->parseSubscriptionPlanDTO($plan),
            HttpClient::POST
        );
    }


    /**
     * @param SubscriptionPlan $plan
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    protected function updatePlan(SubscriptionPlan $plan)
    {
        $this->request(
            'plans/' . $plan->getExternalId(),
            $this->parseSubscriptionPlanDTO($plan),
            HttpClient::POST
        );
    }


    /**
     * @param SubscriptionPlan $plan
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    protected function deactivatePlan(SubscriptionPlan $plan)
    {
        $this->request(
            'plans/' . $plan->getExternalId() . '/delete',
            [],
            HttpClient::POST
        );
    }


    /**
     * @param SubscriptionPlan $plan
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    protected function activatePlan(SubscriptionPlan $plan)
    {
        $this->request(
            'plans/' . $plan->getExternalId() . '/unarchive',
            [],
            HttpClient::POST
        );
    }

    /**
     * @param string $url
     * @param array [$parameters]
     * @param string [$method]
     * @return array | null
     * @throws ParsingErrorException
     * @throws PermissionDeniedException
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    protected function request($url, $parameters = [], $method = HttpClient::GET)
    {

        try {
            $this->httpClient
                ->resetParameters()
                ->setUri($this->url . 'api/' . self::API_VERSION . '/' . $url)
                ->setAuth($this->apiKey)
                ->setMethod($method)
                ->setEncType(HttpClient::ENC_URLENCODED);
            $method === HttpClient::GET
                ? $this->httpClient->setParameterGet($parameters)
                : $this->httpClient->setParameterPost($parameters);
            $response = $this->httpClient->request();
        } catch (Exception $e) {
            throw new ServerErrorException($e->getMessage(), $e->getCode());
        }
        if ($response->isError()) {

            $message = $response->getMessage();
            try {
                $parsedResponse = $this->parseResponse($response->getBody());
                if (!empty($parsedResponse['message'])) {
                    $message = ' Chargebee error / ' . $parsedResponse['message'];
                }
            } catch (Zend_Json_Exception $e) { }
            if (in_array($response->getStatus(), [404])) {
                throw new NotFoundException($message);
            }
            if (in_array($response->getStatus(), [401, 403])) {
                throw new PermissionDeniedException($message);
            }
            if (in_array($response->getStatus(), [400])) {
                throw new BadRequestException($message);
            }
            throw new ServerErrorException($message);
        }
        try {
            return $this->parseResponse($response->getBody());
        } catch (Zend_Json_Exception $e) {
            throw new ParsingErrorException($e->getMessage());
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws Zend_Json_Exception
     */
    protected function parseResponse($data)
    {
        return Json::decode($data, Json::TYPE_ARRAY);
    }

    /**
     * @param array $data
     * @return SubscriptionPlan
     * @throws ParsingErrorException
     */
    protected function composeSubscriptionPlanDTO(array $data)
    {
        if (empty($data['plan'])) {
            throw new ParsingErrorException();
        }
        $planInfo = $data['plan'];
        return new SubscriptionPlan(
            $planInfo['name'] ?: '',
            $planInfo['description'] ?: '',
            $planInfo['period'] ? (int)$planInfo['period'] : null,
            $planInfo['period_unit'] ? array_search($planInfo['period_unit'], $this->periodUnitsMatrix) : null,
            $planInfo['trial_period'] ? (int)$planInfo['trial_period'] : null,
            $planInfo['trial_period_unit'] ? array_search($planInfo['trial_period_unit'], $this->periodUnitsMatrix) : null,
            $planInfo['price'] ? (int)$planInfo['price'] : 0,
            $planInfo['currency_code'] ?: '',
            $planInfo['status'] ? array_search($planInfo['status'], $this->statusesMatrix) : License::STATUS_INACTIVE,
            $planInfo['is_trial'] ?: '',
            $planInfo['id'] ?: ''
        );
    }

    /**
     * @param SubscriptionPlan $plan
     * @return array
     */
    protected function parseSubscriptionPlanDTO(SubscriptionPlan $plan)
    {
        return array_filter([
            'name' => $plan->getName(),
            'description' => $plan->getDescription(),
            'period' => $plan->getPeriod(),
            'period_unit' => isset($this->periodUnitsMatrix[$plan->getPeriodUnit()]) ? $this->periodUnitsMatrix[$plan->getPeriodUnit()] : null,
            'trial_period' => $plan->getTrialPeriod(),
            'trial_period_unit' => isset($this->periodUnitsMatrix[$plan->getTrialPeriodUnit()]) ? $this->periodUnitsMatrix[$plan->getTrialPeriodUnit()] : null,
            'price' => $plan->getPrice(),
            'currency_code' => $plan->getCurrency(),
        ]);
    }
}
