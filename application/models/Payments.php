<?php

class Application_Model_Payments extends Muzyka_DataModel
{
    protected $_name = 'payments';

    const PAYMENT_METHOD_PAYPAL      = 'paypal';
    const PAYMENT_METHOD_DOTPAY      = 'dotpay';
    const PAYMENT_METHOD_PLATNOSCI24 = 'platnosci24';

    const PAYMENT_STATUS_PAID    = 'paid';
    const PAYMENT_STATUS_FAILED  = 'failed';
    const PAYMENT_STATUS_UNKNOWN = 'unknown';

    const PAYMENT_PURPOSE_BUY                 = 'buy';
    const PAYMENT_PURPOSE_DEPOSIT_BALANCE     = 'deposit_balance';
    const PAYMENT_PURPOSE_INCREASE_USER_LIMIT = 'increase_user_limit';
    const PAYMENT_PURPOSE_UPGRADE_VERSION     = 'upgrade_version';

    const PAYMENT_APPROVED_YES = 1;
    const PAYMENT_APPROVED_NO  = 0;

    public static $paymentMethods = [
        self::PAYMENT_METHOD_PAYPAL,
        self::PAYMENT_METHOD_DOTPAY,
        self::PAYMENT_METHOD_PLATNOSCI24,
    ];

    public function getList($conditions = array(), $limit = null, $order = null)
    {
        $select = $this->_db->select()
            ->from(array('p' => $this->_name), array('*'))
            ->joinInner(array('cu' => 'currencies'), 'p.currency_id = cu.id', array('currency_code' => 'cu.code'));

        if ($order) {
            $select->order($order);
        } else {
            $select->order('id DESC');
        }

        if ($limit) {
            $select->limit($limit);
        }

        $this->addConditions($select, $conditions);

        return $this->getListFromSelect($select, $conditions);;
    }

    /**
     * @param $hash
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    public function getByHash($hash)
    {
        return $this->select()->where("hash=?", $hash)->query()->fetch();
    }
}
