<?php

class Application_Service_FreeTrials_DTO_ConfirmResult
{
    /** @var int */
    protected $trialId;

    /** @var string */
    protected $login;

    /** @var string */
    protected $password;

    /**
     * @param int $trialId
     * @param string $login
     * @param string $password
     */
    public function __construct($trialId, $login, $password)
    {
        $this->trialId = $trialId;
        $this->login = $login;
        $this->password = $password;
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
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
