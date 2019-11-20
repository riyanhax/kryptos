<?php
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class SystemsconfigurationController extends Muzyka_Admin
{
    protected $baseUrl = '/systemsconfiguration';

    /** @var Application_Model_TicketsStatuses */
    private $ticketsStatuses;
    /** @var Application_Model_TicketsTypes */
    private $ticketsTypes;
    /** @var Application_Model_TicketsRoles */
    private $ticketRoles;
    /** @var Application_Model_Role */
    private $roles;
    /** @var Application_Model_KomunikatRola */
    private $komunikatRoles;

    protected $configurationModel;

    protected $apiModel;
 
    protected $apiconfigurationModel;
    
    public function init()
    {
        parent::init();

        Zend_Layout::getMvcInstance()->assign('section', 'Administracja');
        Zend_Layout::getMvcInstance()->assign('section', 'Strona główna');
        $this->notificationsManager = Application_Service_NotificationsManager::getInstance();
        $this->view->baseUrl = $this->baseUrl;

        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        $this->mcrypt = $config->mcrypt->toArray();
        $this->key = $this->mcrypt ['key'];
        $this->iv = $this->mcrypt ['iv'];
        $this->paymentsService = Application_Service_Payments::getInstance();
        $this->bit_check = $this->mcrypt ['bit_check'];

        $this->ticketsStatuses = Application_Service_Utilities::getModel('TicketsStatuses');
        $this->ticketsTypes = Application_Service_Utilities::getModel('TicketsTypes');
        $this->ticketRoles = Application_Service_Utilities::getModel('TicketsRoles');
        $this->roles = Application_Service_Utilities::getModel('Role');
        $this->komunikatRoles = Application_Service_Utilities::getModel('KomunikatRola');
        $this->configurationModel = Application_Service_Utilities::getModel('NotificationsControl');
        $this->apiModel = Application_Service_Utilities::getModel('ApiKeys');
        $this->apiconfigurationModel = Application_Service_Utilities::getModel('ApiConfiguration');
		$this->SetingsModel=Application_Service_Utilities::getModel('Settings');
        
    }

    public static function getPermissionsSettings() {
        $settings = array(
            'nodes' => array(
                'systemsconfiguration' => array(
                    '_default' => array(
                        'permissions' => array(),
                    ),
                    'terms-accepted' => array(
                        'permissions' => array('user/anyone'),
                    ),
                ),
            )
        );

        return $settings;
    }

    public function indexAction()
    {
     $apiconfigurationModel = Application_Service_Utilities::getModel('ApiConfiguration');
        $apidata = $apiconfigurationModel->getApidataAction();

        $apidataobj['email'] = $apidata[0];
        $apidataobj['sms'] = $apidata[1];
        // $apidataobj = (object) $apidataobj;

        /** @var Application_Model_Settings $settings */
        $settings = Application_Service_Utilities::getModel('Settings');
		$$id='18';
		$this->SetingsModel->delete_rec($condition);
		
        $fieldsets = array();
        $allSettings = $settings->getAll();
        foreach ($allSettings as $setting) {
			
			/* echo"<pre>";
			print_r($setting);die; */
			
            $fieldset = $setting['fieldset'];
            if (empty($fieldsets[$fieldset])) {
                $fieldsets[$fieldset] = array();
            }
            $fieldsets[$fieldset][] = $setting;
        }
        /*Notification configuration*/
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        $conf = $this->configurationModel->getAllById($user_id);
        /*Notification configuration*/

        /*Change password*/
        Zend_Layout::getMvcInstance()->assign('section', 'Konfiguracja systemu');
        $session = new Zend_Session_Namespace('user');
        $userModel = Application_Service_Utilities::getModel('Users');

        if (!Application_Service_Authorization::getInstance()->getUserId()) {
            $this->_redirect('/');
        }

        if (isset($_GET['reset'])) {
            $session->user->set_password_date = date('Y-m-d');

            if ($_GET['reset'] === '1' && $this->userIsSuperadmin()) {
                $user = $userModel->getOne(Application_Service_Authorization::getInstance()->getUserId());
                $user->set_password_date = date('Y-m-d');
                $user->save();

                $this->flashMessage('success', 'Przesunięto datę zmiany hasła');
            }

            $this->_redirect('/home');
        }
        /*Change password*/

        /*api config*/
        $api = $this->apiModel->getAllByName();
        $this->view->api = $api;

        /*end api config*/
        $this->view->companyConfirmed = Application_Service_Authorization::isCompanyConfirmation();
        $this->view->countries = $settings->getCountries();
        $this->view->fieldsets = $fieldsets;
        $this->view->settings = $allSettings;
         $this->view->apidataobj = $apidataobj['email'];
         $this->view->apidataobj1 = $apidataobj['sms'];
        $this->view->assign(compact('conf'));
        
    }
    public function companyInformationAction(){
        $data = $this->_getParam('setting');
        /** @var Application_Model_Settings $settings */
        $settings = Application_Service_Utilities::getModel('Settings');
        $this->setDefaultSetting($settings);
        if (is_array($data)) {
            $this->disableCheckboxValues($settings, $data);
            foreach ($data as $k => $val) {
                if ($k == 11) {
                    $test = $settings->get($k);
                    if ($test['value'] != $val) {
                        $settings->update(array('value' => $val), 'id=' . intval($k));
                        //$this->przeladujDokumenty($val);
                    }
                } else {
                    $settings->update(array('value' => $val), 'id=' . intval($k));
                }
            }
            $this->flashMessage('success', 'Zapisano dane');
            $this->redirect('/Systemsconfiguration');
        }
    }

    /* Vipin code starts */
    public function companyInformationNewAction(){
        $data = $this->_getParam('setting');
        $db = Zend_Db_Table::getDefaultAdapter();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        $check_confirmation = $db->select()->from('users')->where('id =?', $user_id)->where('isAdmin = ?', 1)->where('company_confirmation =?', 1);
        $check = $db->fetchRow($check_confirmation);

        $config = new Zend_Config_Ini(__DIR__ . '/../configs/subscriptions.ini');
        $getConfig = $config->get('free_trials');

         if(empty($check)){
        //     $curl = curl_init();

        //     curl_setopt_array($curl, array(
        //       CURLOPT_URL => $getConfig->api_url."check-trial-email",
        //       CURLOPT_RETURNTRANSFER => true,
        //       CURLOPT_ENCODING => "",
        //       CURLOPT_MAXREDIRS => 10,
        //       CURLOPT_TIMEOUT => 30,
        //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //       CURLOPT_CUSTOMREQUEST => "POST",
        //       CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n".$data['31']."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
        //       CURLOPT_HTTPHEADER => array(
        //         "cache-control: no-cache",
        //         "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        //       ),
        //     ));

        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);

        //     curl_close($curl);

        //     if ($err) {
        //       echo "cURL Error #:" . $err;
        //       exit;
        //     } else {
        //         $resArr = json_decode($response);
        //         if($resArr->api_status != 1){
        //             $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Podany email jest inny niż podany w formularz rejestracyjnym. Istnieje możliwość aktywowania tylko jednej bezpłatnej wersji na jedno konto email. W razie pytań, skontaktuj się z Działem Obsługi Klienta.', 'danger'));
        //             $this->redirect('/Systemsconfiguration#company-information-1');
        //             // return;
        //         }
        //     }
        //     $name = $data['29'];
        //     $surname = $data['31'];
        //     $phone = $data['32'];
        //     $country = $data['33'];
        //     $name_of_company = $data['34'];
        //     $address = $data['35'];
        //     $vat_number = $data['36'];
        //     $confirm_agreement = $data['37'];
        //     $confirm_marketing = $data['38'];
        //     $email = $resArr->email;
        //     $code = $resArr->confirmation_code;
        //     $salt = $getConfig->api_salt;

        //     $sign = hash('sha256',  $email.$code.$salt);

        //     $curl = curl_init();

        //     curl_setopt_array($curl, array(
        //       CURLOPT_URL => $getConfig->api_url."free-trial-confirm",
        //       CURLOPT_RETURNTRANSFER => true,
        //       CURLOPT_ENCODING => "",
        //       CURLOPT_MAXREDIRS => 10,
        //       CURLOPT_TIMEOUT => 30,
        //       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //       CURLOPT_CUSTOMREQUEST => "POST",
        //       CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\n".$name."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"surname\"\r\n\r\n".$surname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n".$email."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"phone\"\r\n\r\n".$phone."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"country\"\r\n\r\n".$country."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"name_of_company\"\r\n\r\n".$name_of_company."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"address\"\r\n\r\n".$address."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"vat_number\"\r\n\r\n".$vat_number."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"confirm_agreement\"\r\n\r\n".$confirm_agreement."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"confirm_marketing\"\r\n\r\n".$confirm_marketing."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"code\"\r\n\r\n".$code."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"sign\"\r\n\r\n".$sign."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
        //       CURLOPT_HTTPHEADER => array(
        //         "cache-control: no-cache",
        //         "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        //       ),
        //     ));

        //     $response_1 = curl_exec($curl);
        //     $err = curl_error($curl);

        //     curl_close($curl);

        //     if ($err) {
        //         $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Free Trial Confirmation Failed', 'danger'));
        //         // $this->view->companyConfirmed = 1;
        //         $this->redirect('/systemsconfiguration#company-information-1');
        //     }else{
        //        $arr = json_decode($response_1);
        //        $dataArr = json_decode($arr->data);
        //     }
            
        //     $db->beginTransaction();
        //     $license = $db->select()->from('licenses')->where('is_trial =?', 1)->order('id Desc');
        //     $license_id = $db->fetchRow($license);
        //     $currentDate = date('Y-m-d H:i:s');
        //     $db->query("INSERT INTO `free_trials` (email, phone, confirmation_code, status, license_subscription_id, post_data, created_at) VALUES('".$dataArr->email."', '".$dataArr->phone."', '".$dataArr->code."', 1, ".$license_id['id'].", '".$arr->data."', '".$currentDate."' )");
        //     $db->query("UPDATE `users` SET company_confirmation = 1 WHERE id =".$user_id);
        //     $db->commit();
         }
        /** @var Application_Model_Settings $settings */
        $settings = Application_Service_Utilities::getModel('Settings');
        $this->setDefaultSetting($settings);
        if (is_array($data)) {
             if ($_FILES["logoimage"]["name"][0] != '') {
                //@unlink(APPLICATION_PATH."/../web/assets/images/logoKrypto.png");
                //@unlink(APPLICATION_PATH."/../web/assets/images/logoKrypto.jpeg");
                //@unlink(APPLICATION_PATH."/../web/assets/images/logoKrypto.jpg");
                $_FILES["logoimage"]["name"][0] = 'logoKrypto.'. pathinfo($_FILES["logoimage"]["name"][0],PATHINFO_EXTENSION);
                 $target_dir = APPLICATION_PATH."/../web/assets/images/";
                    $target_file = $target_dir . basename($_FILES["logoimage"]["name"][0]);
                    if (move_uploaded_file($_FILES["logoimage"]["tmp_name"][0], $target_file)) {
                        $data[39] = '/assets/images/'.basename($_FILES["logoimage"]["name"][0]);
                    }
             }
            $this->disableCompanyCheckboxValues($settings, $data);
            foreach ($data as $k => $val) {
                if ($k == 11) {
                    $test = $settings->get($k);
                    if ($test['value'] != $val) {
                        $settings->update(array('value' => $val), 'id=' . intval($k));
                        //$this->przeladujDokumenty($val);
                    }
                } else {
                    $settings->update(array('value' => $val), 'id=' . intval($k));
                }
            }
            $this->flashMessage('success', 'Zapisano dane');
            // $this->view->companyConfirmed = 1;
            $this->redirect('/Systemsconfiguration');
        }
        
    }
    /* Vipin code end */


    private function setDefaultSetting($settings)
    {
        /** @var Application_Model_Settings $settings */
        $defaultVariables = [
            'SIMPLE LOGIN' => [
                'variable' => 'SIMPLE LOGIN',
                'value' => '0',
                'description' => 'Logowanie bez maskowania',
                'class' => 'checkbox',
                'fieldset' => 'Dodatkowe ustawienia'
            ],
        ];

        foreach ($settings->getAll() as $row) {
            /** @var Application_Service_EntityRow $row */
            unset($defaultVariables[$row->variable]);
        }

        foreach ($defaultVariables as $data) {
            $settings->save($data);
        }
    }

    private function disableCheckboxValues($settings, &$data)
    {
        /** @var Application_Model_Settings $settings */
        foreach ($settings->getAll() as $row) {
            /** @var Application_Service_EntityRow $row */
            /* Vipin code starts */
            if ($row->class == 'checkbox' && $row->fieldset == 'Dodatkowe ustawienia') {
            /* Vipin code end */
                if (!isset($data[$row->id])) {
                    $data[$row['id']] = 0;
                }
            /* Vipin code starts */    
            }
            /* Vipin code end */
        }
    }
    /* Vipin code starts */
    private function disableCompanyCheckboxValues($settings, &$data){
        /** @var Application_Model_Settings $settings */
        foreach ($settings->getAll() as $row) {
            /** @var Application_Service_EntityRow $row */
            if ($row->class == 'checkbox' && $row->fieldset == 'Informacje o firmie' ) {
                if (!isset($data[$row['id']])) {
                    $data[$row['id']] = 0;
                }
            }
        }
    }
    /* Vipin code end */
    public function configuringNotificationsAction(){
        $req = $this->getRequest();
        $data['task_email'] = $req->getParam('task_email');
        if ($data['task_email'] == NULL) {
            $data['task_email'] = 0;
        } else {
            $data['task_email'] = 1;
        }
        $data['task_sms'] = $req->getParam('task_sms');
        if ($data['task_sms'] == NULL) {
            $data['task_sms'] = 0;
        } else {
            $data['task_sms'] = 1;
        }
        $data['activity_email'] = $req->getParam('activity_email');
        if ($data['activity_email'] == NULL) {
            $data['activity_email'] = 0;
        } else {
            $data['activity_email'] = 1;
        }
        $data['activity_sms'] = $req->getParam('activity_sms');
        if ($data['activity_sms'] == NULL) {
            $data['activity_sms'] = 0;
        } else {
            $data['activity_sms'] = 1;
        }
        $data['tickets_email'] = $req->getParam('tickets_email');
        if ($data['tickets_email'] == NULL) {
            $data['tickets_email'] = 0;
        } else {
            $data['tickets_email'] = 1;
        }
        $data['tickets_sms'] = $req->getParam('tickets_sms');
        if ($data['tickets_sms'] == NULL) {
            $data['tickets_sms'] = 0;
        } else {
            $data['tickets_sms'] = 1;
        }

        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        $this->configurationModel->update($data, $user_id);
        /*end Notification Configuration*/

        $this->flashMessage('success', 'Zapisano dane');
        $this->redirect('/Systemsconfiguration');
    }

    public function changePasswordAction(){
        Zend_Layout::getMvcInstance()->assign('section', 'Zmiana hasła');

        if (!Application_Service_Authorization::getInstance()->getUserId()) {
            $this->_redirect('/');
        }

        if ($this->getRequest()->isPost()) {

            $req = $this->getRequest();
            $old_pass = $req->getParam('old_pass', '');
            $new_pass1 = $req->getParam('new_pass1', '');
            $new_pass2 = $req->getParam('new_pass2', '');

            if ($old_pass && $new_pass1 && $new_pass2) {

                if ($new_pass1 !== $new_pass2) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Hasła powinny być takie same', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                if ($new_pass1 === $old_pass) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Hasła nie mogą być takie same2', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }
                if (strlen($new_pass1) < 10) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Minimalna długość hasła do 10 znaków', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }
                if (strlen($new_pass1) > 15) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Maksymalna długość hasła do 15 znaków', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                if (preg_match('/[0-9]+/', $new_pass1) == 0) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna cyfra', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                if (preg_match('/[A-ZĄĆĘŁŃÓŚŹŻ]+/', $new_pass1) == 0) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna wielka litera', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                if (preg_match('/[a-ząćęłńóśźż]+/', $new_pass1) == 0) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jedna mała litera', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                if (preg_match('/[[:punct:]]+/', $new_pass1) == 0) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wymagana jest przynajmniej jeden znak interpunkcyjny', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                $userModel = Application_Service_Utilities::getModel('Users');
                $user = $userModel->getOne(Application_Service_Authorization::getInstance()->getUserId());

                $passwordClean = substr($user->password, 0, strpos($user->password, '~'));
                $passwordDecrypt = $this->decryptPassword($passwordClean);

                $authorizationService = Application_Service_Authorization::getInstance();
                $encryptedPassword = $authorizationService->decryptPassword($user->password);

                if ($old_pass !== $passwordDecrypt) {
                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Stare hasło jest niepoprawne.', 'danger'));
                    $this->redirect('/Systemsconfiguration');
                }

                $this->addLogDb("users", $this->session->user->id, "Application_Model_Users::changePassword");
                //die('homeCont');

                $this->session->user->set_password_date = date('Y-m-d');
                $this->savePassword($user, $new_pass1);
            }
        }
        $this->flashMessage('success', 'Zapisano dane');
        $this->redirect('/Systemsconfiguration');
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

    public function encryptPassword($decryptedPassword)
    {
        $passwordLength = mb_strlen($decryptedPassword);

        $text_num = str_split($decryptedPassword, $this->bit_check);
        $text_num = $this->bit_check - strlen($text_num[count($text_num) - 1]);

        for ($i = 0; $i < $text_num; $i++) {
            $decryptedPassword = $decryptedPassword . chr($text_num);
        }

        $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'cbc', '');
        mcrypt_generic_init($cipher, $this->key, $this->iv);

        $decrypted = mcrypt_generic($cipher, $decryptedPassword);

        mcrypt_generic_deinit($cipher);

        return base64_encode($decrypted) . '~' . $passwordLength;
    }

    public function apiConfigurationAction(){

            $req = $this->getRequest()->getPost();
            $result = $this->apiconfigurationModel->saveAction($req);
      
        $this->redirect('/Systemsconfiguration');

    }

    public function getInfoAction()
    {

      // $sender = Application_Service_Email::GetInstance();   
      // $sender->senderInfo();

      $smssender = Application_Service_SMS::GetInstance();  
      $smssender->smsInfo();

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