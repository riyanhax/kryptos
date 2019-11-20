<?php
class RegistryController extends Muzyka_Admin
{
    /** @var Application_Model_Registry */
    protected $registryModel;
    /** @var Application_Model_Osoby */
    protected $osobyModel;
    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;
    /** @var Application_Model_Entities */
    protected $entitiesModel;
    /** @var Application_Service_Registry */
    protected $registryService;
     /** @var Application_Model_Accordions */
	protected $accordionModel;
	/** @var Application_Model_RegistryEntities */
	protected $registryEntitites;
    protected $registryActionModel;
	protected $triggerModel;
     /** @var Application_Service_Entities */
     protected $serviceModel;
  /** @var Application_Model_RegistryEntities */
  protected $menuModel;
  /** @var Application_Model_RegistryEntities */
    protected $registryFilterModel;
    /** @var Application_Model_RegistryEntities */
    protected $registrymenuModel;

    protected $baseUrl = '/registry';
	public static $logFile;

    public function init()
    {
        parent::init();

        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
        $this->entitiesModel = Application_Service_Utilities::getModel('Entities');
        $this->registryEntitites = Application_Service_Utilities::getModel('RegistryEntities');
        $this->registryActionModel = Application_Service_Utilities::getModel('RegistryAction');
         $this->registryFilterModel = Application_Service_Utilities::getModel('RegistryFilter');
        $this->registryService = Application_Service_Registry::getInstance();
        $this->osobyModel = Application_Service_Utilities::getModel('Osoby');
        $this->menuModel = Application_Service_Utilities::getModel('Menu');
        $this->registrymenuModel = Application_Service_Utilities::getModel('RegistryMenu');
        $this->triggerModel = Application_Service_Utilities::getModel('Trigger');
        $this->serviceModel = Application_Service_Entities::getInstance();
		RegistryController::$logFile = ROOT_PATH . '/logs/registry_log.log';


        $registryId = $this->getParam('registry_id', 0);
  
        if (!empty($registryId)) {
            $data=$this->registrymenuModel->getlabel($registryId );
            $label=$data['label'];
        }

        Zend_Layout::getMvcInstance()->assign('section', $label);

        $this->view->baseUrl = $this->baseUrl;
    }

	private function savelog($login, $type = 'registry')
    {
        $log = time() . "||$type||$login\n";

        file_put_contents(UserController::$logFile, $log, FILE_APPEND | LOCK_EX);
    }

