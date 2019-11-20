<?php
include_once('OrganizacjaController.php');

class HomeController extends OrganizacjaController
{
    protected $welcome_msg;
    protected $notificationsManager;
    protected $paymentsService;
    protected $welcome;
    
    public function init()
    {
        parent::init();
         $userId = Application_Service_Authorization::getInstance()->getUserId();
	 $login = $userId;
	 $userModel = Application_Service_Utilities::getModel('Users');
        $user = $userModel->getloginuser($login);
	  $loginname= $user[0]['login'];
	
	
	
	$token='xoxp-606403853634-617261255300-649879048182-eef631bedbf5c9f2caf26fd0bfd78e17';
		$ch = curl_init("https://slack.com/api/channels.create");
		$data = http_build_query([
			"token" => $token,
			"name" =>  strtolower($loginname),  
			"validate"=>true,			
		]);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		$res=json_decode($result,true);
		//print_r($res);exit;
		 $channelid=	$res['channel']['id'];
			
			if($channelid !=''){
			$data=array('channelid'=>$channelid);
			 $where='id="'.$userId.'"';
            
            $model = new Application_Model_Users();
            $model->update($data, $where);
			}
		
	
	$userModel = Application_Service_Utilities::getModel('Users');
        $user = $userModel->getloginuser($login);
	 $loginname= $user[0]['login'];
	 $channelid= $user[0]['channelid'];
	 
	 
	$this->session->user->channelid =$channelid;
	
        Zend_Layout::getMvcInstance()->assign('section', 'Strona główna');
        $this->notificationsManager = Application_Service_NotificationsManager::getInstance();
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        $this->mcrypt = $config->mcrypt->toArray();
        $this->key = $this->mcrypt ['key'];
        $this->iv = $this->mcrypt ['iv'];
        $this->paymentsService = Application_Service_Payments::getInstance();
        $this->bit_check = $this->mcrypt ['bit_check'];
    }

    public static function getPermissionsSettings() {
        $settings = array(
            'nodes' => array(
                'home' => array(
                    '_default' => array(
                        'permissions' => array(),
                    ),
                    'terms-accepted' => array(
                        'permissions' => array('user/anyone'),
                    ),
                    'przelewy-callback' => array(
                        'permissions' => array('user/anyone'),
                    ),
                ),
            )
        );

        return $settings;
    }

    public function welcomeAction()
    {
        $this->setTemplate('index');
        if(isset($_COOKIE['welcome_message'])) {
            $this->welcome = 0;
        } else {
            setcookie('welcome_message', 1, time()+31556926, '/');
            $this->welcome = 1;
        }
        //$this->welcome = 1;
        
        $this->indexAction();
        $this->afterLoginEvent();
    }

    public function indexAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('home');
        
        $logicTasks = new Logic_Tasks();
        
        //comagom code start 20119.3.28
        $showCalendar = true;
        //comagom code end
        if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
            $user_id = Application_Service_Authorization::getInstance()->getUserId();
            $this->notificationsManager->update($user_id);
            //code to refresh the calendar pending!!
            $showCalendar = true;
        }

        $tasks = $logicTasks->getUserActiveTasksData();
        
        $documentsModel = Application_Service_Utilities::getModel('Documents');
        $documentsVersionedModel = Application_Service_Utilities::getModel('DocumentsVersioned');
        $userSignaturesModel = Application_Service_Utilities::getModel('UserSignatures');
        $osobyModel = Application_Service_Utilities::getModel('Osoby');
        
        $myTasks = $tasks;
        
        /* @todo Remove if tasks works
        $myTasks = $storageTasksModel->getAll(array(
            'user_id' => $identity->id,
            'status' => 0,
            'limit' => 10,
        ));
        */
// ------------------------------   COMAGOM CODE START ---------------------------------------//
        // Get Tasks which has active document ,  not archived , pending.
        /*
        if($myTasks != []) {
            foreach($myTasks as $key => $value) {
                $tempDocumentData = $documentsModel->getDocumentByDocumentId($value['object_id']);
                
                if($tempDocumentData['active'] == Application_Service_Documents::VERSION_ARCHIVE) {
                    unset($myTasks[$key]);
                }
            }
        }
        */
