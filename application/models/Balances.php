<?php

class Application_Model_Balances extends Muzyka_DataModel
{
    protected $_name = 'balances';

    const REFERENCE_TYPE_PAYMENT = 'payment';
    const REFERENCE_TYPE_SPEND   = 'spend';

    /**
     * @param $currencyId
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    public function getLastRowByCurrencyId($currencyId) {
        return $this->getAdapter()
            ->select()
            ->from('balances')
            ->where("currency_id = ?", $currencyId)
            ->order('id DESC')
            ->query()
            ->fetch();
    }

    /**
     * @param $currencyId
     * @return int
     * @throws Zend_Db_Statement_Exception
     */
    public function getBalance($currencyId) {
        $lastRow = $this->getLastRowByCurrencyId($currencyId);
        return !empty($lastRow['balance']) ? $lastRow['balance'] : 0;
    }

    /**
     * @param $debit
     * @param $credit
     * @param $currencyId
     * @param null $referenceType
     * @param null $referenceId
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    public function create($debit, $credit, $currencyId, $referenceType = null, $referenceId = null) {
        $time = date('Y-m-d H:i:s');
        return $this->insert([
            'currency_id'    => $currencyId,
            'debit'          => $debit,
            'credit'         => $credit,
            'balance'        => $this->getBalance($currencyId) + ($credit - $debit),
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'created_at'     => $time,
            'updated_at'     => $time,
        ]);
    }

}
