<?php
class Logic_Tasks extends Logic_Abstract
{
    const STATUS_TASK_ACTIVE = 1;
    
    const STATUS_STORAGE_TASK_ACTIVE = 0;
    const STATUS_STORAGE_TASK_INACTIVE = 1;
    
    public function getTaskRow($idTask)
    {
        $model = new Application_Model_Tasks();
        
        $select = $model->select()
            ->where('id = ?', $idTask);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * @param integer $id
     * @return Zend_Db_Table_Row
     */
    public function getStorageTaskRow($id)
    {
        $model = new Application_Model_StorageTasks();
        
        $select = $model->select()
            ->where('id = ?', $id);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę aktywnych zadań użytkownika o id podanym w parametrze
     * @param integer $idUser
     * @return Zend_Db_Table_Rowset|null
     */
    public function getUserActiveTasksData($idUser = null)
    {
        $data = null;
        
        if (empty($idUser)) {
            $identity = Base_Auth::getInstance()->getIdentity();
            $idUser = $identity->id;
        }
        
        if (!empty($idUser)) {
            $model = new Application_Model_StorageTasks();

            $select = $model->select()
                ->setIntegrityCheck(false)
                ->from(['st' => 'storage_tasks'])
                ->joinLeft(['t' => 'tasks'], 't.id = st.task_id', ['task_title' => 'COALESCE(st.title, t.title)', 'task_type' => 'type'])
                ->joinLeft(['o' => 'osoby'], 'o.id = t.author_osoba_id', ['author_name' => "CONCAT(o.nazwisko, ' ', o.imie)", 'author_login' => 'o.login_do_systemu'])
                ->joinLeft(['oe' => 'osoby'], 'oe.id = st.user_id', ['employee_name' => "CONCAT(oe.nazwisko, ' ', oe.imie)", 'employee_login' => 'oe.login_do_systemu'])
                ->where('st.user_id = ?', $idUser)
                ->where('st.status = ?', self::STATUS_STORAGE_TASK_ACTIVE)
                ->where('t.status = ?', self::STATUS_TASK_ACTIVE)
                ->group('st.id')
                ->order(['st.created_at DESC']);

            $data = $model->fetchAll($select);
        }
        
        return $data;
    }
    
    public function getStorageTasksData()
    {
        $model = new Application_Model_StorageTasks();
        
        $select = $model->select();
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * Pobierz listę tasków podpiętych do obiektu o id podanym w parametrze
     * @param integer $idObject Może to być np. documenttemplate_id lub document_id lub document_pending_id
     * @return Zend_Db_Table_Rowset
     */
    public function getTaskByObjectIdData($idObject)
    {
        $data = [];
        
        if (!empty($idObject)) {
            $model = new Application_Model_Tasks();

            $select = $model->select()
                ->where('object_id = ?', $idObject);

            $data = $model->fetchAll($select);
        }
        
        return $data;
    }
    
    public function addDocumentStorageTask($idTask, $idDocument)
    {
        $logicDocuments = new Logic_Documents();
        $logicUsers = new Logic_Users();
        
        $rowTask = $this->getTaskRow($idTask);
        $rowDocument = $logicDocuments->getDocumentRow($idDocument);
        
        if (empty($rowTask)) {
            throw new Exception('Zadanie o podanym id nie istnieje');
        }
        
        if (empty($rowDocument)) {
            throw new Exception('Brak dokuemntu o podanym id');
        }
        
        $rowUser = $logicUsers->getOsobaByWorkerIdRow($rowDocument->worker_id);
        
        $triggerConfig = json_decode($rowTask->trigger_config, true);
        
        $this->createStorageTaskRow([
            'task_id' => $idTask,
            'type' => $rowTask->type,
            'status' => self::STATUS_STORAGE_TASK_ACTIVE,
            'user_id' => $rowUser->id,
            'author_osoba_id' => $rowTask->author_osoba_id,
            'object_id' => $rowDocument->id,
            'title' => $rowTask->title,
            'deadline_date' => $this->getDeadlineDate($triggerConfig),
            'created_at' => new Zend_Db_Expr("NOW()"),
        ]);
    }
    
    public function deactiveStorageTask($id)
    {
        $row = $this->getStorageTaskRow($id);
        
        $row->setFromArray([
            'status' => self::STATUS_STORAGE_TASK_INACTIVE,
        ])->save();
    }
    
    protected function createStorageTaskRow($data)
    {
        $model = new Application_Model_StorageTasks();
        
        $id = $model->createRow($data)->save();
        
        return $id;
    }
    
    protected function getDeadlineDate($triggerConfig)
    {
        $return = null;
        
        if ($triggerConfig['day']) {
            $return = new Zend_Db_Expr("DATE_ADD(NOW(), INTERVAL {$triggerConfig['day']} DAY)");
        }
        
        if ($triggerConfig['date']) {
            $return = $triggerConfig['date'];
        }
        
        return $return;
    }
}
