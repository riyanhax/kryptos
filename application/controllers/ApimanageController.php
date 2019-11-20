<?php
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class ApimanageController extends Muzyka_Admin {

    protected $apiModel;

    public function init()
    {
       parent::init();

        $this->apiModel = Application_Service_Utilities::getModel('ApiKeys');
    }

    public function indexAction(){
	$api = $this->apiModel->getAllByName();
	$this->view->api = $api;
    }

    public function saveAction(){
	$req = $this->getRequest();
	
	try{
	    $data['sms_value'] = $req->getParam('smsapi_value');
	    $data['sms_from'] = $req->getParam('smsapi_additional');
	    $data['mail_value'] = $req->getParam('email_value');
	    $data['mail_from'] = $req->getParam('email_additional');
	    $result = $this->testsend($data);
	    if($result){ 
	    	$this->apiModel->update($data);
	    }
	}
	catch(Exception $e)
	{
	    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wystapil blad podczas przetwarzania.<br />' . implode('<br />', $this->log) . '<br />' . $e->getMessage(), 'danger'));
	}
	$this->redirect('/apimanage');
    }

    public function testsend($data)
    {
	$email = new \SendGrid\Mail\Mail(); 
	$email->setFrom($data['mail_from'], "Admin");
	$email->setSubject("confirmation");
	$email->addTos($data['mail_from']);
	$email->addContent("text/plain", 'If you recieve this mail, it means that your mail has been setup successfully.');
	$email->addContent("text/html", "<strong>If you recieve this mail, it means that your mail has been setup successfully.</strong>");
	$sendgrid = new \SendGrid($data['mail_value']);
	try {
	    $response = $sendgrid->send($email);
	    print $response->statusCode() . "\n";
	    print_r($response->headers());
	    print $response->body() . "\n";
	} catch (Exception $e) {
	    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wystapil blad podczas przetwarzania.<br />' . implode('<br />', $this->log) . '<br />' . $e->getMessage(), 'danger'));
	    return 0;
	}

	$params = array(
	    'access_token'  => $data['sms_value'],
	    'to'            => +919785422410,
	    'from'          => $data['sms_from'],
	    'message'       => "Your api has been successfully activated",
		);
	
		if ($params['access_token']&&$params['to']&&$params['message']&&$params['from']) {
		    $date = '?'.http_build_query($params);
		    $file = fopen('https://api.smsapi.com/sms.do'.$date,'r');
		    $result = fread($file,1024);
		    fclose($file);
		    if($result){
				echo 'Send Message Successfully Here is Sender Id : ';	
				echo $result;
			}else{
				echo 'Send Fail';
				return 0;
			}
		}
	return 1;
    }
}
