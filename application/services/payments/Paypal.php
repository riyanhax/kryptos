<?php

class Paypal extends PaymentSystem
{
    /** @var \PayPal\Rest\ApiContext  */
    protected $apiContext;

    /**
     * Paypal constructor.
     * @throws Zend_Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->apiContext = $this->initApiContext();
    }

    /**
     * @return \PayPal\Rest\ApiContext
     * @throws Zend_Exception
     */
    protected function initApiContext() {
        $libraryPath = Zend_Registry::getInstance()->get('config')->production->includePaths->library;
        require $libraryPath  . '/PayPal-PHP-SDK/autoload.php';
        $clientId     = "AdtWFOew5mP98XIdvZ_PQw19Af_Qg3vxfyr40dxhQFn-yV_pEYTeUebSBk6pIrZy_YinIbZm9bruDrID";
        $clientSecret = "EApxJZ1EgFQastQvCBcx-LfpJWdlQ6H9fYi6WrhcIiGcyfMqm77JyOLJxu4Bo9G13IG3qqqDAB7cyTRb";

        $apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($clientId, $clientSecret));
        $apiContext->setConfig(
            [
                'mode'           => $this->config->paypal->mode,
                'log.LogEnabled' => $this->config->paypal->log->LogEnabled == 1,
                'log.FileName'   => $this->config->paypal->log->FileName,
                'log.LogLevel'   => $this->config->paypal->log->LogLevel,
            ]
        );

        return $apiContext;
    }

    /**
     * @param array $queryParams
     * @return null|string
     * @throws Exception
     */
    public function getRedirectUrl(array $queryParams = [])
    {
        try {
            $payment = $this->createPayment($queryParams);
            return $payment->getApprovalLink();
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
    echo $ex->getCode(); // Prints the Error Code
    echo $ex->getData(); // Prints the detailed error message 
    die($ex);
}
    }

    /**
     * @param array $params
     * @return \PayPal\Api\Payment
     * @throws Exception
     */
    public function pay(array $params)
    {
        try {
            return $this->executePayment($params['payment_id'], $params['payer_id']);
        } catch (Exception $e) {
            throw new Exception('Error while executing payment using Paypal.');
        }
    }

    /**
     * @param array $queryParams
     * @return \PayPal\Api\Payment
     */
    protected function createPayment(array $queryParams) {
        $paymentData = Application_Service_Utilities::getModel('Payments')->getByHash($queryParams['hash']);

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal($this->amount)
            ->setCurrency($this->currencyCode);

        $item = new \PayPal\Api\Item();
        $item->setName($paymentData['description'])
            ->setCurrency(Application_Service_Utilities::getModel('Currencies')->getCodeById($paymentData['currency_id']))
            ->setQuantity(1)
            ->setPrice($paymentData['amount']);

        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems([$item]);

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setInvoiceNumber(uniqid())
            ->setDescription($this->description);

        $redirectUrls = new \PayPal\Api\RedirectUrls();
        //$redirectUrls->setReturnUrl(Zend_Registry::getInstance()->get('config')->production->url . '/payments/paypal-callback?' . http_build_query($queryParams));
        //$redirectUrls->setCancelUrl(Zend_Registry::getInstance()->get('config')->production->url . '/payments/paypal-cancel?' . http_build_query($queryParams));
        /* Vipin code starts */
        $uri = Zend_Controller_Front::getInstance()->getRequest();
        $redirectUrls->setReturnUrl($uri->getScheme().'://'.$uri->getHttpHost() . '/payments/paypal-callback?' . http_build_query($queryParams));
        $redirectUrls->setCancelUrl($uri->getScheme().'://'.$uri->getHttpHost() . '/payments/paypal-cancel?' . http_build_query($queryParams));
        /* Vipin code end */        

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        return $payment->create($this->apiContext);
    }


    /**
     * @param $paymentId
     * @param $payerId
     * @return \PayPal\Api\Payment
     */
    protected function executePayment($paymentId, $payerId)
    {
        $payment = \PayPal\Api\Payment::get($paymentId, $this->apiContext);

//        $details = new \PayPal\Api\Details();
//        $details->setShipping(0.2)->setTax(0.3)->setSubtotal(0.50);

//        $amount = new \PayPal\Api\Amount();
//        $amount->setCurrency('USD');
//        $amount->setTotal(10);
//        $amount->setDetails($details);

//        $transaction = new \PayPal\Api\Transaction();
//        $transaction->setAmount($amount);

        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payerId);
//        $execution->addTransaction($transaction);
        return $payment->execute($execution, $this->apiContext);
    }
}
