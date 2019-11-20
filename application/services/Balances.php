<?php

class Application_Service_Balances
{
    /** @var self */
    protected static $_instance = null;
    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    const DEFAULT_BALANCE_CURRENCY = 'PLN';

    /** @var Application_Model_Currencies */
    protected $currenciesModel;

    /** @var Application_Model_Balances */
    protected $balancesModel;

    /**
     * Application_Service_Balances constructor.
     * @throws Exception
     */
    private function __construct()
    {
        self::$_instance = $this;
        $this->currenciesModel = Application_Service_Utilities::getModel('Currencies');
        $this->balancesModel   = Application_Service_Utilities::getModel('Balances');
    }


    /**
     * Gets current balance
     *
     * @return float
     * @throws Exception
     */
    public function getBalance() {
        $currencyId = $this->currenciesModel->getIdByCode(self::DEFAULT_BALANCE_CURRENCY);
        return $this->balancesModel->getBalance($currencyId);
    }

    /**
     * Deposits balance
     *
     * @param $amount
     * @param $currencyId
     * @param $paymentId
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function deposit($amount, $currencyId, $paymentId) {
        return (bool)$this->balancesModel->create(0, $amount, $currencyId, Application_Model_Balances::REFERENCE_TYPE_PAYMENT, $paymentId);
    }

    /**
     * Withdraws balance
     *
     * @param $amount
     * @param $currencyId
     * @param $spendId
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function withdraw($amount, $currencyId, $spendId) {
        return (bool)$this->balancesModel->create($amount, 0, $currencyId, Application_Model_Balances::REFERENCE_TYPE_SPEND, $spendId);
    }

}
