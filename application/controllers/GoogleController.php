<?php
session_start();
define('BASE_PATH', realpath(dirname(__DIR__)));
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class GoogleController extends Muzyka_Admin
{
    public function handlerAction()
    {
	// Get the API client and construct the service object.
	    $client = new Google_Client();
	    $client->setApplicationName('Calendar');
	    $client->setScopes(Google_Service_Calendar::CALENDAR);
	    $client->setRedirectUri('google/handler');
	    $client->setAuthConfig('credentials.json');
	    $client->setAccessType('offline');
	    $client->setApprovalPrompt('force');
	    $client->setIncludeGrantedScopes(true);

	    if($_GET['code'] != NULL){
		$client->authenticate($_GET['code']);
		$_SESSION['g_calendar_access_token'] = $client->getAccessToken();
		$this->_redirect('/home');
	    }
	    if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
		$accessToken = $_SESSION['g_calendar_access_token'];
		$client->setAccessToken($accessToken);
		// Refresh the token if it's expired.
		    if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			$_SESSION['g_calendar_access_token'] = $client->getAccessToken();
		    }
	    } else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
	    	header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
	    }
    }
}
