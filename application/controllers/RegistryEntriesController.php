<?php

class RegistryEntriesController extends Muzyka_Admin
{

    const CACHE_PREFIX = 'registry_values_';
    const PERMISSION_REGISTRY_NAME = 'Uprawnienia';
    const EMPLOYEE_REGISTERY = "Pracownicy";
    const BUILDING_REGISTERY = "Buildings";
    const PLACE_REGISTERY = "Places";

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    /** @var Application_Service_DocumentsPrinter */
    protected $documentsPrinter;

    /** @var Application_Model_PermissionStatus */
    protected $permissionStatusModel;

    /** @var Application_Model_PermissionStatus */
    protected $deletedWorkerModel;

    /** @var Application_Model_Registry */
    protected $registryModel;

    /** @var Application_Model_Registry */
    protected $todolistModal;

    /** @var Application_Model_Trigger */
    protected $triggerModel;

    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;

    /** @var Application_Model_RegistryEntities */
    protected $registryEntitiesModel;

    /** @var Application_Model_RegistryFilter */
    protected $registryFilterModel;

    /** @var Application_Service_Registry */
    protected $registryService;
    protected $baseUrl = '/registry-entries';
    protected $dictionaryModel;

    /** @var Application_Model_Documenttemplates */
    protected $documentTemplates;

    /** @var Application_Model_Documenttemplatesosoby */
    protected $documentTemplatesOsoby;

    /** @var Application_Model_Entities */
    protected $entitiesModel;

    /** @var Application_Service_Documents */
    protected $documentsService;

    /** @var Application_Model_Documents */
    protected $documents;

    /** @var Application_Model_DocumentsPending */
    protected $documentsPending;

    /** @var Application_Model_Osoby */
    protected $osoby;

    /** @var Application_Model_Documents */
    protected $documentsModel;

    /**
     * @throws Zend_Layout_Exception
     * @throws Exception
     */
    public function init()
    {
		
        parent::init();

        $this->cache = Zend_Cache::factory(
                'Core', 'File', array(
                'automatic_serialization' => true
                ), array(
                'cache_dir' => ROOT_PATH . '/cache'
                )
        );

        $this->documentsPrinter = Application_Service_DocumentsPrinter::getInstance();
        $this->deletedWorkerModel = Application_Service_Utilities::getModel('DeletedWorker');
        $this->documentsModel = Application_Service_Utilities::getModel('Documents');
        $this->permissionStatusModel = Application_Service_Utilities::getModel('PermissionStatus');
        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->todolistModal = Application_Service_Utilities::getModel("TodoList");
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
        $this->dictionaryModel = Application_Service_Utilities::getModel('Dictionary');
        $this->triggerModel = Application_Service_Utilities::getModel('Trigger');
        $this->registryService = Application_Service_Registry::getInstance();
        $this->registryEntitiesModel = Application_Service_Utilities::getModel('RegistryEntities');
        $this->registryFilterModel = Application_Service_Utilities::getModel('RegistryFilter');
        $this->registryActionModel = Application_Service_Utilities::getModel('RegistryAction');
        $this->entitiesModel = Application_Service_Utilities::getModel('Entities');
        $this->documentsService = Application_Service_Documents::getInstance();
        $this->documents = Application_Service_Utilities::getModel('Documents');
        $this->documentsPending = Application_Service_Utilities::getModel('DocumentsPending');
        $this->documentTemplates = Application_Service_Utilities::getModel('Documenttemplates');
        $this->documentTemplatesOsoby = Application_Service_Utilities::getModel('Documenttemplatesosoby');
        $this->osoby = Application_Service_Utilities::getModel('Osoby');
        Zend_Layout::getMvcInstance()->assign('section', 'Kategorie szkoleń');
        $this->view->baseUrl = $this->baseUrl;
        $this->view->server_url = $_SERVER['SERVER_NAME'];
		
    }

    public static function getPermissionsSettings()
    {
        $readPermissionsResolverById = array(
            'function' => 'registryAccessById',
            'params' => array('id'),
            'manualParams' => array(2 => 'read'),
            'permissions' => array(
                -1 => ['perm/registry/all-access'],
                0 => ['perm/registry/all-access'],
                1 => ['user/anyone'],
            ),
        );
		
        $readPermissionsResolverByRegistryId = $readPermissionsResolverById;
        $readPermissionsResolverByRegistryId['params'] = array('registry_id', 'id');

        $writePermissionsResolverById = $readPermissionsResolverById;
        $writePermissionsResolverById['manualParams'][2] = 'write';
        $writePermissionsResolverByRegistryId = $writePermissionsResolverById;
        $writePermissionsResolverByRegistryId['params'] = array('registry_id', 'id');

        $adminPermissionsResolverById = $readPermissionsResolverById;
        $adminPermissionsResolverById['manualParams'][2] = 'admin';
        $adminPermissionsResolverByRegistryId = $adminPermissionsResolverById;
        $adminPermissionsResolverByRegistryId['params'] = array('registry_id');

        $permissionsResolverBase = array(
            'function' => 'registryAccessBase',
            'permissions' => array(
                0 => ['perm/registry/all-access'],
                1 => ['user/anyone'],
            ),
        );

        $settings = array(
            'nodes' => [
                'registry-entries' => [
                    '_default' => [
                        'permissions' => [],
                    ],
                    'index' => [
                        'getPermissions' => [$readPermissionsResolverByRegistryId],
                    ],
                    'bulk-actions' => [
                        'getPermissions' => [$readPermissionsResolverByRegistryId],
                    ],
                    'report' => [
                        'getPermissions' => [$readPermissionsResolverByRegistryId],
                    ],
                    'ajax-update' => [
                        'getPermissions' => [$permissionsResolverBase],
                    ],
                    'update' => [
                        'getPermissions' => [$writePermissionsResolverByRegistryId],
                    ],
                    'save' => [
                        'getPermissions' => [$writePermissionsResolverByRegistryId],
                    ],
                    'remove' => [
                        'getPermissions' => [$writePermissionsResolverByRegistryId],
                    ],
                ],
            ]
        );

        return $settings;
    }

    public function datagridModalAction()
    {
        $this->view->ajaxModal = 1;
        $registryId = $this->getParam('id', 0);
        if ($registryId) {
            $registry = $this->registryModel->getOne(['id = ?' => $registryId]);
        }
        $registries = $this->registryModel->getList();
        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $this->view->paginator = $paginator;
        $this->view->registry = $registry;
        $this->view->registries = $registries;
        $this->view->id = $this->getParam('id', 0);
        $this->view->registryId = $registryId;
    }

    public function indexAction()
    {	
        $employee_registryId = $this->registryModel->getRegistryIdByName(Self::EMPLOYEE_REGISTERY);
        $registryId = $this->getParam('registry_id', 0);
        $page = (int) $this->getParam('page', 1);
        $pageSize = (int) $this->getParam('page_size', 9);
        $limitNum = $pageSize;
        $limitOffset = ($page - 1) * $pageSize;
        $totalItems = 0;
        
        $logicDocuments = new Logic_Documents();

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);
		
	

        $registry->loadData('entities');
			
	/* 	echo"<pre>";
		print_r($registry);
		exit;  */
        $auth = Application_Service_Authorization::getInstance();
        if (!$isSuperAdmin) {
            if (!$auth->isGranted('perm/' . $this->getParam('registry_id'), ['author' => $registry->author_id])) {
                $this->redirect("/");
            }
        }
        $filterId = $this->getParam('filter_id', 0);
        // this code
        $condition_stmt = [
            'registry_id = ?' => $registryId,
            'NOT ghost',
        ];

        $user = Application_Service_Authorization::getInstance()->getUser();
        $adminLink = Application_Service_Utilities::getModel('AdminLink');
        $osobyType = $adminLink->getTypeByLogin($user['login']);
        if (empty($osobyType)) {
            $osobyType['type'] = 'expert';
        }


        $tempWorkersInfo = $this->registryEntriesModel->getEntriesByRegistryId($employee_registryId);
        $allOfWorkers = array();
        foreach ($tempWorkersInfo as $key => $value) {
            $allOfWorkers[$value['id']] = $value;
        }
        $this->view->allOfWorkers = $allOfWorkers;
        // comagom code end 2019.3.21


        if ($user['rightsPermissions']) {
            $rights_permissions = json_decode($user['rightsPermissions'], true);
        }

        // this code
        // end
        if (!$auth->isGranted('perm/' . $registryId . '/admin', ['author' => $registry->author_id])) {
            if (!$auth->isGranted('perm/' . $registryId . '/read.all', ['author' => $registry->author_id]))
            //this code
                $condition_stmt['re.author_id = ?'] = $user['id'];
            //end
            //exit;
        }

