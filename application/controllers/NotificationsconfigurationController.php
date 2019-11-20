<?php  

class NotificationsconfigurationController extends Muzyka_Admin
{

    protected $configurationModel;
      
    public function init()
    {
       parent::init();

        $this->configurationModel = Application_Service_Utilities::getModel('NotificationsControl');
    }

    public function indexAction()
    {
	$user_id = Application_Service_Authorization::getInstance()->getUserId();
	$conf = $this->configurationModel->getAllById($user_id);
	$this->view->assign(compact('conf'));
    }

/*    public static function getPermissionsSettings() {
	    echo("hit");
	    die();    
	    $baseIssetCheck = array(
            'function' => 'issetAccess',
            'params' => array('id'),
            'permissions' => array(
                1 => array('perm/notificationsConfiguration/create'),
                2 => array('perm/notificationsConfiguration/update'),
            ),
        );

        $settings = array(
            'modules' => array(
                'notificationsConfiguration' => array(
                    'label' => ''
			array(
                        'permissions' => ['perm/notificationsConfiguration'],
                    ),
                ),
            )
        );

        return $settings;
    }

*/
    public function saveAction()
    {
     	$req = $this->getRequest();

     	$data['task_email'] = $req->getParam('task_email');
	if($data['task_email'] == NULL)
	{
	    $data['task_email'] = 0;
	}
	else{
	    $data['task_email'] = 1;
	}
	$data['task_sms'] = $req->getParam('task_sms');
	if($data['task_sms'] == NULL)
	{
	    $data['task_sms'] = 0;
	}else{
	    $data['task_sms'] = 1;
	}
    	$data['activity_email'] = $req->getParam('activity_email');
	if($data['activity_email'] == NULL)
	{
	    $data['activity_email'] = 0;
	}else{
	    $data['activity_email'] = 1;
	}
    	$data['activity_sms'] = $req->getParam('activity_sms');
	if($data['activity_sms'] == NULL)
	{
	    $data['activity_sms'] = 0;
	}else{
	    $data['activity_sms'] = 1;
	}
    	$data['tickets_email'] = $req->getParam('tickets_email');
	if($data['tickets_email'] == NULL)
	{
	    $data['tickets_email'] = 0;
	}else{
	    $data['tickets_email'] = 1;
	}
    	$data['tickets_sms'] = $req->getParam('tickets_sms');
	if($data['tickets_sms'] == NULL)
	{
	    $data['tickets_sms'] = 0;
	}else{
	    $data['tickets_sms'] = 1;
	}
	$user_id = Application_Service_Authorization::getInstance()->getUserId();
	$this->configurationModel->update($data, $user_id);

	$this->redirect('/notificationsConfiguration');
    }
}
