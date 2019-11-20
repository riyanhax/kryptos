<?php

class ActivationController extends Muzyka_Action {

    //protected $usersModel;
    protected $osobyModel;
    protected $baseUrl = '/activation';
    public $data;
    
    public function init() {
        parent::init();
        // $this->usersModel = Application_Service_Utilities::getModel('Users');
        $this->data = Zend_Registry::get('session');
        $this->osobyModel = Application_Service_Utilities::getModel('Osoby');
    }

    public function indexAction() {
        error_log('succes');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $settings = Application_Service_Utilities::getModel('Settings');
        /** @var Application_Model_Users $userModel */
       $userModel = Application_Service_Utilities::getModel('Users');
       /** @var Application_Model_Osoby $osobyModel */
       $osobyModel = Application_Service_Utilities::getModel('Osoby');
       $emailPrzedstawiciela = $settings->getKey('Email przedstawiciela');
        $req = $this->getRequest();
        $useremail = $req->getParam('email');
        if (!empty($useremail)) {
            if(filter_var($useremail, FILTER_VALIDATE_EMAIL)) {
                $logindoSystem=$osobyModel->getLoginByEmail($useremail);
            }
            $user = $userModel->getUserByLogin($logindoSystem);
            if ($user) {
                //if record exists then send email with username and password and link of new system
                $host = $_SERVER['HTTP_HOST'];
                $URL = $host;
                $Username=$user->login;
                $passwordClean = substr($user->password, 0, strpos($user->password, '~'));
                $password = $this->decryptPassword($passwordClean);
                //insert recovery key in database
                    $html = new Zend_View();
                    $html->setScriptPath(APPLICATION_PATH . '/views/templates/layouts/');
                    // assign valeues
                    $html->assign('URL', $URL);
                    $html->assign('username', $Username);
                    $html->assign('password', $password);
                    
                    // create mail object
                    $mail = new Zend_Mail('utf-8');

                    // render view
                    $bodyText = $html->render('loginpwd.html');
                    try {
                        $this->sendSmtpMail($bodyText, 'Kryptos Szczegóły próby/Kryptos Trial Deatils', $useremail, $emailPrzedstawiciela);
                        error_log('Email is send Successfully');
                    } catch (Exception $e) {
                        error_log('Unable to send Email, Please try latter');
                    }
            } else {
                error_log('Email is not correct or not exists');
                }
        } else {
            error_log('Please enter email to send mail');
        }
    }

        
    public function afterdeployAction() {
        // check if user enter dummy data directly by URL
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        /** @var Application_Model_Users $userModel */
        $userModel = Application_Service_Utilities::getModel('Users');
        error_log(json_encode($_REQUEST));
        $email = $_REQUEST['email'];
        $phone = $_REQUEST['phone'];
        // get info from trial manager about this user to create user as superadmin
        if (!empty($email) && !empty($phone)) {
            $postData = array(
                'email' => $email,
                'phone' => $phone,
            );
            error_log('38');
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => 'https://0000156.kryptos72.com/api/create-admin',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_FOLLOWLOCATION => true
            ));

            $output = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            $outputArr = json_decode($output);
            $status = $outputArr->status;
            if ($status == 0) {
                error_log("Something went wrong:" . $outputArr->message);
            }
            if ($status == 1) {
                //login as superadmin first
                $user = $userModel->getUserByLogin('superadmin');
                $this->auth->setCredentialTreatment('');
                $this->auth->setCredential($user->password)->setIdentity('superadmin');
                $userAray=(object) $user->toArray();
                Application_Service_Authorization::login($userAray);
                $this->setAuth($user);
                
                //$date = time()+(13*86400);
                $finalDate = date('Y-m-d H:i:s', time()+(13*86400));
                //$newdate = $date->addDay('13');
               // $finalDate = $newdate->get('YYYY-MM-dd HH:mm:ss');
                $email = $outputArr->email;
                $post_Data = json_decode($outputArr->post_data);
                // enter data in settings table
                 /** @var Application_Model_Settings $settings */
                $settings = Application_Service_Utilities::getModel('Settings');
                $settings->update(array('value' => $post_Data->imie), 'id=29');
                $settings->update(array('value' => $post_Data->nazwisko), 'id=30');
                $settings->update(array('value' => $outputArr->email), 'id=31');
                $settings->update(array('value' => $phone), 'id=32');
                $settings->update(array('value' => $phone), 'id=10');
                $settings->update(array('value' => $outputArr->email), 'id=11');
                $settings->update(array('value' => $post_Data->organization_name), 'id=34');
                $settings->update(array('value' => $post_Data->organization_name), 'id=1');
                $settings->update(array('value' => $post_Data->company_shortname), 'id=26');
                $settings->update(array('value' => $post_Data->company_city), 'id=16');
                $settings->update(array('value' => $post_Data->company_NIP), 'id=7');
                
                $postData = array(
                    'email' => $outputArr->email,
                    'phone' => $phone,
                    'empType' => "expert",
                    "imie" => $post_Data->imie,
                    "nazwisko" => $post_Data->nazwisko,
                    "isAdmin" => 1,
                    "id_role"=>3,
                    "license_id" => 4,
                    "license_status" => 1,
                    "license_end_date" => $finalDate,
                    'stopredirect' => 1
                );
                error_log('77');
                $this->_request->setPost($postData);
                $this->_forward('save', 'osoby', 'default');
                error_log('80');
            }
        }
    }



   public function setAuth()
    {
        $this->auth = new Zend_Auth_Adapter_DbTable($this->db, 'users', 'login', 'password', 'MD5(?)');
    }
    
    private function decryptPassword($encrypted_text)
    {
        $authorizationService = Application_Service_Authorization::getInstance();
        return $authorizationService->decryptPassword($encrypted_text);
    }
}