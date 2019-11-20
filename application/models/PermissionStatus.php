<?php

class Application_Model_PermissionStatus extends Muzyka_DataModel
{
    protected $_name = "permission_status";

    public $id;
    public $registry_id;
    public $parent_id;
    public $status;
    public $registry_entry_id;
    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     */
    public function savePermissionStatus($data)
    {
        if (is_array($data) && empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = date('Y-m-d H:i:s');
        }
        $row->registry_entry_id = $data['registry_entry_id'];
        $row->registry_id = $data['registry_id'];
        $row->status = $data['status'];
        $row->parent_id = $data['parent_id'];
        $id = $row->save();

        // $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }
    public function changePermissionStatusByID($id , $data) {
        $forbidden = array('registry_id', 'parent_id', 'status', 'registry_entry_id');
        
        $keys = array_keys($data);
        foreach ($forbidden as $forbid) {
            if (in_array($forbid, $keys)) {
                return false;
            }
        }
        
        $this->update($data, 'id =' . (int)$id);

    }
    public function getOneOfPermissionStatus($registry_entry_id)
    {
        return $this->getAdapter()
            ->select()
            ->from($this->_name)
            ->where("registry_entry_id=?", $registry_entry_id)
            ->query()
            ->fetch();
    }
    public function getWithdrawlInformationByRegistryIdAndRegistryEntryId($registry_id, $id)
    {
        return $this->getAdapter()
            ->select()
            ->from($this->_name)
            ->where("registry_id=?", $registry_id)
            ->where("registry_entry_id=?", $id)
            ->query()
            ->fetch();
    }
    public function updatePermissionByRegistryEntryID($data, $id)
    {
        
        $arr = array(
            'reason_content' => $data['reason_content'],
            'withdrawal_date_time' => $data['withdrawal_date_time'],
            'status' => $data['status']
        );
        
        return $this->update($arr, 'id=' . $id);
    }
    public function updatePermissionByRegistryEntryIDViaWorkerId($data, $registry_entry_id)
    {
        
        $arr = array(
            'status' => $data['status']
        );
        
        return $this->update($arr, 'registry_entry_id=' . $registry_entry_id);
    }
    public function getAllOfPermissionStatus() 
    {
       return $this->fetchAll()->toArray();
    }
    public function resultsFilter(&$results)
    {
    }


}
