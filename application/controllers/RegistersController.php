<?php
require_once ROOT_PATH . '/library/FreshMail/class.rest.php';
require_once ROOT_PATH . '/library/FreshMail/config.php';

class RegistersController extends Muzyka_Action
{
    //protected $usersModel;
    protected $osobyModel;
    protected $baseUrl = '/registers';
    public $auth;

    public function init()
    {
        parent::init();
        // $this->usersModel = Application_Service_Utilities::getModel('Users');
        $this->auth = Zend_Registry::get('session');
        $this->osobyModel = Application_Service_Utilities::getModel('Osoby');
    }

    public function indexAction()
    {
        $this->_helper->layout->setLayout('register');
        if (isset($this->auth->success)) {
            $this->view->success = $this->auth->success;
            $this->view->message = $this->auth->message;
            unset($this->auth->success);
            unset($this->auth->message);
        } else if (isset($this->auth->error)) {
            $this->view->error = $this->auth->success;
            $this->view->message = $this->auth->message;
            unset($this->auth->error);
            unset($this->auth->message);
        }
        $req = $this->getRequest();
        $data['email'] = $req->getParam('email');
        $data['phone'] = $req->getParam('phone');
        $this->view->assign('data', $data);
    }

    public function saveAction()
    {
        // check if user enter dummy data directly by URL
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $email = $_POST['email'];
        $phone = $_POST['telefon_komorkowy'];
        if (!empty($email) && !empty($phone)) {
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = $db->select()->from('free_trials')->where('email =?', $email);
            $row = $db->fetchRow($query);
            if (!empty($row)) {
                if ($row['status'] == 0) {
                    if ($email != $row['email']) {
                        $this->auth->error = 1;
                        $this->auth->message = "Email jest inny niż podany przy rejestracji";
                        $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
                        $this->_redirect($redirectUrl);
                    } else {
                        $json_encode = json_encode($request->getPost(), true);
                        $err_1 = '';
                        $db->query("UPDATE `free_trials` SET status = 1, post_data = '" . $json_encode . "' WHERE email = '" . $email . "'");
                        //$db->commit();
                        $config = new Zend_Config_Ini(__DIR__ . '/../configs/subscriptions.ini');
                        $getConfigDeploy = $config->get('control_deploy');
                        $postData = array(
                            'email' => $email,
                            'phone' => $phone,
                            'deploy' => 1,
                            'auth' => $getConfigDeploy->auth_key
                        );

                        $url = "https://control.kryptos72.com/api/new_system.php";
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                        $response_1 = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);
                        $outputArr = json_decode($response_1);
                    }

                    // check for error
                    if ($err) {
                        $this->auth->error = 1;
                        $this->auth->message = $outputArr->message;
                        $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
                        $this->_redirect($redirectUrl);
                    } else {
                        $this->auth->success = 1;
                        $this->auth->message = "Konto zostało potwierdzone, wkrótce otrzymasz e-mail ze szczegółami swojego systemu próbnego";
                        $rest = new FmRestAPI();
                        $rest->setApiKey(FM_API_KEY);
                        $rest->setApiSecret(FM_API_SECRET);
                        $data = array(
                            'email' => $email,
                            'list' => 'rpilofdy7i',
                            'active' => 1,
                            'confirm' => 1
                        );
                        $response = $rest->doRequest('subscriber/get/rpilofdy7i/' . $email);
                        //testing transactional mail request
                        if ($response['status'] == OK && $response['data']['email'] == $email) {
                            $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
                        } else if ($response['status'] == ERROR && $response['errors'][0]['code'] = 1311)
                            $rest->doRequest('subscriber/add', $data);
                        $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
                    }
                    $this->_redirect($redirectUrl);
                }
            }else if ($row['status'] == 1) {
                $this->auth->error = 1;
                $this->auth->message = "Żądanie zostało już wysłane. Powiadomisz wkrótce";
                $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
                $this->_redirect($redirectUrl);
            }
        } else {
            $this->auth->error = 1;
            $this->auth->message = "E-mail nie istnieje";
            $redirectUrl = $this->baseUrl . '?phone=' . $phone . '&email=' . $email;
            $this->_redirect($redirectUrl);
        }
    }
}
