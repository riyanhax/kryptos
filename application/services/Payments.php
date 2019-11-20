<?php

require_once 'payments/PaymentSystem.php';

class Application_Service_Payments
{
    const PAYMENT_METHOD_PAYPAL      = 'paypal';
    const PAYMENT_METHOD_DOTPAY      = 'dotpay';
    const PAYMENT_METHOD_PLATNOSCI24 = 'platnosci24';

    /** @var self */
    protected static $_instance = null;

    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    /** @var Application_Model_Payments */
    protected $paymentsModel;

    /** @var Application_Service_Balances */
    protected $balancesService;

    public static $paymentMethods = [
        self::PAYMENT_METHOD_PAYPAL,
        self::PAYMENT_METHOD_DOTPAY,
        self::PAYMENT_METHOD_PLATNOSCI24,
    ];

    /**
     * Application_Service_Payments constructor.
     * @throws Exception
     * @throws Zend_Exception
     */
    private function __construct()
    {
        self::$_instance = $this;
        $this->paymentsModel = Application_Service_Utilities::getModel('Payments');
        $this->balancesService = Application_Service_Balances::getInstance();
    }

    /**
     * @param $paymentMethod
     * @param $amount
     * @param $currencyCode
     * @param array $data
     * @return mixed
     * @throws Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function getRedirectUrl($paymentMethod, $amount, $currencyCode, array $data) {
        /** @var Application_Model_Currencies $currenciesModel */
        $currenciesModel = Application_Service_Utilities::getModel('Currencies');
        $paymentHash = $this->generatePaymentHash();

        $time = date('Y-m-d H:i:s');
        $paymentId = $this->storeBasicPaymentData([
            'payment_method'  => $paymentMethod,
            'amount'          => $amount,
            'currency_id'     => $currenciesModel->getIdByCode($currencyCode),
            'hash'            => $paymentHash,
            'description'     => $data['description'],
            'payment_purpose' => $data['payment_purpose'],
            'purpose_data'    => json_encode($data['purpose_data']),
            'created_at'      => $time,
            'updated_at'      => $time,
        ]);

        $paymentSystem = PaymentSystem::factory($paymentMethod);
        $paymentSystem->setAmount($amount)->setCurrencyCode($currencyCode);
        $paymentSystem->setDescription($data['description']);

        return $paymentSystem->getRedirectUrl([
            'hash' => $paymentHash,
        ]);
    }

    /**
     * @param $paymentHash
     * @param array $paymentParams
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function pay($paymentHash, array $paymentParams) {
        $payment = $this->paymentsModel->getByHash($paymentHash);
        $paymentSystem = PaymentSystem::factory($payment['payment_method']);
        $response = $paymentSystem->pay($paymentParams);

        $results = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($response instanceof \PayPal\Api\Payment) {
            if ($response->getState() == 'approved') {
                $status = Application_Model_Payments::PAYMENT_STATUS_PAID;
                $this->balancesService->deposit($payment['amount'], $payment['currency_id'], $payment['id']);
                // @todo refactoring
                $licenseService = Application_Service_Licenses::getInstance();

                $orderedLicense = json_decode($payment['purpose_data'], true);
                $licenseSubscriptionService = Application_Service_LicenseSubscriptions::getInstance();
                $license = $licenseSubscriptionService->getActive(null, Application_Model_LicenseSubscription::STATUS_ACTIVATED);

                $license->expert_count = $license->expert_count + (int) $orderedLicense['admin_user'];
                $license->pro_count = $license->pro_count + (int) $orderedLicense['standard_user'];
                $license->mini_count = $license->mini_count + (int) $orderedLicense['mini_user'];
                $license->save();

                /*if ($payment['payment_purpose'] == Application_Model_Payments::PAYMENT_PURPOSE_BUY) {
                    $licenseService->createLicense([
                        'url'        => $orderedLicense['url'],
                        'version'    => $orderedLicense['version'],
                        'mini_user' => $orderedLicense['mini_user'],
			'user_limit' => $orderedLicense['mini_user']+$orderedLicense['standard_user'],
                        'standard_user' => $orderedLicense['standard_user'],
                        'months'     => $orderedLicense['months'],
                        'status'     => Application_Model_License::STATUS_ACTIVATED,
                        'start_date' => date('Y-m-d H:i:s'),
                        'end_date'   => date('Y-m-d H:i:s', strtotime("+{$orderedLicense['months']} months")),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
			'approved'   => 1,
    		    ]);
                } elseif ($payment['payment_purpose'] == Application_Model_Payments::PAYMENT_PURPOSE_INCREASE_USER_LIMIT) {
                    $licenseService->increaseUserLimit($orderedLicense['user_limit']);
                } elseif ($payment['payment_purpose'] == Application_Model_Payments::PAYMENT_PURPOSE_UPGRADE_VERSION) {
                    $licenseService->upgradeVersion($orderedLicense['version']);
                }*/
            } else {
                $status = Application_Model_Payments::PAYMENT_STATUS_UNKNOWN;
            }
            $results = array_merge([
                'external_payment_id' => $response->getId(),
                'details'             => $response->toJSON(),
            ], $results);
        } else {
            $status = Application_Model_Payments::PAYMENT_STATUS_FAILED;
        }

        $results['status'] = $status;

        $this->updatePaymentResults($payment['id'], $results);

        return $status == Application_Model_Payments::PAYMENT_STATUS_PAID;
    }

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function isAcceptedPaymentMethod($paymentMethod) {
        return in_array($paymentMethod, self::$paymentMethods);
    }

    /**
     * @param array $data
     * @return mixed The primary key of the row inserted.
     */
    protected function storeBasicPaymentData(array $data) {
        return $this->paymentsModel->insert($data);
    }

    /**
     * @param $paymentId
     * @param array $results
     */
    protected function updatePaymentResults($paymentId, array $results) {
        $this->paymentsModel->edit($paymentId, $results);
    }

    /**
     * @return string
     * @throws Zend_Db_Statement_Exception
     */
    protected function generatePaymentHash() {
        $hash = bin2hex(openssl_random_pseudo_bytes(16));
        if ($this->paymentsModel->getByHash($hash)) {
            return $this->generatePaymentHash();
        }
        return $hash;
    }

}
