<?php

class EmailController extends Muzyka_Action {
    
     public function init()
    {
        parent::init();
        $registry = Zend_Registry::getInstance();
       
    }

    public static function getPermissionsSettings() {
        $settings = array(
            'nodes' => array(
                'api' => array(
                    '_default' => array(
                        'permissions' => array(),
                    ),
                ),
            )
        );

        return $settings;
    }

    public function trialexpiresnotificationAction() {
       $this->_helper->layout()->disableLayout();
       $this->_helper->viewRenderer->setNoRender(true);
        $settings = Application_Service_Utilities::getModel('Settings');
        $emailPrzedstawiciela = $settings->getKey('Email przedstawiciela');
        $companyName=$settings->getKey('Pełna nazwa firmy');
        /** @var Application_Model_Users $usersModel */
        $usersModel = Application_Service_Utilities::getModel('Users');
        /** @var Application_Model_Osoby $osobyModel */
        $osobyModel = Application_Service_Utilities::getModel('Osoby');
        $req = $this->getRequest();
        $para = $req->getParam('para', 0);
        //fetch all free subscription data
        $db = Zend_Db_Table::getDefaultAdapter();
        // send mail after seven days of registration
        if($para==1){
            $query = $db->select()->from('license_subscriptions')->where('license_id =?', 4)->where('status =?', 1)->where('DATE(created_at) = CURRENT_DATE - INTERVAL 7 DAY');
        }
        // send mail one day before
        if($para==2){
            $query = $db->select()->from('license_subscriptions')->where('license_id =?', 4)->where('status =?', 1)->where('DATE(end_date) = CURDATE()+ INTERVAL 1 DAY');
        }
        // send mail on expiry
        if($para==3){
            $query = $db->select()->from('license_subscriptions')->where('license_id =?', 4)->where('status =?', 1)->where('DATE(end_date) = CURRENT_DATE');
        }
        // send mail after expiry one day after
        if($para==4){
            $query = $db->select()->from('license_subscriptions')->where('license_id =?', 4)->where('status =?', 1)->where('DATE(end_date) = CURRENT_DATE - INTERVAL 1 DAY');
       }
        $row = $db->fetchAll($query);
        $html = new Zend_View();
        $html->setScriptPath(APPLICATION_PATH . '/views/templates/layouts/');
        // create mail object
        $mail = new Zend_Mail('utf-8');
        // fetch all recors
        if(!empty($row)){
            foreach ($row as $key=>$value){
                // get detail of user and his/her mail
                $osoba = $osobyModel->getOne($value['osoby_id']);
            if(!empty($osoba)){
                // get name and user mail
                    $useremail=$osoba->email;
                // send mail after seven day of registration
                  //get Organization name
                if($para==1){
                    // assign values
                    $html->assign('company', $companyName);
                    $html->assign('endDate', $value['end_date']);
                    // render view
                    $bodyText = $html->render('emailexpireseven.html');
                }
                // send mail one day before
                if($para==2){
                    // assign values
                    $html->assign('company', $companyName);
                    $bodyText = $html->render('emailexpiretomorrow.html');
                }
                //send mail on exipry
                if($para==3){
                    // assign values
                   $bodyText = $html->render('emailexpired.html');
                }
                // send mail after one day of expiry
                if($para==4){
                    // assign values
                    $html->assign('name', $value['imie']);
                    // render view
                    $bodyText = $html->render('emailexpiredfinal.html');
                }
                try {
                      $this->sendSmtpMail($bodyText,'Trial Expiry Notification', $useremail, $emailPrzedstawiciela);

                  } catch (Exception $e) {
                          throw new Exception($e->getMessage());
                    }
            }
        }// end of foreach  
        }else{
            error_log('No Data Found');
        }
        
    }
}
