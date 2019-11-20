<?php

class Application_Model_DeletedWorker extends Muzyka_DataModel
{
    private $id;
    private $registry_id;
    private $worker_id;
    private $selected_permission_entry_id;
    private $worker_name;
    private $worker_surname;

    protected $_name = "deleted_worker_lists";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getAllofDeletedWorkers()
    {
        $result = $this->_db->query(sprintf('SELECT * FROM `deleted_worker_lists`'))->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getTodoItemsByRegistryId($registry_id)
    { 
        $result = $this->_db->query(sprintf('SELECT * FROM `todo_list_items` where `registry_id` = "'.$registry_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }

    public function saveDeletedWorkerInfo($data)
    {
        if (is_array($data) && empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
        }
        $row->registry_id = $data['registry_id'];
        $row->worker_id = $data['worker_id'];
        $row->selected_permission_entry_id = $data['selected_permission_entry_id'];
        $row->worker_name = $data['worker_name'];
        $row->worker_surname = $data['worker_surname'];
        $id = $row->save();

        return $id;
    }
    // comagom code start 2019.3.21
    public function updateLimitInfoByType($type,$limit)
    {
        $sql = $this->select('1')
            ->where('type = ?', $type);
    
        $type_exist = $this->fetchRow($sql);
        if($type_exist) {        
            $qur = "UPDATE `user_records_limit` SET `limit_info`='". $limit ."' WHERE `type`='" .$type."'";
            $this->_db->query(sprintf($qur));
        }
    }
    // comagom code end 2019.3.21
}
