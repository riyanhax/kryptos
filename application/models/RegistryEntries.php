<?php

class Application_Model_RegistryEntries extends Muzyka_DataModel
{
    protected $_name = "registry_entries";
    protected $_base_name = 're';
    protected $_base_order = 're.id ASC';
    public $_rowClass = 'Application_Service_RegistryEntryRow';

    public $injections = [
        'author' => ['Osoby', 'author_id', 'getList', ['o.id IN (?)' => null], 'id', 'author', false],
        'registry' => ['Registry', 'registry_id', 'getListFull', ['r.id IN (?)' => null], 'id', 'registry', false],
    ];

    public $id;
    public $registry_id;
    public $author_id;
    public $title;
    public $created_at;
    public $updated_at;

    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     */
    public function save($data)
    {
        if (is_array($data) && empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            if ($data instanceof Application_Service_EntityRow) {
                $row = $data;
                if (empty($data->id)) {
                    $data->created_at = date('Y-m-d H:i:s');
                }
            } else {
                $row = $this->requestObject($data['id']);
                $row->setFromArray($data);
            }
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }

    public function resultsFilter(&$results)
    {
    }

    public function countByRegistryID($registry_id)
    {
       $result = $this->_db->query(sprintf('SELECT count(*) as totalRows FROM `registry_entries` where `registry_id` = "'.(int)$registry_id.'" And `status_of_worker` = 0'))->fetchAll(PDO::FETCH_ASSOC);
       return $result[0]['totalRows'];
    }
    public function countByRegistryIDAndAuthorID($registry_id, $author_id)
    {
        $result = $this->_db->query(sprintf('SELECT count(*) as totalRows FROM `registry_entries` where `registry_id` = "'.(int)$registry_id.'" And `author_id` = "'.(int)$author_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['totalRows'];
    }
	
	
	/* public function checkdataexist($data){
		$registry_id= $data['registry_id'];
		 $data['element_755'];
		$result = $this->_db->query(sprintf('SELECT count(*) as totalRows FROM `registry_entries_entities_text` where `registry_id` = "'.(int)$registry_id.'" And `status_of_worker` = 0'))->fetchAll(PDO::FETCH_ASSOC);
       return $result[0]['totalRows'];
	} */

    public function getAllForTypeahead($conditions = [])
    {
        $query = $this->_db->select()
            ->from(array($this->_base_name => $this->_name), array('id', 'name' => 'title'))
            ->order('title ASC');

        $this->addConditions($query, $conditions);

        return $query->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getListTemplatePracownicyZatrudnienie($conditions = [])
    {
        $pracownikEntity = Application_Service_Registry::getInstance()->getEntityId('rejestr_zatrudnien', 'employee');

        $select = $this->getSelect()
            ->joinLeft(['po' => 'registry_entries_entities_int'], 'po.entry_id = re.id AND po.registry_entity_id = ' . $pracownikEntity->id, []);

        $this->addConditions($select, $conditions);
        $results = $this->getListFromSelect($select, $conditions);
        Application_Service_Registry::getInstance()->entriesGetEntities($results);

        return Application_Service_Utilities::renderView('registry-entries/ui/index-zatrudnienie.html', [
            'paginator' => $results,
            'registry' => Application_Service_Utilities::getModel('Registry')->getOne($conditions['registry_id = ?']),
        ]);
    }

    public function getListTemplatePracownicyPliki($conditions = [])
    {
        $pracownikEntity = Application_Service_Registry::getInstance()->getEntityId('rejestr_plikow_pracownikow', 'employee');

        $select = $this->getSelect()
            ->joinLeft(['po' => 'registry_entries_entities_int'], 'po.entry_id = re.id AND po.registry_entity_id = ' . $pracownikEntity->id, []);

        $this->addConditions($select, $conditions);
        $results = $this->getListFromSelect($select, $conditions);
        Application_Service_Registry::getInstance()->entriesGetEntities($results);

        return Application_Service_Utilities::renderView('registry-entries/ui/index-pracownicy-pliki.html', [
            'paginator' => $results,
            'registry' => Application_Service_Utilities::getModel('Registry')->getOne($conditions['registry_id = ?']),
        ]);
    }

    public function getListTemplateZbioryPliki($conditions = [])
    {
        $zbiorEntity = Application_Service_Registry::getInstance()->getEntityId('rejestr_plikow_zbiorow', 'zbior');

        $select = $this->getSelect()
            ->joinLeft(['po' => 'registry_entries_entities_int'], 'po.entry_id = re.id AND po.registry_entity_id = ' . $zbiorEntity->id, []);

        $this->addConditions($select, $conditions);
        $results = $this->getListFromSelect($select, $conditions);
        Application_Service_Registry::getInstance()->entriesGetEntities($results);

        return Application_Service_Utilities::renderView('registry-entries/ui/index-rejestry-pliki.html', [
            'paginator' => $results,
            'registry' => Application_Service_Utilities::getModel('Registry')->getOne($conditions['registry_id = ?']),
        ]);
    }

    public function getEmployeeConsents($conditions = [])
    {
        $pracownikEntity = Application_Service_Registry::getInstance()->getEntityId('consents_registry', 'employee');

        $select = $this->getSelect()
            ->joinLeft(['po' => 'registry_entries_entities_int'], 'po.entry_id = re.id AND po.registry_entity_id = ' . $pracownikEntity->id, []);

        $this->addConditions($select, $conditions);
        $results = $this->getListFromSelect($select, $conditions);
        Application_Service_Registry::getInstance()->entriesGetEntities($results);

        return Application_Service_Utilities::renderView('registry-entries/ui/index-pracownicy-zgody.html', [
            'paginator' => $results,
            'registry' => Application_Service_Utilities::getModel('Registry')->getOne($conditions['registry_id = ?']),
        ]);
    }
    public function getEntriesByRegistryIdAndId($registry_id,$id) {
        return $this->fetchRow($this->select()
            ->where('id = ?', $id)
            ->where('registry_id = ?', $registry_id));
    }
    public function getEntriesByRegistryIdAndWorkerId($registry_id,$worker_id) {
        return $this->fetchRow($this->select()
            ->where('worker_id = ?', $worker_id)
            ->where('registry_id = ?', $registry_id));
    }
    public function getEntriesByRegistryId($registry_id)
    {
       $result = $this->_db->query(sprintf('SELECT * FROM `registry_entries` where `registry_id` = "'.$registry_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }
    public function getWorkerIdByRegistryEntryId($id) {
        $result = $this->_db->query(sprintf('SELECT `worker_id` FROM `registry_entries` where `id` = "'.$id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }
    public function getRegystrIdByRegistryEntryId($id)
    {
        $result = $this->_db->query(sprintf('SELECT `registry_id` FROM `registry_entries` where `id` = "'.$id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }

    public function getEntriesByWorkerId($worker_id)
    {
       $result = $this->_db->query(sprintf('SELECT * FROM `registry_entries` where `worker_id` = "'.$worker_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }

    public function getEnitiesValuesForSelectedDatatypes($paginator, $registryId)
    {
        $selectedAttrName = [];
        $tab_count = [];
      
        foreach ($paginator as $entity) {
            if (isset($entity->id)) {
                $row = $this->getFull([
                    'id' => $entity->id,
                    'registry_id' => $registryId,
                    ], true);
                Application_Service_Registry::getInstance()->entryGetEntities($row);            
            }
            $max = 0;
            foreach ($row->registry->entities as $registryEntity) {
                $selId = $registryId.'-'.$entity->id. '-'.$registryEntity->id;
                $tab_id = $registryId.'-'.$entity->id;

                $character = json_decode($registryEntity->config);
                
                $tab_count[$tab_id] = $max;
                //get number of tabs 
                if ($character->tab > $max) {
                    $max = $character->tab;
                    $tab_count[$tab_id] = $character->tab;
                }
                //tag array for which value of ids to be get
                $tag_array = [
                    'bs.relationshipMatrixMultiple',
                    'bs.relationshipMatrixExtra',
                    'bs.relationshipMatrix',
                    'bs.relationshipMatrixDynamic',
                    'bs.smartRadioGroup',
                    'bs.smartMultiSelect',
                    'bs.hyperlink',
                    'bs.checkboxGroup',
                    'bs.moreInfo',
                    'bs.typeahead',
                    'bs.texthtml'
                ];
              
                if (isset($registryEntity->entity->config_data->element->tag) 
                    && in_array($registryEntity->entity->config_data->element->tag, $tag_array)
                )  {
                    $paramsArr =  Application_Service_Entities::getEntityParams($registryEntity, $row);
                    
                    // if element is relationship Matrix or Matrix Multiple or Matrix Extra
                    if (($registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrixMultiple' 
                        || $registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrix'
                        || $registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrixExtra') 
                        && isset($paramsArr['attributes']['value'])
                        && !empty($paramsArr['attributes']['value'])
                    ) {
                        foreach ($paramsArr['attributes']['value'] as $attrValue) {
                            $allAttr = explode('-', $attrValue);
                            
                            $val1 = (isset($allAttr['0']) && !empty($allAttr['0'])) ?  $allAttr['0']   : '';
                            $val2 = (isset($allAttr['1']) && !empty($allAttr['1'])) ?  $allAttr['1']   : '';
                            $val3 = (isset($allAttr['2']) && !empty($allAttr['2'])) ?  $allAttr['2']   : '';
                            $selectedAttrName[$selId][] = '';
                            if ($val1 != '' && $val2 != '' && $val3 !='') { 
                                $attr_name = '';
                                $all_options = [];

                                if (($registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrixMultiple' 
                                    || $registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrix')
                                    && isset($paramsArr['attributes']['col_options'])
                                    && isset($paramsArr['attributes']['row_options'])
                                ) {
                                    $all_options = $paramsArr['attributes']['col_options'] + $paramsArr['attributes']['row_options'];    
                                }
                                if ($registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrixExtra' 
                                    && isset($paramsArr['attributes']['col_options'])
                                    && isset($paramsArr['attributes']['row_options'])
                                    && isset($paramsArr['attributes']['item_options'])
                                ) {
                                    $all_options = $paramsArr['attributes']['col_options'] + $paramsArr['attributes']['row_options'] + $paramsArr['attributes']['item_options'];    
                                }

                                if ((isset($all_options[$val1]) && !empty($all_options[$val1]))) {
                                    $attr_name .= $all_options[$val1]. ' - ';
                                }
                                if ((isset($all_options[$val2]) && !empty($all_options[$val2]))) {
                                    $attr_name .= $all_options[$val2].' - ';
                                }
                                if ((isset($all_options[$val3]) && !empty($all_options[$val3]))) {
                                    $attr_name .= $all_options[$val3]. '<br/>';
                                }

                                $selectedAttrName[$selId][] = ($attr_name != '') ? $attr_name : '';
                            }
                        }
                        $selectedAttrName[$selId] = array_filter($selectedAttrName[$selId]);
                        $selectedAttrName[$selId] = (!empty($selectedAttrName[$selId])) ? implode(', ',$selectedAttrName[$selId]) : '';
                    }
                    // if element is relationship Matrix Dynamic
                    if ($registryEntity->entity->config_data->element->tag == 'bs.relationshipMatrixDynamic'
                        && isset($paramsArr['attributes']['all_options'])
                        && isset($paramsArr['attributes']['value'])
                        && !empty($paramsArr['attributes']['value']) 
                    ) {
                        $decode_options =  json_decode($paramsArr['attributes']['all_options'],true);
                        
                        // loop selected values
                        foreach ($paramsArr['attributes']['value'] as $attr_value) {
                            $split_attr = explode('-', $attr_value);
                            $val1 = $split_attr['1'];
                            $val2 = $split_attr['2'];
                            //find selected id names
                            $name_str = '';
                            foreach ($decode_options as $options_arr) {
                                if (isset($options_arr['values'][$val1]) && !empty($options_arr['values'][$val1])) {
                                    $name_str .= $options_arr['values'][$val1];
                                }
                                if (isset($options_arr['values'][$val2]) && !empty($options_arr['values'][$val2])) {
                                    $name_str .= ' - '.$options_arr['values'][$val2];
                                }
                            }
                            $selectedAttrName[$selId][] = ($name_str !='') ? $name_str : '';
                        }
                        $selectedAttrName[$selId] = array_filter($selectedAttrName[$selId]);
                        $selectedAttrName[$selId] = (!empty($selectedAttrName[$selId])) ? implode(', ',$selectedAttrName[$selId]) : ''; 
                    }
                    // if element is smart Radio Group or smartMultiSelect
                    if (($registryEntity->entity->config_data->element->tag == 'bs.smartRadioGroup' 
                        || $registryEntity->entity->config_data->element->tag == 'bs.smartMultiSelect') 
                        && isset($paramsArr['attributes']['value'])
                        && !empty($paramsArr['attributes']['value'])
                    ) {
                        //if all option array exists in which all options values are present                                    
                        if (isset($paramsArr['attributes']['all_options'])  && !empty($paramsArr['attributes']['all_options'])) {
                            //get first key of all option array
                            $alloption_firstkey = (array_keys($paramsArr['attributes']['all_options'])['0']) ? array_keys($paramsArr['attributes']['all_options'])['0'] : '';
                            if ($alloption_firstkey != '') {
                                //loop for each attribute selected values
                                foreach ($paramsArr['attributes']['value'] as $attrValue) {
                                    //if selected value exists in all option array
                                    if (isset($paramsArr['attributes']['all_options'][$alloption_firstkey]['values'][$attrValue]) && !empty($paramsArr['attributes']['all_options'][$alloption_firstkey]['values'][$attrValue])) {
                                        $selectedAttrName[$selId][] = implode(',',$paramsArr['attributes']['all_options'][$alloption_firstkey]['values'][$attrValue]);
                                    } else {
                                        $selectedAttrName[$selId][] = '';
                                    } 
                                }
                                $selectedAttrName[$selId] = array_filter($selectedAttrName[$selId]);
                                $selectedAttrName[$selId] = (!empty($selectedAttrName[$selId])) ? implode(', ',$selectedAttrName[$selId]) : '';
                            }
                        }    
                    }
                    // if element is hyperlink
                    if (($registryEntity->entity->config_data->element->tag == 'bs.hyperlink')) {
                        //if url of hyperlink exist
                        if (isset($paramsArr['attributes']['url']) && !empty($paramsArr['attributes']['url'])) {
                            $selectedAttrName[$selId] = $paramsArr['attributes']['url'];
                        } else {
                            $selectedAttrName[$selId] = '';
                        }  
                    }
                    // if element is hyperlink
                    if (($registryEntity->entity->config_data->element->tag == 'bs.texthtml')) {
                        //if url of hyperlink exist
                        if (isset($paramsArr['attributes']['value']) && !empty($paramsArr['attributes']['value'])) {
                            $selectedAttrName[$selId] = $paramsArr['attributes']['value'];
                        } else {
                            $selectedAttrName[$selId] = '';
                        }  
                    }
                    // if element is checkboxgroup
                    if ($registryEntity->entity->config_data->element->tag == 'bs.checkboxGroup' 
                        && isset($paramsArr['attributes']['options']) && !empty($paramsArr['attributes']['options'])) {
                        foreach ($paramsArr['attributes']['options'] as $options_arr) {
                            //if current option is selected then eneter into array
                            if (isset($options_arr['selected']) && !empty($options_arr['selected']) && $options_arr['selected'] == 1) {
                                $selectedAttrName[$selId][] = (isset($options_arr['name']) && !empty($options_arr['name'])) ? $options_arr['name'] : '';
                            } else {
                                $selectedAttrName[$selId][] = '';
                            } 
                        }
                        $filter_array = array_filter($selectedAttrName[$selId]); //remove empty records
                        $selectedAttrName[$selId] = (!empty($filter_array)) ? implode(', ',$filter_array) : '';
                    }
                    // if element is more info
                    if (($registryEntity->entity->config_data->element->tag == 'bs.moreInfo')) {
                        //if moreInfo description exists
                        if (isset($paramsArr['attributes']['description']) && !empty($paramsArr['attributes']['description'])) {
                            $selectedAttrName[$selId] = $paramsArr['attributes']['description'];
                        } else {
                            $selectedAttrName[$selId] = '';
                        }  
                    }
                    // if element is typehead for selection of employees
                    if ($registryEntity->entity->config_data->element->tag == 'bs.typeahead'
                        && isset($paramsArr['attributes']['value']) 
                        && !empty($paramsArr['attributes']['value'])
                        && isset($paramsArr['attributes']['model']) 
                        && !empty($paramsArr['attributes']['model'])
                        && $paramsArr['attributes']['model'] == 'Osoby'
                    ) {
                        //get selected user info
                        $osobyModel = Application_Service_Utilities::getModel('Osoby');
                        $user_row = $osobyModel->getOne($paramsArr['attributes']['value']); //get user info
                        if (isset($user_row->imie) && !empty($user_row->imie)) { //if user name exists
                            $selectedAttrName[$selId] = $user_row->imie;
                        } else {
                            $selectedAttrName[$selId] = '';
                        }  
                    }
                }
            }    
        }

        $result = array(
            'selectedAttrName' => $selectedAttrName,
            'tab_count' => $tab_count
        );
        return $result;
    }
}
