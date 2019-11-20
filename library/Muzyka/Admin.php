<?php

/**
 * @property Zend_View_Interface|object $view
 */
abstract class Muzyka_Admin extends Muzyka_Action {

    protected $breadcrumb;
    protected $menuItems;
    protected $relativeDocPath;
    protected $log = array();
    protected $folders = array(
        'documents' => 'docs/',
        'backups' => '/backups/'
    );
    protected $domain;
    protected $sections;
    protected $navigation;
    protected $osobaNadawcaId;
    protected $url403 = '/?r=403';
    protected $userIsKodoOrAbi;
    protected $_forcePdfDownload = true;
    protected $updateSessionExpirationTime = true;
    protected $sectionNavigationVariableSet = false;

    /** @var Application_Model_Menu */
    protected $menuModel;

    public function init() 
    {
        parent::init();
        
        $logicTasks = new Logic_Tasks();
        
        $session = new Zend_Session_Namespace('user');
        $this->view->user_session = $session;
        $theTime = time();
        if (!empty($this->session->session_expired_at) && $this->session->session_expired_at < $theTime) {
            Application_Service_Authorization::logout();
            $this->redirect('/');
            return null;
        }
        
        /* Vipin code starts */
        $db = Zend_Db_Table::getDefaultAdapter();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        
        $user = Application_Service_Authorization::getInstance()->getUser();
        $controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        if($user['isAdmin'] == 1  && $user['company_confirmation'] == 0 && !$this->userIsSuperadmin()){
            if( $controllerName != 'systemsconfiguration'){
                $this->redirect('/systemsconfiguration#company-information-1');
            }
        }
        if($user_id)
        {
            /* Vipin code starts */
            $licenseSubscriptionService = Application_Service_LicenseSubscriptions::getInstance();
			// $license = $licenseSubscriptionService->getActive(null, Application_Model_LicenseSubscription::STATUS_ACTIVATED);
			/* Jack's working START */
            $license = $licenseSubscriptionService->getActive($user_id, Application_Model_LicenseSubscription::STATUS_ACTIVATED);
			/* Jack's working START */
            /* Vipin code end */
            $currentDate = date('Y-m-d H:i:s');
            if($user['isAdmin'] == 1)
            {
                $subscription = $db->select()->from(['table1' => 'license_subscriptions'])
                            ->joinInner(['table2' => 'licenses'], 'table2.id = table1.license_id', ['*'])
                            ->where('table1.osoby_id =?', $user_id)
                            ->order('table1.id Desc');
                $getLicense = $db->fetchRow($subscription);
            
                $LicenseRepository = Application_Service_Utilities::getModel('License');
            
                $todaytDate = date("Y-m-d");
                $endDate = date('Y-m-d', strtotime($getLicense['end_date']) );
            
                $diff_days = date_diff(date_create($todaytDate),date_create($endDate));
                $this->view->diff_days = $diff_days->days;
                $this->view->is_trial = $getLicense['is_trial'];
                $this->view->paginator = $LicenseRepository->getAllLicenses();
                $this->view->company_confirmation = $user['company_confirmation'];
            }

        }
        

        $this->view->end_date =$endDate || $license->end_date; // Replace this line
        $this->view->currentDate = $currentDate;
        /* Vipin code end */
        
        $this->userIsKodoOrAbi = $this->userIsKodoOrAbi();
        $this->view->userIsKodoOrAbi = $this->userIsKodoOrAbi();
        $this->view->userIsSuperadmin = $this->userIsSuperadmin();
        $this->view->userIsAdmin = $this->userIsAdmin();

        $license = Application_Service_Licenses::getInstance()->getLicense();
        $this->view->hasLicense = !empty($license);
        $this->view->license = $license;
        $this->view->isAbleToUpgradeVersion = !empty($license['version']) && $license['version'] != Application_Service_Products::KRYPTOS_VERSION_ENTERPRISE;
        $this->view->userBalance = Application_Service_Balances::getInstance()->getBalance();
        $this->view->usersCount = Application_Service_Utilities::getModel('Osoby')->countUsers();
        $this->menuModel = Application_Service_Utilities::getModel('Menu');



        $odOstatniejZmianyHasla = time() - strtotime($session->user->set_password_date);

        if (!isset($this->session->passwordRemindShown)) {
            // alert o zmianie hasła co 27 dni
            if ($odOstatniejZmianyHasla > 60 * 60 * 24 * 27) {
                $this->flashMessage('danger', 'Należy zmieniać hasło do tego konta nie rzadziej niż 30 dni!');
                $this->session->passwordRemindShown = true;
            }
        }

        // Zmiana hasła co 30dni
        if ($odOstatniejZmianyHasla > 60 * 60 * 24 * 30) {
            if ($this->getRequest()->getControllerName() == 'home' && ($this->getRequest()->getActionName() == 'zmianahasla' || $this->getRequest()->getActionName() == 'zmianahaslasave')) {
                $this->flashMessage('danger', 'Należy zmieniać hasło do tego konta nie rzadziej niż 30 dni!');
            } else {
               $this->_redirect('home/zmianahasla');
            }
        }

        $this->osobaNadawcaId = $session->user->id;
        $this->view->osobaNadawcaId = $this->osobaNadawcaId;

        if ($this->osobaNadawcaId == null) {
            $this->osobaNadawcaId = 0;
        }
        
        $tasks = $logicTasks->getUserActiveTasksData();
        
        //$storageTasksModel = Application_Service_Utilities::getModel('StorageTasks');
        
        $this->view->tasks = $tasks;
        $this->view->tasksCount = $tasks instanceof Zend_Db_Table_Rowset ? $tasks->count() : 0;
        
        /* @todo Remove if tasks works
        $this->view->tasks = $storageTasksModel->getAll(array(
            'user_id' => $this->osobaNadawcaId,
            'status' => 0,
            'limit' => 10,
        ));
        $this->view->tasksCount = $storageTasksModel->getAll(array(
            'user_id' => $this->osobaNadawcaId,
            'status' => 0,
            'limit' => 10,
            'countMode' => true,
        ));
        */
        
        $this->getNavigation();

        Application_Service_Events::initEventManager();

        $this->_helper->layout->setLayout('admin');
    }

