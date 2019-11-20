<?php

class RightsPermissionsController extends Muzyka_Admin
{

    /** @var Application_Model_Registry */
    protected $registryModel;

    /** @var Application_Model_RightsPermissions */
    protected $rightspermissionsModel;
    protected $adminLink;
    protected $osoby;
    protected $userRecordsLimit;

    protected $baseUrl = '/rights-permissions';

    public function init()
    {
        parent::init();
        $this->registryModel = Application_Service_Utilities::getModel('Registry');

        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        $this->mcrypt = $config->mcrypt->toArray();
        $this->key = $this->mcrypt['key'];
        $this->iv = $this->mcrypt['iv'];
        $this->bit_check = $this->mcrypt['bit_check'];

        $this->rightspermissionsModel = Application_Service_Utilities::getModel('TypeRightsPermissions');
        $this->adminLink = Application_Service_Utilities::getModel('AdminLink');
        $this->osoby = Application_Service_Utilities::getModel('Osoby');
        $this->registryAssignees = Application_Service_Utilities::getModel('RegistryAssignees');
        $this->userRecordsLimit = Application_Service_Utilities::getModel('UserRecordsLimit');  
         
        if ($registry->get('config')->production->dev->debug) {
            $this->debugLogin = true;
        }
    }

    public function indexAction()
    {

        $rightspermissionsMini = json_decode($this->rightspermissionsModel->getBytype('mini'));
        $rightspermissionsPro = json_decode($this->rightspermissionsModel->getBytype('pro'));
        $rightspermissionsExpert = json_decode($this->rightspermissionsModel->getBytype('expert'));

        $rightspermissionsExpertArray = json_decode($this->rightspermissionsModel->getBytype('expert'), true);
        $rightspermissionsProArray = json_decode($this->rightspermissionsModel->getBytype('pro'), true);
        $rightspermissionsMiniArray = json_decode($this->rightspermissionsModel->getBytype('mini'), true);
 
        $paginator = $this->registryModel->getList();

        foreach ($paginator as $key => $value) {
            $json_data['label'] = $value->title;

            $name_title = str_replace(" ", "-", $value->title);

            $permissions_Arr = [];

            /*******************************************************/
            $json_second_data['id'] = '-module-access';
            $json_second_data['label'] = 'Dostęp do modułu';
            $json_second_data['name'] = 'perm/' . $value->id;
            $json_second_data['basePermission'] = null;
            $json_second_data['permitted'] = true;
            $json_second_data['expanded'] = true;
            array_push($permissions_Arr, $json_second_data);
            /*******************************************************/
            $permissions = Application_Service_Utilities::getModel('RegistryPermissions')->getList(['registry_id' => $value->id]);

            foreach ($permissions as $permission) {

                // var_dump($permission->title);
                // echo '<br>_________<br>';
    
                $permission_name = str_replace(" ", "-", $permission->title);

                $json_second_data['id'] = $permission_name;
                $json_second_data['label'] = $permission->title;
                $json_second_data['name'] = 'perm/' . $value->id . '/' . $permission->system_name;
                $json_second_data['basePermission'] = 'perm/' . $value->id;
                $json_second_data['permitted'] = true;
                $json_second_data['expanded'] = true;
                array_push($permissions_Arr, $json_second_data);

            }

            $json_data['permissions'] = $permissions_Arr;

            $res_key = $name_title;
            $res[$res_key] = $json_data;
        }
        // comagom code start 2019.3.28
        $limitExpert = $this->userRecordsLimit->getLimitByType('expert');
        $limitPro = $this->userRecordsLimit->getLimitByType('pro');
        $limitMini = $this->userRecordsLimit->getLimitByType('mini');

        $expertCountInfos = json_decode($limitExpert['limit_info']);
        $proCountInfos = json_decode($limitPro['limit_info']);
        $miniCountInfos = json_decode($limitMini['limit_info']);

        $limitExpertJson = [];
        $limitProJson = [];
        $limitMiniJson = [];

        foreach($expertCountInfos as $key => $value) {
            $permkey = 'perm/'.$key;
            $limitExpertJson[$permkey] = $value; 
        }

        foreach($proCountInfos as $key => $value) {
            $permkey = 'perm/'.$key;
            $limitProJson[$permkey] = $value; 
        }

        foreach($miniCountInfos as $key => $value) {
            $permkey = 'perm/'.$key;
            $limitMiniJson[$permkey] = $value; 
        }
        // comagom code end

        $this->view->limitExpertJson = $limitExpertJson;
        $this->view->limitProJson = $limitProJson;
        $this->view->limitMiniJson = $limitMiniJson;

        $this->view->rightspermissionsExpertConfigExtended = $res;
        $this->view->rightspermissionsProConfigExtended = $res;
        $this->view->rightspermissionsMiniConfigExtended = $res;

        $this->view->rightspermissionsExpertExtended = $rightspermissionsExpertArray;
        $this->view->rightspermissionsProExtended = $rightspermissionsProArray;
        $this->view->rightspermissionsMiniExtended = $rightspermissionsMiniArray;
        $this->view->rightspermissionsMini = $this->userRights($rightspermissionsMini);
        $this->view->rightspermissionsPro = $this->userRights($rightspermissionsPro);
        $this->view->rightspermissionsExpert = $this->userRights($rightspermissionsExpert);

    }

