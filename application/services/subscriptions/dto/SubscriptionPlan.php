<?php

class Application_Service_Subscription_DTO_SubscriptionPlan
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var int */
    protected $period;

    /** @var int */
    protected $period_unit;

    /** @var int */
    protected $trial_period;

    /** @var int */
    protected $trial_period_unit;

    /** @var int */
    protected $price;

    /** @var string */
    protected $currency;

    /** @var int */
    protected $status;

    /* Vipin code starts */
    /** @var int */
    protected $is_trial;
    /* Vipin code end */

    /** @var string */
    protected $external_id;

    /**
     * @param string $name
     * @param string $description
     * @param int $period
     * @param int $period_unit
     * @param int $trial_period
     * @param int $trial_period_unit
     * @param int $price
     * @param string $currency
     * @param int $status
     * @param string $external_id
     */
    public function __construct(
        $name,
        $description,
        $period,
        $period_unit,
        $trial_period,
        $trial_period_unit,
        $price,
        $currency,
        $status,
        /* Vipin code starts */
        $is_trial,
        /* Vipin code end */
        $external_id
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->period = $period;
        $this->period_unit = $period_unit;
        $this->trial_period = $trial_period;
        $this->trial_period_unit = $trial_period_unit;
        $this->price = $price;
        $this->currency = $currency;
        $this->status = $status;
         /* Vipin code starts */
        $this->is_trial = $is_trial;
        /* Vipin code end */
        $this->external_id = $external_id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return int
     */
    public function getPeriodUnit()
    {
        return $this->period_unit;
    }

    /**
     * @return int
     */
    public function getTrialPeriod()
    {
        return $this->trial_period;
    }

    /**
     * @return int
     */
    public function getTrialPeriodUnit()
    {
        return $this->trial_period_unit;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /* Vipin code starts */
    /**
     * @return int
     */
    public function getIsTrial()
    {
        return $this->is_trial;
    }
    /* Vipin code end */

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'period' => $this->period,
            'period_unit' => $this->period_unit,
            'trial_period' => $this->trial_period,
            'trial_period_unit' => $this->trial_period_unit,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status,
            /* Vipin code starts */
            'is_trial' => $this->is_trial,
            /* Vipin code end */
            'external_id' => $this->external_id,
        ];
    }
}