    public function forceKodoOrAbi() {
        if (!$this->userIsKodoOrAbi()) {
            $this->_redirect($this->url403);
        }
    }

    public function forceSuperadmin() {
        if (!$this->userIsSuperadmin()) {
            $this->_redirect($this->url403);
        }
    }

    public function forcePermission($permission) {
        if (!Application_Service_Authorization::isGranted($permission)) {
            $this->_redirect($this->url403);
        }
    }

    public function userIsSuperadmin() {
        $session = new Zend_Session_Namespace('user');
        return (bool) $session->user->isSuperAdmin;
    }

    public function userIsAdmin() {
        $session = new Zend_Session_Namespace('user');
        return (bool) $session->user->isAdmin;
    }

    public function userIsKodoOrAbi() {
        return (bool) $this->userIsKodoOrAbi || $this->userIsSuperadmin() || $this->userIsAdmin();
    }

    public function throwErrorPage($errorNumber) {
        $this->_helper->layout->setLayout('blank');
        $layout = $this->_helper->layout->getLayoutInstance();
		
        $this->view->content = $this->view->render('error/' . $errorNumber . '.html');
        $htmlResult = $layout->render();

        echo $htmlResult;
        exit;
    }

    public function checkAuthController() {
        $session = new Zend_Session_Namespace('user');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller_name = strtolower($request->getControllerName());
        $publicControllers = array('home');
        $allAccessControllers = array();
        $noRedirectControllers = array('ajax');

        if (!$session->user) {
            if (in_array($controller_name, $allAccessControllers)) {
                return true;
            } elseif (in_array($controller_name, $noRedirectControllers)) {

            } else {
                $this->_redirect($this->url403);
                return false;
            }
        }

        if (in_array($controller_name, $publicControllers)) {
            return true;
        }

        // DISABLED
        return;
    }