        if (isset($filterId) && $filterId > 0) {
            // echo "came";
            $conditions = array();
            $filter_arr = $this->registryFilterModel->getOne($filterId);
		
            $conditions_arr = unserialize($filter_arr['meta_content']);


            if (isset($conditions_arr) && sizeof($conditions_arr) > 0) {

                foreach ($conditions_arr as $condition_detail) {
                    if (isset($condition_detail['parameter_id']) && $condition_detail['parameter_id'] > 0) {
                        $keyword = $condition_detail['keyword'];
                        $condition = trim($condition_detail['condition']);
                        $entityId = trim($condition_detail['entity_id']);
                        switch ($condition) {
                            case 'equal':
                                $str = " `value` = ? ";
                                break;
                            case 'not-equal':
                                $str = " `value` != ? ";
                                break;
                            case 'empty':
                                $str = " `value` = ? ";
                                $keyword = "''";
                                break;
                            case 'like':
                                $str = " `value` LIKE ? ";
                                $keyword = "%" . $keyword . "%";
                                break;
                            case 'not-like':
                                $str = " `value` NOT LIKE ? ";
                                $keyword = "%" . $keyword . "%";
                                break;
                            case 'start_with':
                                $str = " `value` LIKE ? ";
                                $keyword = $keyword . "%";
                                break;
                            case 'not_start_with':
                                $str = " `value` NOT LIKE ? ";
                                $keyword = $keyword . "%";
                                break;
                            case 'end_with':
                                $str = " `value` LIKE ? ";
                                $keyword = "%" . $keyword;
                                break;
                            case 'not_end_with':
                                $str = " `value` NOT LIKE ? ";
                                $keyword = "%" . $keyword;
                                break;
                        }
                        if ($entityId == 1) {
                            if ($str == '')
                                $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_varchar where registry_entity_id = '" . $condition_detail['parameter_id'] . "')"] = trim($keyword);
                            else
                                $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_varchar where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $str . " )"] = trim($keyword);
                        }else if ($entityId == 2) {
                            if ($str == '')
                                $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_text where registry_entity_id = '" . $condition_detail['parameter_id'] . "'"] = trim($keyword);
                            else
                                $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_text where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $str . " )"] = trim($keyword);
                        }else if ($entityId == 4) {
                            $start_str = " `value` >= ? ";
                            $end_str = " `value` <= ? ";
                            $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_date where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $start_str . " )"] = trim($keyword['from']);
                            $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_date where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $end_str . " )"] = trim($keyword['to']);
                        } else if ($entityId == 5) {
                            $start_str = " `value` >= ? ";
                            $end_str = " `value` <= ? ";
                            $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_datetime where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $start_str . " )"] = trim($keyword['from']);
                            $condition_stmt["re.id IN (SELECT entry_id from registry_entries_entities_datetime where registry_entity_id = '" . $condition_detail['parameter_id'] . "' AND " . $end_str . " )"] = trim($keyword['to']);
                        } else if ($entityId == 6) {
                            $str = str_replace("`value`", "CONCAT(UPPER(o.nazwisko),' ',UPPER(o.imie))", $str);
                            $condition_stmt["re.id IN (SELECT entry_id FROM registry_entries_entities_int as ree,registry_entities as re,osoby as o WHERE re.id=ree.registry_entity_id AND o.id=ree.value AND re.entity_id='" . $entityId . "' AND " . $str . " )"] = trim($keyword);
                        }
                    }
                }

                //echo "<pre>";print_r($condition_stmt);echo "</pre>";
                $paginator = $this->registryEntriesModel->getList($condition_stmt, [$limitNum, $limitOffset]);
                // $totalItems = $this->registryEntriesModel->count();
                $totalItems = $this->registryEntriesModel->countByRegistryID($registryId);
            }
        } 
		
		else {
            $paginator = $this->registryEntriesModel->getList($condition_stmt, [$limitNum, $limitOffset]);
            $totalItems = $this->registryEntriesModel->countByRegistryID($registryId);
        }


        // get my registry entries from totalItems. because of limit. comagom code start 2019.4.10
        $mytotalItems = $this->registryEntriesModel->countByRegistryIDAndAuthorID($registryId, $user['id']);
        $userRecordsLimit = Application_Service_Utilities::getModel('UserRecordsLimit');
        $limit = $userRecordsLimit->getLimitByType($osobyType['type']);
        // comagom code start 2019.3.21
        $selected_limit = json_decode($limit['limit_info']);
        $realLimitInfo = array();
        foreach ($selected_limit as $key => $value) {
            $realLimitInfo[$key] = $value;
        }
        $isSuperAdmin = Application_Service_Authorization::isSuperAdmin();
        if ($isSuperAdmin) {
            $this->view->recordLimit = -1;
        } else {
            $this->view->recordLimit = (int) $realLimitInfo[(int) $registryId];
            $this->view->mytotalItems = $mytotalItems;
        }
        // comagom code end 2019.4.10
        $totalPages = floor($totalItems / $pageSize) + ($totalItems % $pageSize > 0 ? 1 : 0);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
        $filters = $this->registryFilterModel->fetchAll();
		// echo"<pre>";print_r($filters);die;
        $this->view->filters = $filters;

        // comagom code start 2019.3.30
        if ($employee_registryId == $registryId) {
            foreach ($paginator as $key => $value) {
                if ($value['status_of_worker'] != 0) {
                    //unset($paginator[$key]);
                }
            }
        }
        $this->view->paginator = $paginator;
        // echo json_encode($paginator);
        $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();
        // if (!is_array($workers)) {
        //     $workers = [$workers];
        // }
        // echo json_encode($workers);
        /* echo '<pre>';print_r($registry->entities['']);die; */
		
		 /* foreach($registry['entities'] as $entity){
			echo"<pre>";
			print_r($entity['title']);
			
		}  */
        $this->view->workers = $workers;
        $this->view->registry = $registry;
        $this->view->rights_permissions = $rights_permissions;
        $this->view->registryId = $registryId;
        $this->view->totalItems = $totalItems;
        $this->view->totalPages = $totalPages;
        $this->view->currentPage = $page;
        $this->view->pageSize = $pageSize;
        $this->view->shownIndex = ($page - 1) * $pageSize + 1;


// -------------------------------COMAGOM CODE START------------------------------------
        $tempDataArray = $this->permissionStatusModel->getAllOfPermissionStatus();
		
        $dataArray = [];
        foreach ($tempDataArray as $key => $value) {
            $dataArray[$value['registry_entry_id']] = $value;
        }
        // echo json_encode($dataArray);
        $this->view->permissionStatus = $dataArray;
        $this->setDetailedSection($registry->title . ' -> lista wpisów');
        $this->setSection($registry->title);
        $this->view->section_url = [
            'title' => $registry->title,
            'section' => ' -> lista wpisów',
            'registryId' => $registryId
        ];


        $layout = $this->_helper->layout->getLayoutInstance();
		 // echo"<pre>";print_r($layout);die;
		// echo"<pre>";print_r($registry);die;
        $layout->assign('page_title_compare', $registry->title);
        $layout->assign('registry_title', $registry->title);
        $layout->assign('registry_id', $registryId);

        $todoListsInfo = $this->todolistModal->getPendingTodoItemsByRegistryId($registryId);
        $selected_window = "index_window";
        $layout->assign('todo_list_infos', $todoListsInfo);
        $layout->assign('selected_window', $selected_window);
        // foreach ($registry->entities as $key => $value) {
        // echo $value->entity->system_name . ":" . $value->title;
        // echo "<br>";
        // echo json_encode($value->entity->system_name);
        // }
// --------------------------------------   END   ---------------------------------------
    }

    public function bulkActionsAction()
    {

        $registryId = $this->getParam('registry_id', 0);

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);
        $module_id_val = $this->registryModel->getRegistryById($registryId);
        $rowAction = $_POST['rowsAction'];
        $rowSelect = $this->_getParam('entry_id');
        $rowSelect = array_keys(Application_Service_Utilities::removeEmptyValues($rowSelect));

        switch ($rowAction) {
            case "delete":
                $data = $this->getRequest()->getParams();
                $count_array = array();
                $user_id = Application_Service_Authorization::getInstance()->getUserId();
                $user_name = $this->osoby->requestObject($user_id);
                // comagom code start 2019.4.3
                //get workers list from employee table.
                $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();
                // comagom code end 2019.4.3
                foreach ($rowSelect as $id) {
                    if ($id && $workers[$id]) {
                        // previous code was handled by comagom 2019.4.3
                        // $this->registryEntriesModel->remove($id);
                        // comagom code start 2019.4.3
                        $registryId = $this->registryModel->getRegistryIdByName(Self::PERMISSION_REGISTRY_NAME);
                        $selectedRegistryEntry = $this->registryEntriesModel->getEntriesByRegistryIdAndWorkerId($registryId, $id);
                        $data = array(
                            'reason_content' => "the worker was deleted",
                            'withdrawal_date_time' => date('Y-m-d H:i:s'),
                            'status' => 3
                        );
                        if ($selectedRegistryEntry != null) {
                            $permission_one_record = $this->permissionStatusModel->getOneOfPermissionStatus($selectedRegistryEntry['id']);
                            $update_success_fail = $this->permissionStatusModel->updatePermissionByRegistryEntryID($data, $permission_one_record['id']);
                        }
                        $this->registryEntriesModel->update([
                            'status_of_worker' => 1,
                            'updated_at' => new Zend_Db_Expr("NOW()"),
                            'ghost' => '1',
                        ], ['id = ?' => $id]);
                        // coamgom code end 2019.4.3
                    } else {
                        $this->registryEntriesModel->remove($id);
                    }
                    $count_array['user_id'] = $user_id;
                    $count_array['module_id'] = $data['registry_id'];
                    $count_array['controller'] = $data['controller'];
                    $count_array['action'] = 'delete';
                    $count_array['field'] = 'awantura';
                    $count_array['action_name'] = $id;
                    $count_array['previous_value'] = '';
                    $count_array['new_value'] = '';
                    $count_array['module_id_value'] = $module_id_val['title'];
                    $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];

                    $this->registryActionModel->save($count_array);
                }
                break;
        }

        $this->redirectBack();
    }

    public function documentsAction()
    {
        $registryId = $this->getParam('registry_id', 0);
        $entryId = $this->getParam('id', 0);

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getFull($registryId, true);
        $entry = $this->registryEntriesModel->getFull([
            'id' => $entryId,
            'registry_id' => $registryId,
            ], true);

        $paginator = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getListFull(['entry_id = ?' => $entryId]);

        $this->view->paginator = $paginator;
        $this->view->registry = $registry;
        $this->view->entry = $entry;
        $this->setDetailedSection($registry->title . ' -> lista dokumentów');
    }

    public function downloadDocumentAction()
    {
        $registryId = $this->getParam('registry_id', 0);
        $entryId = $this->getParam('entry_id', 0);
        $documentId = $this->getParam('id', 0);

        $document = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getOne([
            'entry_id' => $entryId,
            'id' => $documentId,
            ], true);

        $this->_helper->layout->setLayout('report');
        $layout = $this->_helper->layout->getLayoutInstance();
	    $layout->assign('content', $document->data);
        $htmlResult = $layout->render();

        $filename = 'dokument_' . $this->getTimestampedDate() . '.pdf';

        $this->outputHtmlPdf($filename, $htmlResult);
    }

    public function previewDocumentAction()
    {
        $registryId = $this->getParam('registry_id', 0);
        $entryId = $this->getParam('entry_id', 0);
        $documentId = $this->getParam('id', 0);

        $document = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getOne([
            'entry_id' => $entryId,
            'id' => $documentId,
            ], true);

        $this->setTemplate('/home/preview-document', null, true);
        $this->view->ajaxModal = 1;
        $this->view->documentContent = $document->data;
    }

    public function updateDocumentAction()
    {
        $registryId = $this->getParam('registry_id', 0);
        $entryId = $this->getParam('entry_id', 0);
        $documentId = $this->getParam('id', 0);

        $document = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getOne([
            'entry_id' => $entryId,
            'id' => $documentId,
            ], true);

        $this->setTemplate('/home/preview-document', null, true);
        $this->view->ajaxModal = 1;
        $this->view->documentContent = $document->data;
    }

    public function reportAction()
    {
        ini_set('max_execution_time', 0);
        $this->_helper->layout->setLayout('report');
        $registryId = $this->getParam('registry_id', 0);

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $conditions = ['registry_id = ?' => $registryId];

        $selected_entiries = $this->getParam('selected_entiries', 0);
        if ($selected_entiries != 0) {
            $selected_entiries = explode(',', $selected_entiries);
            $conditions['id IN (?)'] = $selected_entiries;
        }

        $paginator = $this->registryEntriesModel->getList($conditions);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $result = $this->registryEntriesModel->getEnitiesValuesForSelectedDatatypes($paginator, $registryId);

        if (empty($result['tab_count']) && empty($result['selectedAttrName'])) {
            echo "No data found to generate report!!";
            exit;
        }
        $this->view->selectedAttrName = $result['selectedAttrName'];
        $this->view->tab_count = $result['tab_count'];

        $this->view->paginator = $paginator;
        $this->view->registry = $registry;
        $this->view->title = $registry->title;
        $this->view->date = $registry->created_at;

        $settings = Application_Service_Utilities::getModel('Settings');
        $this->view->config = $settings->getAll();

        //$htmlResult = $this->view->render('registry-entries/report.html');
        if (isset($result['tab_count']) && !empty($result['tab_count']) && array_values($result['tab_count'])['0'] > 0) {
            $this->view->maxstep = array_values($result['tab_count'])['0'];
            $htmlResult = $this->view->render('registry/generatereport_tabs.html');
        } else {
            $htmlResult = $this->view->render('registry/generatereport.html');
        }

        $date = new DateTime();
        $time = $date->format('\TH\Hi\M');
        $timeDate = new DateTime();
        $timeDate->setTimestamp(0);
        $timeInterval = new DateInterval('P0Y0D' . $time);
        $timeDate->add($timeInterval);
        $timeTimestamp = $timeDate->format('U');
        $filename = 'raport_rejestry_' . date('Y-m-d') . '_' . $timeTimestamp . '.pdf';

        $htmlResult = html_entity_decode($htmlResult);

        $this->_forcePdfDownload = false;
        $res = $this->outputHtmlPdf($filename, $htmlResult, true, false, true);
        echo $res;
        exit;
    }

    public function ajaxUpdateAction()
    {
        $this->setDialogAction();
        $this->updateAction();
        $this->view->dialogTitle = 'Dodaj wpis';
    }

