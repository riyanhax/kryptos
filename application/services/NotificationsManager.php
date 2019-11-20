<?php
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class Application_Service_NotificationsManager
{
    /** @var self */
    protected static $_instance = null;

    const TYPE_TASK = 1;
    const TYPE_TICKET = 2;

    public static function getInstance() { return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance; }

    public function process($data)
    {
	$apis = Application_Service_Utilities::getModel('NotificationsControl');
	$sms = $apis->getAllByType('smsapi');
	$mail = $apis->getAllByType('email');
	$user_id = Application_Service_Authorization::getInstance()->getUserId();
	$conf = $apis->getAllById($user_id);
	if($data['type'] == Application_Service_NotificationsManager::TYPE_TASK)
	{
	    if($conf['task_email']){
	    	$this->sendEmail($data, $mail);
	    }
	    if($conf['task_sms'])
	    {
	    	$this->sendSMS($data, $sms);
	    }
	}else if($data['type'] == Application_Service_NotificationsManager::TYPE_TICKET)
	{
	    if($conf['tickets_email']){
	    	$this->sendEmail($data, $mail);
	    }
	    if($conf['tickets_sms'])
	    {
	    	$this->sendSMS($data, $sms);
	    }
	}else if($data['type'] == Application_Service_NotificationsManager::TYPE_ACTIVITY)
	{
	    if($conf['activity_email']){
	    	$this->sendEmail($data, $mail);
	    }
	    if($conf['activity_sms'])
	    {
	    	$this->sendSMS($data, $sms);
	    }
	}
	return true;
    }

//To send the email.
    public function sendEmail(Array $data, $api)
    {
	$osoby = Application_Service_Utilities::getModel('Osoby');
	$attendeesArray = explode(';',$data['attendees']);

	$attendeesID = array(); 
	foreach($attendeesArray as $attend)
	{
	    $attend = trim($attend," ");
	    $attendeesID[] = $attend;
	}

	$users = $osoby->getAllUserByLogin($attendeesID);

	$attendeesEmail = array(); 
	foreach($users as $user){
	    if($user['notification_email'] != NULL){
		$attendeesEmail[] = array($user['notification_email'] => $user['imie']); }
	}
	$email = new \SendGrid\Mail\Mail(); 
	$email->setFrom($api['additional'], "Shadab Arif");
	$email->setSubject($data['title']);
	$email->addTos($attendeesEmail);
	$email->addContent("text/plain", $data["text"]);
	$email->addContent("text/html", "<strong>".$data["text"]."</strong>");
	$sendgrid = new \SendGrid($api['Value']);
	try {
	    $response = $sendgrid->send($email);
	    print $response->statusCode() . "\n";
	    print_r($response->headers());
	    print $response->body() . "\n";
	} catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
    }

    public function sendSMS(Array $data, $api)
    {
	$osoby = Application_Service_Utilities::getModel('Osoby');
	$attendeesArray = explode(';',$data['attendees']);

	$attendeesID = array(); 
	foreach($attendeesArray as $attend)
	{
	    $attend = trim($attend," ");
	    $attendeesID[] = $attend;
	}

	$users = $osoby->getAllUserByLogin($attendeesID);
	$i = 0;
	$numbers = "";
	foreach($users as $user){
	    if($user['telefon_komorkowy'] != NULL){
		if($i == 0){
		    $numbers = $user['telefon_komorkowy'];
		    $i++;
		}else{
		    $numbers = $numbers . ", " . $user['telefon_komorkowy']; 
		}
	    }
	}

	$params = array(
	    'access_token'  => $api['Value'],          //sms api access token
	    'to'            => $numbers,         	  								//destination number  
	    'from'          => $api['additional'],                								//sender name has to be active  
	    'message'       => $data["text"],    			  								//message content
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
			}
		}
/*
        $temp1 = "Thank you for contacting Kryptos here is your tracking ID : #".$data['tid'].", You can check status of your ticket at http://localhost/webform/trackstatus/tid/"; // new ticket

        $temp2 = "Your ticket has been updated. Here is your tracking ID : #".$data['tid'].", You can check status of your ticket at http://localhost/webform/trackstatus/tid/".$data['tid']; //update ticket
        $temp3 = "Thank you ! Your ticket has been closed. Here is your tracking ID : #".$data['tid'].", You can check status of your ticket at http://localhost/webform/trackstatus/tid/".$data['tid'];  //closed ticket

        switch ($data['type']) {
        	case 1:
        		$msg = $temp1;
        		break;

    		case 2:
	    		$msg = $temp2;
	    		break;

    		case 3:
	    		$msg = $temp3;
	    		break;

        	default:
        		$msg = "Error! Messgae type not found";
        		break;
        }

		$mobile = $data['mobile'];

		$params = array(
	    'access_token'  => 'ZUxxqXctw7A84H2PSYQShLCB3zmANki5wwZHeLyB',          //sms api access token
	    'to'            => $mobile,         	  								//destination number  
	    'from'          => 'Info',                								//sender name has to be active  
	    'message'       => $msg,    			  								//message content
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
			}
		}
	return true;
*/
    }


    public function update($user_id)
    {
	$osoby = Application_Service_Utilities::getModel('Osoby');
	$googleModel = Application_Service_Utilities::getModel('GoogleEvents');
	$dataArray = $googleModel->getAllById($user_id);
	
	foreach($dataArray as $data){

	    $attendeesArray = explode(';',$data['attendees']);

	    $attendeesID = array(); 
	    foreach($attendeesArray as $attend)
	    {
		$attend = trim($attend," ");
		$attendeesID[] = $attend;
	    }


	    $users = $osoby->getAllUserByLogin($attendeesID);

	    $attendeesEmail = array(); 
	    foreach($users as $user){
	    if($user['notification_email'] != NULL){
		$attendeesEmail[] = array('email' => $user['notification_email']); }
	    }
	// Get the API client and construct the service object.
	    $client = new Google_Client();
	    $client->setApplicationName('Calendar');
	    $client->setScopes(Google_Service_Calendar::CALENDAR);
	    $client->setRedirectUri('http://000062.kryptos24.pl/google/handler');
	    $client->setAuthConfig('credentials.json');
	    $client->setAccessType('offline');
	    $client->setIncludeGrantedScopes(true);
	    $accessToken = $_SESSION['g_calendar_access_token'];
	    $client->setAccessToken($accessToken);

	    if ($client->isAccessTokenExpired()) {
			$refreshToken = $client->getRefreshToken();
			$client->fetchAccessTokenWithRefreshToken($refreshToken);
			$_SESSION['g_calendar_access_token'] = $client->getAccessToken();
			$accessToken = $_SESSION['g_calendar_access_token'];
			$creds['created'] = time();
		        $creds['refresh_token'] = $refreshToken;
       			$client->setAccessToken($creds);
			$client->setAccessToken($accessToken);
		    }

	     $service = new Google_Service_Calendar($client);

	     $event = new Google_Service_Calendar_Event(array(
	     	'summary' => $data['summary'],
	  	'location' => 'None',
	  	'description' => $data['description'],
	  	'start' => array(
	    	   'dateTime' => $data['start_time'],
	    	   'timeZone' => 'America/Los_Angeles',
	     	),
	  	'end' => array(
	    	   'dateTime' => $data['end_time'],
	    	   'timeZone' => 'America/Los_Angeles',
	  	),
	  	'attendees' => $attendeesEmail,
	  	'reminders' => array(
	    	   'useDefault' => FALSE,
	    	   'overrides' => array(
	      		array('method' => 'email', 'minutes' => 24 * 60),
	      		array('method' => 'popup', 'minutes' => 10),
	    	   ),
	  	),
	     ));

	    $calendarId = 'primary';

	    $event = $service->events->insert($calendarId, $event);

	    $googleModel->markAsDone($data['id']);
	    return true;
	}
    }
}
?>