    public function postDispatch() {
        parent::postDispatch();

        $messages = $this->_helper->flashMessenger->getMessages();
        $currentMessages = $this->_helper->flashMessenger->getCurrentMessages();
        $messages = array_merge($messages, $currentMessages);

        if (!empty($currentMessages)) {
            $this->_helper->flashMessenger->clearCurrentMessages();
        }

        $messages = array_unique($messages);
        if (count($messages)) {
            $this->view->flashMessages = implode($messages);
        } else {
            $this->view->flashMessages = null;
        }

        $messagesModel = Application_Service_Utilities::getModel('Messages');
        $messagesService = Application_Service_Messages::getInstance();
        if (Application_Service_Authorization::getInstance()->getUserId() != null) {
            $this->view->nieprzeczytane = array_slice($messagesModel->getAllByIdUserRec(Application_Service_Authorization::getInstance()->getUserId())->toArray(), 0, 6);
            $this->view->nieprzeczytaneSum = $messagesModel->getNotReadCounter(Application_Service_Authorization::getInstance()->getUserId());
            $this->view->lastMessageDate = $messagesModel->getLastMessageDate(Application_Service_Authorization::getInstance()->getUserId());
        }

        if ($this->updateSessionExpirationTime) {
            Application_Service_Authorization::getInstance()->extendSessionExpirationTime();
        }

        $this->session->session_expired_at = $this->userSession->user->session_expired_at;

        $this->view->session_expired_at = $this->session->session_expired_at;

        // to get the action name so that we can call the navigation array according to that view.
        $action = $this->getRequest()->getActionName();

        $this->getTopNavigation($action);

        @$this->view->jsVersion = (int) file_get_contents(ROOT_PATH . '/data/js_version.txt');

        $systemsModel = Application_Service_Utilities::getModel('Systems');
        $appId = Zend_Registry::getInstance()->get('config')->production->app->id;
        $system = $systemsModel->getOne(array('bq.subdomain = ?' => $appId));
        $this->view->packageName = $system->type;

        $this->view->languages = [
            'pl' => [
                'name' => 'Polski',
                'icon' => 'pl.png',
                'symbol' => 'pl',
            ],
            'en' => [
                'name' => 'English',
                'icon' => 'gb.png',
                'symbol' => 'en',
            ],
        ];
        $this->view->currentLanguage = $_COOKIE['zf-translate-language'] ? $_COOKIE['zf-translate-language'] : 'pl';

        // $this->view->flashMessages = $this->getHelper('flashMessenger')->getCurrentMessages();
        // $this->view->breadcrumbs = $this->breadcrumb->render();
    }

    public function setActive($name) {
        // $this->view->active = $name;
    }

    public function showMessage($text, $type = 'success') {
        return sprintf('<div data-type="%s" data-disappear="10" data-title="Wiadomość systemowa" data-position="top right">%s</div>', $type, $text);
    }

    public function flashMessage($type, $text, $title = 'Wiadomość systemowa', $disappear = 10, $position = 'top right') {
        $this->getFlash()->addMessage(Application_Service_Utilities::getFlashMessage($type, $text, $title, $disappear, $position));
    }

    public function addLog($log) {
        array_push($this->log, $log);
    }

    public function renderView($template, $data) {
        $view = clone $this->getLayout()->getView();
        $view->assign($data);
        return $view->render($template);
    }