// -------------------------COMAGOM CODE START---------------------------------

    public function ajaxPopUpWithdrawalInformationAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-pop-up-withdrawal-information');

        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);
        $worker_name = $this->getParam('worker_name', '') . '-' . $this->getParam('worker_surname', '');

        if ($id) {
            $withdrawlInfo = $this->permissionStatusModel->getWithdrawlInformationByRegistryIdAndRegistryEntryId($registryId, $id);
            $this->view->fullname = $worker_name;
            $this->view->reason_content = $withdrawlInfo['reason_content'];
            $this->view->withdrawal_date_time = $withdrawlInfo['withdrawal_date_time'];
        }
    }

    public function ajaxPopUpNotAccessAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-pop-up-not-access');

        $this->view->message = "you can not access to this registry!";
    }

    public function ajaxPopUpWithdrawalAction()
    {
        $this->setDialogAction();
        $this->setTemplate('ajax-pop-up-withdrawal');

        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);

        if ($id) {
            $row = $this->registryEntriesModel->getFull([
                'id' => $id,
                'registry_id' => $registryId,
                ], true);
            $workersList = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();

            if (!is_array($workersList)) {
                $workersList = [$workersList];
            }
            $this->view->fullname = $workersList[$row->worker_id]['imie'] . "-" . $workersList[$row->worker_id]['nazwisko'];
            $this->view->registry_entry_id = $id;
            $this->view->registry_id = $registryId;
        }
    }

    public function withdrawnAction()
    {
        $id = $this->getParam('registry_entry_id', 0);
        $registryId = $this->getParam('registry_id', 0);
        
        $logicDocuments = new Logic_Documents();
        $logicPermissions = new Logic_Permissions();
        
        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $adapter->beginTransaction();
        
        try {
            $data = [
                'reason_content' => $this->getParam('reason_content'),
                'withdrawal_date_time' => date('Y-m-d H:i:s'),
            ];
            
            $logicPermissions->changePermissionsStatus($id, Logic_Permissions::STATUS_DELETED, $data);
            $logicDocuments->archivizeRegistryEntryActiveDocuments($id);
            
            $this->flashMessage('success', 'Uprawnienie zostało wycofane');
            
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            $this->flashMessage('error', $e->getMessage());
        }
        
        return $this->redirect($this->baseUrl . '/index/registry_id/' . $registryId);
    }

    // public function todoitemAction() { 
    //     $registryId = $_POST['registry_id'];
    //     $registryTitle = $_POST['registry_title'];
    //     $todo_task_name = $_POST['todo_task_name'];
    //     $data = array(
    //         'registry_id' => (int)$registryId,
    //         'registry_title' => $registryTitle,
    //         'todo_title' => $todo_task_name,
    //         'status' => 0,
    //     );
    //     $insertId = $this->todolistModal->saveTodoItem($data);
    //     if($insertId) {
    //         echo "success";
    //     }
    // }
