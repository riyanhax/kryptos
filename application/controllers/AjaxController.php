<?php
define('BASE_PATH', realpath(dirname(__DIR__)));
define('NEW_PATH', str_replace("application","",realpath(dirname(__DIR__))));
require NEW_PATH . '/vendor/autoload.php';

class AjaxController extends Muzyka_Admin
{
    /** @var Application_Service_Tasks */
    protected $tasksService;

    /** @var Application_Model_Messages */
    protected $messagesModel;

    /** @var Application_Service_Messages */
    protected $messagesService;

    public function init()
    {
        parent::init();

        $this->tasksService = Application_Service_Tasks::getInstance();
        $this->messagesModel = Application_Service_Utilities::getModel('Messages');
        $this->messagesService = Application_Service_Messages::getInstance();
    }

    public static function getPermissionsSettings() {
        $settings = array(
            'nodes' => array(
                'ajax' => array(
                    '_default' => array(
                        'permissions' => array(),
                    ),
                ),
            )
        );

        return $settings;
    }

    function getCalendarHomeEventsAction()
    {
        $userId = Application_Service_Authorization::getInstance()->getUserId();
        $tasks = Application_Service_Utilities::getModel('StorageTasks')->getList([
            'st.user_id = ?' => $userId,
            'st.status = 0'
        ]);

        $notes = Application_Service_Utilities::getModel('Notes')->getList();
	$results['tasks'][] = $tasks;
	$results['notes'][] = $notes;
//this call was not working properly.
       // $results = Application_Service_SharedUsers::getInstance()->apiCall('api/get-user-calendar');
        $calendarResults = [];
        foreach ($results['tasks'] as $result) {
            if (!empty($result)) {
                foreach ($result as $task) {
                    $deadlineDate = new DateTime($task['deadline_date']);

                    $task = [
                        'id' => sprintf('%s-%s-%s', $result['shared_app_id'], $result['shared_user_id'], $task['id']),
                        'url' => sprintf('/ajax/shared-open?%s', http_build_query([
                            'shared_user_id' => $result['shared_user_id'],
                            'url' => sprintf('/tasks-my/details/id/%s', $task['id']),
                        ])),
                        'title' => $task['title'],
                        'tooltip' => 'System: '. $result['shared_app_comment'] . '<br>' . $task['title'],
                        'class' => 'event-warning',
                        'end' => $deadlineDate->format('U') * 1000,
                        'start' => $deadlineDate->modify('-15 minutes')->format('U') * 1000,
                    ];

                    $calendarResults[] = $task;
                }
            }
	}
	foreach ($results['notes'] as $result) {
            if (!empty($result)) {
                foreach ($result as $note) {
                    $startDate = new DateTime($note['date_start']);
                    $endDate = new DateTime($note['date_end']);

                    $task = [
                        'id' => sprintf('%s-%s-%s', $result['shared_app_id'], $result['shared_user_id'], $note['id']),
                        'title' => $note['title'],
                        'tooltip' => 'System: '. $result['shared_app_comment'] . '<br>' . $note['title'],
                        'class' => 'event-info choose-from-dial',
                        'start' => $startDate->format('U') * 1000,
                        'end' => $endDate->format('U') * 1000,
                        'data' => [
                            'dial-url' => sprintf('/ajax/shared-open?%s', http_build_query([
                                'shared_user_id' => $result['shared_user_id'],
                                'url' => sprintf('/messages/ajax-view-calendar-note/id/%s', $note['id']),
                            ])),
                            'new-dialog' => 1,
                        ]
                    ];

                    $calendarResults[] = $task;
                }
            }
        }

        $this->outputJson(['success' => true, 'result' => $calendarResults]);
    }

    function sharedOpenAction()
    {
        $url = $this->getParam('url');
        $sharedUserId = $this->getParam('shared_user_id');

        if (!$sharedUserId) {
            $this->redirect($url);
        }

        $loginLink = Application_Service_SharedUsers::getInstance()->getLoginLink($sharedUserId);

        $loginLink .= '?url=' . $url;

        $this->redirect($loginLink);
    }

    function komunikatWidgetAction()
    {
        $komunikat = $this->messagesModel->findOneBy(array(
            'type = ?' => Application_Model_Messages::TYPE_KOMUNIKAT,
            'recipient_id = ?' => Application_Service_Authorization::getInstance()->getUserId(),
            'read_status = ?' => 0,
        ));

        if (!$komunikat) {
            echo 'NO_KOMUNIKAT';
            exit;
        }

        $this->disableLayout();
        $this->view->komunikat = $komunikat;
    }

    function komunikatAcceptAction()
    {
        $komunikat = $this->messagesModel->findOne($this->_getParam('id'));
        $komunikat->read_status = 1;
        $komunikat->save();

        exit;
    }
    
    public function watsonAction()
    {
        $request = $this->getRequest();
        $this->disableLayout();
        $logic = new Logic_Watson();
        
        if ($request->isXmlHttpRequest()) {
            $values = $request->getPost();
            $data = [
                'is_error' => false,
            ];
            
            try {
                $response = $logic->getResponse($values['message'], $values['context']);
                
                if ($response->isError()) {
                    throw new Exception($response->getErrorMessage());
                }
                
                $data['message'] = $response->getText();
                $data['context'] = $response->getContext();
            } catch (Exception $e) {
                $data['is_error'] = true;
                $data['error'] = $e->getMessage();
            }
            
            echo json_encode($data);
        }
        
        exit;
    }
    
    /**
	* @checkUserame is unique 
	*
	* This function will take posted variable and check that User name is aready availabel or not. 
	**/
    
	public function checkUseremailAction(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		/** @var Application_Model_Osoby $osobyModel */
                 $osobyModel = Application_Service_Utilities::getModel('Osoby');
		 $req = $this->getRequest();
                 $email = $req->getParam('email');
		 $userRecord = $osobyModel->getUserByEmail($email);
                if(!empty($userRecord)){
			$status=$userRecord->id;
		}else{
			$status=0;
		}
                $this->outputJson(array('status'=>$status));
	}
}