    protected function getFCKeditor($content = '') {
        require_once(APPLICATION_PATH . "/../assets/plugins/fckeditor/fckeditor.php");

        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');

        $oFCKeditor = new FCKeditor("text");
        $oFCKeditor->BasePath = "/assets/plugins/fckeditor/";
        $oFCKeditor->Value = stripslashes($content);
        // $oFCKeditor->Value = $content;
        $oFCKeditor->Height = '700';

        // paths
        $dir = 'images/fck';
        $oFCKeditor->Config ["UserFilesAbsolutePath"] = "/" . $dir;
        $oFCKeditor->Config ["UserFilesPath"] = $config->get(APPLICATION_ENV)->url . $dir;
        //$oFCKeditor->Config ['FullPage'] = false;
        //$oFCKeditor->Config ['ProtectedTags'] = 'head|body';

        $fck = $oFCKeditor->CreateHtml();
        return $fck;
    }

    protected function setAjaxAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
    }

    protected function setDialogAction($config = array()) {
        $config = array_merge(array(
            'id' => 'default',
            'size' => 'lg',
            'title' => '',
            'footer' => null,
                ), $config);

        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('dialog');
        $layout->assign('dialog', $config);
    }

    protected function notifyEvent($mail_content, $mail_subject = 'Kryptos - powiadomienie systemowe') {
        $settings = Application_Service_Utilities::getModel('Settings');
        $to = $settings->pobierzUstawienie('ADRES E-MAIL DO POWIADOMIEŃ SYSTEMOWYCH');

        if (strlen($to)) {
            $this->sendMail($mail_content, $mail_subject, $to);
        }
    }

    protected function sendMail($mail_content, $mail_subject, $to, $replyTo) {
        /*$config = array('auth' => 'login',
            'ssl' => 'tls',
            'port' => '465',
            'username' => 'partner@kryptos24.pl',
            'password' => 'QuS,f7CVpDaj');*/
        $config = array('auth' => 'login',
            'ssl' => 'tls',
            'port' => '587',
            'username' => 'kryptos72@kryptos72.com',
            'password' => 'K*V72*nR');
        //$transport = new Zend_Mail_Transport_Smtp('serwer1724226.home.pl', $config);
        $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);

        $mail = new Zend_Mail('UTF-8');
        //$mail_content = strip_tags($mail_content);

        if (strlen($replyTo)) {
            $mail->setReplyTo($replyTo);
        }
        if(empty($to))
        {
            $to = 'm.rolka@kryptos.co';
        }
        $mail->setBodyHtml($mail_content)
                ->setFrom('kryptos72@kryptos72.com', 'Kryptos')
                ->addTo($to)
                ->setSubject($mail_subject)
                ->send($transport);
    }

    protected function addLogDb($type, $userId, $info, $data = "") {
        $logiModel = Application_Service_Utilities::getModel('Logi');
        $logData = array(
            "typ" => $type,
            "user_id" => $userId,
            "info" => $info,
            "ip" => $_SERVER['REMOTE_ADDR'],
            "data" => $data, //http_build_query($_POST),
            "dodano" => new Zend_Db_Expr('NOW()')
        );
        $logiModel->add($logData);
    }

    protected function uploadFile($uploadDir, $name) {
        try {
            $upload = new Zend_File_Transfer_Adapter_Http();
            $file = $upload->getFileInfo();
            if (!$file) {
                return false;
            }

            if (!$upload->getFileName(null, false)) {
                return false;
            }

            //@TODO move it config
            $uploadDir = $uploadDir . '/' . $name;

            if (!is_dir(realpath(dirname(APPLICATION_PATH)) . $uploadDir)) {
                mkdir(realpath(dirname(APPLICATION_PATH)) . $uploadDir, 0777, true);
            }

            $fileUploaded = realpath(dirname(APPLICATION_PATH)) . $uploadDir . '/' . $upload->getFileName(null, false);
            $upload->addFilter('Rename', array(
                'target' => $fileUploaded,
                'overwrite' => true
            ));
            $upload->receive();
            return $uploadDir . '/' . $upload->getFileName(null, false);
        } catch (Exception $e) {
            print_r($e);
            $e->message();
            exit();
        }
    }

    protected function outputHtmlPdf($filename, $htmlResult, $includePn = false, $landscape = false, $saveFile = false) {
        /*
          $htmlResult = preg_replace_callback('/src=\"\/([^"]*)\"/', 'src="'.Zend_Registry::getInstance()->get('config')->production->url.'$1"', $htmlResult);
          $htmlResult = preg_replace_callback('/src=\"\/([^"]*)\"/', function ($matches) {
          $fileUrl = str_replace('&amp;', '&', Zend_Registry::getInstance()->get('config')->production->url . $matches[1]);

          $headers   = array();
          $headers[] = 'Cookie: ' . http_build_query($_COOKIE);

          $ch = curl_init('http://test.v2.kryptos24.mr.com/home');//$fileUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLINFO_HEADER_OUT, true);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

          $output = curl_exec($ch);
          $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);

          vd(curl_getinfo($ch));
          curl_close($ch);

          vdie($output, $headers);

          return 'src="data:image/gif;base64,'.$result.'"';
          }, $htmlResult);
         */


        require_once('mpdf60/mpdf.php');
        if ($landscape) {
            $mpdf = new mPDF('', 'A4-L', '', '', '0', '0', '0', '0', '', '', 'P');
        } else {
            $mpdf = new mPDF('', 'A4', '', '', '0', '0', '0', '0', '', '', 'P');
        }

        $mpdf->WriteHTML($htmlResult);
        if ($includePn) {
            $mpdf->setFooter('Strona {PAGENO} / {nb}');
        }

        if ($this->_forcePdfDownload) {
            $mpdf->output($filename, 'D');
        } else if ($saveFile == true) {
            $mpdf->output($filename, 'F');
        }else {
            $mpdf->output();
        }
        
        if ($saveFile == true) {
            return $filename;
        }        
        exit;
    }

    protected function getTimestampedDate() {
        $date = new DateTime();
        $time = $date->format('\TH\Hi\M');
        $timeDate = new DateTime();
        $timeDate->setTimestamp(0);
        $timeInterval = new DateInterval('P0Y0D' . $time);
        $timeDate->add($timeInterval);
        $timeTimestamp = $timeDate->format('U');

        return date('Y-m-d') . '_' . $timeTimestamp;
    }

    public function getUser() {
        $session = new Zend_Session_Namespace('user');
        return $session->user;
    }

    public function preDispatch() 
    {
/*
        $authorizationNodeStatus = Application_Service_Authorization::isGranted(sprintf('node/%s/%s', $this->getRequest()->getControllerName(), $this->getRequest()->getActionName()), $this->getRequest()->getParams());
        
        if (!$authorizationNodeStatus) {
            throw new Exception('Unauthorized', 403);
            //return $this->renderView('err/index.html',['data' => 'null']);
        }
        */
        $activePage = $this->selectActivePage($_SERVER['REQUEST_URI']);
        if ($activePage) {
            Zend_Layout::getMvcInstance()->assign('sectionIcon', $activePage['icon']);
            $this->view->sectionIcon = $activePage['icon'];
        }

        preg_match('/(.*)\.kryptos/', $_SERVER['SERVER_NAME'], $serverName);
        if (!empty($serverName[1])) {
            Zend_Layout::getMvcInstance()->assign('appDisplayName', $serverName[1]);
        }

        $this->view->applicationName = Application_Service_Utilities::getModel('Settings')->getKey('NAZWA SKRÓCONA')->value;

        $this->view->navigation = $this->getUserNavigation();
        $this->view->auth = Application_Service_Authorization::getInstance();
        $this->view->utilities = Application_Service_Utilities::getInstance();
        $this->view->jsEventAfterLogin = 0;
        $this->view->ajaxModal = 0;
    }

    protected function selectActivePage($url) {
        $parsedUrl = parse_url($url);
        $urlParts = explode('/', $parsedUrl['path']);
        $urlPartsCount = count($urlParts);
        $i = 0;

        do {
            $testUrl = implode('/', array_slice($urlParts, 0, $i > 0 ? -$i : $urlPartsCount));

            foreach ($this->navigation as &$navBase) {
                if (!empty($navBase['children'])) {
                    foreach ($navBase['children'] as &$navChild) {
                        if (in_array($navChild['path'], [$testUrl, $testUrl . '/'])) {
                            $navChild['active'] = 1;
                            return $navChild;
                        }
                    }
                }
                if (!empty($navBase['activate-routes'])) {
                    foreach ($navBase['activate-routes'] as $navActivateRoute) {
                        if (strstr($testUrl, $navActivateRoute)) {
                            $navBase['active'] = 1;
                            return $navBase;
                        }
                    }
                }
                if (in_array($navBase['path'], [$testUrl, $testUrl . '/'])) {
                    $navBase['active'] = 1;
                    return $navBase;
                }
            }
        } while (++$i < $urlPartsCount - 1);
    }

    public function setSection($name) {
        Zend_Layout::getMvcInstance()->assign('section', $name);
    }

    public function setDetailedSection($name) {
        Zend_Layout::getMvcInstance()->assign('sectionDetailed', $name);
    }

    public function setSectionNavigation($nav) {
        if ($this->sectionNavigationVariableSet) {
            return;
        }

        $this->sectionNavigationVariableSet = true;
        $nav = $this->filterAuthorizedNavigation($nav);
        Zend_Layout::getMvcInstance()->assign('subNavigation', $nav);
    }