// -------------------------COMAGOM CODE END-----------------------------------
    //ftforest640@gmail.com
    public function ajaxPopUpWindowAction()
    {

        //$req = $this->getRequest();
        //$data = $req->getParam('document');
        $this->setDialogAction();
        $this->setTemplate('ajax-pop-up-window');

        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);
        $registry = $this->registryModel->getOne($registryId, true);
        if ($id) {
            $row = $this->registryEntriesModel->getFull([
                'id' => $id,
                'registry_id' => $registryId,
                ], true);
            Application_Service_Registry::getInstance()->entryGetEntities($row);


            $sectionName = 'edytuj wpis';
        } elseif ($registryId) {


            $row = $this->registryEntriesModel->createRow([
                'registry_id' => $registryId,
            ]);

            $row->loadData(['registry']);

            $sectionName = 'dodaj wpis';
        } else {
            throw new Exception('404', 404);
        }
        foreach ($row->registry->entities as $val) {
            $character = json_decode($val->config);
            $steps[] = $character->tab;
        }

        $this->view->data = $row;

        //========================Autor
        $registryId = $this->getParam('registry_id', 0);

        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        foreach ($paginator as $d) {
            if ($id == $d['id']) {
                $this->view->autor = $d['author']['display_name'];
            }
        }
    }

    public function createFromTaskAction()
    {
        $id = $this->getParam('task', 0);
        $storageTasksModel = Application_Service_Utilities::getModel('StorageTasks');
        $tasksModel = Application_Service_Utilities::getModel('Tasks');
        $storageTask = $storageTasksModel->findOne($id);
        $task = $tasksModel->findOne($storageTask['task_id']);

        $this->setParam('registry_id', $task['object_id']);

        $this->updateAction();

        $this->setTemplate('update');

        $this->view->task = $task;
        $this->view->storageTask = $storageTask;
        $this->view->taskMode = true;
    }

    public function addnewrecordAction()
    {
        $data = $this->_request->getPost();

        $registryId = $data['registry_id'];

        $primarykeyrow = Application_Service_Utilities::getModel('RegistryEntities')->checkPrimaryKeyField($registryId);
        $elementid = $primarykeyrow['id'];
        /* Only for registry Employee/Pracownik */
        $employee_registry = Application_Model_Registry::EMPLOYEE_REGISTERY;
        $registry = $this->registryModel->getOne($registryId, true);
        $createDocumentForNewEmployee = $newUser = $dataChanged = false;
        if ($registry['title'] == $employee_registry) {
            $createDocumentForNewEmployee = true;
        }

        $valueofpk = $this->db->query("SELECT `value` FROM `registry_entries_entities_varchar` WHERE `registry_entity_id` in (select `id` from `registry_entities` where `system_name`='" . $primarykeyrow['system_name'] . "')")->fetchAll();

        foreach ($valueofpk as $val) {

            if ($_POST['element_' . $elementid] == $val['value']) {
                $idarr = $this->db->query("SELECT `entry_id` FROM `registry_entries_entities_varchar` WHERE `registry_entity_id` = '" . $elementid . "' AND `value` = '" . $_POST['element_' . $elementid] . "' ")->fetchAll();
                $id = $idarr[0]['entry_id'];
            }
        }

        /** @var object $row */
        $newUser = true;
        $row = $this->registryEntriesModel->createRow(array_merge($this->getRequest()->getParams(), [
            'registry_id' => $registryId,
            'author_id' => Application_Service_Authorization::getInstance()->getUserId(),
        ]));

        $documentTemplateId = $this->documentTemplates->getActiveDocumentTemplateId($registryId);

        try {
            $this->db->beginTransaction();
            $createDocument = true;
            $count = 0;
            $count_array = array();
            $user_id = Application_Service_Authorization::getInstance()->getUserId();
            $module_id_val = $this->registryModel->getRegistryById($registryId);
            $list_id = $this->registryEntriesModel->getall();
            $user_name = $this->osoby->requestObject($user_id);
            foreach ($list_id as $data_id) {
                $last_id = $data_id->id;
            }
            foreach ($data as $data_key => $val) {

                if (strpos($data_key, 'element_') !== false) {
                    if (is_array($val)) {
                        foreach ($val as $n_value) {
                            $new_val .= $n_value . ' ';
                        }
                        $val = $new_val;
                    }
                    if (!empty($val)) {
                        $element_id[] = ltrim($data_key, 'element_');
                        $element_val[] = html_entity_decode(strip_tags($val));
                    }
                }
            }

            $element_ids = json_encode($element_id);
            $element_value = json_encode($element_val);

            $count_array['user_id'] = $user_id;
            $count_array['module_id'] = $data['registry_id'];
            $count_array['controller'] = $data['controller'];
            $count_array['action'] = 'wstaw wiersz';
            $count_array['field'] = 'awantura';
            $count_array['action_name'] = $element_ids;
            $count_array['previous_value'] = '';
            $count_array['new_value'] = $element_value;
            $count_array['record_id'] = $last_id;
            $count_array['module_id_value'] = $module_id_val['title'];
            $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
            $this->registryActionModel->save($count_array);

            $registryEntities = $this->registryEntitiesModel->getEntitiesByRegistryId($registryId);
            $entriesCount = count($registryEntities);
            $entityIds = array();

            if ($entriesCount > 0) {
                foreach ($registryEntities as $entity) {
                    $entityIds[] = $entity['id'];
                }
            }

            $formIds = array();
            foreach ($data as $k => $v) {
                if (stripos($k, "element_") !== false) {
                    $formId = str_replace("element_", "", $k);
                    $formIds[] = $formId;
                }
            }

            foreach ($formIds as $id) {
                $val = $this->getParam('element_' . $id);
                if (empty($val)) {
                    $createDocument = false;
                }
            }

            $this->registryService->entrySave($row, $data);

            if ($data['update_documents']) {
                $this->registryService->entryUpdateDocuments($row->id);
            }

            if (!$id) {
                foreach ($row->registry->documents_templates as $documentsTemplate) {
                    if ($documentsTemplate->flag_auto_create) {
                        $this->registryService->entryCreateDocument($row->id, $documentsTemplate->id);
                    }
                }
            }

            if ($newUser && $createDocumentForNewEmployee) {
                /* Template IDs for which documents will be created for new employees */
                $documentTemplatesIds = $this->documentTemplates->getActiveDocumentTemplateIdForNewAssigny();
                foreach ($documentTemplatesIds as $tempId) {
                    $p_data = array(
                        'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                        'worker_id' => $row->id,
                        'registry_entry_id' => $row->id,
                        'documenttemplate_id' => $tempId,
                    );
                    //gohar no
                    $this->documentsPending->save($p_data);
                    $t_data = array(
                        'documenttemplate_id' => $tempId,
                        'worker_id' => $row->id,
                    );
                    $this->documentTemplatesOsoby->save($t_data);
                }
            }

            $regid = $this->dictionaryModel->getRegidByName('dictionary');
            if ($registryId === $regid) {
                $this->dictionaryModel->setDictionary();
            }

            $this->db->commit();

            if ($documentTemplateId) {
                foreach ($row->getDocumentWorkerIds() as $workerId) {
                    $latestPendingDocument = $this->documentsPending->getLatestPendingDocument($workerId, $documentTemplateId);
                    if (count($latestPendingDocument) > 0) {
                        $status = $latestPendingDocument[0]['status'];
                        $pendingDocumentId = $latestPendingDocument[0]['id'];
                        $documentId = 0;
                        $latestDocument = $this->documents->getLatestRegistryDocuments($workerId, [$documentTemplateId]);
                        if (count($latestDocument) > 0) {
                            $documentId = $latestDocument[0]['id'];
                        }

                        if ($pendingDocumentId && $documentId) {
                            if ($createDocument) {
                                if ($status != Application_Model_DocumentsPending::STATUS_PENDING) {
                                    //gohar no 
                                    $this->documentsPending->save([
                                        'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                                        'worker_id' => $workerId,
                                        'registry_entry_id' => $row->id,
                                        'documenttemplate_id' => $documentTemplateId,
                                    ]);
                                } else {
                                    $this->documentsPending->save([
                                        'id' => $pendingDocumentId,
                                        'registry_entry_id' => $row->id,
                                    ]);
                                }
                                $documentContent = Application_Service_DocumentsPrinter::getInstance()->getPendingDocumentPreview($pendingDocumentId);
                                $this->documents->save([
                                    'id' => $documentId,
                                    'active' => Application_Service_Documents::VERSION_OUTDATED,
                                    'registry_entry_id' => $row->id,
                                    'new_content' => $documentContent
                                ]);
                            } else {
                                $documents = Application_Service_Utilities::getModel('Documents');
                                $documents->update([
                                    'active' => Application_Service_Documents::VERSION_ARCHIVE,
                                    ], ['id = ?' => $documentId]);
                                $documentsPending = Application_Service_Utilities::getModel('DocumentsPending');
                                $documentsPending->update([
                                    'status' => Application_Model_DocumentsPending::STATUS_REMOVED,
                                    ], ['id = ?' => $pendingDocumentId]);
                            }
                        }
                    } else {
                        if ($createDocument) {
                            $this->documentsService->create($documentTemplateId, $workerId, $row->id);
                        }
                    }

                    $documentTemplateOsoby = $this->documentTemplatesOsoby->getDocumentTemplateOsoby($workerId, $documentTemplateId);
                    if (!$documentTemplateOsoby) {
                        $workerData = array(
                            'worker_id' => $workerId,
                            'documenttemplate_id' => $documentTemplateId,
                        );
                        $r = $this->documentTemplatesOsoby->save($workerData);
                    }
                }
            }
        } catch (Application_SubscriptionOverLimitException $x) {
            $this->_redirect('subscription/limit');
        } catch (Exception $e) {
            throw new Exception('Próba zapisu danych nie powiodła się.' . $e->getMessage(), 500, $e);
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->outputJson([
                'status' => (int) 1,
                'result' => $row->id
            ]);
        } else {
            $this->flashMessage('success', 'Dodano wpis');
            if ($this->getParam('addAnother')) {
                
            } else {
                $req = $this->getRequest();
                $params = $req->getParams();
                foreach ($params as $k => $val) {
                    if ((stripos($k, 'element')) !== false) {
                        if (!is_array($val)) {
                            $name = $val;
                        }
                    }
                }
            }
        }
    }

    public function updateAction()
    {
        $request = $this->getRequest();
        $idRegistryEntry = $request->getParam('id');
        $idRegistry = $request->getParam('registry_id');
        
        $isEdit = !empty($idRegistryEntry);
        
        $logic = new Logic_Registry();
        
        // comagom code start 2019.4.10
		//echo"Fdsfs";die;
        
        $auth = Application_Service_Authorization::getInstance();
        $isSuperAdmin = Application_Service_Authorization::isSuperAdmin();
        // end
        $registry = $this->registryModel->getOne($this->getParam('registry_id'), true);
        $registry->loadData('entities');
        if (!$isSuperAdmin) {
            if (!($auth->isGranted('perm/' . $this->getParam('registry_id') . '/admin', ['author' => $registry->author_id]) || $auth->isGranted('perm/' . $this->getParam('registry_id') . '/write.all', ['author' => $registry->author_id]) || $auth->isGranted('perm/' . $this->getParam('registry_id') . '/write.my', ['author' => $registry->author_id]))) {
                $this->redirect("/");
                //    die();
            }
        }
		

        // $htmlContent = $this->getmodalviewAction($this->getParam('registry_id', 0));

        if (!$isSuperAdmin) {
            $user = Application_Service_Authorization::getInstance()->getUser();
            $adminLink = Application_Service_Utilities::getModel('AdminLink');
            $osobyType = $adminLink->getTypeByLogin($user['login']);
            $mytotalItems = $this->registryEntriesModel->countByRegistryIDAndAuthorID($this->getParam('registry_id'), $user['id']);
            $userRecordsLimit = Application_Service_Utilities::getModel('UserRecordsLimit');
            $limit = $userRecordsLimit->getLimitByType($osobyType['type']);

            $registryId = $this->getParam('registry_id');
            $selected_limit = json_decode($limit['limit_info']);
            $realLimitInfo = array();
            foreach ($selected_limit as $key => $value) {
                $realLimitInfo[$key] = $value;
            }
            $this->view->limit = 1;
            $this->view->totalItems = $mytotalItems;
            $this->view->recordLimit = (int) $realLimitInfo[(int) $registryId];
        } else {
            $registryId = $this->getParam('registry_id');
            $totalItems = $this->registryEntriesModel->countByRegistryID($this->getParam('registry_id'));
            $this->view->limit = -1;
            $this->view->totalItems = $totalItems;
            $this->view->recordLimit = (int) $realLimitInfo[(int) $registryId];
        }
        // comagom code end

        $id = $this->getParam('clone', 0);
        $cloneMode = true;
        if (!$id) {
            $id = $this->getParam('id', 0);
            $cloneMode = false;
        }

        $workers = array();
        $selectedWorkers = array();
        $workers_temp = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();
        $documentTemplateId = 0;

        if (!$this->getParam('id')) {
//---------------- COMAGOM CODE START -----------------
            $whichWindow = "addWindow";

// ---------------         END         ----------------
            $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();
            $selectedWorkers = Application_Service_DocumentsPrinter::getInstance()->getActiveWorkersList();

            $documentTemplateId = $this->documentTemplates->getDocumentTemplateId($this->getParam('registry_id'));

            if ($documentTemplateId) {
                $templateWorkerIds = $this->documentTemplatesOsoby->getWorkerIds($documentTemplateId);
                if ($templateWorkerIds) {
                    foreach ($workers as $k => $worker) {
                        if (in_array($k, $templateWorkerIds)) {
                            unset($workers[$k]);
                        }
                    }
                    foreach ($selectedWorkers as $k => $worker) {
                        if (in_array($k, $templateWorkerIds)) {
                            unset($selectedWorkers[$k]);
                        }
                    }
                }
            } else {

                $workers = array();
                $selectedWorkers = array();
            }
        }
        
        // echo "<pre>";
        //     print_r($workers);
        //     echo "</pre>";
        //     exit;

        $registryId = $this->getParam('registry_id', 0);

        $totalItems = $this->registryEntriesModel->countByRegistryID($registryId);

        if ($id) {
//---------------- COMAGOM CODE START -----------------
            $whichWindow = "updateWindow";
// ---------------         END         ----------------
            $row = $this->registryEntriesModel->getFull([
                'id' => $id,
                'registry_id' => $registryId,
                'NOT ghost',
                ], true);
            Application_Service_Registry::getInstance()->entryGetEntities($row);

            if ($cloneMode) {
                $sectionName = 'dodaj wpis';
                $row->id = null;
            } else {
                $sectionName = 'edytuj wpis';
            }
        } elseif ($registryId) {
            $row = $this->registryEntriesModel->createRow([
                'registry_id' => $registryId,
            ]);
            $row->loadData(['registry']);
            
            $sectionName = 'dodaj wpis';
        } else {
            throw new Exception('404', 404);
        }
        $tab_names = $old_values = array();
        $steps = array(0);

        // comagom code start 2019.3.31
        $deletedAllWorkers = $this->deletedWorkerModel->getAllofDeletedWorkers();
        $deletedAllWorkersInfo = [];
        foreach ($deletedAllWorkers as $key => $value) {
            $deletedAllWorkersInfo[$value['worker_id']] = $value;
        }
		
		
        //comagom code end
        foreach ($row->registry->entities as $val) {
            $entityData = $row->entities[$val->id];
            // $test1 = $val->id;
            // $test2 = $val->registry_id;
            // $test3 = $entityData->registry_entity_id;
            if (!is_array($entityData)) {
                $test = $this->registryEntitiesModel->getRegistryIdByRegistryEntityId($entityData->registry_entity_id);
            } else {
                $test = $this->registryEntitiesModel->getRegistryIdByRegistryEntityId($entityData[0]->registry_entity_id);
            }
            // $test = $this->registryEntitiesModel->getRegistryIdByRegistryEntityId($entityData[0]->registry_entity_id);
            $registryTitle = $this->registryModel->getRegistryById($test['registry_id']);
            
            if ($registryTitle['title'] == 'Uprawnienia') {
                $selected_worker_id = $this->registryEntriesModel->getWorkerIdByRegistryEntryId($id);
                
                if ((int) $selected_worker_id[0]['worker_id'] != 0) {
                    if (!empty($workers_temp[(int) $selected_worker_id[0]['worker_id']])) {
                        $entityData->value = $workers_temp[(int) $selected_worker_id[0]['worker_id']]['imie'] . " - " . $workers_temp[(int) $selected_worker_id[0]['worker_id']]['nazwisko'];
                    } else {
                        $entityData->value = $deletedAllWorkersInfo[(int) $selected_worker_id[0]['worker_id']]['worker_name'] . "-" . $deletedAllWorkersInfo[(int) $selected_worker_id[0]['worker_id']]['worker_surname'];
                    }
                }
            }
            $old_values[$val->id] = $entityData->value;

            $character = json_decode($val->config);
            $steps[] = $character->tab;
            if (isset($character->tab_name)) {
                $tab_names[$character->tab] = $character->tab_name;
            }
        }
        $maxstepval = max($steps);
        if (isset($maxstepval)) {
            $maxstep = $maxstepval;
        } else {
            $maxstep = 0;
        }
        $test = $row["_data_custom"]['_entities_named']['name']['_data']['value'];
        $this->view->maxstep = $maxstep;
        $this->view->tab_names = $tab_names;
        $this->view->data = $row;
        $this->view->old_data_value = $old_values;
        $workers2 = array();
        $selectedWorkers2 = array();
        foreach ($workers as $k => $v) {
            $worker = array(
                "id" => (string) $k,
                "name" => $v['imie'] . " - " . $v['nazwisko'],
                "icon" => "fa icon-wrench"
            );
            array_push($workers2, $worker);
        }
        foreach ($selectedWorkers as $k => $v) {
            $worker = array(
                "id" => (string) $k,
                "name" => $v['imie'] . " - " . $v['nazwisko'],
                "icon" => "fa icon-wrench"
            );
            
            if (Logic_Registry::REGISTRY_PERMISSIONS == $idRegistry && $logic->hasWorkerRegistryEntry($idRegistry, $k)) {
                continue;
            }
            
            array_push($selectedWorkers2, $worker);
        }
        
        if (!empty($idRegistryEntry) && Logic_Registry::REGISTRY_PERMISSIONS == $idRegistry) {
            $selectedWorkers2 = [];
            $rowEntry = $logic->getRegistryEntryRow($idRegistryEntry);
            $dataEntities = $logic->getRegistryEntitiesData($idRegistryEntry);
            $string = null;
            
            foreach ($dataEntities as $rowEntity) {
                if ($rowEntity['data']->count() > 0) {
                    foreach ($rowEntity['data'] as $rowValue) {
                        $string .= $rowValue->value;
                    }
                }
            }
            
            $selectedWorkers2[] = [
                'id' => $rowEntry->worker_id,
                'name' => $string,
                'selected' => true,
            ];
            
            $this->view->workerSelectDisabled = true;
        }
        
        if (empty($selectedWorkers2) && $registryId == Logic_Registry::REGISTRY_PERMISSIONS) {
            $this->flashMessage('error', 'By móc dodać uprawnienie najpierw dodaj pracowników do zasobów ludzkich');
            return $this->_helper->redirector('index', 'registry-entries', null, ['registry_id' => Logic_Registry::REGISTRY_PERMISSIONS]);
        }
        
        $this->view->workers = $workers2;
        $this->view->selectedWorkers = $selectedWorkers2;
        $this->view->documentTemplateId = $documentTemplateId;
        $this->view->registryId = $registryId;
        $registry = $this->registryModel->getOne($registryId, true);
		
		 $layout = $this->_helper->layout->getLayoutInstance();
        $layout->assign('registry_id', $registryId);
        $this->view->setScriptPath(APPLICATION_PATH . '/views/templates/');
        $this->view->addModelContent = $htmlContent;
        $this->setDetailedSection($registry->title . ' : ' . $sectionName);
        $this->view->section_url = [
            'title' => $registry->title,
            'section' => ' : ' . $sectionName,
            'registryId' => $registryId
        ];

        // ----------------------------------------- COMAGOM CODE START ---------------------------------
        if ($whichWindow == 'updateWindow') {
            $selectedStatusInfo = $this->permissionStatusModel->getOneOfPermissionStatus($id);
        }
        $this->view->selectedStatusInfo = $selectedStatusInfo;
        $this->view->whichWindow = $whichWindow;
        $layout = $this->_helper->layout->getLayoutInstance();
		
        $this->view->isEdit = $isEdit;
        $selected_window = "update_window";
        $layout->assign('selected_window', $selected_window);
    }

    public function getmodalviewAction($registryId)
    {
        //$view = new Zend_View();
        $workers = array();
        $documentTemplateId = 0;

        $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();

        $documentTemplateId = $this->documentTemplates->getDocumentTemplateId($registryId);


        if ($documentTemplateId) {
            $templateWorkerIds = $this->documentTemplatesOsoby->getWorkerIds($documentTemplateId);
            if ($templateWorkerIds) {
                foreach ($workers as $k => $worker) {
                    if (in_array($k, $templateWorkerIds)) {
                        unset($workers[$k]);
                    }
                }
            }
        } else {
            $workers = array();
        }

        if ($registryId) {

            $row = $this->registryEntriesModel->createRow([
                'registry_id' => $registryId,
            ]);
            $row->loadData(['registry']);

            $sectionName = 'dodaj wpis';
        }

        $tab_names = $old_values = array();
        $steps = array(0);
        foreach ($row->registry->entities as $val) {
            $entityData = $row->entities[$val->id];

            $old_values[$val->id] = $entityData->value;

            $character = json_decode($val->config);
            $steps[] = $character->tab;
            if (isset($character->tab_name)) {
                $tab_names[$character->tab] = $character->tab_name;
            }
        }

        $maxstepval = max($steps);
        if (isset($maxstepval)) {
            $maxstep = $maxstepval;
        } else {
            $maxstep = 0;
        }

        $this->view->maxstep = $maxstep;
        $this->view->tab_names = $tab_names;
        $this->view->data = $row;
        $this->view->old_data_value = $old_values;

        $workers2 = array();

        foreach ($workers as $k => $v) {
            $worker = array(
                "id" => (string) $k,
                "name" => $v['imie'] . " - " . $v['nazwisko'],
                "icon" => "fa icon-wrench"
            );
            array_push($workers2, $worker);
        }

        $this->view->workers = $workers2;
        $this->view->documentTemplateId = $documentTemplateId;
        //$registry = $this->registryModel->getOne($registryId, true);        
        //$this->_helper->layout()->setLayout("modelview");
        //$view->setDetailedSection($registry->title . ': ' . $sectionName);
        //$layout_path = $this->_helper->layout()->getLayoutPath();
        //$layout_getmodel = new Zend_Layout();
        //$layout_getmodel->setLayoutPath($layout_path) // assuming your layouts are in the same directory, otherwise change the path
        //       ->setLayout('blank');
        //$this->_helper->layout->setLayout('blank');
        $this->view->setScriptPath(APPLICATION_PATH . '/views/templates/');
        $htmlResult = $this->view->render('registry-entries/getmodelview.html');
        //$htmlResult = html_entity_decode($htmlResult);

        $layout_mail = new Zend_Layout();
        $layout_mail->setLayoutPath(APPLICATION_PATH . '/views/templates/layouts') // assuming your layouts are in the same directory, otherwise change the path
            ->setLayout('modelview');

        // Filling layout
        $layout_mail->content = $htmlResult;
        // Recovery rendering your layout
        $mail_content = $layout_mail->render();

        return $mail_content;
        /*
          $view->workers = $workers2;
          $view->documentTemplateId = $documentTemplateId;
          $view->maxstep = $maxstep;
          $view->tab_names = $tab_names;
          $view->data = $row;
          $view->old_data_value = $old_values;

          $view->setScriptPath(APPLICATION_PATH . '/controllers/');
          $output = $view->render('getmodelview.php');
          echo $output; exit;
         */
        /*

          $layout_path = $this->_helper->layout()->getLayoutPath();
          $layout_mail = new Zend_Layout();
          $layout_mail->setLayoutPath($layout_path) // assuming your layouts are in the same directory, otherwise change the path
          ->setLayout('modalview');

          // Filling layout
          $layout_mail->content = $html_content;
          // Recovery rendering your layout
          $mail_content = $layout_mail->render();
          var_dump($mail_content); exit;
         */
    }

    public function checkduplicatedentryAction($data = null)
    {
        if ($data == null) {
            $data = $this->getRequest()->getParams();
            $data = $data['data'];
        }

        $data = json_decode($data);
        $condition_stmt = array();
        $condition_stmt['registry_id = ?'] = $data->registry_id;
        $registry = $this->registryModel->getOne($data->registry_id, true);
        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $data->registry_id]);
        $registry->loadData('entities');
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $duplicated_flag = false;
        foreach ($paginator as $d) {
            $flag = true;
            foreach ($registry['entities'] as $entity) {

                if (preg_replace("/\r|\n/", "", strip_tags($data->element_ . '' . $entity->id)) != preg_replace("/\r|\n/", "", strip_tags($d->entityToString($entity->id)))) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $duplicated_flag = true;
            }
        }
        $this->outputJson([
            'status' => (int) $duplicated_flag
        ]);
        return $duplicated_flag;
    }

    public function saveAction()
    {
        $logic = new Logic_Documents();
        $logicRegistry = new Logic_Registry();
        $logicPermissions = new Logic_Permissions();
		//echo'dsfdf';die;
		
		//print_r($_POST);exit;
        $request = $this->getRequest();
        $values = $request->getPost();
        
		$worker_id= $_POST['element_1178'][0];
        
        if (empty($worker_id) && !empty($values['worker_id'])) {
            $worker_id = $values['worker_id'];
        }
        
        $id = $this->getParam('id', 0);
//-------------------------------------- COMAGOM CODE START ----------------------------------------
        $whichWindowID = 0;
        $whichWindowID = $this->getParam('id', 0);

        $new_status = $this->getParam('status', 0);
        // echo $new_status;
        // die();
//--------------------------------------         END        ----------------------------------------

        $registryId = $this->getParam('registry_id', 0);
        //comagom code start 2019.4.2 : when user add or edit building , place registry. then, this cache will save these two values in cache file.
        // $buildingRegistryId = $this->registryModel->getRegistryIdByName(Self::BUILDING_REGISTERY);
        // $placeRegistryId = $this->registryModel->getRegistryIdByName(Self::PLACE_REGISTERY);
        // if($registryId == $buildingRegistryId || $registryId == $placeRegistryId) {
        //     $cache_building_place_name = 'registryEntriesTable_'.Self::BUILDING_REGISTERY.'_'. Self::PLACE_REGISTERY .'_flag';
        //     $cacheData = array(
        //         "Buildings" => "true",
        //         "Places" => "true",
        //     );
        //     $this->cache->save($cacheData, $cache_building_place_name);
        // }
        //comagom code end 2019.4.2
        
        $primarykeyrow = Application_Service_Utilities::getModel('RegistryEntities')->checkPrimaryKeyField($registryId);
         $elementid = $primarykeyrow['id'];
        /* Only for registry Employee/Pracownik */
        $employee_registry = Application_Model_Registry::EMPLOYEE_REGISTERY;
        $registry = $this->registryModel->getOne($registryId, true);
		/* echo"<pre>";
		print_r($registry);
		exit; */
        $createDocumentForNewEmployee = $newUser = $dataChanged = false;
        if ($registry['title'] == $employee_registry) {
            $createDocumentForNewEmployee = true;
        }

        $valueofpk = $this->db->query("SELECT `value` FROM `registry_entries_entities_varchar` WHERE `registry_entity_id` in (select `id` from `registry_entities` where `system_name`='" . $primarykeyrow['system_name'] . "')")->fetchAll();

        foreach ($valueofpk as $val) {

            if ($_POST['element_' . $elementid] == $val['value']) {
                $idarr = $this->db->query("SELECT `entry_id` FROM `registry_entries_entities_varchar` WHERE `registry_entity_id` = '" . $elementid . "' AND `value` = '" . $_POST['element_' . $elementid] . "' ")->fetchAll();
                $id = $idarr[0]['entry_id'];
            }
        }

        /** @var object $row */
        
        try {
            $this->db->beginTransaction();
            
            if ($id) {
                $row = $this->registryEntriesModel->getFull([
                    'id' => $id,
                    'registry_id' => $registryId,
                    ], true);
                Application_Service_Registry::getInstance()->entryGetEntities($row);
            } else {
                $newUser = true;
                $row = $this->registryEntriesModel->createRow(array_merge($this->getRequest()->getParams(), [
                    'registry_id' => $registryId,
                    'author_id' => Application_Service_Authorization::getInstance()->getUserId(),
                    'worker_id' => $worker_id,
                ]));
            }

            $documentTemplateId = $this->documentTemplates->getActiveDocumentTemplateId($registryId);

            $createDocument = true;
            $data = $this->getRequest()->getParams();

            $count_array = array();
            $old_values = array();
            
            foreach ($row->registry->entities as $val) {
                $entityData = $row->entities[$val->id];
                $old_values['element_' . $val->id] = $entityData->value;
            }
            
            if (!empty($old_values)) {
                foreach ($data as $data_key => $val) {
                    if (strpos($data_key, 'element_') !== false) {
                        if (is_array($val)) {
                            $registry_entity_id_ = ltrim($data_key, 'element_');
                            $prev_val = $this->db->query("SELECT `value` FROM `registry_entries_entities_varchar` WHERE `registry_entity_id` = '" . $registry_entity_id_ . "' AND `entry_id` = '" . $data['id'] . "' ")->fetchAll();
                            foreach ($prev_val as $key => $p_val) {
                                $prev_value .= $p_val['value'] . ' ';
                            }
                            $old_values[$data_key] = $prev_value;
                        }
                    }
                }
            }

            $user_id = Application_Service_Authorization::getInstance()->getUserId();
            $module_id_val = $this->registryModel->getRegistryById($registryId);

            $registryEntities = $this->registryEntitiesModel->getEntitiesByRegistryId($registryId);
            $entriesCount = count($registryEntities);
            $entityIds = array();

            if ($entriesCount > 0) {
                foreach ($registryEntities as $entity) {
                    $entityIds[] = $entity['id'];
                }
            }
			
            $formIds = array();
            foreach ($data as $k => $v) {
                if (stripos($k, "element_") !== false) {
                    $formId = str_replace("element_", "", $k);
                    $formIds[] = $formId;
                }
            }

            foreach ($formIds as $idForm) {
                $val = $this->getParam('element_' . $idForm);
                if (empty($val)) {
                    $createDocument = false;
                }
            }
		
		/* $dataiii=$this->registryService->entryGetEntities($data);
		print_r($dataiii);exit; */
		
			$uniqueda = $this->registryActionModel->getuniquerecords($row,$data);
        
			if($uniqueda=='1') {
				//echo $msg='data already exist';
                $this->view->message = "Data Already Exist";
                return $this->_redirect('/registry');
			} else {
                $entry_data = $this->registryService->entrySave($row, $data);
            }
            
            $list_id = $this->registryEntriesModel->getall();
            $user_name = $this->osoby->requestObject($user_id);
            foreach ($list_id as $data_id) {
                $last_id = $data_id->id;
            }

            $element_id = array();
            $element_val = array();
            foreach ($row->registry->entities as $registryEntity) {
                $title['element_' . $registryEntity->id] = $registryEntity->title;
            }

            if (empty($old_values)) {
                foreach ($data as $data_key => $val) {

                    if (strpos($data_key, 'element_') !== false) {
                        if (is_array($val)) {
                            foreach ($val as $n_value) {
                                $new_val .= $n_value . ' ';
                            }
                            $val = $new_val;
                        }
                        if (!empty($val)) {
                            $element_id[] = ltrim($data_key, 'element_');
                            $element_val[] = html_entity_decode(strip_tags($val));
                        }
                    }
                }

                $element_ids = json_encode($element_id);
                $element_value = json_encode($element_val);

                $count_array['user_id'] = $user_id;
                $count_array['module_id'] = $data['registry_id'];
                $count_array['controller'] = $data['controller'];
                $count_array['action'] = 'wstaw wiersz';
                $count_array['field'] = 'awantura';
                $count_array['action_name'] = $element_ids;
                $count_array['previous_value'] = '';
                $count_array['new_value'] = $element_value;
                $count_array['record_id'] = $last_id;
                $count_array['module_id_value'] = $module_id_val['title'];
                $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
                $this->registryActionModel->save($count_array);
            } else {
                foreach ($data as $data_key => $data_val) {
                    if (strpos($data_key, 'element_') !== false) {
                        if (is_array($data_val)) {
                            foreach ($data_val as $n_value) {
                                $new_val .= $n_value . ' ';
                            }
                            $data_val = $new_val;
                        }

                        foreach ($old_values as $old_key => $old_val) {

                            if ($data_key == $old_key) {

                                if ($data_val != $old_val) {

                                    $count_array['user_id'] = $user_id;
                                    $count_array['module_id'] = $data['registry_id'];
                                    $count_array['controller'] = $data['controller'];
                                    $count_array['action'] = 'update value';
                                    $count_array['field'] = 'pole';
                                    $count_array['action_name'] = $title[$data_key];
                                    $count_array['previous_value'] = html_entity_decode(strip_tags($old_val));
                                    $count_array['new_value'] = html_entity_decode(strip_tags($data_val));
                                    $count_array['record_id'] = $data['id'];
                                    $count_array['module_id_value'] = $module_id_val['title'];
                                    $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];

                                    $this->registryActionModel->save($count_array);
                                }
                            }
                        }
                    }
                }
            }

            $pstatus_data = array();
            
            if ($data['update_documents']) {
                $this->registryService->entryUpdateDocuments($row->id);
            }

            if (!$id) {
                
                foreach ($row->registry->documents_templates as $documentsTemplate) {
                    if ($documentsTemplate->flag_auto_create) {
                        $this->registryService->entryCreateDocument($row->id, $documentsTemplate->id);
                    }
                }
            }

            if ($newUser && $createDocumentForNewEmployee) {
                /* Template IDs for which documents will be created for new employees */
                $documentTemplatesIds = $this->documentTemplates->getActiveDocumentTemplateIdForNewAssigny();
                
                foreach ($documentTemplatesIds as $tempId) {
                    $p_data = array(
                        'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                        'worker_id' => $row->id,
                        'registry_entry_id' => $row->id,
                        'documenttemplate_id' => $tempId,
                    );
                    if ($registryId == 134) {
                        $this->documentsPending->save($p_data);    
                    }
                    
                    $t_data = array(
                        'documenttemplate_id' => $tempId,
                        'worker_id' => $row->id,
                    );
                    $this->documentTemplatesOsoby->save($t_data);
                }
            }
            
            if (!$newUser && $createDocumentForNewEmployee && $dataChanged) {
                $templatesIds = $this->documentTemplatesOsoby->getAllDocumentTemplatesIds($row->id);
                $activePendingDocuments = array();
                if (count($templatesIds)) {
                    $activePendingDocuments = $this->documentsPending->getAllActivePendingDocumentIds([], $templatesIds);
                }
                if (count($activePendingDocuments)) {
                    foreach ($activePendingDocuments as $apd) {
                        $this->documentsPending->remove($apd->id);
                    }

                    foreach ($templatesIds as $t_id) {
                        $p_data = array(
                            'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                            'worker_id' => $row->id,
                            'registry_entry_id' => $row->id,
                            'documenttemplate_id' => $t_id,
                        );
                        $this->documentsPending->save($p_data);
                    }
                }
            }

            $regid = $this->dictionaryModel->getRegidByName('dictionary');
            if ($registryId === $regid) {
                $this->dictionaryModel->setDictionary();
            }
            
            if ($documentTemplateId) {
                if (Logic_Registry::REGISTRY_PERMISSIONS == $registryId && !$newUser) {
                    $logic->createRegistryDocumentsPending($values['worker_id'], $registryId, $row->id);
                }
                
                foreach ($row->getDocumentWorkerIds() as $workerId) {
                    if (!empty($values['worker_id']) && $workerId !== $values['worker_id']) {
                        // worker id is different than choosen in form
                        continue;
                    }
                    
                    if ($createDocument) {
                        //  -------------------------------COMAGOM CODE START-----------------------------------//
                        if (!$whichWindowID) {
                            // $this->documentsPrinter->printPendingDocuments(array(46), true);
                            $created_document_pending = $this->documentsService->create($documentTemplateId, $workerId, $row->id);
                            $pstatus_data = array(
                                'registry_entry_id' => $entry_data['id'],
                                'registry_id' => $registryId,
                                'parent_id' => $entry_data['id'],
                                'status' => 0,
                                'document_pending_id' => $created_document_pending['id']
                            );
                            $this->permissionStatusModel->savePermissionStatus($pstatus_data);
                        } else {
                            if ($new_status != 0) {
                                $recordOne = $this->permissionStatusModel->getOneOfPermissionStatus($whichWindowID);

                                $recordOne['status'] = 0;
                                $this->permissionStatusModel->edit($recordOne['id'], $recordOne);
                                
                                if (!$logic->hasWorkerPendingDocumentByTemplateId($documentTemplateId, $workerId)) {
                                    $created_document_pending = $this->documentsService->create($documentTemplateId, $workerId, $row->id, true);
                                } else {
                                    $created_document_pending = $logic->getWorkerPendingDocumentByTemplateId($documentTemplateId, $workerId);
                                }
                                
                                if ($new_status == 3) {
                                    $recordOne['document_pending_id'] = $created_document_pending['id'];
                                    $this->permissionStatusModel->edit($recordOne['id'], $recordOne);
                                }
                            } else {
                                $recordOne = $this->permissionStatusModel->getOneOfPermissionStatus($whichWindowID);
                                $cache_file_name = 'pendingPreviewAction' . $recordOne['document_pending_id'] . 'flag';
                                $documentContentFlag = "true";
                                $this->cache->save($documentContentFlag, $cache_file_name);
                            }
                        }
                    }

                    $documentTemplateOsoby = $this->documentTemplatesOsoby->getDocumentTemplateOsoby($workerId, $documentTemplateId);

                    if (!$documentTemplateOsoby) {
                        $workerData = array(
                            'worker_id' => $workerId,
                            'documenttemplate_id' => $documentTemplateId,
                        );
                        
                        $this->documentTemplatesOsoby->save($workerData);
                    }
                }
            }
            
            if (Logic_Registry::REGISTRY_PERMISSIONS == $registryId && !$newUser) {
                // zmiana uprawnień przy edycji na oczekujące
                $logicPermissions->changePermissionsStatus($id, Logic_Permissions::STATUS_WAIT);
            }

            $this->db->commit();
        } catch (Application_SubscriptionOverLimitException $x) {
            $this->_redirect('subscription/limit');
        } catch (Exception $e) {
            throw new Exception('Próba zapisu danych nie powiodła się.' . $e->getMessage(), 500, $e);
        }
        // comagom code start 2019.4.2
        if ($registryId == "177" || $registryId == "176" || $registryId == "126" || $registryId == "105") {
            $entryID = 0;
            $cache_file_name = 'getregistryentities_paginator_' . $registryId . "_" . $entryID;
            $registryEntriesRepository = Application_Service_Utilities::getModel('RegistryEntries');
            $paginator = $registryEntriesRepository->getList(['registry_id = ?' => $registryId]);
            //error_log(print_r("registryId ===>". $registryId, true));
            $serviceRegistry = Application_Service_Registry::getInstance();
            $serviceRegistry->entriesGetEntities($paginator, $entryID);

            $paginator_data_custom = [];
            foreach ($paginator as $d) {
                $paginator_data_custom[] = (object) array('entities' => $d->entities, 'entities_named' => $d->entities_named);
            }

            $this->cache->save($paginator, $cache_file_name);
            $this->cache->save($paginator_data_custom, $cache_file_name . '_custom');
        }
        // comagom code end 2019.4.2
        if ($this->_request->isXmlHttpRequest()) {
            $this->outputJson([
                'status' => (int) 1,
            ]);
        } else {

            $storageTaskId = $this->getParam('task', 0);
            if ($storageTaskId) {
                return $row;
            }

            $this->flashMessage('success', 'Dodano wpis');
            if ($this->getParam('addAnother')) {
                $this->redirect($this->baseUrl . '/update/registry_id/' . $registryId);
            } else {
                $req = $this->getRequest();
                $params = $req->getParams();
                foreach ($params as $k => $val) {
                    if ((stripos($k, 'element')) !== false) {
                        if (!is_array($val)) {
                            $name = $val;
                        }
                    }
                }
                $this->redirect($this->baseUrl . '/index/registry_id/' . $registryId);
            }
        }
    }

    public function removeAction()
    {
        $logic = new Logic_Registry();
        
        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $adapter->beginTransaction();
        
        try {
            $req = $this->getRequest();
            $id = $req->getParam('id', 0);
            $data = $this->getRequest()->getParams();
            
            $this->documentsModel = Application_Service_Utilities::getModel('Documents');
            
            
            if ($data['registry_id'] == 134) {

                $this->tasksModel = Application_Service_Utilities::getModel('Tasks');
                $this->StoragetasksModel = Application_Service_Utilities::getModel('StorageTasks');
                $this->documentsModel = Application_Service_Utilities::getModel('Documents');
                $this->pendingDocumentsModel = Application_Service_Utilities::getModel('DocumentsPending');
                $documentsTemplatesOsoby = Application_Service_Utilities::getModel('Documenttemplatesosoby')->getAllDocumentTemplatesIds($data['id']);
                $assignedToUser = Application_Service_Utilities::getModel('Osoby')->getUserIdFromWorkerId($data['id']);

                foreach ($documentsTemplatesOsoby as $key => $dto) {
                    $task = $this->tasksModel->getTaskByObjectId($dto);
                    if (isset($task[0]) && !empty($assignedToUser)) {
                        $checkExisting = $this->StoragetasksModel->checkExisting($dto, $task[0]['id'], $assignedToUser);
                        if (!empty($checkExisting)) {
                            $this->StoragetasksModel->remove($checkExisting[0]['id']);
                        }
                    }
                }
            }
            
            $count_array = array();
            $user_id = Application_Service_Authorization::getInstance()->getUserId();
            $module_id_val = $this->registryModel->getRegistryById($data['registry_id']);
            $user_name = $this->osoby->requestObject($user_id);

            /** @var object $row */
            $row = $this->registryEntriesModel->requestObject($id);
            
            //get workers list from employee table. 
            $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();
            
            if ($id && $workers[$id]) {
                //if $id exist in workers list, then getdocuments by workerid and these documents will be archived by workerid
                $archivedDate = date('Y-m-d H:i:s');
                $this->documentsModel->updateStatusArchiveAndarchivedDateByWorkerId($id, $archivedDate);
                // at the same time, his permission will become also archived.
                // $registryId = $this->registryModel->getRegistryIdByName(Self::PERMISSION_REGISTRY_NAME);
                // $selectedRegistryEntryId = $this->registryEntriesModel->getEntriesByRegistryIdAndWorkerId($registryId, $id);
                // if(!empty($selectedRegistryEntryId)) {
                //     $data['status'] = 2;
                //     $test1 = $selectedRegistryEntryId['id'];
                //     $test2 = $selectedRegistryEntryId->id;
                //     $this->permissionStatusModel->updatePermissionByRegistryEntryIDViaWorkerId($data,$selectedRegistryEntryId['id']);
                // }
                $registryId = $this->registryModel->getRegistryIdByName(Self::PERMISSION_REGISTRY_NAME);
                $selectedRegistryEntry = $this->registryEntriesModel->getEntriesByRegistryIdAndWorkerId($registryId, $id);
                $data = array(
                    'reason_content' => "the worker was deleted",
                    'withdrawal_date_time' => date('Y-m-d H:i:s'),
                    'status' => 3
                );
                if ($selectedRegistryEntry != null) {
                    $permission_one_record = $this->permissionStatusModel->getOneOfPermissionStatus($selectedRegistryEntry['id']);
                    $update_success_fail = $this->permissionStatusModel->updatePermissionByRegistryEntryID($data, $permission_one_record['id']);
                }

                //at the same time, save deleted worker on related table
                // $tempdata = array(
                //     'registry_id' => $data['registry_id'],
                //     'worker_id' => $id,
                //     'selected_permission_entry_id' => $selectedRegistryEntryId['id'],
                //     'worker_name' => $workers[$id]['imie'],
                //     'worker_surname' => $workers[$id]['nazwisko'],
                // );
                // $this->deletedWorkerModel->saveDeletedWorkerInfo($tempdata);
                $this->registryEntriesModel->update([
                    'status_of_worker' => 1,
                    'updated_at' => new Zend_Db_Expr("NOW()"),
                    'ghost' => '1',
                ], ['id = ?' => $id]);
            } else {
                $this->registryEntriesModel->remove($row->id);
            }

            //end
            $count_array['user_id'] = $user_id;
            $count_array['module_id'] = $data['registry_id'];
            $count_array['controller'] = $data['controller'];
            $count_array['action'] = 'delete';
            $count_array['field'] = 'awantura';
            $count_array['action_name'] = $data['id'];
            $count_array['previous_value'] = '';
            $count_array['new_value'] = '';
            $count_array['module_id_value'] = $module_id_val['title'];
            $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
            $this->registryActionModel->save($count_array);
            
            $logic->removeDependencyEntities($id);
            $logic->removeRegistryEntryRow($id);
            $logic->archivizeDependencyDocuments($id);
            $logic->removeDependencyPendingDocuments($id);
            $dataD = $this->documentsModel->fetchAll();
            
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            $this->flashMessage('error', $e->getMessage());
            return $this->redirectBack();
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->outputJson([
                'status' => (int) 1,
            ]);
        } else {
            $this->redirectBack();
        }
    }

    public function ajaxCreateDocumentAction()
    {
        $this->setDialogAction();
        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);
        $registry = $this->registryModel->getFull($registryId, true);

        $row = $this->registryEntriesModel->getFull([
            'id' => $id,
            'registry_id' => $registryId,
            ], true);
        $this->registryService->entryGetEntities($row);

        $this->view->entry = $row;
        $this->view->registry = $registry;
        $this->view->dialogTitle = 'Utwórz dokument';
    }

    public function bulkeditAction()
    {

        $time = date("Y-m-d H:i:s");
        $id = $_POST['id'];
        $entry_ids = $_POST['entry_ids'];
        $data = $_POST['data'];
        $entity_id = $_POST['entity_id'];
        $entry_ids = explode(",", $entry_ids);
        $counter = $_POST['counter'];
        $count_array = array();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        /* if(!empty($entry_ids[$counter])) {
          $count_array['user_id'] = $user_id;
          $count_array['module_id']  = $_POST['registry_id'];
          $count_array['controller'] = $_POST['controller'];
          $count_array['action'] = 'usuń wiersz';
          $count_array['field'] = 'awantura';
          $count_array['action_name'] = $entry_ids[$counter];
          $count_array['previous_value'] = '';
          $count_array['new_value'] = '';

          $this->registryActionModel->save($count_array);
          } */

        if ($entity_id == "4")
            $tablename = "registry_entries_entities_date";
        else
            $tablename = "registry_entries_entities_varchar";

        foreach ($entry_ids as $entry_id) {
            $query = 'SELECT id FROM ' . $tablename . '
            WHERE entry_id = ' . $entry_id . ' AND
            registry_entity_id = ' . $id;
            $num = $this->db->query($query)->num_rows;

            // if(empty($num) || $num == 0){
            //     $query = "INSERT INTO ".$tablename."
            //     (registry_entity_id, entry_id, value , created_at) VALUES
            //     (".$id.",".$entry_id.","."'".$data."', '".$time."')";
            //     $this->db->query($query);
            //     return;
            // }

            $query = 'UPDATE ' . $tablename . '  
                SET value = ' . "'" . $data . "' " . '
                WHERE entry_id = ' . $entry_id . ' AND 
                registry_entity_id = ' . $id;
            $this->db->query($query);
        }

        $response['status'] = "success";
        return $response;
    }

    public function ajaxSaveCreateDocumentAction()
    {
        try {
            $req = $this->getRequest();
            $data = $req->getParam('document');
            $this->db->beginTransaction();

            $this->registryService->entryCreateDocument($data['entry_id'], $data['document_template_id']);

            $this->db->commit();
        } catch (Exception $e) {
            throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }

        $this->flashMessage('success', 'Utworzono dokument');

        $this->outputJson([
            'status' => (int) 1,
            'app' => [
                'reload' => 1,
            ],
        ]);
    }

    public function ajaxSearchFilterAction()
    {
        try {
            $req = $this->getRequest();
            $registryId = $_POST['registry_id'];
            $count = $_POST['count'];
            $parameterEntityId = $_POST['parameter-entity-id'];
            list($parameterId, $entityId) = explode("-", $parameterEntityId);
            $entity_details = $this->entitiesModel->getOne($entityId);
            if (isset($entity_details['conditions']) && !empty($entity_details['conditions'])) {
                //$optionStr = '<div class="col-sm-4"><select name="condition_for_'.$count.'" id="condition_for_'.$count.'" class="form-control">';
                $optionStr = '<select name="condition_for[]" id="condition_for_' . $count . '" class="form-control">';
                $lists = unserialize($entity_details['conditions']);
                if (count($lists) > 0 && !empty($lists)) {
                    foreach ($lists as $k => $v) {
                        $optionStr .= "<option value='" . $k . "'>" . $v . "</option>";
                    }
                }
                $optionStr .= "</select>";
                echo $optionStr;
                exit();
            }
        } catch (Exception $e) {
            throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }

        exit();
    }

    public function ajaxSaveFilterAction()
    {
		
        $filterCondition = array();
        if (isset($_POST['regtype']) && sizeof($_POST['regtype']) > 0) {
            $con = 0;
            $da = 0;
            foreach ($_POST['regtype'] as $k => $parameterEntityId) {
                list($parameterId, $entityId) = explode('-', $parameterEntityId);
                $filterCondition[$k]['parameter_id'] = $parameterId;
                $filterCondition[$k]['entity_id'] = $entityId;
                $conditionFieldName = 'condition_for';

                $expEntity = explode("-", $parameterEntityId);
                if ($expEntity[1] == 4) {
                    $filterCondition[$k]['keyword'] = array("from" => $_POST['from'][$da], "to" => $_POST['to'][$da]);
                    $da++;
                } else {
                    //$_POST[$conditionFieldName][] = 'equal';
                    if (isset($_POST[$conditionFieldName][$con])) {
                        $filterCondition[$k]['condition'] = $_POST[$conditionFieldName][$con];
                    }

                    $keyword = 'keyword';
                    if (isset($_POST[$keyword][$con])) {
                        $filterCondition[$k]['keyword'] = $_POST[$keyword][$con];
                    }
                    $con++;
                }
            }
			
             //serialize($filterCondition);
            $userId = $this->userSession->user->id;
            $regId = $_POST['reg_id'];
            $filter_name = trim($_POST["filter_name"]);
            $filter_scope = trim($_POST["filter_scope"]);
			$register_id = trim($_POST["register_id"]);
            if (isset($_POST['hidFilterId']) && $_POST['hidFilterId'] != "") {
                $updateSql = 'UPDATE registry_filters SET meta_content = ' . "'" . serialize($filterCondition) . "' " . 'WHERE id=' . $_POST['hidFilterId'];
                $this->db->query($updateSql);
            } else {
				
                $insertSql = 'INSERT INTO registry_filters SET
                user_id = ' . $userId . ',
                meta_content = ' . "'" . serialize($filterCondition) . "'" . ' ,
                filter_name = ' . "'" . $filter_name . "'" . ',
                filter_score = ' . "'" . $filter_scope . "'" . ',
				register_id=' . "'" . $register_id . "'" . ',
                created_at = ' . "'" . date('Y-m-d :h:i:s') . "'";
                $this->db->query($insertSql);
            }
        }
        exit();
    }

    public function getTopNavigation($action = '')
    {

        /* $data = array(
          array(
          'label' => 'Operacje',
          'path' => 'javascript:;',
          'icon' => 'fa icon-tools',
          'rel' => 'operations',
          'children' => array(
          array(
          'label' => 'Import',
          'path' => '/osoby/import',
          'icon' => 'icon-align-justify',
          'rel' => 'admin',
          ),
          array(
          'label' => 'Transfer upoważnień',
          'path' => '/osoby/upowaznienia-transfer',
          'icon' => 'icon-align-justify',
          'rel' => 'admin',
          ),
          array(
          'label' => 'Modyfikacja uprawnień',
          'path' => '/osoby/permissions-setter',
          'icon' => 'icon-align-justify',
          'rel' => 'admin',
          ),
          array(
          'label' => 'Usunięcie wszystkich użytkowników',
          'path' => '/osoby/remove-all-users/id/1',
          'icon' => 'icon-align-justify',
          'rel' => 'admin',
          ),
          )
          )
          ); */
        $data = array(array(
                'label' => 'Filtry',
                'path' => 'javascript:;',
                'icon' => 'fa icon-filter',
                'rel' => 'filters',
                'children' => array(
                )
            ));
        $userId = $this->userSession->user->id;
        $registryId = $this->getParam('registry_id', 0);

        $resultSets = $this->db->query("select * from registry_filters where user_id = '" . $userId . " ' and register_id='". $registryId."' and filter_table is null ")->fetchAll();
        if (isset($resultSets) && count($resultSets) > 0) {
            foreach ($resultSets as $result) {
                $filterDialog = "#filterDialog_" . $result['id'];
                $filter_arr[] = array(
                        'label' => $result['filter_name'],
                        'path' => 'javascript:;',
                        'isdialog' => 1,
                        'target' => $filterDialog,
                        'icon' => (isset($result['filter_score']) && $result['filter_score'] == 'private') ? 'icon-lock-1' : 'icon-lock-open-1',
                        'rel' => 'filters',
                        'isdelete' => 1
                );
            }
            $filter_arr[] = array(
                    'label' => 'Zresetuj filtr',
                    'path' => '/registry-entries/index/registry_id/' . $registryId,
                    'icon' => 'icon-arrows-ccw',
                    'rel' => 'filters'
            );
        }
        $filter_arr[] = array(
                'label' => 'Nowy filtr',
                'path' => 'javascript:;',
                'isdialog' => 1,
                'target' => '#filterDialog',
                'icon' => 'icon-plus',
                'rel' => 'filters'
        );

        if (isset($filter_arr) && count($filter_arr) > 0) {
            $data[0]['children'] = $filter_arr;
        }

        $this->setSectionNavigation($data);
    }

    public function ajaxDeleteFilterAction()
    {
        if (isset($_POST['filter_id'])) {
            $filter_id = trim($_POST["filter_id"]);
            $sql = 'DELETE FROM registry_filters WHERE
            id = ' . $_POST['filter_id'];
            $this->db->query($sql);
        }
        exit();
    }

    public function diagramdAction()
    {
        try {
            $data = $this->_request->getPost();
            $id = $data['id'];
            $select = $this->db->select()
                ->from('event_diagram')
                ->where('id = ?', $id);
            $result = $this->db->fetchAll($select);
            if (!empty($result)) {
                foreach ($result as $value) {
                    echo $value['diagramj'];
                    exit;
                    // $this->view->loaddiagram11 = '{"Hello Ali"}';
                }
            } else {
                echo "Ali";
                exit;
            }
        } catch (Exception $e) {
            var_dump($e);
            exit;
            Throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }
    }

    public function diagramrAction()
    {
        try {
            $data = $this->_request->getPost();
            $dn = $data['dn'];
            $str = $data['str1'];
            $loadid = $data['loadid'];
            $select = $this->db->select()
                ->from('event_diagram')
                ->where('id = ?', $loadid);
            $result = $this->db->fetchAll($select);
            if (!empty($result)) {
                $data = array('diagramj' => $str, 'name' => $dn);

                $where[] = "id = " . $loadid;

                $this->db->update('event_diagram', $data, $where);
                echo "Updated Succeccfully";
                exit;
            } else {
                $req = $this->getRequest();
                $data = $req->getParam('value');
                $data = array('name' => $dn,
                    'diagramj' => $str);
                $this->db->insert('event_diagram', $data);
                echo "Save Succeccfully";
                exit;
            }
        } catch (Exception $e) {
            var_dump($e);
            exit;
            Throw new Exception('Operacja nieudana', $e->getCode(), $e);
        }
    }

    public function diagramAction()
    {
        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);
        $this->view->id = $id;
        $this->view->registryId11 = $registryId;

        $select = $this->db->select()
                ->from('registry_event_diagram')
                ->where('rid = ?', $registryId)->where('eid = ?', $id);
        $result = $this->db->fetchAll($select);

        if (!empty($result)) {
            foreach ($result as $value) {
                $this->view->loaddiagram11 = json_encode($value['diagramj']);
                // $this->view->loaddiagram11 = '{"Hello Ali"}';
            }
        } else {
            $this->view->loaddiagram11 = "";
        }
    }

    public function diagramblockAction()
    {
        $id = $this->getParam('id', 0);
        $registryId = $this->getParam('registry_id', 0);
        $this->view->id = $id;
        $this->view->registryId11 = $registryId;

        $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $this->view->paginator = $paginator;
        $select = $this->db->select()
                ->from('registry_event_diagram')
                ->where('rid = ?', $registryId)->where('eid = ?', $id);
        $result = $this->db->fetchAll($select);

        $this->view->registry = $registry;

        if (!empty($result)) {
            foreach ($result as $value) {
                $this->view->loaddiagram11 = json_encode($value['diagramj']);
                // $this->view->loaddiagram11 = '{"Hello Ali"}';
            }
        } else {
            $this->view->loaddiagram11 = "";
        }
    }

    public function getentitiesAction()
    {
        $registryId = $_POST['rid'];
        $result = array();
        if (!$registryId) {
            $this->redirect('/registry');
        }

        $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        $i = 0;
        foreach ($paginator as $d) {
            foreach ($registry['entities'] as $entity) {
                $result[$i] = $d->entityToString($entity['id']);
                //$result[$i]=array('eventid'=>$d['id'],'eventname'=>$d->entityToString($entity['id']),'eventtype'=>$d->entityToString($entity['id']));
                break;
            }
            $i++;
        }

        echo $res = json_encode($result);

        die();
    }

    /**
     * Get multiple registry entities as associative list (batch mode)
     * @throws Exception
     */
    public function ajaxGetValuesAction()
    {
        $out = array();
        $service = new Application_Service_RegistryEntries();
        $registryIds = (array) $this->getParam('registry_id');
        $registryStringifyParams = $this->getParam('stringify', []);
        foreach ($service->getAllEntities($registryIds, $registryStringifyParams) as $registryId => $registryInfo) {
            $values = array();
            foreach ($registryInfo['values'] as $valueId => $valueTitle) {
                $values [] = array(
                    'id' => $valueId,
                    'title' => $valueTitle,
                );
            }
            $out[] = array(
                'id' => $registryId,
                'title' => $registryInfo['title'],
                'values' => $values,
            );
        }
        $this->outputJson($out);
    }

    public function updatetodonameAction()
    {
        $taskName = $_POST['taskName'];
        $selectedTaskDbId = $_POST['task_db_id'];
        $complexity = $_POST['complexity'];
        $this->todolistModal->update([
            'taskName' => $taskName,
            'complexity' => $complexity,
            ], ['id = ?' => $selectedTaskDbId]);

        $todoListsInfo = $this->todolistModal->getAllTodoItemsBYRegistryId($_POST['registry_id']);

        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "taskName" => $value['taskName'],
                "complexity" => $value['complexity'],
                "creationDate" => $value['creationDate'],
                "startDate" => $value['startDate'],
                "completionDate" => $value['completionDate'],
                "state" => $value['state'],
            );
            array_push($filteredDatas, $item);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function getstarteddateAction()
    {
        $completionDate = date('Y-m-d');
        echo $completionDate;
        die();
    }

    public function getalltaskitemsAction()
    {
        $registry_id = $_POST['registry_id'];
        $todoListsInfo = $this->todolistModal->getAllTodoItemsBYRegistryId($_POST['registry_id']);

        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "taskName" => $value['taskName'],
                "complexity" => $value['complexity'],
                "creationDate" => $value['creationDate'],
                "startDate" => $value['startDate'],
                "completionDate" => $value['completionDate'],
                "state" => $value['state'],
            );
            array_push($filteredDatas, $item);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function savecompletedtodoitemAction()
    {
        $completionDate = date('Y-m-d');
        $task_id = $_POST['task_db_id'];
        $registry_id = $_POST['registry_id'];
        $state = $_POST['state'];
        $this->todolistModal->update([
            'state' => $state,
            'completionDate' => $completionDate,
            ], ['id = ?' => $task_id, 'registry_id = ?' => $registry_id]);

        $todoListsInfo = $this->todolistModal->getAllTodoItemsBYRegistryId($_POST['registry_id']);

        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "taskName" => $value['taskName'],
                "complexity" => $value['complexity'],
                "creationDate" => $value['creationDate'],
                "startDate" => $value['startDate'],
                "completionDate" => $value['completionDate'],
                "state" => $value['state'],
            );
            array_push($filteredDatas, $item);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function deletetodoitemAction()
    {
        $deleteTodoItemId = $_POST['delete_todo_id'];
        $registry_id = $_POST['registry_id'];
        $this->todolistModal->remove($deleteTodoItemId);

        $todoListsInfo = $this->todolistModal->getAllTodoItemsBYRegistryId($registry_id);

        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "taskName" => $value['taskName'],
                "complexity" => $value['complexity'],
                "creationDate" => $value['creationDate'],
                "startDate" => $value['startDate'],
                "completionDate" => $value['completionDate'],
                "state" => $value['state'],
            );
            array_push($filteredDatas, $item);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function savetodoitemAction()
    {

        $creationDate = date('Y-m-d');
        $data = array(
            'registry_id' => $_POST['registry_id'],
            'registry_title' => $_POST['registry_title'],
            'taskName' => $_POST['taskName'],
            'complexity' => $_POST['complexity'],
            'creationDate' => $creationDate,
            'completionDate' => $_POST['completionDate'],
            'startDate' => $_POST['startDate'],
            'state' => $_POST['state'],
        );
		
        $insertId = $this->todolistModal->saveTodoItem($data);
        if ($insertId) {
            $todoListsInfo = $this->todolistModal->getAllTodoItemsBYRegistryId($_POST['registry_id']);
        }
        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "taskName" => $value['taskName'],
                "complexity" => $value['complexity'],
                "creationDate" => $value['creationDate'],
                "startDate" => $value['startDate'],
                "completionDate" => $value['completionDate'],
                "state" => $value['state'],
            );
            array_push($filteredDatas, $item);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function todoitemsAction()
    {
        $registry_id = $_POST['registry_id'];
        // echo $registry_id;
        $todoListsInfo = $this->todolistModal->getPendingTodoItemsByRegistryId((int) $registry_id);
        // echo json_encode($todoListsInfo);
        // $test = $todoListsInfo;
        $filteredDatas = [];
        foreach ($todoListsInfo as $key => $value) {
            $item = array(
                "id" => $value['id'],
                "registry_id" => $value['registry_id'],
                "registry_title" => $value['registry_title'],
                "todo_title" => $value['todo_title'],
                "status" => $value['status'],
                "todo_created_at" => $value['todo_created_at'],
            );
            array_push($filteredDatas, $value);
            // $filteredDatas[$value['id']] = $item;
        }
        $test = json_encode($filteredDatas);
        echo $test;
        die();
    }

    public function selectedtodoitemsAction()
    {
        $info = $_POST['data'];
        foreach ($info as $key => $value) {
            $this->todolistModal->update([
                'status' => 1,
                ], ['id = ?' => $value]);
        }
    }
}
