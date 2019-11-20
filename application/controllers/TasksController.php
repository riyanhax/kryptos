<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

define('BASE_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require BASE_PATH . '/vendor/autoload.php';

class TasksController extends Muzyka_Admin
{
    /** @var Application_Model_Tasks */
    protected $tasksModel;
    /** @var Application_Service_Tasks */
    protected $tasksService;
    /** @var Application_Model_StorageTasks */
    protected $storageTasksModel;
    /** @var Application_Model_Documenttemplates */
    protected $documenttemplates;
    /** @var Application_Model_DocumentsVersioned */
    protected $documentsVersioned;
    /** @var Application_Model_Courses */
    protected $courses;
    /** @var Application_Model_Osoby */
    protected $osoby;
     /** @var Application_Model_Zbiory */
    protected $zbiory;
    /** @var Application_Model_Registry */
    protected $registries;

    protected $googleModel;

    protected $notificationsManager;


    protected $baseUrl = '/tasks';

    protected $requestedTaskData;

    public function init()
    {
        parent::init();
        $this->tasksModel = Application_Service_Utilities::getModel('Tasks');
        $this->tasksService = Application_Service_Tasks::getInstance();
        $this->storageTasksModel = Application_Service_Utilities::getModel('StorageTasks');
        $this->documenttemplates = Application_Service_Utilities::getModel('Documenttemplates');
        $this->documentsVersioned = Application_Service_Utilities::getModel('DocumentsVersioned');
        $this->courses = Application_Service_Utilities::getModel('Courses');
        $this->osoby = Application_Service_Utilities::getModel('Osoby');

	    $this->googleModel = Application_Service_Utilities::getModel('GoogleEvents');
        $this->notificationsManager = Application_Service_NotificationsManager::getInstance();
        $this->zbiory = Application_Service_Utilities::getModel('Zbiory');
        $this->registries = Application_Service_Utilities::getModel('Registry');

        Zend_Layout::getMvcInstance()->assign('section', 'Zadania');

        $this->view->assign(array(
            'section' => 'Zadania',
            'baseUrl' => $this->baseUrl,
        ));
    }

    public static function getPermissionsSettings() {
        $baseIssetCheck = [
            'function' => 'issetAccess',
            'params' => ['id'],
            'permissions' => [
                1 => ['perm/tasks/create'],
                2 => ['perm/tasks/update'],
            ],
        ];

        $settings = [
            'modules' => [
                'tasks' => [
                    'label' => 'Zadania',
                    'permissions' => [
                        [
                            'id' => 'create',
                            'label' => 'Tworzenie wpisów',
                        ],
                        [
                            'id' => 'update',
                            'label' => 'Edycja wpisów',
                        ],
                        [
                            'id' => 'remove',
                            'label' => 'Usuwanie wpisów',
                        ],
                    ],
                ],
            ],
            'nodes' => [
                'tasks' => [
                    '_default' => [
                        'permissions' => ['user/superadmin'],
                    ],

                    'mini-add-storage-task' => [
                        'permissions' => ['user/anyone'],
                    ],

                    'index' => [
                        'permissions' => ['perm/tasks'],
                    ],
                    'report' => [
                        'permissions' => ['perm/tasks'],
                    ],
                    'mini-preview' => [
                        'permissions' => ['perm/tasks'],
                    ],
                    'storage-tasks' => [
                        'permissions' => ['perm/tasks'],
                    ],

                    'update' => [
                        'getPermissions' => [$baseIssetCheck],
                    ],
                    'save' => [
                        'getPermissions' => [$baseIssetCheck],
                    ],

                    'del' => [
                        'permissions' => ['perm/tasks/remove'],
                    ],
                    'delchecked' => [
                        'permissions' => ['perm/tasks/remove'],
                    ],

                    'create-documents-versioned-task' => [
                        'permissions' => ['perm/tasks/create'],
                    ],

                ],
            ]
        ];

        return $settings;
    }

    public function indexAction()
    {
        $showCalendar = false;
        
        if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
            $showCalendar = true;
        }

        $req = $this->getRequest();
        $search = $req->getParam('search', array());
        $search['not_system'] = 1;

        $paginator = $this->tasksModel->getAll($search);

        $this->view->assign(array(
            'paginator' => $paginator,
            'get' => $_GET,
            'l_list' => http_build_query($_GET),
            'taskTypes' => $this->tasksService->getTypes(),
            'taskTriggerTypes' => $this->tasksService->getTriggerTypes(),
            'search' => $search,
	    'showCalendar' => $showCalendar,
        ));
    }

    public function sendhurrymessageAction() 
    {
        $data = $this->getRequest()->getParams();
        $users = explode(',', $data['users']);
        $deadlines = explode(',', $data['deadlines']);
        $title = $data['title'];        
        $emailSender = Application_Service_Email::GetInstance();
        $smsSender = Application_Service_SMS::GetInstance();
        $success = true;

        for($i = 0; $i < count($users); $i++) {
            $templateData = [];
            $user = $this->osoby->getOne(['o.id = ?' => $users[$i]]);
            $templateData['user'] = $user;
            $templateData['title'] = $title;
            $templateData['deadline'] = $deadlines[$i];

            $mailData = [];
            $templateFile = 'notifications/templates/email/task_hurry.html';            
            $mailData['text'] = Application_Service_Utilities::renderView($templateFile, $templateData);            
            $mailData['sender_id'] = 1;
            $mailData['recipient_address'] = $user['email'];
            $mailData['title'] = 'Przypomnienie o terminie zadania';

            $smsData = [];
            $templateFile = 'notifications/templates/sms/task_hurry.html';            
            $smsData['text'] = Application_Service_Utilities::renderView($templateFile, $templateData);            
            $smsData['sender_id'] = 1;
            $smsData['recipient_address'] = $user['telefon_komorkowy'];
            $smsData['title'] = 'Przypomnienie o terminie zadania';

            try {
                $emailSender->send($mailData);
                $smsSender->send($smsData);
            } catch (Exception $e) {
                $success = false;
            }
        }
        
        $this->outputJson([
            'status' => (int) $success
        ]);
    }
    public function updateAction()
    {
      
        $req = $this->getRequest();
        $id = $req->getParam('id', 0);
        $data = array(
            'status' => 1,
            'users_type' => 1,
        );

        if ($id) {
            $data = $this->tasksModel->getFull($id);
            if (empty($data)) {
                throw new Exception('Podany rekord nie istnieje');
            }
        } else {
            if ($this->requestedTaskData) {
                $data = array_merge($data, $this->requestedTaskData);
            }
        }
         
        $osoby = $this->osoby->getAllForTypeahead();
        $this->utf8_encode_deep($osoby);
        // comagom code start 2019.3.25
        $documentTemplatesInfo = $this->documenttemplates->getAllForTypeahead(['active = ?' => 1]);
        $documentTemplatesIds = array();
        foreach($documentTemplatesInfo as $key => $value){
            $documentTemplatesIds[$value['id']] = $value;
        }
        $allTasks = $this->tasksModel->getAll();
        $allTasksDocumentTemplateIds = array();
        foreach($allTasks as $key => $value) {
            $allTasksDocumentTemplateIds[$value['object_id']] = $value;
        }
        foreach ($documentTemplatesIds as $k => $value) {
            if ($allTasksDocumentTemplateIds[$k]) {
                unset($documentTemplatesIds[$k]);
            }
        }
        $completedDocumentTemplatesData = array();
        foreach($documentTemplatesIds as $key => $value) {
            $completedDocumentTemplatesData[] = $value;
        }
        // comagom code end
        $this->view->assign(array(
            'data' => $data,
            'taskTypes' => $this->tasksService->getTaskTypes(),
            'taskTriggerTypes' => $this->tasksService->getTriggerTypes(),
            'osoby' => $osoby,
            'osobyList' => $this->osoby->getAll(),
            'sets' => $this->zbiory->getAllForTypeahead(),
            'documenttemplates' => $completedDocumentTemplatesData,
            'documentsVersioned' => $this->documentsVersioned->getAllForTypeahead(),
            'courses' => $this->courses->getAllForTypeahead(),
            'registries' => $this->registries->getAllForTypeahead(),
            'tasks' => $this->tasksModel->getAllForTypeahead(),
      ));
    }

    protected function utf8_encode_deep(&$input) {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                $this->utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                $this->utf8_encode_deep($input->$var);
            }
        }
    }

    public function saveAction()
    {
        $logic = new Logic_Tasks();
        
        try {
            $this->db->beginTransaction();

            $req = $this->getRequest();
            $params = $req->getParams();
         /*  echo "<pre>";
           print_r($params);
           die;*/
            if($params['users_type'] == 2) {
                foreach ($params['task_users'] as $userId => $isSelected) {
                    if ($isSelected === '0') {
                        $params['task_users'][$userId] = 1;
                    }
                }  
            }
            if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {             
    	       $this->push($params);
            }
            
            /*  echo "<pre>";
           print_r($params);
           die;*/

            //unset($params['mail_ids']); 
            $this->tasksService->create($params);
            
            if (!empty($params['task_users'])) {
                foreach ($params['task_users'] as $userId => $isSelected) {
                    if ($isSelected === '0') {
                       
                      $this->storageTasksModel->delete(array('task_id = ?' => $params['id'], 'user_id = ?' => $userId));
                    }
                }
            }
            
            $notificationService = Application_Service_Notifications::getInstance();
            $notificationService->processAllNotifications();
            
            $notificationsService = Application_Service_NotificationsServer::getInstance();
            $notificationsService->sendAllNotifications();

            $this->getFlash()->addMessage($this->showMessage('Zmiany zostały poprawnie zapisane'));

            $this->db->commit();
        } catch(Application_SubscriptionOverLimitException $x){
            $this->_redirect('subscription/limit');
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Proba zapisu danych nie powiodla sie', null, $e);
        }
        //for test
        
        // $notificationModal = Application_Service_Utilities::getModel('Notifications');;
        // echo 'notification:' . count($notificationModal->getList([])); 
        
        if ($req->getParams()['addAnother'] == 1) {
            $this->_redirect($this->baseUrl . '/update');
        } else {
            $this->_redirect($this->baseUrl);
        }
    }

    public function push(Array $data)
    {
//data to add to google

	$trimDate = explode("\"",$data['trigger_mode_1']['date']);

            $startDate = $trimDate['0'];
	    $endDate = $startDate;

	    $startTime = date("H:i:s", strtotime("09:00:00"));
	    $endTime = date("H:i:s",strtotime("09:00:00"));

	    $dataToModel['sdate'] = $startDate.'T'.$startTime;
	    $dataToModel['edate'] = $endDate.'T'.$endTime;
	    $dataToModel['summary'] = $data['title'];
	    $dataToModel['description'] = $data['message_template'];
	    $dataToModel['attendees'] = $data['attendees'];
	    $dataToModel['user_id'] = Application_Service_Authorization::getInstance()->getUserId();
	    $this->googleModel->save($dataToModel);

	//send notification
	    $this->notificationsManager->process([
		'type' => Application_Service_NotificationsManager::TYPE_TASK,
		'user_id' => $this->getParam('regid'),
                'title' => $data['parameter']['activity_name'],
                'text' => $data['parameter']['notes'],
		'attendees' => $attendeesEmail,
	    ]);
		
	if (isset($_SESSION['g_calendar_access_token']) && !empty($_SESSION['g_calendar_access_token'])) {
	    $user_id = Application_Service_Authorization::getInstance()->getUserId();
	    $this->notificationsManager->update($user_id);
	}
	//EOF send nofication
	return 0;
    }