/*side menu update list using database menu by rahul*/

    protected function getNavigation() 
    {
        $order=' odr ASC';
        $menuList = $this->menuModel->getList($conditions = array(), $limit = null,$order);
        $this->view->baseUrl = $this->baseUrl;

        $data = array();
        foreach ($menuList as $row) {
            $tmp = array();
            $tmp['id'] = $row['id'];
            $tmp['label'] = $row['label'];
            $tmp['path'] = $row['path'];
            $tmp['parent_id'] = $row['parent_id'];
            $tmp['icon'] = $row['icon'];
            $tmp['rel'] = $row['rel'];
            array_push($data, $tmp);
        }


        $itemsByReference = array();

        // Build array of item references:
        foreach($data as $key => &$item) {
            $itemsByReference[$item['id']] = &$item;
            // Children array:
            $itemsByReference[$item['id']]['children'] = array();
        }


        // Set items as children of the relevant parent item.
        foreach($data as $key => &$item) {
            //echo "<pre>";print_r($itemsByReference[$item['parent_id']]);die;
            if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
                $itemsByReference [$item['parent_id']]['children'][] = &$item;
            }
        }


        // Remove items that were added to parents elsewhere:
        foreach($data as $key => &$item) {
            if (is_array($data[$key]['children'])) {
                if (count($data[$key]['children']) == 0) {
                    unset($data[$key]['children']);
                }
            }
            if($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
                unset($data[$key]);
            }
        }


        $tmp = [];
        foreach($data as $key => $value) {
            array_push($tmp, $value);
        }


