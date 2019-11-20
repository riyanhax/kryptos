<?php

use Application_Service_Subscription_Adapter_AdapterInterface as AdapterInterface;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;
use Application_Service_Subscription_DTO_Subscription as Subscription;

class Application_Service_Subscription_Adapter_DummyAdapter implements AdapterInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(Zend_Http_Client $httpClient, Zend_Config $config)
    {
    }

    /**
     * @inheritdoc
     */
    public function getPlan($id)
    {
    }

    /**
     * @inheritdoc
     */
    public function getPlansList()
    {
    }


    /**
     * @inheritdoc
     */
    public function synchronizePlansList(array $subscriptionPlans)
    {
    }

    /**
     * @inheritdoc
     */
    public function synchronizePlan(SubscriptionPlan $newPlan, SubscriptionPlan $oldPlan = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function create(Subscription $subscription)
    {
    }
}
