<?php

use Application_Service_Subscription_DTO_Customer as Customer;
use Application_Service_Subscription_DTO_SubscriptionPlan as SubscriptionPlan;

class Application_Service_Subscription_DTO_Subscription
{
    /** @var SubscriptionPlan */
    protected $plan;

    /** @var Customer */
    protected $customer;

    /**
     * @param SubscriptionPlan $plan
     * @param Customer $customer
     */
    public function __construct(SubscriptionPlan $plan, Customer $customer)
    {
        $this->plan = $plan;
        $this->customer = $customer;
    }

    /**
     * @return SubscriptionPlan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
