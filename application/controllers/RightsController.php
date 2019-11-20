<?php


class RightsController extends Muzyka_Admin
{

    /** @var Application_Model_Rights */
    protected $rightsModel;

    public function init()
    {
        parent::init();
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('config');
        $this->mcrypt = $config->mcrypt->toArray();
        $this->key = $this->mcrypt ['key'];
        $this->iv = $this->mcrypt ['iv'];
        $this->bit_check = $this->mcrypt ['bit_check'];
        
	
        $this->rightsModel = Application_Service_Utilities::getModel('TypeRights');

        if ($registry->get('config')->production->dev->debug) {
            $this->debugLogin = true;
        }
    }

    public function indexAction(){
	
        $rightsMini = json_decode($this->rightsModel->getBytype('mini'));
        $rightPro = json_decode($this->rightsModel->getBytype('pro'));
        $rightExpert = json_decode($this->rightsModel->getBytype('expert'));
        
        $rightsExpertArray = json_decode($this->rightsModel->getBytype('expert'), true);
        $rightsProArray = json_decode($this->rightsModel->getBytype('pro'), true);
        $rightsMiniArray = json_decode($this->rightsModel->getBytype('mini'), true);
        // comagom code start 2019.3.27
        $rightsExpertConfigExtendedFilters = Application_Service_Authorization::getInstance()->getModuleSettingsSorted($rightsExpertArray);
        $filteredExpertModules = array();
 
        foreach($rightsExpertConfigExtendedFilters['modules'] as $key => $value) {
            if($key == "courses") {
                $value['label'] = "kursy";
                $filteredExpertModules[$key] = $value;
            } elseif($key == "settings") {
                $value['label'] = "ustawienia";
                $filteredExpertModules[$key] = $value;
            } else {
                $filteredExpertModules[$key] = $value;
            }
        }
        $rightsExpertConfigExtendedFilters['modules'] = $filteredExpertModules;

        $rightsProConfigExtendedFilters = Application_Service_Authorization::getInstance()->getModuleSettingsSorted($rightsProArray);
        $filteredProModules = array();
        foreach($rightsProConfigExtendedFilters['modules'] as $key => $value) {
            if($key == "courses") {
                $value['label'] = "kursy";
                $filteredProModules[$key] = $value;
            } elseif($key == "settings") {
                $value['label'] = "ustawienia";
                $filteredProModules[$key] = $value;
            } else {
                $filteredProModules[$key] = $value;
            }
        }
        $rightsProConfigExtendedFilters['modules'] = $filteredProModules;

        $rightsMiniConfigExtendedFilters = Application_Service_Authorization::getInstance()->getModuleSettingsSorted($rightsMiniArray);
        $filteredMiniModules = array();
        foreach($rightsMiniConfigExtendedFilters['modules'] as $key => $value) {
            if($key == "courses") {
                $value['label'] = "kursy";
                $filteredMiniModules[$key] = $value;
            } elseif($key == "settings") {
                $value['label'] = "ustawienia";
                $filteredMiniModules[$key] = $value;
            } else {
                $filteredMiniModules[$key] = $value;
            }
        }
        $rightsMiniConfigExtendedFilters['modules'] = $filteredMiniModules;

        $this->view->rightsExpertConfigExtended = $rightsExpertConfigExtendedFilters;
        $this->view->rightsProConfigExtended = $rightsProConfigExtendedFilters;
        $this->view->rightsMiniConfigExtended = $rightsMiniConfigExtendedFilters;
        $this->view->rightsExpertExtended = $rightsExpertArray;
        $this->view->rightsProExtended = $rightsProArray;
        $this->view->rightsMiniExtended = $rightsMiniArray;
        $this->view->rightsMini = $this->userRights($rightsMini);
        $this->view->rightsPro = $this->userRights($rightsPro);
        $this->view->rightsExpert = $this->userRights($rightsExpert);
    }

    public function saveAction(){
	
        $req = $this->getRequest();
            $rightsPro = $req->getParam('rightsPro', false);
            $rightsMini = $req->getParam('rightsMini', false);
            $rightsExpert = $req->getParam('rightsExpert', false);
	    
	    if ($rightsPro) {
                    foreach ($rightsPro as $rel => $right) {
			if($rel == "perm/registry" && (int)!empty($right) == 0){
			    $items[$rel] = 1;
			    $items['perm/registry/all-access'] = 0;
			}
			else
			{
                            $items[$rel] = (int)!empty($right);
			}
                    }
                    $rightsJsonPro = json_encode($items);
                }

	    if ($rightsMini) {
                    foreach ($rightsMini as $rel => $right) {
			if($rel == "perm/registry" && (int)!empty($right) == 0){
			    $items[$rel] = 1;
			    $items['perm/registry/all-access'] = 0;
			}
			else
			{
                            $items[$rel] = (int)!empty($right);
			}
                    }
                    $rightsJsonMini = json_encode($items);
                }

	    if ($rightsExpert) {
                    foreach ($rightsExpert as $rel => $right) {
			if($rel == "perm/registry" && (int)!empty($right) == 0){
			    $items[$rel] = 1;
			    $items['perm/registry/all-access'] = 0;
			}
			else
			{
                            $items[$rel] = (int)!empty($right);
			}
                    }
                    $rightsJsonExpert = json_encode($items);
                }
	
	    $this->rightsModel->update('pro', $rightsJsonPro);
	    $this->rightsModel->update('mini', $rightsJsonMini);
	    $this->rightsModel->update('expert', $rightsJsonExpert);
	    $this->_redirect('rights');
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
            if(!is_array($nav['rights'])) break;
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