//EOF changes.

    public function delAction()
    {
        $this->forceKodoOrAbi();
        try {
            $req = $this->getRequest();
            $id = $req->getParam('id', 0);
            $task = $this->tasksModel->requestObject($id);
            $task->status = 0;
            $task->save();
            $this->getFlash()->addMessage($this->showMessage('Zmiany zostały poprawnie zapisane'));
        } catch (Exception $e) {
            $this->getFlash()->addMessage($this->showMessage('Proba skasowania zakonczyla sie bledem', 'danger'));
        }

        $this->_redirect($this->baseUrl);
    }

    public function delcheckedAction()
    {
        $this->forceKodoOrAbi();
        foreach ($_POST['id'] AS $id) {
            if ($id > 0) {
                try {
                    $task = $this->tasksModel->requestObject($id);
                    $task->status = 0;
                    $task->save();
                } catch (Exception $e) {
                }
            }
        }

        $this->_redirect($this->baseUrl);
    }

    public function storageTasksAction()
    {
        $taskId = $this->getRequest()->getParam('id');
        $search = $this->getRequest()->getParam('search');

        $task = $this->tasksModel->getFull($taskId);
        Zend_Layout::getMvcInstance()->assign('section', $task['title']);

        $paginator = $this->storageTasksModel->getAll(array('task_id' => $taskId));

        $this->view->paginator = $paginator;
        $this->view->get = $_GET;
        $this->view->task = $task;
        $this->view->l_list = http_build_query($_GET);
        $this->view->taskTypes = $this->tasksService->getTypes();
        $this->view->taskTriggerTypes = $this->tasksService->getTriggerTypes();
        $this->view->search = $search;
    }

    public function storageTasksBulkAction()
    {
        $rowsAction = $this->_getParam('rowsAction');

        switch ($rowsAction) {
            case "remove":
                $this->forward('storage-tasks-remove-selected');
                break;
        }

    }

    public function storageTasksRemoveAction()
    {
        $taskId = $this->_getParam('id');
        $storageTaskId = $this->_getParam('storage_task_id');

        $object = $this->storageTasksModel->getOne(['st.task_id = ?' => $taskId, 'st.id = ?' => $storageTaskId], true);
        $this->storageTasksModel->remove($object['id']);

        $this->_redirect('/tasks/storage-tasks/id/' . $taskId);
    }

    public function storageTasksRemoveSelectedAction()
    {
        $taskId = $this->_getParam('id');
        $ids = array_keys(Application_Service_Utilities::removeEmptyValues($this->_getParam('rows')));

        $objects = $this->storageTasksModel->getList(['st.task_id = ?' => $taskId, 'st.id IN (?)' => $ids]);
        foreach ($objects as $object) {
            $this->storageTasksModel->remove($object['id']);
        }

        $this->_redirect('/tasks/storage-tasks/id/' . $taskId);
    }

    public function createDocumentsVersionedTaskAction()
    {
        $documentsVersionedModel = Application_Service_Utilities::getModel('DocumentsVersioned');
        $documentId = $this->_getParam('task-id');

        $document = $documentsVersionedModel->requestObject($documentId);

        $this->requestedTaskData = array(
            'object_id' => $documentId,
            'type' => Application_Service_Tasks::TYPE_DOCUMENT_VERSIONED,
            'trigger_type' => Application_Service_Tasks::TRIGGER_TYPE_SINGLE_IMMEDIATELY,
            'activate_before_days' => 7,
            'trigger_config_data' => array(
                'day' => 7,
            ),
            'title' => 'Zapoznaj się z dokumentem: ' . $document->title,
            'author_osoba_id' => Application_Service_Authorization::getInstance()->getUserId(),
        );

        $this->setTemplate('update');
        $this->updateAction();
    }

    public function miniAddAction()
    {
        $this->view->ajaxModal = 1;
        $search['not_system'] = 1;
        $this->view->records = $this->tasksModel->getAll($search);
    }
    
    public function checkDocumentsAction()
    {
        $req = $this->getRequest();
        $params = $req->getParams();

        if($params['type'] == Application_Service_Tasks::TYPE_DOCUMENT) {
            if (!empty($params['task_users']) && $params['users_type'] == 2) {
                foreach ($params['task_users'] as $userId => $isSelected) {
                    if ($isSelected === '1') {
                        $usersIds[] = $userId;
                    }
                }
            }else{
                if(empty($params['id']))
                    $usersIds = $this->tasksModel->findAllUsersWithoutTask(0);
                else
                    $usersIds = $this->tasksModel->getUsersWithoutTask($params);;
            }
                $documentsModel = Application_Service_Utilities::getModel('Documents');
                $osobyModel = Application_Service_Utilities::getModel('Osoby');
                $workerIds = [];
                foreach($usersIds as $userId) {
                    if($osobyModel->getWorkerIdFromUserId($userId) != null) {
                        $workerIds[] = $osobyModel->getWorkerIdFromUserId($userId);
                    }
                }
                $documents = $documentsModel->getList(array(
                    'd.worker_id IN (?)' => $workerIds,
                    'd.documenttemplate_id = ?' => $params['object_id'],
                    'd.active = ?' => Application_Service_Documents::VERSION_OBLIGATORY,
                ));
            if(empty($documents))
            {
                if(empty($params['id']))
                {
                    //$this->getFlash()->addMessage($this->showMessage('Brak dostępnych dokumentów do przypisania', 'danger'));
                    //$this->db->rollBack();
                    //$this->_redirect($this->baseUrl . '/update');
                    echo 0;
                    exit;
                }else{
                    //$this->getFlash()->addMessage($this->showMessage('Brak dostępnych dokumentów do przypisania', 'danger'));
                    echo 0;
                    exit;
                }
            }
            echo 1;
            exit;
        }
    }
}
