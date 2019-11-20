<?php

class Application_Model_TodoList extends Muzyka_DataModel
{
    protected $_name = "todo_list_items";

    public $id;
    public $registry_id;
    public $registry_title;
    public $status;
    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     */
    public function saveTodoItem($data)
    {
		
		 
        if (is_array($data) && empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
        }
		
		
        $row->registry_id = $data['registry_id'];
        $row->registry_title = $data['registry_title'];
        $row->taskName = $data['taskName'];
        $row->complexity = $data['complexity'];
        $row->creationDate = $data['creationDate'];
        $row->startDate = $data['startDate'];
        $row->completionDate = $data['completionDate'];
        $row->state = $data['state'];
        $id = $row->save();

        // $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $id;
    }
    public function getAllTodoItemsBYRegistryId($registry_id)
    {
		// echo $registry_id;die;
        return $this->fetchAll($this->select()
            ->where('registry_id = ?', $registry_id));
    }
    public function getPendingTodoItemsByRegistryId($registry_id) {
        return $this->fetchAll($this->select()
            ->where('status = ?', 0)
            ->where('registry_id = ?', $registry_id));
    }
    public function getTodoItemsByRegistryId($registry_id)
    { 
        $result = $this->_db->query(sprintf('SELECT * FROM `todo_list_items` where `registry_id` = "'.$registry_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }


}