//        $json_menu = json_encode($tmp, JSON_UNESCAPED_UNICODE);

//        echo "<pre>";print_r($tmp);exit;


        $nav = $tmp;

/*
        if ($this->userIsSuperadmin()) {
            $nav[] = array(
                'label' => 'Administracja',
                'path' => 'javascript:;',
                'icon' => 'icon-cogs',
                'rel' => 'administracja',
                'children' => array(
                    array(
                        'label' => 'Konfiguracja komunikatów',
                        'path' => '/config/komadm',
                        'icon' => 'icon-wrench',
                        'rel' => 'admin'
                    )
                )
            );
            $nav[] = [
                'label' => 'Your license',
                'path' => '/license',
                'icon' => 'fa fa-bars',
                'rel' => 'license'
            ];
        }*/

        $this->navigation = $nav;
    }

    /*end side menu update list using database menu by rahul*/

    protected function getRepository() {
        return Application_Service_Repository::getInstance();
    }

    /**
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function getFlash() {
        return $this->_helper->getHelper('flashMessenger');
    }

    /**
     * @return Zend_Layout
     */
    public function getLayout() {
        return $this->_helper->layout->getLayoutInstance();
    }

    protected function _getSelectedValues($data) {
        $result = array();

        foreach ($data as $k => $v) {
            if ($v) {
                $result[] = $k;
            }
        }

        return $result;
    }

    public function getUserNavigation() 
    {
        $navigation = $this->navigation;
        
        return $this->filterAuthorizedNavigation($navigation);
    }

    public function filterAuthorizedNavigation($navigation) 
    {
        // comagom code start 2019.4.4
        $user = Application_Service_Authorization::getInstance()->getUser();
        $temprightsPermissions = json_decode($user['rightsPermissions']);
        $rightsPermissions = array();
        
        foreach($temprightsPermissions as $key => $value) {
            $tempKey = explode('/', $key);
            if(sizeof($tempKey) <= 2) {
                $rightsPermissions[$tempKey[1]] = $value;
            } 
            
        }
        foreach ($navigation as $k => $item) {
            if ($item['path'] !== 'javascript:;') {
                if (!Application_Service_Authorization::isGranted(sprintf('node%s', $item['path']))) {
                    unset($navigation[$k]);
                    continue;
                }
            }

            if (!empty($item['children'])) {
                foreach ($item['children'] as $ck => $citem) {
                    if ($citem['path'] === 'javascript:;') {
                        continue;
                    }
                    $tempCitemPath = explode("/",$citem['path']);
                    $isSuperAdmin = Application_Service_Authorization::isSuperAdmin();
                    if($isSuperAdmin != true)
                    {
                        if($tempCitemPath[1] == "registry-entries") {
                            $permTempId = $tempCitemPath[sizeof($tempCitemPath) - 1];
                            if($rightsPermissions[$permTempId] != 1) {
                                unset($navigation[$k]['children'][$ck]);
                            } else {
                                if (!isset($citem['nohref'])) {
                                    $navigation[$k]['children'][$ck]['nohref'] = false;
                                }
                            }
                        } else {
                            if (!Application_Service_Authorization::isGranted(sprintf('node%s', $citem['path']))) {
                                unset($navigation[$k]['children'][$ck]);
                                continue;
                            } else {
                                if (!isset($citem['nohref'])) {
                                    $navigation[$k]['children'][$ck]['nohref'] = false;
                                }
                            }
                        }
                    }
                    
                    
                    
                    //previous code
                    
                }
            }

            if (empty($navigation[$k]['children']) && $item['path'] === 'javascript:;') {
                unset($navigation[$k]);
                continue;
            }
             //previous code
            if (!isset($citem['nohref'])) {
                $navigation[$k]['nohref'] = false;
            }
        }
        // comagom code start 2019.3.20
        $isCompanyConfirmation = Application_Service_Authorization::getInstance()->isCompanyConfirmation();
        $isAdmin = Application_Service_Authorization::isAdmin();
        $isSuperAdmin = Application_Service_Authorization::isSuperAdmin();
        
        if($isAdmin != true && $isSuperAdmin != true) {
            foreach($navigation as $k => $value) {
                if($value['label'] == "Konfiguracja") {
                    unset($navigation[$k]);
                }
            }
        }
        
        return $navigation;
        // comagom code end 2019.3.20
    }

    public function isGranted($permission, $params = null) {
        return Application_Service_Authorization::isGranted($permission, $params);
    }

    protected function afterLoginEvent() {
        $this->view->jsEventAfterLogin = 1;
    }

    public function getTopNavigation($action='') {

    }

}