// ------------------------------   COMAGOM CODE END   ---------------------------------------//
        $myDocuments = $documentsModel->getList(array(
            'd.osoba_id = ?' => $this->osobaNadawcaId,
        ), 10, 'd.id DESC');

        $documentsVersioned = $documentsVersionedModel->getAll(array(
            'dv.status' => 1
        ));

        $mySignatures = $userSignaturesModel->getList(array(
            'us.user_id = ?' => Application_Service_Authorization::getInstance()->getUserId(),
        ), 10, 'us.id DESC');
        
        $usersCounter = count($osobyModel->getIdAllUsers());

        $loggedInCounter = Application_Service_Utilities::getModel('Users')->getLoggedInCounter();
        
        $this->view->assign(compact('myTasks', 'myDocuments', 'mySignatures', 'usersCounter', 'documentsVersioned', 'loggedInCounter', 'showCalendar'));

        if (Application_Service_Authorization::isGranted('perm/tickets')) {
            $tickets = Application_Service_Utilities::getModel('Tickets');
            $tickets= $tickets->getList();
            foreach ($tickets as $v => $k) {
                if ($tickets[$v]['updated_at'] > $tickets[$v]['created_at']) {
                    $tickets[$v]['timeline'] = $tickets[$v]['updated_at'];
                } else
                {
                    $tickets[$v]['timeline'] = $tickets[$v]['created_at'];
                }
            }
            function array_sort_func($a,$b=NULL) {
                static $keys;
                if($b===NULL) return $keys=$a;
                foreach($keys as $k) {
                    if(@$k[0]=='!') {
                        $k=substr($k,1);
                        if(@$a[$k]!==@$b[$k]) {
                            return strcmp(@$b[$k],@$a[$k]);
                        }
                    }
                    else if(@$a[$k]!==@$b[$k]) {
                        return strcmp(@$a[$k],@$b[$k]);
                    }
                }
                return 0;
            }

            function array_sort(&$array) {
                if(!$array) return $keys;
                $keys=func_get_args();
                array_shift($keys);
                array_sort_func($keys);
                usort($array,"array_sort_func");
            }

            array_sort($tickets,'!timeline');
            $tickets = Application_Service_Authorization::filterResults($tickets, 'node/tickets/view', ['id' => ':id']);
            $this->view->myTickets = array_slice($tickets, 0, 5);
        }
        $this->view->displayVideo = Zend_Registry::getInstance()->get('config')->production->dev->spoof->display_video;
        $userId = Application_Service_Authorization::getInstance()->getUserId();
        $osoba = $osobyModel->getOne($userId);
        $this->view->showTerms =  $osoba->zapoznanaZRegulaminem === "0";
        $this->view->showwelcome =  $this->welcome;
        $this->view->myTasks = $myTasks;
        // $this->view->showwelcome = 1;
   }

    public function termsAcceptedAction(){
        $osobyModel = Application_Service_Utilities::getModel('Osoby');
        $user = $osobyModel->fetchRow(array('id = ?' => Application_Service_Authorization::getInstance()->getUserId()));
        $user->zapoznanaZRegulaminem = 1;
        $user->save();
        $this->outputJson(array('accepted' => 'ok'));
    }

    public function error403Action()
    {

    }

    public function previewDocumentAction()
    {
        $this->view->ajaxModal = 1;

        $id = $this->getRequest()->getParam('id');
        $this->view->documentContent = Application_Service_DocumentsPrinter::getInstance()->getDocumentPreview($id);
    }

    public function zmianahaslaAction()
    {
        Zend_Layout::getMvcInstance()->assign('section', 'Zmiana hasła');
        $session = new Zend_Session_Namespace('user');
        $userModel = Application_Service_Utilities::getModel('Users');

        if (!Application_Service_Authorization::getInstance()->getUserId()) {
            $this->_redirect('/');
        }

        if (isset($_GET['reset'])) {
            $session->user->set_password_date = date('Y-m-d');

            if ($_GET['reset'] === '1' && $this->userIsSuperadmin()) {
                $user = $userModel->getOne(Application_Service_Authorization::getInstance()->getUserId());
                // comagom code start 2019.3.28
                $user->home_page = "";
                // comagom code end 2019.3.28
                $user->set_password_date = date('Y-m-d');
                $user->save();

                $this->flashMessage('success', 'Przesunięto datę zmiany hasła');
            }

            $this->_redirect('/home');
        }
    }

    public function zmianahaslasaveAction()
    {
         // $this->redirect('/home');
        Zend_Layout::getMvcInstance()->assign('section', 'Zmiana hasła');

        if (!Application_Service_Authorization::getInstance()->getUserId()) {
            $this->_redirect('/');
        }

        if ($this->getRequest()->isPost()) {

            $req = $this->getRequest();
            $old_pass = $req->getParam('old_pass', '');
            $new_pass1 = $req->getParam('new_pass1', '');
            $new_pass2 = $req->getParam('new_pass2', '');

            if ($new_pass1 !== $new_pass2) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Hasła powinny być takie same', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if ($new_pass1 === $old_pass) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Hasła nie mogą być takie same', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if (strlen($new_pass1) < 10) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Minimalna długość hasła do 10 znaków', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if (strlen($new_pass1) > 15) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Maksymalna długość hasła do 15 znaków', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if (preg_match('/[0-9]+/', $new_pass1) == 0) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna cyfra', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if (preg_match('/[A-ZĄĆĘŁŃÓŚŹŻ]+/', $new_pass1) == 0) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna wielka litera', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            if (preg_match('/[a-ząćęłńóśźż]+/', $new_pass1) == 0) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna mała litera', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            //
            if (preg_match('/[[:punct:]]+/', $new_pass1) == 0) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jeden znak interpunkcyjny', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }

            $userModel = Application_Service_Utilities::getModel('Users');
            $user = $userModel->getOne(Application_Service_Authorization::getInstance()->getUserId());

            $passwordClean = substr($user->password, 0, strpos($user->password, '~'));
            $passwordDecrypt = $this->decryptPassword($passwordClean);

            $authorizationService = Application_Service_Authorization::getInstance();
            $encryptedPassword = $authorizationService->decryptPassword($user->password);

            if ($old_pass !== $passwordDecrypt) {
                $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Stare hasło jest niepoprawne.', 'danger'));
                $this->_redirect('/home/zmianahasla');
            }
            $this->addLogDb("users", $this->session->user->id, "Application_Model_Users::changePassword");
            //die('homeCont');

            $this->session->user->set_password_date = date('Y-m-d');
            $this->savePassword($user, $new_pass1);
            // comagom code start 2019.4.3
            
            // comagom code end 2019.4.3
            $this->_helper->getHelper('flashMessenger')->clearMessages();
            $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Zmieniono hasło do konta'));
            $this->redirect('/home');
        }
    }

    private function savePassword(Zend_Db_Table_Row $osoba, $pass)
    {
        $authorizationService = Application_Service_Authorization::getInstance();
        $encryptedPassword = $authorizationService->encryptPassword($pass);

        $userModel = Application_Service_Utilities::getModel('Users');
        $data ['id'] = $osoba->id;
        $data ['password'] = $encryptedPassword;
        $data ['set_password_date'] = date('Y-m-d H:i:s');

        $userModel->changePassword($data);
        
    }

    private function encryptPassword($text)
    {
        $text_num = str_split($text, $this->bit_check);
        $text_num = $this->bit_check - strlen($text_num [count($text_num) - 1]);
        for ($i = 0; $i < $text_num; $i++) {
            $text = $text . chr($text_num);
        }
        $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', '');
        mcrypt_generic_init($cipher, $this->key, $this->iv);
        $decrypted = mcrypt_generic($cipher, $text);
        mcrypt_generic_deinit($cipher);
        return base64_encode($decrypted);
    }

    private function decryptPassword($encrypted_text)
    {
        $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', '');
        mcrypt_generic_init($cipher, $this->key, $this->iv);
        $decrypted = mdecrypt_generic($cipher, base64_decode($encrypted_text));
        mcrypt_generic_deinit($cipher);
        $last_char = substr($decrypted, -1);
        for ($i = 0; $i < $this->bit_check - 1; $i++) {
            if (chr($i) == $last_char) {
                $decrypted = substr($decrypted, 0, strlen($decrypted) - $i);
                break;
            }
        }
        return $decrypted;
    }

    public function changeLanguageAction()
    {
        setcookie("zf-translate-language", $this->getParam('id'), 0, "/", $_SERVER['SERVER_NAME']);
        $this->redirect('/home');
    }

    public function ajaxGetSectionAction()
    {
        $name = $this->getParam('name');
        $context = $this->getParam('context', []);

        echo Application_Service_Ui::getInstance()->getSectionByName($name, $context);
        exit;
    }

    public function universalMiniChooseAction()
    {
        $this->view->ajaxModal = 1;
        $model = $this->getParam('model');
        $class = $this->getParam('class');
        $const = $this->getParam('const');

        if ($model) {
            $this->view->records = Application_Service_Utilities::getModel($model)->getAllForTypeahead();
        } elseif ($class && $const) {
            $this->view->records = constant("$class::$const");
        } else {
            Throw new Exception('Invalid parameters', 500);
        }
    }

    public function paypalCallbackAction()
    {
        $paymentHash = $this->getParam('hash');
        $paymentParams = [
            'payment_id' => $this->getParam('paymentId'),
            'payer_id'   => $this->getParam('PayerID'),
        ];
        try {
            $success = $this->paymentsService->pay($paymentHash, $paymentParams);
        } catch (Exception $e) {
            $success = false;
        }

        if ($success) {
            $this->flashMessage('success', 'You have successfully paid! Thank you.');
        } else {
            $this->flashMessage('error', 'You have failed to pay!');
        }

        $this->redirect('/home');
    }

    public function paypalCancelAction() {
        $paymentHash = $this->getParam('hash');
        $this->redirect('/home');
    }
    /* Vipin code starts */
    public function licensesViewAction(){
        $this->setDialogAction([
            'id'    => 'messages-response',
            'title' => 'Aktywuj lub przedłuż dostęp do usługi Kryptos',
        ]);
        $request = $this->getRequest();
       
        $LicenseRepository = Application_Service_Utilities::getModel('License');
        $this->view->currentLicenses = $LicenseRepository->manageHistory();
        $this->view->paginator = $LicenseRepository->getAllLicenses();
    }

   public function licensesRequestAction(){

        $db = Zend_Db_Table::getDefaultAdapter();
        $getSuperAdmin = $db->select()->from('users')->where('isSuperAdmin =?', 1);
        $superAdmin = $db->fetchRow($getSuperAdmin);
        $request = $this->getRequest();
        $user = Application_Service_Authorization::getInstance()->getUser();
        
        $uri = Zend_Controller_Front::getInstance()->getRequest();

        $config = new Zend_Config_Ini(__DIR__ . '/../configs/subscriptions.ini');
        $getConfig = $config->get('przelewy24');

        ob_start();
        include_once(APPLICATION_PATH.'/services/payments/Przelewy24_API.php');

        $p24_url_return = $uri->getScheme().'://'.$uri->getHttpHost().'/home/return-callback';
        $p24_url_status = $uri->getScheme().'://'.$uri->getHttpHost().'/index/przelewy-callback';

        $p24_merchant_id = $getConfig->PRZELEWY24_MERCHANT_ID;
        $p24_pos_id = $getConfig->PRZELEWY24_MERCHANT_ID;
        $p24_crc = $getConfig->PRZELEWY24_CRC;
        $p24_type = $getConfig->PRZELEWY24_TYPE;

        $P24 = new Przelewy24($p24_merchant_id, $p24_pos_id, $p24_crc, $p24_type);
        $p24_session_id = uniqid();
        $amount = $request->getPost("license_price") * 100;
        $description = 'License Purchase';
        $email = $user['email'];
        $description = 'License Purchase';

        $_SESSION['p24_session_id'] = $p24_session_id;
        $P24->addValue("p24_session_id",$p24_session_id);
        $P24->addValue("p24_amount", $amount);
        $P24->addValue("p24_currency",'PLN');
        $P24->addValue("p24_email",$email);
        $P24->addValue("p24_url_return", $p24_url_return);
        $P24->addValue("p24_url_status", $p24_url_status);
        $P24->addValue("p24_description", $description);

        
        
        if ($licenseId = $request->getPost("license_id")) {
            $licenseSubscriptionService = Application_Service_LicenseSubscriptions::getInstance();
            
            $arrTypeCounts = array(
                    'expert_count' => 0,
                    'pro_count' => 0,
                    'mini_count' => 0
                );
            foreach ($licenseSubscriptionService->getList($user['id'], Application_Model_LicenseSubscription::STATUS_ACTIVATED) as $subscription) {
                $arrTypeCounts['expert_count'] = ($subscription->expert_count? $subscription->expert_count - $subscription->license->expert_count:0);
                
                $arrTypeCounts['pro_count'] = ($subscription->pro_count? $subscription->pro_count - $subscription->license->pro_count:0);
                
                $arrTypeCounts['mini_count'] = ($subscription->mini_count? $subscription->mini_count - $subscription->license->mini_count:0);
            }
            $currentDate = date('Y-m-d H:i:s');
            if($request->getPost("validity") == 'month'){
                $date = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($currentDate)));
            }else{
                $date = date('Y-m-d H:i:s', strtotime("+1 year", strtotime($currentDate)));
            }
            $licenseService = Application_Service_Licenses::getInstance();
            $license = $licenseService->get($licenseId);
            $data = array(
                'license_id' => $request->getPost("license_id"),
                'user_id' => $user['id'],
                'end_date' => $date,
                'status' => 0,
                'expert_count' => $license->expert_count + $arrTypeCounts['expert_count'],
                'pro_count' => $license->pro_count + $arrTypeCounts['pro_count'],
                'mini_count' => $license->mini_count + $arrTypeCounts['mini_count'],
                'subscription_price' => $request->getPost("license_price"),
                'session_id' => $_SESSION['p24_session_id']
            );
            $LicenseSubscription = Application_Service_Utilities::getModel('LicenseSubscription');
            $LicenseSubscription->insertData($data);
        }
        $RET = $P24->trnRegister(true);
        if($RET["error"]!=='0') {
            echo "Error: 0";
            exit;
        }   
        
        exit;        
    }
    
    public function przelewyCallbackAction()
    {
        $json = json_encode($_REQUEST);
        $subject = 'test';
        $this->sendMail($json, $subject, 'vipin@yugtechno.com');
        if($_REQUEST['p24_session_id']){
            $licenseSubscriptionService = Application_Service_LicenseSubscriptions::getInstance();
            foreach ($licenseSubscriptionService->getList(NULL, Application_Model_LicenseSubscription::STATUS_ACTIVATED) as $subscription) {

                $licenseSubscriptionService->deactivate($subscription);

            }
            $LicenseSubcriptionRepository = Application_Service_Utilities::getModel('LicenseSubscription');
            $data = array(
                'status' => 1
            );
            $where = array('session_id = ?' => $_REQUEST['p24_session_id']);
            $LicenseSubcriptionRepository->updateSubscription($data, $where);
            $session_id = $_REQUEST['p24_session_id'];
            
            $getUserId = $LicenseSubcriptionRepository->getSubscriptionBySessionId($session_id);
            $user_id = $getUserId['osoby_id'];

            $getUser = $LicenseSubcriptionRepository->getUsetById($user_id);
            
            $to = $getUser['email'];
            $subject = 'License Activation';
            $message = 'Hello '.$getUser["login"].',<br>';
            $message .= 'Your license is activated.';

            $this->sendMail($message, $subject, $to);
            exit;
        }
    }
    
    public function returnCallbackAction()
    {
       
        try {
            unset($_SESSION['p24_session_id']);
            $this->flashMessage('success', 'Payment successfully completed, you will get subscription activation email soon.');
        } catch (\Exception $ex) {
            $err_msg = $ex->getMessage();
            $this->flashMessage('danger', $err_msg);
        }
        $this->_redirect('/home');
    }
    /* Vipin code end */
}
