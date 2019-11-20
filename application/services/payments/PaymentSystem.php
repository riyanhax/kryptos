<?php

abstract class PaymentSystem
{
    protected $config = null;

    protected $amount =  null;

    protected $currencyCode = null;

    protected $description = null;

    public function __construct()
    {
        $this->config = $this->getConfig();
    }

    public function getConfig() {
        if (empty($this->config)) {
            $configsPath = Zend_Registry::getInstance()->get('config')->smarty->config_dir;
            $this->config = new Zend_Config_Ini($configsPath . '/payment.ini');
        }

        return $this->config;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param $paymentSystemName
     * @return static
     */
    public static function factory($paymentSystemName) {
        $className = ucfirst($paymentSystemName);
        require_once $className . '.php';
        return new $className();
    }

    /**
     * @param array $queryParams
     * @return mixed
     */
    abstract public function getRedirectUrl(array $queryParams = []);

    /**
     * @param array $credentials
     * @return bool
     */
    abstract public function pay(array $credentials);

}