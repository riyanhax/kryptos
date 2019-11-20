<?php  
define('BASE_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require BASE_PATH . '/vendor/autoload.php';

class ActivityController extends Muzyka_Admin
{

      protected $activityModel;
      protected $activityLogModel;
      protected $googleModel;
      protected $userloggedin;
      protected $registryService;
      protected $notificationsManager;
      protected $entitiesModel;
    /** @var Application_Model_Registry */
    protected $registryModel;

    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;

    public function init()
    {
       parent::init();

        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
       	$this->activityModel = Application_Service_Utilities::getModel('Activity');
	$this->googleModel = Application_Service_Utilities::getModel('GoogleEvents');
       	$this->activityLogModel = Application_Service_Utilities::getModel('ActivityLog');
       	$this->userloggedin = new Zend_Session_Namespace('user');
       	$this->registryService = Application_Service_Registry::getInstance();
        $this->notificationsManager = Application_Service_NotificationsManager::getInstance();
       	$this->entitiesModel = Application_Service_Utilities::getModel('Entities');
       $this->activityModel = Application_Service_Utilities::getModel('Activity');
       $this->activityLogModel = Application_Service_Utilities::getModel('ActivityLog');
       $this->userloggedin = new Zend_Session_Namespace('user');
       $this->registryService = Application_Service_Registry::getInstance();
       $this->entitiesModel = Application_Service_Utilities::getModel('Entities');
       
    }


    public function indexAction()
    {
	$showCalendar = false;
	if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
	    $showCalendar = true;
	}
        $this->setDetailedSection('Lista rejestrów');

        $paginator = $this->activityModel->getList(["`status` = ?" => 0]);

        $this->view->paginator = $paginator;
        $this->view->curr_date = date_create(); // Current time and date
	$this->view->assign(compact('showCalendar'));

    }

    public function ajaxAddActivityAction()
    {
        $this->setDialogAction();
        $activity = $this->activityModel->getActivity("Types of Activity");

        $this->view->dialogTitle = "Schedule an activity";
        $this->view->activityTypes = $activity;
        $this->view->userlogged = $this->userloggedin->user->login;
    }

    public function ajaxEditActivityAction()
    {
        $req = $this->getRequest();
        $activityId = $req->getParam('id');
        $this->setDialogAction();

        $row = $this->activityModel->getFull([
          'id' => $activityId
        ], true);
        $activity = $this->activityModel->getActivity("Types of Activity");

        $this->view->data = $row;
        $this->view->userlogged = $this->userloggedin->user->login;
        $this->view->dialogTitle = "Edit activity ";
        $this->view->activityId =  $activityId;
        $this->view->activityTypes = $activity;
    }

    public function saveActivityAction()
    {
         $data = $this->getRequest()->getPost();
//push function call
	 $this->push($data);
	 
         $id = $this->activityModel->save($data['parameter']);
         $this->flashMessage('success', 'Aktywność zapisana');
         $this->redirect('activity/index');
    }
    
//push data to google api
    public function push(Array $data)
    {
//data to add to google
	    $startDate = $data['parameter']['date'];
	    $endDate = $data['parameter']['date'];

	    $startTime = $data['parameter']['time'];
	    
	    $breakDuration = explode(':',$data['parameter']['duration']);

	    $startTime = date("H:i:s", strtotime($startTime));

	    $endTime = date("H:i:s", strtotime("+".$breakDuration['0']." hours +".$breakDuration['1']." minutes", strtotime($startTime)));

	    $dataToModel['sdate'] = $startDate.'T'.$startTime;
	    $dataToModel['edate'] = $endDate.'T'.$endTime;
	    $dataToModel['summary'] = $data['parameter']['activity_name'];
	    $dataToModel['description'] = $data['parameter']['notes'];
	    $dataToModel['attendees'] = $data['attendees'];
	    $dataToModel['user_id'] = Application_Service_Authorization::getInstance()->getUserId();
	    $this->googleModel->save($dataToModel);

	//send notification
	    $this->notificationsManager->process([
		'type' => Application_Service_NotificationsManager::TYPE_TASK,
		'user_id' => $this->getParam('regid'),
                'title' => $data['parameter']['activity_name'],
                'text' => $data['parameter']['notes'],
		'attendees' => $data['attendees'],
	    ]);
		
	if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
	    $user_id = Application_Service_Authorization::getInstance()->getUserId();
	    $this->notificationsManager->update($user_id);
	}
	//EOF send nofication
	return 0;

    }
//EOF changes.


    public function updateAction()
    {
      $data = $this->getRequest()->getPost();
      $this->activityModel->update($data);
    }

    public function getosobyAction()
    {
        header("content-type:application/json");
        echo json_encode($this->activityModel->getOsobyData());
        die();
    }

    public function getactlistAction()
    {
        $registryname = $this->getParam('name');
        header("content-type:application/json");
        echo json_encode($this->activityModel->getActlistData($registryname));
        die();
    }

    public function ajaxSaveParamAction()
    {
        $data = $this->getRequest()->getPost();

        $result = $this->activityLogModel->save($data);

         $this->flashMessage('success', 'Aktywność zapisana');
         $this->redirect('activity/index');
    }

    public function ajaxAddParamAction()
    {
        $registryId = $this->getParam('regid');
        $actId = $this->getParam('actid');
        $this->setDialogAction();
        $this->setTemplate('ajax-param-form');
        //$registryId = 32;

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $this->view->paginator = $paginator;
        $this->view->registry = $registry;

        $this->view->actid = $actId;
        $this->view->dialogTitle = 'Add Registry to Activity';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function getRegistryValueAction()
    {
        $req = $this->getRequest();
        $regId = $req->getParam('regid');
       // die($regId);
        header("content-type:application/json");
        echo json_encode($this->activityModel->getRegistryEntitiesById($regId, 'Name'));
        die();
    }

    public function actlogAction()
    {
        $activityLog = $this->activityLogModel->getList();
        $this->view->paginator = $activityLog;
    }

	public function removeAction()
    {
        try {
            $this->getRepository()->getOperation()->operationBegin(Application_Service_Repository::OPERATION_IMPORTANT);

            $req = $this->getRequest();
            $id = $req->getParam('id', 0);
            $this->activityModel->remove($id);
            $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Element został poprawnie usunięty'));

            $this->getRepository()->getOperation()->operationComplete('activity.remove', $id);
        } catch (Exception $e) {
            $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Proba skasowania zakonczyla sie bledem', 'danger'));
        }

        $this->_redirect('/activity');
    }

}