    public function saveAction()
    {

        $req = $this->getRequest();
        $rightsPro = $req->getParam('rightspermissionsPro', false);
        $rightsMini = $req->getParam('rightspermissionsMini', false);
        $rightsExpert = $req->getParam('rightspermissionsExpert', false);
        // comagom code start 2019.3.21
        $limitMini = $req->getparam('limitMini',false);
        $limitPro = $req->getParam('limitPro',false);
        $limitExpert = $req->getParam('limitExpert',false);
        // comagom code end 2019.3.21
        if ($rightsPro) {
            foreach ($rightsPro as $rel => $right) {
                    $items[$rel] = (int) !empty($right);
            }
            $rightsJsonPro = json_encode($items);
    
            $osoby_logins = $this->adminLink->getAllByType('pro');
            
            foreach ($osoby_logins as $osoby_login) {
                $row = $this->osoby->getUserByLogin($osoby_login);
                $rightsOsoby = json_encode($items);
                $row->rightsPermissions = $rightsOsoby;
                if ($row->id != null) {
                    $this->osoby->save($row);

                    $this->registryAssignees->saveRowWithUserPermissionID($row->id, $items);
                }
            }
            // comagom code start 2019.3.21
            $limitProJson = array();
            foreach($limitPro as $key => $value) {
                $sel_reg_id = explode('/', $key);
                $sel_reg_id = $sel_reg_id[1];
                // Here, sel_reg_id is the same as registry_id.
                $limitProJson[$sel_reg_id] = $value;

            }
            $limitProJson = json_encode($limitProJson);
            // comagom code end 2019.3.21
            if($req->getParam('limitPro', false)) {
                // $limit = $req->getParam('pro_limit', false);
                // $this->userRecordsLimit->update('pro', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('pro',$limitProJson);
                // comagom code end 2019.3.21
            }
            if($req->getParam('limitPro', false)) {
                // $limit = $req->getParam('pro_limit', false);
                // $this->userRecordsLimit->update('pro', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('pro',$limitProJson);
                // comagom code end 2019.3.21
            }
        }
        if ($rightsMini) {
            foreach ($rightsMini as $rel => $right) {
                $items[$rel] = (int) !empty($right);
            }
            $rightsJsonMini = json_encode($items);
            
            $osoby_logins = $this->adminLink->getAllByType('mini');
            foreach ($osoby_logins as $osoby_login) {
                $row = $this->osoby->getUserByLogin($osoby_login);
                
                $rightsOsoby = json_encode($items);
                $row->rightsPermissions = $rightsOsoby;
                $this->osoby->save($row);
                
                $this->registryAssignees->saveRowWithUserPermissionID($row->id, $items);
            }
            // comagom code start 2019.3.21
            $limitMiniJson = array();
            foreach($limitMini as $key => $value) {
                $sel_reg_id = explode('/', $key);
                $sel_reg_id = $sel_reg_id[1];
                // Here, sel_reg_id is the same as registry_id.
                $limitMiniJson[$sel_reg_id] = $value;

            }
            $limitMiniJson = json_encode($limitMiniJson);
            // comagom code end 2019.3.21
            if($req->getParam('limitMini', false)) {
                // $limit = $req->getParam('mini_limit', false);
                // $this->userRecordsLimit->update('mini', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('mini',$limitMiniJson);
                // comagom code end 2019.3.21
            }
            if($req->getParam('limitMini', false)) {
                // $limit = $req->getParam('mini_limit', false);
                // $this->userRecordsLimit->update('mini', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('mini',$limitMiniJson);
                // comagom code end 2019.3.21
            }

        }
        
        if ($rightsExpert) {

            foreach ($rightsExpert as $rel => $right) {
                $items[$rel] = (int) !empty($right);
            }
            $rightsJsonExpert = json_encode($items);

            $osoby_logins = $this->adminLink->getAllByType('expert');

            foreach ($osoby_logins as $osoby_login) {
                $row = $this->osoby->getUserByLogin($osoby_login);

                $rightsOsoby = json_encode($items);
                $row->rightsPermissions = $rightsOsoby;
                if ($row->id != null) {
                    $this->osoby->save($row);

                    $this->registryAssignees->saveRowWithUserPermissionID($row->id, $items);
                }
            }
            
            // if($req->getParam('expert_limit', false)) {
            //     $limit = $req->getParam('expert_limit', false);
            //     if($limit == 'Nieograniczony') {
            //         $limit = -1;
            //     }
            //     $this->userRecordsLimit->update('expert', $limit);
            // }

            // comagom code start 2019.3.21
            $limitExpertJson = array();
            foreach($limitExpert as $key => $value) {
                $sel_reg_id = explode('/', $key);
                $sel_reg_id = $sel_reg_id[1];
                // Here, sel_reg_id is the same as registry_id.
                $limitExpertJson[$sel_reg_id] = $value;

            }
            $limitExpertJson = json_encode($limitExpertJson);
            // comagom code end 2019.3.21
            if($req->getParam('limitExpert', false)) {
                // $limit = $req->getParam('mini_limit', false);
                // $this->userRecordsLimit->update('mini', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('expert',$limitExpertJson);
                // comagom code end 2019.3.21
            }
            if($req->getParam('limitExpert', false)) {
                // $limit = $req->getParam('mini_limit', false);
                // $this->userRecordsLimit->update('mini', $limit);
                // comagom code start 2019.3.21
                $this->userRecordsLimit->updateLimitInfoByType('expert',$limitExpertJson);
                // comagom code end 2019.3.21
            }

        }
        
        $this->rightspermissionsModel->update('pro', $rightsJsonPro);
        $this->rightspermissionsModel->update('mini', $rightsJsonMini);
        $this->rightspermissionsModel->update('expert', $rightsJsonExpert);

        $this->_redirect($this->baseUrl);

    }

    private function userRights($rights = array())
    {
        $items = array();
        foreach ($this->extractRightsFromNavigation() as $baseRight => $baseRightConfig) {
            $items[$baseRight] = $this->checkRights($baseRight, $rights);
            foreach ($baseRightConfig['children'] as $extendedRight => $label) {
                $items[$extendedRight] = $this->checkRights($extendedRight, $rights);
            }
        }
        return $items;
    }

    private function checkRights($item, $rights)
    {
        return !empty($rights->$item);
    }

    private function extractRightsFromNavigation()
    {
        
        $results = array();
        $items = array();

        foreach ($this->navigation as $nav) {
            $items[$nav['rel']] = $nav['label'];
            if (!is_array($nav['rights'])) {
                break;
            }

            foreach ($nav['rights'] as $right => $label) {
                $items[$right] = $label;
            }
        }

        foreach ($items as $right => $label) {
            list($baseRight, $extendedRight) = explode('.', $right);

            if (!isset($results[$baseRight])) {
                $results[$baseRight] = array('label' => '', 'children' => array());
            }

            if ($extendedRight) {
                $results[$baseRight]['children'][$right] = $label;
            } else {
                $results[$baseRight]['label'] = $label;
            }
        }

        return $results;
    }

}
