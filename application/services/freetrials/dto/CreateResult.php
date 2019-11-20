<?php

class Application_Service_FreeTrials_DTO_CreateResult
{
    /** @var int */
    protected $trialId;

    /** @var string */
    protected $confirmationCode;

    /**
     * @param int $trialId
     * @param string $confirmationCode
     */
    public function __construct($trialId, $confirmationCode)
    {
        $this->trialId = $trialId;
        $this->confirmationCode = $confirmationCode;
    }

    /**
     * @return int
     */
    public function getTrialId()
    {
        return $this->trialId;
    }

    /**
     * @return string
     */
    public function getConfirmationCode()
    {
        return $this->confirmationCode;
    }
}
