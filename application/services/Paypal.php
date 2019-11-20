<?php
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class Application_Service_Paypal
{
    /** @var self */
    protected static $_instance = null;

    public static function getInstance() { return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance; }

    public function process()
    {
	$apiContext = $this->getContext();

	$payer = new \PayPal\Api\Payer();
	$payer->setPaymentMethod('paypal');

	$amount = new \PayPal\Api\Amount();
	$amount->setTotal('100000.00');
	$amount->setCurrency('INR');

	$transaction = new \PayPal\Api\Transaction();
	$transaction->setAmount($amount);

	$redirectUrls = new \PayPal\Api\RedirectUrls();
	$redirectUrls->setReturnUrl("http://dev-kryptos24.pl:8080/payments/success")
	    ->setCancelUrl("http://dev-kryptos24.pl:8080/payments/failed");

	$payment = new \PayPal\Api\Payment();
	$payment->setIntent('sale')
	    ->setPayer($payer)
	    ->setTransactions(array($transaction))
	    ->setRedirectUrls($redirectUrls);


	try {
	    $payment->create($apiContext);
	  //  echo $payment;

	    return $payment->getApprovalLink();
	}
	catch (\PayPal\Exception\PayPalConnectionException $ex) {
	    echo $ex->getData();
	}
    }

    public function getContext()
    {
	$client_id = 'AdtWFOew5mP98XIdvZ_PQw19Af_Qg3vxfyr40dxhQFn-yV_pEYTeUebSBk6pIrZy_YinIbZm9bruDrID';
	$client_secret = 'EApxJZ1EgFQastQvCBcx-LfpJWdlQ6H9fYi6WrhcIiGcyfMqm77JyOLJxu4Bo9G13IG3qqqDAB7cyTRb';
	$apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential($client_id, $client_secret)
	);
	return $apiContext;
    }

    
}
?>