    public static function getPermissionsSettings() {
        $readPermissionsResolverById = array(
            'function' => 'registryAccessById',
            'params' => array('id'),
            'permissions' => array(
                -1 => ['perm/registry/all-access'],
                0 => ['perm/registry/all-access'],
                1 => ['user/anyone'],
            ),
        );
        $readPermissionsResolverByRegistryId = $readPermissionsResolverById;
        $readPermissionsResolverByRegistryId['params'] = array('id');

        $writePermissionsResolverById = $readPermissionsResolverById;
        $writePermissionsResolverById['manualParams'][2] = 'write';
        $writePermissionsResolverByRegistryId = $writePermissionsResolverById;
        $writePermissionsResolverByRegistryId['params'] = array('id');

        $adminPermissionsResolverById = $readPermissionsResolverById;
        $adminPermissionsResolverById['manualParams'][2] = 'admin';
        $adminPermissionsResolverByRegistryId = $adminPermissionsResolverById;
        $adminPermissionsResolverByRegistryId['params'] = array('id');

        $permissionsResolverBase = array(
            'function' => 'registryAccessBase',
            'permissions' => array(
                0 => ['perm/registry/all-access'],
                1 => ['user/anyone'],
            ),
        );

        $settings = array(
            'modules' => [
                'registry' => [
                    'label' => 'Rejestry',
                    'permissions' => [
                        array(
                            'id' => 'all-access',
                            'label' => 'Dostęp do wszystkich wpisów',
                        ),
                    ],
                ],
            ],
            'nodes' => array(
                'registry' => array(
                    '_default' => array(
                        'getPermissions' => array(),
                    ),

                    'index' => [
                        'permissions' => ['perm/registry'],
                    ],

                    'update' => [
                        'getPermissions' => [$permissionsResolverBase, $adminPermissionsResolverById],
                    ],
                    'save' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'remove' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-add-param' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-edit-param' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-save-param' => [
                        'getPermissions' => [$adminPermissionsResolverByRegistryId],
                    ],
                    'ajax-remove-param' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-order-up-param' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-order-down-param' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],

                    'ajax-update' => [
                        'getPermissions' => [$permissionsResolverBase, $adminPermissionsResolverById],
                    ],
                    'ajax-add-assignee' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                    'ajax-remove-assignee' => [
                        'getPermissions' => [$adminPermissionsResolverById],
                    ],
                ),
            )
        );

        return $settings;
    }

    public function indexAction()
    {
        $menuId = $this->getRequest()->getParam('id');

        $this->setDetailedSection('Lista rejestrów');

        $user = Application_Service_Authorization::getInstance()->getUser();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        $conditions = array();
        $registries = [];
        $isAdmin = '0';
        if(Application_Service_Authorization::isSuperAdmin()){
            $isAdmin = '1';
        }else{
          
            if($user['rightsPermissions'])
            {
                $entries = json_decode($user['rightsPermissions'], true);

                foreach ($entries as $key => $perm) {
                    if($entries[$key])
                    {
                        $end = end(explode("/", $key));
                        if(is_numeric($end))
                            $registries[] = $end;
                    }
                }
            }
                    
            //$conditions["author_id = $user_id OR id IN(?)"] = implode(',', array_unique($registries));
            $conditions["id IN(?)"] = array_unique($registries);
            
        }

        $paginator = $this->registryModel->getList($conditions);
        $this->registryModel->loadData('author', $paginator);

        $this->view->user_id = $user_id;
        $this->view->isAdmin = $isAdmin;
        $this->view->data = $entries;
        $this->view->paginator = $paginator;
        $this->view->menuId = $menuId;
    }
    public function ajaxSaveFilterAction()
    {
        $filterCondition = array();
      //  print_r($_POST);
      //  die('here');
        if(isset($_POST['regtype']) && sizeof($_POST['regtype']) > 0 )
        {

            $con = 0;
            $da = 0;
            foreach($_POST['regtype'] as $k => $parameterEntityId )
            {
               
                
                $filterCondition[$k]['parameter_id'] = $parameterEntityId;

                $conditionFieldName = 'condition_for';
                    if($parameterEntityId == 9) {
                          $filterCondition[$k]['keyword'] = array("from"=>$_POST['from'][$da],"to"=>$_POST['to'][$da]);
                    $da++;
                    
                    }else {
                          if(isset($_POST[$conditionFieldName][$con]))
                            {
                                $filterCondition[$k]['condition'] = $_POST[$conditionFieldName][$con];
                            }

                            $keyword = 'keyword';
                            if(isset($_POST[$keyword][$con]))
                            {
                                $filterCondition[$k]['keyword'] = $_POST[$keyword][$con];
                            }
                            $con++;

                    }
                    
                   
             
            }
             
          //  print_r($filterCondition);
            
            $userId = $this->userSession->user->id;
            $regId = $_POST['reg_id'];
            $filter_name = trim($_POST["filter_name"]);
            $filter_scope = trim($_POST["filter_scope"]);

            if(isset($_POST['hidFilterId']) && $_POST['hidFilterId']!=""){
                $updateSql = 'UPDATE registry_filters SET meta_content = '."'".serialize($filterCondition)."' ".'WHERE id='.$_POST['hidFilterId'];
                $this->db->query($updateSql);
            } else {

                $insertSql = 'INSERT INTO registry_filters SET
                user_id = '.$userId. ',
                meta_content = '."'".serialize($filterCondition)."'". ' ,
                filter_name = '."'".$filter_name."'".',
                filter_score = '."'".$filter_scope."'".',
                created_at = '."'".date('Y-m-d :h:i:s')."'".',
                filter_table = "audit"';
                $this->db->query($insertSql);
            }
        }
        exit();
    }
    public function ajaxDeleteFilterAction()
    {
        if(isset($_POST['filter_id']))
        {
            $filter_id = trim($_POST["filter_id"]);
            $sql = 'DELETE FROM registry_filters WHERE
            id = '.$_POST['filter_id'];
            $this->db->query($sql);
        }
        exit();
    }
    public function auditAction() {
       //   echo'sdfsf';die;
         $registryId = $this->getParam('registry_id', 0);
       
        $column_name = array( 1=>'Nazwa Użytkownika', 3=>'polecenie listu', 4=>'działanie', 6=>'Wiersz / pole', 7=>'Poprzednia wartość', 8=>'Nowa wartość', 9=>'Czas');
 
       $column_name_db = array( 1=>'user_id_value', 2=>'module_type', 3=>'module_id_value', 4=>'action', 5=>'action_on', 6=>'action_name', 7=>'previous_value', 8=>'new_value', 9=>'insert_date');
        /*===start filter data===*/

        $filterId = $this->getParam('filter_id', 0);
        
        if(isset($filterId) && $filterId > 0)
        {
            $result_registry_action = array();
            $conditions = array();
            $filter_arr = $this->registryFilterModel->getOne($filterId);

            $conditions_arr = unserialize($filter_arr['meta_content']);
           
            
            if(isset($conditions_arr) && sizeof($conditions_arr) > 0)
            {
                $condition_stmt = array();
                
                foreach($conditions_arr as $condition_detail)
                {

                    if(isset($condition_detail['parameter_id']) && $condition_detail['parameter_id'] > 0 )
                    {
                        
                        $keyword = $condition_detail['keyword'];
                        $condition = trim($condition_detail['condition']);
                        
                    //    $entityId = trim($condition_detail['entity_id']);
                        switch ($condition) {
                            case 'equal':
                                $str = " = ";
                                break;
                            case 'not-equal':
                                $str = " != ";
                                break;
                            case 'empty':
                                $str = " = ";
                                $keyword = "";
                                break;
                            case 'like':
                                $str = " LIKE ";
                                $keyword = "%".$keyword."%";
                                break;
                            case 'not-like':
                                $str = " NOT LIKE ";
                                $keyword = "%".$keyword."%";
                                break;
                            case 'start_with':
                                $str = " LIKE ";
                                $keyword = $keyword."%";
                                break;
                            case 'not_start_with':
                                $str = " NOT LIKE ";
                                $keyword = $keyword."%";
                                break;
                            case 'end_with':
                                $str = " LIKE ";
                                $keyword = "%".$keyword;
                                break;
                            case 'not_end_with':
                                $str =  " NOT LIKE ";
                                $keyword = "%".$keyword;
                                break;
                        }
                        
                        if($condition_detail['parameter_id'] == 9) {
                            $from = $keyword['from'];
                            $to = $keyword['to'];
                           
                              $start_str = " >= ";
                              $end_str = " <= ";
                              $insertSql = "SELECT * FROM registry_action where ".$column_name_db[$condition_detail['parameter_id']] ."". $start_str ."'". date("Y-m-d", strtotime($from)) ." 00:00:00 '". " and ". $column_name_db[$condition_detail['parameter_id']] ."" .$end_str. "'" .date("Y-m-d", strtotime($to)). " 23:59:59 ' and module_id = ".$registryId;
                              
                               
                        }else {
                            $insertSql = "SELECT * FROM registry_action where ".$column_name_db[$condition_detail['parameter_id']]."".$str."'".$keyword."' and module_id = ".$registryId; 
                               
                        }
                        $result_registry = $this->db->query($insertSql);
                        
                        $result_registry_action[] = $result_registry->fetchAll();
                        
                       
                    }
               }  
            }
        }
     //  print_r($query1);
       // die('he');
        $result = $this->registryModel->getRegistryById($registryId);
        $registry = $this->registryModel->getFull($registryId, true);
        $key_count = 0;
        foreach($result_registry_action as $value) {
        
            foreach($value as $key_value =>$f_value) {
                $filter_result[$key_count] = $f_value;
                $filter_result[$key_count]['registry_title'] = $result['title'];
                $user_name =  $this->osobyModel->requestObject($f_value['user_id']);
                $filter_result[$key_count]['f_name'] = $user_name['imie'];
                $filter_result[$key_count]['l_name'] = $user_name['nazwisko'];
              
                if($filter_result[$key_count]['action'] == 'wstaw wiersz'){

                    $element_keys = json_decode($filter_result[$key_count]['action_name']);
                    $element_values = json_decode($filter_result[$key_count]['new_value']);
                    $filter_result[$key_count]['action_name'] = '';
                    foreach($element_keys as $key_ => $element_key) {
                        foreach($registry->entities as $val) {
                            if($val->id == $element_key) {
                                foreach($element_values as $value_key => $element_val) {
                                    if($value_key == $key_)
                                        $filter_result[$key_count]['action_name'] .= $val->title . ' : ' . $element_val. ',' ;
                                }
                             
                            }
                        } 
                    }
                    $filter_result[$key_count]['new_value'] = '';
                }
               
                if($f_value['record_id'] == 0) {
                    if($f_value['module_type'] == 'RegistryMultiFormBuilder'){
                        $filter_result[$key_count]['link'] = "https://$_SERVER[HTTP_HOST]/registry-multi-form-builder/index/id/".$registryId;
                    }else{
                        $filter_result[$key_count]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/index/registry_id/".$registryId;
                    }
                }else {
                $filter_result[$key_count]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/update/id/". $f_value['record_id']."/registry_id/".$registryId;
                }
                $key_count++;
            }
        }
      
        $filters = $this->registryFilterModel->fetchAll();
        /*===start filter data====*/
        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        
        $data = $this->registryActionModel->getAllAuditAction('module_id', $registryId);
        $entityResult = array();
        
        foreach ($data as $key => $value) {  
            $user_name =  $this->osobyModel->requestObject($value['user_id']);
            $entityResult[$key]['id'] = $value['id'];
            $entityResult[$key]['f_name'] = $user_name['imie'];
            $entityResult[$key]['l_name'] = $user_name['nazwisko'];
            $entityResult[$key]['registry_title'] = $result['title'];
            $entityResult[$key]['module_type'] = $value['module_type'];
            $entityResult[$key]['action'] = $value['action'];
            $entityResult[$key]['action_on'] = $value['action_on'];
             if($value['action'] == 'wstaw wiersz'){
                $element_keys = json_decode($value['action_name']);
                $element_values = json_decode($value['new_value']);
                foreach($element_keys as $key_ => $element_key) {
                    foreach($registry->entities as $val) {
                        if($val->id == $element_key) {
                            foreach($element_values as $value_key => $element_val) {
                                if($value_key == $key_)
                                    $entityResult[$key]['action_name'] .= $val->title . ' : ' . $element_val. ',' ;
                            }
                         
                        }
                    } 
                }
                $entityResult[$key]['new_value'] = '';
            } else{
               $entityResult[$key]['action_name'] = $value['action_name'];
                $entityResult[$key]['new_value'] = $value['new_value'];  
            }
            $entityResult[$key]['previous_value'] = $value['previous_value'];
           
            $entityResult[$key]['insert_date'] = $value['insert_date'];
            if($value['record_id'] == 0) {
                if($value['module_type'] == 'RegistryMultiFormBuilder'){
                    $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-multi-form-builder/index/id/".$registryId;
                }else{
                    $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/index/registry_id/".$registryId;
                }
            }else {
                $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/update/id/". $value['record_id']."/registry_id/".$registryId;
            }
        }
        $data_log = $this->registryActionModel->getAllLogAction('module_id', $registryId);
        $entityResultLog = array();
       
        foreach ($data_log as $key => $value) {
            
            $user_name = $this->osobyModel->requestObject($value['user_id']);
            $entityResultLog[$key]['id'] = $value['id'];
            $entityResultLog[$key]['f_name'] = $user_name['imie'];
            $entityResultLog[$key]['l_name'] = $user_name['nazwisko'];
            $entityResultLog[$key]['registry_title'] = $result['title'];
            $entityResultLog[$key]['module_type'] = $value['module_type'];
            $entityResultLog[$key]['action'] = $value['action'];
            $entityResultLog[$key]['action_on'] = $value['action_on'];
            if($value['action'] == 'wstaw wiersz'){
                $element_keys = json_decode($value['action_name']);
                $element_values = json_decode($value['new_value']);
                foreach($element_keys as $key_ => $element_key) {
                    foreach($registry->entities as $val) {
                        if($val->id == $element_key) {
                            foreach($element_values as $value_key => $element_val) {
                                if($value_key == $key_)
                                    $entityResultLog[$key]['action_name'] .= $val->title . ' : ' . $element_val. ',' ;
                            }
                         
                        }
                    } 
                }
                
            } else{
               $entityResultLog[$key]['action_name'] = $value['action_name'];  
               $entityResultLog[$key]['new_value'] = $value['new_value'];
            }
           
            $entityResultLog[$key]['previous_value'] = $value['previous_value'];
             
            $entityResultLog[$key]['insert_date'] = date("F j, Y, g:i a", strtotime($value['insert_date']));
            if($value['record_id'] == 0) {
             
                if($value['module_type'] == 'RegistryMultiFormBuilder'){
                    $entityResultLog[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-multi-form-builder/index/id/".$registryId;
                }else{
                    $entityResultLog[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/index/registry_id/".$registryId;
                }
            }else {
                $entityResultLog[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/update/id/". $value['record_id']."/registry_id/".$registryId;
            }
             
        }

        $this->view->registryId = $registryId;
        if($filterId != ""){
            $this->view->logAction = $filter_result;
        }else {
            $this->view->logAction = $entityResultLog;
        }
        if($filterId != ""){
            $this->view->registryAction = $filter_result;
        }else {
            $this->view->registryAction = $entityResult;
        }
        $this->view->column_name = $column_name;
        $this->view->filters = $filters; //$result_registry_action;
       

    }
    
    public function logAction() {
        $registryId = $this->getParam('registry_id', 0); 
       
        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $data = $this->registryActionModel->getAllLogAction('module_id', $registryId);
       
        $result = $this->registryModel->getRegistryById($registryId);
        $registry = $this->registryModel->getFull($registryId, true);
      
        $entityResult = array();
       
        foreach ($data as $key => $value) {
            
            $user_name = $this->osobyModel->requestObject($value['user_id']);
            $entityResult[$key]['id'] = $value['id'];
            $entityResult[$key]['f_name'] = $user_name['imie'];
            $entityResult[$key]['l_name'] = $user_name['nazwisko'];
            $entityResult[$key]['registry_title'] = $result['title'];
            $entityResult[$key]['module_type'] = $value['module_type'];
            $entityResult[$key]['action'] = $value['action'];
            $entityResult[$key]['action_on'] = $value['action_on'];
            if($value['action'] == 'wstaw wiersz'){
                $element_keys = json_decode($value['action_name']);
                $element_values = json_decode($value['new_value']);
                foreach($element_keys as $key_ => $element_key) {
                    foreach($registry->entities as $val) {
                        if($val->id == $element_key) {
                            foreach($element_values as $value_key => $element_val) {
                                if($value_key == $key_)
                                    $entityResult[$key]['action_name'] .= $val->title . ' : ' . $element_val. ',' ;
                            }
                         
                        }
                    } 
                }
                
            } else{
               $entityResult[$key]['action_name'] = $value['action_name'];  
               $entityResult[$key]['new_value'] = $value['new_value'];
            }
           
            $entityResult[$key]['previous_value'] = $value['previous_value'];
             
            $entityResult[$key]['insert_date'] = date("F j, Y, g:i a", strtotime($value['insert_date']));
            if($value['record_id'] == 0) {
             
                if($value['module_type'] == 'RegistryMultiFormBuilder'){
                    $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-multi-form-builder/index/id/".$registryId;
                }else{
                    $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/index/registry_id/".$registryId;
                }
            }else {
                $entityResult[$key]['link'] = "https://$_SERVER[HTTP_HOST]/registry-entries/update/id/". $value['record_id']."/registry_id/".$registryId;
            }
             
        }
       
        $this->view->registryId = $registryId;
        $this->view->logAction = $entityResult;

        
    }
    public function diagramdAction(){
        try {
            $this->setDetailedSection('Lista rejestrów');

            $paginator = $this->registryModel->getList();
            $this->registryModel->loadData('author', $paginator);


             $regArray = array();
             $i = 0;

             foreach ($paginator  as $d){
                 $regArray[$i] = $d['id'] .",". $d['title'];
                    $i++;
             }

                    //= <option value="{$d.id}">{$d.title}</option>

             echo(json_encode($regArray));
             exit;
        } catch (Exception $e) {
            var_dump($e);
            exit;
            Throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }
    }

    public function adddiagramblockAction()
    {
        $this->setDetailedSection('Lista rejestrów');

        $paginator = $this->registryModel->getList();
        $this->registryModel->loadData('author', $paginator);

        $this->view->paginator = $paginator;
        try {

            $select =  $this->db->select()
            ->from( 'event_diagram' );
            $result = $this->db->fetchAll($select);

            if(!empty($result))
            {
                $this->view->diagramData = $result ;

            }
            else{
                 $this->view->diagramData = "";
            }

        } catch (Exception $e) {

        }

    }

    //new Flowchart tool 25/1/2018
    public function flowcharttoolAction()
    {
        $this->_helper->layout->disableLayout();
        $this->setDetailedSection('Lista rejestrów');

        $paginator = $this->registryModel->getList();
        $this->registryModel->loadData('author', $paginator);

        $this->view->paginator = $paginator;

        try {

            $select =  $this->db->select()
            ->from( 'event_diagram' );
            $result = $this->db->fetchAll($select);

            if(!empty($result))
            {
                $this->view->diagramData = $result ;

            }
            else{
                 $this->view->diagramData = "";
            }

        } catch (Exception $e) {

        }

    }

    public function openAction()
    {
        // $this->_helper->layout()->disableLayout();
        //  $this->_helper->viewRenderer->setNoRender(true);

    }


    public function updateAction()
    {

        Zend_Layout::getMvcInstance()->setLayout('home');
        $id = $this->getParam('id');
        $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
        $registryGroupAssigneesModel = Application_Service_Utilities::getModel('RegistryAssigneesGroup');
        $activityLogModel = Application_Service_Utilities::getModel('ActivityLog');
        $triggerlist = $this->triggerModel->getAllTrigger($id);

        if ($id) {
            $registry = $this->registryModel->getFull($id, true);

            $this->view->data = $registry;

            $this->setDetailedSection($registry->title . ': edytuj rejestr');
        } else {
            $this->setDetailedSection('Dodaj rejestr');
        }

        $assignees = $registryAssigneesModel->getList([
            'registry_id' => $id
        ]);
        $registryAssigneesModel->loadData(['user', 'role'], $assignees);

        $user_group_assignees = $registryGroupAssigneesModel->getList([
            'registry_id' => $id
        ]);
        $registryGroupAssigneesModel->loadData(['groups', 'role'], $user_group_assignees);

        $roles = Application_Service_Utilities::getModel('RegistryRoles')->getList(['registry_id' => $id]);

        $permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $id]);

        if (empty($permissions)) {
            Application_Service_Utilities::getModel('RegistryPermissions')->save([
                'registry_id' => $id,
                'title' => 'Zapis swoich',
                'system_name' => 'write.my',
            ]);
            Application_Service_Utilities::getModel('RegistryPermissions')->save([
                'registry_id' => $id,
                'title' => 'Odczyt swoich',
                'system_name' => 'read.my',
            ]);
            Application_Service_Utilities::getModel('RegistryPermissions')->save([
                'registry_id' => $id,
                'title' => 'Zapis wszystkich',
                'system_name' => 'write.all',
            ]);
            Application_Service_Utilities::getModel('RegistryPermissions')->save([
                'registry_id' => $id,
                'title' => 'Odczyt wszystkich',
                'system_name' => 'read.all',
            ]);
            Application_Service_Utilities::getModel('RegistryPermissions')->save([
                'registry_id' => $id,
                'title' => 'Zarządzanie',
                'system_name' => 'admin',
            ]);

            $permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $id]);
        }
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
        $this->view->assignees = $assignees;
        $this->view->user_group_assignees = $user_group_assignees;
        $this->view->documentsTemplates = Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->getAllForTypeahead();
        $this->view->roles = $roles;
        $this->view->permissions = $permissions;
        $this->view->activitylogs = $activityLogModel->getList(["`registryid` = ?" => $id]);
        $this->view->triggerlists = $triggerlist;
    }

 public function saveAction()
    {
        try {
            $req = $this->getRequest();
			$old_registry = $this->registryModel->getFull($req->id,true);
			$logdata = time()."||Title:".$old_registry->title.", Author:".$old_registry->author_id."||".$req->title."||Title:".$req->title.",Author:".$req->author_id;

            $this->registryModel->logregistry($logdata,RegistryController::$logFile);
            $params = $req->getParams();

            $registry = $this->registryModel->save($req->getParams());
            $cloneId = $this->getParam('clone');

            if ($cloneId) {
                $this->registryModel->cloneRegistrySettings($registry->id, $cloneId);
            }

            $menuId = $params['menuId'];
            $data = [
                'registery_id' => $registry->id,
                'label' => $registry['title'],
                'path' => '/registry-entries/index/registry_id/'.$registry->id,
                'icon' => 'glyphicon '.$registry['icon'],
                'rel' => 'registry',
                'parent_id' => $menuId
            ];
//            $this->menuModel->addNewRow($data);
            $this->registrymenuModel->addNewRow($data);

        } catch(Application_SubscriptionOverLimitException $x){
            $this->_redirect('subscription/limit');
        } catch (Exception $e) {
          	Throw new Exception('Próba zapisu danych nie powiodła się. ' . $e->getMessage(), 500, $e);
          // die();
        }
        $menuId = $this->getParam('menuId');
        $this->view->menuId = $menuId;

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->outputJson([
                'status' => true,
                'api' => [
                    'notification' => 'Zapisano dane',
                ],
            ]);
            return;
        } else {
            // $this->redirect($this->baseUrl);
            $params = $req->getParams();
            $menuId = $params['menuId'];
            $this->redirect($this->baseUrl.'?id='.$menuId);
        }
    }

    public function removeAction()
    {
        try {
            $req = $this->getRequest();
            $id = $req->getParam('id', 0);
            $menuId = $req->getParam('menuId');

            $row = $this->registryModel->requestObject($id);
            if ($row->is_visible == "1") {
                $path = '/registry-entries/index/registry_id/'.$id;
//                $this->menuModel->deleteRow($path);
                $this->registrymenuModel->deleteRow($path);
            }
            $this->registryModel->remove($row->id);
        } catch (Exception $e) {
            Throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }

        $this->flashMessage('success', 'Usunięto rekord');

//        $this->redirect($this->baseUrl);
        $this->redirect($this->baseUrl.'?id='.$menuId);
    }

    public function updateVisibleAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $data = $this->_request->getPost();
        $menuId = $data['menuId'];

        $paramId = $data['id'];
        $checked = $data['checked'];

        // update Registry model
        $data = array(
            'is_visible'      => ($checked == "true"?1:0)
        );
        $registryModel = Application_Service_Utilities::getModel('Registry');
        $where = $registryModel->getAdapter()->quoteInto('id = ?', $paramId);
        $registryModel->update($data, $where);

        $registry = $registryModel->getRegistryById($paramId);
        if ($checked == "true") { // insert row into menu table
            $data = [
                'label' => $registry['title'],
                'path' => '/registry-entries/index/registry_id/'.$paramId,
                'icon' => 'glyphicon '.$registry['icon'],
                'rel' => 'registry',
                'parent_id' => $menuId
            ];
            $this->menuModel->addNewRow($data);
        } else { // delete row from menu table
            $path = '/registry-entries/index/registry_id/'.$paramId;
            $this->menuModel->deleteRow($path);
        }

        echo $paramId;
        exit;
    }

    public function saveTriggerAction()
    {
         $data = $this->getRequest()->getPost();
        // echo "<pre>";print_r($data); die;
         $id = $this->triggerModel->save($data);
         $this->flashMessage('success', 'Aktywność zapisana');
        // $this->redirect('/registry/update/');
    }


    public function ajaxAddTriggerAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-add-trigger');
        $registryId = $this->getParam('id');
        $disabled = '';
        $row = Application_Service_Utilities::getModel('RegistryEntities')->createRow(['registry_id' => $registryId]);
        $primarykeyrow = Application_Service_Utilities::getModel('RegistryEntities')->checkPrimaryKeyField($registryId);
        if($primarykeyrow['set_primary'] == 1)
        {
            $disabled = 'disabled';
        }
        //var_dump(($row->config)); die;

        $this->view->data = $row;
        $this->view->$disabled = $disabled;
        $this->view->dialogTitle = 'Dodaj Trigger to Registry';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxAddParamAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-param-form');
        $registryId = $this->getParam('id');
        $disabled = '';
        $row = Application_Service_Utilities::getModel('RegistryEntities')->createRow(['registry_id' => $registryId]);
        $primarykeyrow = Application_Service_Utilities::getModel('RegistryEntities')->checkPrimaryKeyField($registryId);
        if($primarykeyrow['set_primary'] == 1)
        {
            $disabled = 'disabled';
        }
        //var_dump(($row->config)); die;

        $this->view->data = $row;
        $this->view->$disabled = $disabled;
        $this->view->dialogTitle = 'Dodaj parametr 1';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxEditParamAction()
    {
        $this->setDialogAction(['size' => 'xxl']);
        $this->setTemplate('ajax-param-form');

        $registryId = $this->getParam('id');
        $paramId = $this->getParam('param_id');

        $row = Application_Service_Utilities::getModel('RegistryEntities')->getOne([
            'id' => $paramId,
            'registry_id' => $registryId,
        ], true);

        if ($row->entity->config_data->type === 'entry') {
            $entries = Application_Service_Utilities::getModel('RegistryEntries')->getList([
                'registry_id' => $row->config_data->registry_id,
            ]);
            $rowValues = Application_Service_Utilities::getIndexedBy($entries, 'title', 'id');

            if (is_array($row->values)) {
                $row->values = array_merge($row->values, $rowValues);
            } else {
                $row->values = $rowValues;
            }
        }

        $this->view->data = $row;
        $this->view->dialogTitle = 'Edytuj parametr';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

	/*
	 * @manageFieldsAction return the view to arrange the order of the
	 * parametric fields
	 * */
    public function manageFieldsAction(){
		$registryId = $this->getParam('registry_id', 0);
		$registryWithoutAcc = $this->registryEntitites->getEntitesWithoutAccordion($registryId);

		$registryWithAcc = $this->registryEntitites->getEntitesWithAccordion($registryId);
		$accordions = $this->accordionModel->getAll();

		$accordionFields = $this->manageFieldsArray($registryWithAcc,$accordions);

		$registry = $this->registryModel->getFull($registryId, true);


		$this->setDetailedSection($registry->title . ': Przeciągnij i Upuść Pola');
		$this->view->registries = $registryWithoutAcc;
		$this->view->accordionFields = $accordionFields;
		$this->view->accordions = $accordions;
		$this->view->data = $registry;
		Zend_Layout::getMvcInstance()->setLayout('home');
	}

	public function manageFieldsArray($registryWithAcc){
		$newArr = [];
		foreach($registryWithAcc as $registry){
			$newArr[$registry['accId']]['accId'] = $registry['accId'];
			$newArr[$registry['accId']]['name'] = $registry['name'];
			$newArr[$registry['accId']]['newArr'][$registry['entityId']]['entityId']    = $registry['entity_id'];
			$newArr[$registry['accId']]['newArr'][$registry['entityId']]['field_title'] = $registry['registry_entries_title'];
			$newArr[$registry['accId']]['newArr'][$registry['entityId']]['acc_order']   = $registry['reg_tab_order'];
			$newArr[$registry['accId']]['newArr'][$registry['entityId']]['entitiestitle']   = $registry['entitiestitle'];
		}
		return $newArr;
	}

	/**
	 *
	 * @UpdateFieldsAction used to update the fields accordion id in the
	 * registry entities table
	 *
	 * **/
	public function updateFieldsAction(){
		try {
			$req = $this->getRequest();
			$req->getParams();
			$entityId = $this->registryEntitites->getIdByRIdAndEntityId($req->getParam('registry_id'),$req->getParam('id'));
			$registry = $this->registryEntitites->updateEntityForAccordion($entityId['id'],$req->getParams());
			if ($registry) {
				$this->outputJson([
					'status' => true,
					'data' => $registry
				]);
			}
			return  false;
		} catch (Exception $e) {
			Throw new Exception('Próba zapisu danych nie powiodła się. ' . $e->getMessage(), 500, $e);
		}
	}

    /**
     * Get registry entities (single mode)
     * @throws Exception
     */
    public function ajaxGetRegistryEntitiesAction()
    {
        $registryId = $this->getParam('id');

        $registry = $this->registryModel->getFull($registryId, true);

        $this->outputJson($registry->entities);
    }

    /**
     * Get multiple registry entities as associative list (batch mode)
     * @throws Exception
     */
    public function ajaxGetMultiRegistryEntitiesAction()
    {
        $out = array();
        foreach ( (array)$this->getParam('id') as $registryId) {
            /** @var object $registry */
            $registry = $this->registryModel->getFull($registryId, true);
            $out[$registry->id] = $registry->entities;
        }

        $this->outputJson($out);
    }

    public function documentsAction() {
        $registryId = $this->getParam('id', 0);

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getFull($registryId, true);
        $documentTemplateIds = Application_Service_Utilities::getValues($registry->documents_templates, 'id');
        $paginator = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getListFull(['document_template_id IN (?)' => $documentTemplateIds]);

        $this->view->paginator = $paginator;
        $this->view->registry = $registry;
        $this->setDetailedSection($registry->title . ': lista dokumentów');
    }

    public function ajaxSaveParamAction()
    {
        $data = $this->getParam('parameter');

        try {
            $this->db->beginTransaction();

            $mode = empty($data['id'])
                ? 'create'
                : 'update';

            $param = Application_Service_Utilities::getModel('RegistryEntities')->save($data);
            Application_Service_Events::getInstance()->trigger(sprintf('registry.param.%s', $mode), $param);

            $this->db->commit();
            $status = true;
            $message = 'Zapisano parametr';
        } catch (Exception $e) {
            $status = false;
            $message = 'Nie udało się zapisać danych';
            vdie($e);
        }

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $message,
            ],
        ]);
    }

    public function ajaxRemoveParamAction()
    {
        $registryId = $this->getParam('id');
        $paramId = $this->getParam('param_id');

        $row = Application_Service_Utilities::getModel('RegistryEntities')->getOne([
            'id' => $paramId,
            'registry_id' => $registryId,
        ], true);

        Application_Service_Utilities::getModel('RegistryEntities')->removeEntity($row);

        $this->outputJson([
            'status' => true,
            'api' => [
                'notification' => 'Usunięto parametr',
            ],
        ]);
    }

    public function ajaxOrderUpParamAction()
    {
        $registryId = $this->getParam('id');
        $paramId = $this->getParam('param_id');

        $row = Application_Service_Utilities::getModel('RegistryEntities')->getOne([
            'id' => $paramId,
            'registry_id' => $registryId,
        ], true);

        $status = Application_Service_Utilities::getModel('RegistryEntities')->setEntityOrder($row, '-1');

        $this->outputJson([
            'status' => true,
            'api' => [
                'notification' => $status
                    ? 'Przeniesiono parametr'
                    : 'Nie udało się przenieść parametru',
            ],
        ]);
    }

    public function ajaxOrderDownParamAction()
    {
        $registryId = $this->getParam('id');
        $paramId = $this->getParam('param_id');

        $row = Application_Service_Utilities::getModel('RegistryEntities')->getOne([
            'id' => $paramId,
            'registry_id' => $registryId,
        ], true);

        $status = Application_Service_Utilities::getModel('RegistryEntities')->setEntityOrder($row, '+1');

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Przeniesiono parametr'
                    : 'Nie udało się przenieść parametru',
            ],
        ]);
    }

    public function ajaxUpdateAction()
    {
        $this->setDialogAction();

        $registryId = $this->getParam('id');
        $cloneId = $this->getParam('clone');
        $menuId = $this->getParam('menuId');

        if ($registryId) {
            $row = Application_Service_Utilities::getModel('Registry')->getFull([
                'id' => $registryId,
            ], true);
        } else {
            $row = Application_Service_Utilities::getModel('Registry')->createRow();
        }

        $this->view->data = $row;
        $this->view->menuId = $menuId;
        $this->view->cloneId = $cloneId;
        $this->view->users = Application_Service_Utilities::getModel('Osoby')->getAllForTypeahead();
        $this->view->dialogTitle = $registryId
            ? 'Edytuj rejestr'
            : ( $cloneId
                ? 'Duplikuj rejestr'
                : 'Dodaj rejestr'
            );
    }

    public function ajaxAddDocumentTemplateAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-document-template-form');
        $registryId = $this->getParam('id');

        $registry = Application_Service_Utilities::getModel('Registry')->getOne(['id' => $registryId], true);
        $registry->loadData(['entities']);

        $row = Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->createRow(['registry_id' => $registryId]);

        $this->view->data = $row;
        $this->view->registry = $registry;
        $this->view->dialogTitle = 'Dodaj dokument';
    }

    public function ajaxEditDocumentTemplateAction()
    {
        $this->setDialogAction();
        $registryId = $this->getParam('id');
        $documentTemplateId = $this->getParam('document_template_id');

        $row = Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->getOne([
            'id' => $documentTemplateId,
            'registry_id' => $registryId,
        ], true);
        $row->loadData(['template', 'registry']);

        switch ($row->template->type_id) {
            case Application_Service_RegistryConst::TEMPLATE_TYPE_HTML_EDITOR:
                $this->setTemplate('ajax-document-template-form');
                break;
            default:
                $this->setTemplate('ajax-document-template-form');
                break;
                Throw new Exception('Unhandled templtae type', 500);
        }

        $entityVariables = [];
        foreach ($row->registry->entities as $registryEntity) {
            $entityVariables[] = [
                'id' => $registryEntity->id,
                'name' => $registryEntity->title,
            ];
        }

        $this->view->data = $row;
        $this->view->registry = $row->registry;
        $this->view->entityVariables = $entityVariables;
        $this->view->dialogTitle = 'Edytuj dokument';
    }

    public function ajaxSaveDocumentTemplateAction()
    {
        $data = $this->getParam('document_template');

        try {
            $this->db->beginTransaction();

            Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->save($data);

            $this->db->commit();
            $status = true;
        } catch (Exception $e) {
            $status = false;
        }

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Zapisano dokument'
                    : 'Nie udało się zapisać danych',
            ],
        ]);
    }

    public function ajaxAddReportTemplateAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-param-form');
        $registryId = $this->getParam('id');
        $templateId = $this->getParam('template_id');

        $row = Application_Service_Utilities::getModel('RegistryEntities')->createRow(['registry_id' => $registryId]);

        $this->view->data = $row;
        $this->view->dialogTitle = 'Dodaj parametr 2';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxAddAssigneeChooseRoleAction()
    {
        $this->setDialogAction();
        $registryId = $this->_getParam('id');
        $userId = $this->_getParam('user_id');

        $registry = $this->registryModel->getOne($registryId, true);
        list($user) = $this->osobyModel->getList($userId, true);
        $roles = Application_Service_Utilities::getModel('RegistryRoles')->getList(['registry_id' => $registryId]);
        $permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $registryId]);

        $registryPermissions = array();
        foreach ($roles as $key => $val) {
            $row = Application_Service_Utilities::getModel('RegistryRoles')->getOne(['registry_id' => $registryId, 'id' => $val->id], true);
            $row->loadData('permissions');
            $registryPermissions[$val->id] = $row['permissionsIds'];
        }

        $this->view->assign(compact('registry', 'user', 'roles', 'permissions', 'registryPermissions'));
    }

    public function ajaxAddUserGroupChooseRoleAction()
    {
        $this->setDialogAction();
        $registryId = $this->_getParam('id');
        $userGruopId = $this->_getParam('user_group_id');

        $groupsModel = Application_Service_Utilities::getModel('Groups');
        $osobyGroupsModel = Application_Service_Utilities::getModel('OsobyGroups');

        $registry = $this->registryModel->getOne($registryId, true);
        $group = $groupsModel->getOne($userGruopId, true);

        $roles = Application_Service_Utilities::getModel('RegistryRoles')->getList(['registry_id' => $registryId]);
        $permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $registryId]);

        $registryPermissions = array();
        foreach ($roles as $key => $val) {
            $row = Application_Service_Utilities::getModel('RegistryRoles')->getOne(['registry_id' => $registryId, 'id' => $val->id], true);
            $row->loadData('permissions');
            $registryPermissions[$val->id] = $row['permissionsIds'];
        }

        $this->view->assign(compact('registry', 'group', 'roles', 'permissions', 'registryPermissions'));
    }

    public function ajaxAddAssigneeAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $userId = $this->getParam('user_id');
        $roleId = $this->getParam('role_id');
        $permissionsOverRide = $this->getParam('permissions');

        $registry = $this->registryModel->getOne($registryId, true);
        $user = $this->osobyModel->requestObject($userId);

        $role = Application_Service_Utilities::getModel('RegistryRoles')->requestObject($roleId);

        $status = $this->registryService->addAssignee($registry, $user, $role, $permissionsOverRide);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Przypisano pracownika'
                    : 'Nie udało się przypisać pracownika',
            ],
        ]);
    }

    public function ajaxAddRoleToUserGroupAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $groupId = $this->getParam('group_id');
        $roleId = $this->getParam('role_id');
        $permissionsOverRide = $this->getParam('permissions');

        $groupsModel = Application_Service_Utilities::getModel('Groups');
        $osobyGroupsModel = Application_Service_Utilities::getModel('OsobyGroups');

        $registry = $this->registryModel->getOne($registryId, true);
        $group = $groupsModel->requestObject($groupId);
        $role = Application_Service_Utilities::getModel('RegistryRoles')->requestObject($roleId);

        $status = $this->registryService->addRoleToUserGroup($registry, $group, $role, $permissionsOverRide);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Role assigned to Group'
                    : 'Role can not assigned to Group',
            ],
        ]);
    }


    public function ajaxRemoveAssigneeAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $assigneeId = $this->getParam('assignee_id');

        $registry = $this->registryModel->getOne($registryId, true);
        $assignee = Application_Service_Utilities::getModel('RegistryAssignees')->getOne([
            'registry_id' => $registryId,
            'id' => $assigneeId,
        ], true);

        $status = $this->registryService->removeAssignee($registry, $assigneeId);
        echo 'adnan';die();
        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Usunięto przypisanie pracownika'
                    : 'Nie udało się usunąć przypisania pracownika',
            ],
            'removedObject' => $assignee,
        ]);
    }

    public function ajaxRemoveAssigneeGroupAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $assigneeGroupId = $this->getParam('assignee_group_id');

        $registry = $this->registryModel->getOne($registryId, true);
        $assignee = Application_Service_Utilities::getModel('RegistryAssigneesGroup')->getOne([
            'registry_id' => $registryId,
            'id' => $assigneeGroupId,
        ], true);

        $status = $this->registryService->removeAssigneeGroup($registry, $assigneeGroupId);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Usunięto przypisanie pracownika'
                    : 'Nie udało się usunąć przypisania pracownika',
            ],
            'removedObject' => $assignee,
        ]);
    }


    public function ajaxAddPermissionAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-permission-form');
        $registryId = $this->getParam('id');

        $row = Application_Service_Utilities::getModel('RegistryPermissions')->createRow(['registry_id' => $registryId]);

        $this->view->data = $row;
        $this->view->dialogTitle = 'Dodaj uprawnienie';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxEditPermissionAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-permission-form');
        $registryId = $this->getParam('id');
        $permissionId = $this->getParam('permission_id');

        $row = Application_Service_Utilities::getModel('RegistryPermissions')->getOne(['registry_id' => $registryId, 'id' => $permissionId], true);

        $this->view->data = $row;
        $this->view->dialogTitle = 'Dodaj uprawnienie';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxSavePermissionAction()
    {
        $data = $this->getAllParams();

        try {
            $this->db->beginTransaction();

            Application_Service_Utilities::getModel('RegistryPermissions')->save($data);

            $this->db->commit();
            $status = true;
            $message = 'Zapisano uprawnienie';
        } catch (Exception $e) {
            $status = false;
            $message = 'Nie udało się zapisać danych';
        }

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $message,
            ],
        ]);
    }

    public function ajaxRemovePermissionAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $permissionId = $this->getParam('permission_id');

        $registry = $this->registryModel->getOne($registryId, true);
        $permission = Application_Service_Utilities::getModel('RegistryPermissions')->getOne([
            'registry_id' => $registryId,
            'id' => $permissionId,
        ], true);

        $status = $this->registryService->removePermission($registry, $permissionId);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Usunięto uprawnienie'
                    : 'Nie udało się usunąć uprawnienia',
            ],
            'removedObject' => $permission,
        ]);
    }

    public function ajaxAddRoleAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-role-form');
        $registryId = $this->getParam('id');

        $row = Application_Service_Utilities::getModel('RegistryRoles')->createRow(['registry_id' => $registryId]);

        $this->view->permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $registryId]);

        $this->view->data = $row;
        $this->view->dialogTitle = 'Dodaj rolę';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxManageFieldsAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-manage-fields-form');
        //$registryId = $this->getParam('registry_id');

        $id = $this->getParam('id');
        $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');

        if ($id) {
            $registry = $this->registryModel->getFull($id, true);

            $this->view->data = $registry;

            $this->setDetailedSection($registry->title . ': edytuj rejestr');
        } else {
            $this->setDetailedSection('Dodaj rejestr');
        }
        $this->view->dialogTitle = 'Rejestry->id('.$id.')';
    }

    public function ajaxEditRoleAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-role-form');
        $registryId = $this->getParam('id');
        $roleId = $this->getParam('role_id');
	$name = $this->getParam('system_name');

	if(!empty($name))
	{
	    $row = Application_Service_Utilities::getModel('RegistryRoles')->getOne(['registry_id' => $registryId, 'id' => $roleId, 'system_name' => $name], true);
	}
	else{
            $row = Application_Service_Utilities::getModel('RegistryRoles')->getOne(['registry_id' => $registryId, 'id' => $roleId], true);
	}
        $row->loadData('permissions');

        $this->view->permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $registryId]);

        $this->view->data = $row;
        $this->view->dialogTitle = 'Edytuj rolę';
        $this->view->entities = $this->entitiesModel->getAllForTypeahead();
    }

    public function ajaxSaveRoleAction()
    {
        $data = $this->getAllParams();

        try {
            $this->db->beginTransaction();

            Application_Service_Utilities::getModel('RegistryRoles')->save($data);

            $this->db->commit();
            $status = true;
            $message = 'Zapisano rolę';
        } catch (Exception $e) {
            $status = false;
            $message = 'Nie udało się zapisać danych';
        }

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $message,
            ],
        ]);
    }

    public function ajaxRemoveRoleAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $roleId = $this->getParam('role_id');

        $registry = $this->registryModel->getOne($registryId, true);
        $role = Application_Service_Utilities::getModel('RegistryRoles')->getOne([
            'registry_id' => $registryId,
            'id' => $roleId,
        ], true);

        $status = $this->registryService->removeRole($registry, $roleId);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Usunięto rolę'
                    : 'Nie udało się usunąć roli',
            ],
            'removedObject' => $role,
        ]);
    }

    public function ajaxRemoveDocumentTemplateAction()
    {
        $this->setAjaxAction();

        $registryId = $this->getParam('id');
        $documentTemplateId = $this->getParam('document_template_id');

        $registry = $this->registryModel->getOne($registryId, true);
        $object = Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->getOne([
            'registry_id' => $registryId,
            'id' => $documentTemplateId,
        ], true);

        $status = $this->registryService->removeDocumentTemplate($registry, $documentTemplateId);

        $this->outputJson([
            'status' => $status,
            'api' => [
                'notification' => $status
                    ? 'Usunięto dokument'
                    : 'Nie udało się usunąć dokumentu',
            ],
            'removedObject' => $object,
        ]);
    }
	public function registryHistoryAction()
    {
		$this->setDetailedSection('rejestr log');
        $new_logs = RegistryController::getLogHistory();
        $this->view->logs = $new_logs;
    }
	public static function getLogHistory($userLogin = null, $limit = 2000)
    {
		//echo RegistryController::$logFile;die;
        $new_logs = [];
        $logs = file(RegistryController::$logFile);

        $logs = array_reverse($logs);

        if (is_array($logs) && count($logs) > 0) {
            foreach ($logs as &$log) {
                $tmp = explode('||', $log);
                $tmp[0] = date("d.m.Y h:i:s", $timestamp);
                $tmpLogin = trim($tmp[3]);
				$new_logs[] = $tmp;
                /*if (!$userLogin || mb_strtolower($tmpLogin) == mb_strtolower($userLogin)) {
                    $new_logs[] = $tmp;
                }*/
            }
        }
        $new_logs = array_slice($new_logs, 0, $limit);

        return $new_logs;
    }


    public function exportAction() {

        $req = $this->getRequest();

        $reqIds = $req->getParam('id', 0);

        if (!is_array($reqIds)) {
            $reqIds = array($reqIds);
        }

        $ids = array_filter($reqIds);


        // dump to temporary file to get CSV formatting
        $dir = ROOT_PATH . '/web/uploads/default/';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!is_dir($dir)) {
            echo Zend_Registry::get('Zend_Translate')->_("directory $dir not found");
            return false;
        }

        $tmp_file = $dir . 'registry.csv';

        if (!$ids) {
            echo Zend_Registry::get('Zend_Translate')->_("Please select some registries to export.");
            return false;
        }

        try {
            $fp = fopen($tmp_file, 'w');
        } catch (Exception $e) {
            echo Zend_Registry::get('Zend_Translate')->_("Error creating file for export ($tmp_file).");
            return false;
        }

        if ($fp) {
            //echo '<pre>';
            foreach ($ids as $id => $value) {

                $registry = $this->registryModel->requestObject($id)->toArray();

                $exportEntities = $this->exportEntities($registry);

                $exportAssignees = $this->exportAssignees($exportEntities);
                $exportEntries = $this->exportEntries($exportAssignees);
                $exportRegistry = $this->exportDocumentTemplate($exportEntries);

                fputcsv($fp, $exportRegistry);
            }
            //die("export funciton testing");
            fclose($fp);
        } else {
            echo Zend_Registry::get('Zend_Translate')->_("No file create permissions to export ($tmp_file).");
            return false;
        }

        try {
            $fh = fopen($tmp_file, 'r');
        } catch (Exception $e) {
            echo Zend_Registry::get('Zend_Translate')->_("Error reading the csv file for ($tmp_file)export.");
            return false;
        }

        if ($fh) {
            header("Content-Disposition: attachment; filename=\"registry.csv\"");
            while (!feof($fh)) {
                echo fgets($fh, 4096);
                flush();
            }
            fclose($fh);
        } else {
            echo Zend_Registry::get('Zend_Translate')->_("Error reading the csv file for ($tmp_file)export.");
            return false;
        }

        @unlink($tmp_file);
        exit;
    }

    public function exportEntities(& $registry) {
        $registryId = $registry['id'];
        $registryEntitites = $this->registryEntitites->getEntitiesByRegistryId($registryId);

        $data = array();
        $exclude = array('id', 'registry_id');
        if($registryEntitites) {
            foreach ($registryEntitites as $key1 => $row) {
                foreach ($row as $columnName => $columnValue) {
                    if (in_array($columnName, $exclude))
                        continue;
                    $data[$columnName][] = $columnValue;
                }
            }
        }

        foreach ($data as $key => $values) {
            $registry["entities_" . $key] = implode(",", $values);
        }
        return $registry;

    }

    public function exportAssignees(& $registry) {

        $registryId = $registry['id'];
        $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
        $registryAssignees = $registryAssigneesModel->getRegistryAssignees($registryId);

        $data = array();
        $exclude = array('id', 'registry_id');

        if($registryAssignees) {
            foreach ($registryAssignees as $key1 => $row) {
                foreach ($row as $columnName => $columnValue) {
                    if (in_array($columnName, $exclude))
                        continue;
                    $data[$columnName][] = $columnValue;
                }
            }
        }

        foreach ($data as $key => $values) {
            $registry["assignees_" . $key] = implode(",", $values);
        }
        return $registry;
    }

    public function exportEntries(& $registry) {
        $registryId = $registry['id'];
        $registryEntries = $this->registryEntriesModel->getEntriesByRegistryId($registryId);
        $data = array();
        $exclude = array('id', 'registry_id');

        if($registryEntries) {
            foreach ($registryEntries as $key1 => $row) {
                foreach ($row as $columnName => $columnValue) {
                    if (in_array($columnName, $exclude))
                        continue;
                    $data[$columnName][] = $columnValue;
                }
            }
        }

        foreach ($data as $key => $values) {
            $registry["entries" . $key] = implode(",", $values);
        }
        //print_r($registry);
        return $registry;
    }

    public function exportDocumentTemplate(& $registry) {
        $registryId = $registry['id'];
        $registryDocumentTemplate = Application_Service_Utilities::getModel('RegistryDocumentsTemplates');
        $documentTemplate = $registryDocumentTemplate->getRegistryDocumntTemplate($registerId);

        $data = array();
        $exclude = array('id', 'registry_id');

        if($documentTemplate) {
            foreach ($documentTemplate as $key1 => $row) {
                foreach ($row as $columnName => $columnValue) {
                    if (in_array($columnName, $exclude))
                        continue;
                    $data[$columnName][] = $columnValue;
                }
            }
        }

        foreach ($data as $key => $values) {
            $registry["entries" . $key] = implode(",", $values);
        }
        //print_r($registry);
        return $registry;
    }

    public function importAction() {
        $this->setDialogAction(array(
            'id' => 'import_registry',
            'title' => 'Importuj rejestry',
            'footer' => '_reuse/_modal-footer-upload.html',
        ));

        $data = [
            'label' => 'Importuj rejestry',
            'name' => 'registry',
        ];

        $this->view->assign(['element' => $data]);

    }

    public function importAjaxAction() {
        $data = $this->_getAllParams();

        $uploadedFiles = json_decode($data['registry_uploaded'], true);
        $basePath = ROOT_PATH . '/web/uploads/default/';
        $filePath = $basePath . $uploadedFiles[0]['uploadedUri'];

        $row = 0;

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 2048, ",")) !== FALSE) {

                if (! array_filter($data)) {
                    continue;
                }

                $row++;
                $author_id_count = $this->registryModel->getRegistryByAuthorId($data[2]);

                $dataImport = [
                    'title' => $data[1] ? $data[1] : '',
                    'author_id' => $data[2] ? $data[2] :
                        Application_Service_Authorization::getInstance()->getUserId(),
                    'type_id' => $data[3] ? $data[3] : null,
                    'object_id' => $data[4] ? $data[4] : null,
                    'system_name' => $data[5] ? $data[5] : null,
                    'is_locked' => $data[6] ? $data[6] : '0',
                    'is_visible' => $data[7] ? $data[7] : '0',
                    'created_at' => $data[8] ? $data[8] : null,
                    'updated_at' => $data[9] ? $data[9] : null,
                ];

                if ($author_id_count > 0) {
                    $counter = $author_id_count + 1;
                    $dataImport['title'] = $data[1] ? $data[1].'_v'.$counter : '';
                }
                //print_r($data);

                $newRegistry = $this->registryModel->save($dataImport);
                $newRegistryId = $newRegistry['id'];

                //Entities
                $entityRow = array();
                $entityRow['entity_id'] = $data[10];
                $entityRow['system_name'] = $data[11];
                $entityRow['default_value'] = $data[12];
                $entityRow['title'] = $data[13];
                $entityRow['is_multiple'] = $data[14];
                $entityRow['set_primary'] = $data[15];
                $entityRow['config'] = $data[16];
                $entityRow['order'] = $data[17];
                $entityRow['created_at'] = $data[18];
                $entityRow['updated_at'] = $data[19];
                if(array_filter($entityRow) != false) {
                    $this->importEntities($newRegistryId, $entityRow);
                }

                //assignees
                $assigneeRow = array();
                $assigneeRow['user_id'] = $data[20];
                $assigneeRow['registry_role_id'] = $data[21];
                $assigneeRow['created_at'] = $data[22];
                $assigneeRow['updated_at'] = $data[23];
                if(array_filter($assigneeRow)  != false) {
                    $this->importAssignees($newRegistryId, $assigneeRow);
                }

                // Entries
                $entriesRow = array();
                $entriesRow['author_id'] = $data[24];
                $entriesRow['title'] = $data[25];
                $entriesRow['created_at'] = $data[26];
                $entriesRow['updated_at'] = $data[27];
                if(array_filter($entriesRow)  != false) {
                    $this->importEntries($newRegistryId, $entriesRow);
                }

                //document template
                $documentTemplate = array();
                $documentTemplate['title'] = $data[28];
                $documentTemplate['default_author_id'] = $data[29];
                $documentTemplate['template_id'] = $data[30];
                $documentTemplate['template_config'] = $data[31];
                $documentTemplate['numbering_scheme'] = $data[32];
                $documentTemplate['numbering_scheme_type_id'] = $data[33];
                $documentTemplate['created_at'] = $data[34];
                $documentTemplate['updated_at'] = $data[35];
                if(array_filter($documentTemplate)  != false) {
                    $this->importDocumentTemplate($newRegistryId, $documentTemplate);
                }
            }

            fclose($handle);

            $status = 1;
            $msg = "$row imported";
        } else {
            $status = 0;
            $msg = "file not found";
        }

        $this->outputJson([
            'status' => $status,
            'message' => $msg,
        ]);

    }

    public function importEntities($registryId, $entity) {

        $entityIds      = explode(",", $entity['entity_id']);
        $systemNames    = explode(",", $entity['system_name']);
        $defaultValues  = explode(",", $entity['default_value']);
        $titles         = explode(",", $entity['title']);
        $isMultiples    = explode(",", $entity['is_multiple']);
        $setPrimary     = explode(",", $entity['set_primary']);
        $configs        = explode(",", $entity['config']);
        $orders         = explode(",", $entity['order']);
        $created_ats    = explode(",", $entity['created_at']);
        $updated_ats    = explode(",", $entity['updated_at']);

        foreach ($entityIds as $key => $entityId) {
            $row = array();
            $row['registry_id'] = $registryId;
            $row['entity_id'] = $entityId;
            $row['system_name'] = $systemNames[$key];
            $row['default_value'] = $defaultValues[$key];
            $row['title'] = $titles[$key];
            $row['is_multiple'] = $isMultiples[$key];
            $row['set_primary'] = $setPrimary[$key];
            $row['config'] = $configs[$key];
            $row['order'] = $orders[$key];
            /*$row['created_at'] = date("Y-m-d H:i:s", $created_ats[$key]);
            $row['updated_at'] = date("Y-m-d H:i:s", $updated_ats[$key]);*/
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = NULL;

            //insert into model
            $this->registryEntitites->save($row);

            //print_r($row);
        }

    }

    public function importAssignees($registryId, $assignee) {
        $userIds          = explode(",", $assignee['user_id']);
        $registryRolesIds = explode(",", $assignee['registry_role_id']);
        $created_ats      = explode(",", $assignee['created_at']);
        $updated_ats      = explode(",", $assignee['updated_at']);

        foreach ($userIds as $key => $userId) {
            $row = array();
            $row['registry_id'] = $registryId;
            $row['user_id'] = $userId;
            $row['registry_role_id'] = $registryRolesIds[$key];
            //$row['created_at'] = date("Y-m-d H:i:s", $created_ats[$key]);
            //$row['updated_at'] = date("Y-m-d H:i:s", $updated_ats[$key]);
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = NULL;
            //insert into model
            $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
            $registryAssigneesModel->save($row);

            //print_r($row);
        }

    }

    public function importEntries($registryId, $entries) {
        $author_ids   = explode(",", $entries['author_id']);
        $titles       = explode(",", $entries['title']);
        $created_ats  = explode(",", $entries['created_at']);
        $updated_ats  = explode(",", $entries['updated_at']);

        foreach ($author_ids as $key => $authorId) {
            $row = array();
            $row['registry_id'] = $registryId;
            $row['author_id']   = $authorId;
            $row['title']       = $titles[$key];
            //$row['created_at']  = date("Y-m-d H:i:s", $created_ats[$key]);
            //$row['updated_at']  = date("Y-m-d H:i:s", $updated_ats[$key]);
            $row['created_at']  = date('Y-m-d H:i:s');
            $row['updated_at']  = NULL;
            //insert into model
            $this->registryEntriesModel->save($row);

            //print_r($row);
        }

    }
    public function getTopNavigation($action='')
    {  
        $data = array(array(
                'label' => 'Filtry',
                'path' => 'javascript:;',
                'icon' => 'fa icon-tools',
                'rel' => 'filters',
                'children' => array(

                )
            ) ); 
        $userId = $this->userSession->user->id;
        $registryId = $this->getParam('registry_id', 0);

        $resultSets = $this->db->query("select * from registry_filters where user_id = '".$userId." ' and filter_table = 'audit' ")->fetchAll();
        if(isset($resultSets) && count($resultSets) > 0 )
        {
            foreach($resultSets as $result)
            {
                $filterDialog = "#filterDialog_".$result['id'];
                $filter_arr[] =
                    array(
                        'label' => $result['filter_name'],
                        'path' => 'javascript:;',
                        'isdialog' => 1 ,
                        'target' => $filterDialog ,
                        'icon' => (isset($result['filter_score']) && $result['filter_score'] == 'private') ? 'icon-lock-1' : 'icon-lock-open-1',
                        'rel' => 'filters',
                        'isdelete' => 1
                    );
            }
            $filter_arr[] =
                array(
                    'label' => 'Zresetuj filtr',
                    'path' => '/registry/audit/registry_id/'.$registryId,
                    'icon' => 'icon-arrows-ccw',
                    'rel' => 'filters'
                );
        }
        $filter_arr[] =
            array(
                'label' => 'Nowy filtr',
                'path' => 'javascript:;',
                'isdialog' => 1 ,
                'target' => '#filterDialog' ,
                'icon' => 'icon-plus',
                'rel' => 'filters'
            );

        if(isset($filter_arr) && count($filter_arr) > 0)
        {
            $data[0]['children'] = $filter_arr;
        }

        $this->setSectionNavigation($data);
    }

    public function importDocumentTemplate($registryId, $documentTemplate) {
        $titles   = explode(",", $documentTemplate['title']);
        $defaultAuthorIds       = explode(",", $documentTemplate['default_author_id']);
        $templateIds  = explode(",", $documentTemplate['template_id']);
        $templateConfigs  = explode(",", $documentTemplate['template_config']);
        $numberingSchemes  = explode(",", $documentTemplate['numbering_scheme']);
        $numberingSchemeTypeIds  = explode(",", $documentTemplate['numbering_scheme_type_id']);
        $created_ats  = explode(",", $documentTemplate['created_at']);
        $updated_ats  = explode(",", $documentTemplate['updated_at']);

        foreach ($templateIds as $key => $templateId) {
            $row = array();
            $row['title'] = $titles;
            $row['default_author_id']   = $templateId;
            $row['template_id']       = $templateIds[$key];
            $row['template_config']  = $templateConfigs[$key];
            $row['numbering_scheme']  = $numberingSchemes[$key];

            $row['numbering_scheme_type_id']  = $numberingSchemeTypeIds[$key];
            //$row['created_at']  = date("Y-m-d H:i:s", $created_ats[$key]);
            //$row['updated_at']  = date("Y-m-d H:i:s", $updated_ats[$key]);
            $row['created_at']  = date('Y-m-d H:i:s');
            $row['updated_at']  = NULL;
            //insert into model
            $registryDocumentTemplate = Application_Service_Utilities::getModel('RegistryDocumentsTemplates');
            $registryDocumentTemplate->save($row);

            //print_r($row);
        }

    }

       public function generatereportAction() {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1024M');
        $this->_helper->layout->setLayout('report');
       
        $registry_ids = $_POST['registry_id'];
        $finalHtml  = '';
        $emptyEntery = 0;
        foreach ($registry_ids  as $registryId ) {
            $registry = $this->registryModel->getOne($registryId, true);
            $registry->loadData('entities');
            
            $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
            
            $this->registryEntriesModel->loadData('author', $paginator);
            Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
            
            //get values of selected data type to be printed on report
            $result = $this->registryEntriesModel->getEnitiesValuesForSelectedDatatypes($paginator, $registryId);
            if(empty($result['tab_count']) && empty($result['selectedAttrName'])){
                $emptyEntery++;
                continue;
            }
            $this->view->selectedAttrName = $result['selectedAttrName'];
            $this->view->tab_count = $result['tab_count'];
            
            $this->view->paginator = $paginator;
            
            $this->view->registry = $registry;
            $this->view->title = $registry->title;
            $this->view->date = date('M d Y') .' at '.date('G:i');
            
            $settings = Application_Service_Utilities::getModel('Settings');
            $this->view->config = $settings->getAll();
            if (isset($result['tab_count']) && !empty($result['tab_count']) && array_values($result['tab_count'])['0'] > 0) {
                $this->view->maxstep = array_values($result['tab_count'])['0'];
                $htmlResult = $this->view->render('registry/generatereport_tabs.html');
            } else {
                $htmlResult = $this->view->render('registry/generatereport.html');
            }
            $finalHtml .= $htmlResult ;
        }
        if(count($registry_ids) == $emptyEntery){
            echo "No data found to generate report!!";
            exit;
        }
        $date = new DateTime();
        $time = $date->format('\TH\Hi\M');
        $timeDate = new DateTime();
        $timeDate->setTimestamp(0);
        $timeInterval = new DateInterval('P0Y0D' . $time);
        $timeDate->add($timeInterval);
        $timeTimestamp = $timeDate->format('U');
        $filename = 'raport_rejestry_' . date('Y-m-d') . '_' . time() . '.pdf';

        $finalHtml = html_entity_decode($finalHtml);
 
        $this->_forcePdfDownload = false;
        $res = $this->outputHtmlPdf($filename, $finalHtml, false, false, true);
        echo $res;
        exit ; 
    }

    public function unlinkpdfAction(){
        $file = $_GET['file'];
        unlink($file);
        exit;
    }

    public function addminiAction()
    {
        $this->view->ajaxModal = 1;
        $this->view->t_data = $this->registryModel->fetchAll('is_visible = 1', 'title')->toArray();
    }

}
