<?php

class Application_Model_RegistryAction extends Muzyka_DataModel
{
    protected $_name = "registry_action";

    public $id;
    public $module_id;
    public $module_type;
    public $action;
    public $action_on;
    public $action_name;
    public $previous_value;
    public $new_value;

    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     */
    public function save($data)
    {
        if(!isset($data['record_id'])) {

           $data['record_id'] = "";
        }
        $registry_action_data = array(
          'module_id' => $data['module_id'],
          'module_type' => $data['controller'],
          'action' => $data['action'],
          'action_on' => $data['field'],
          'action_name' => $data['action_name'],
          'previous_value' => $data['previous_value'],
          'new_value' => $data['new_value'],
          'insert_date' => date('Y-m-d H:i:s'),
          'user_id' => $data['user_id'],
          'record_id' => $data['record_id'],
          'module_id_value' => $data['module_id_value'],
          'user_id_value' => $data['user_id_value'],
        );
        
        $row = $this->createRow($registry_action_data);
        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        
        return $row;
    }
	public function getuniquerecords($data,$rec){
		 $registry_id=$rec['registry_id'];
		 $element_755=$rec['element_755'];
	  $select = $this->getAdapter()->select()
	    ->from('registry_action')
		 ->where('module_id= ?', $registry_id)
		->where('new_value= ?','["'.$element_755.'"]');
        $data= $select->query()->fetchAll(PDO::FETCH_ASSOC);
		 if(!empty($data)){
			 return 1;
		 }else{
			 return 0;
		 }
		}

    
}
