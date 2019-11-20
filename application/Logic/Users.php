<?php
class Logic_Users extends Logic_Abstract
{
    public function assignTasks($idUser, $values)
    {
        if ($values instanceof Base_Form_Form) {
            $values = $values->getValues();
        }
        
        $rowOsoba = $this->getOsobaRow($idUser);
        
        if (empty($rowOsoba)) {
            throw new Exception('Użytkownik nie istnieje');
        }
        
        $dataDocuments = $this->getWorkerDocuments($values['assigned_worker_id']);
        
        if ($rowOsoba->assigned_worker_id !== $values['assigned_worker_id']) {
            // przypisano innego pracownika niż ten obecnie przypisany do konta
            foreach($dataDocuments as $row) {
                $this->createTask($idUser, $row->id);
            }
        }
    }
    
    /**
     * Pobierz wiersz dla użytkownika
     * @param integer $idUser
     * @return Application_Service_EntityRow
     */
    public function getUserRow($idUser)
    {
        $model = new Application_Model_Users();
        
        $select = $model->select()
            ->where('id = ?', $idUser);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    public function getOsobaByWorkerIdRow($idWorker)
    {
        $model = new Application_Model_Osoby();
        
        $select = $model->select()
            ->where('assigned_worker_id = ?', $idWorker);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz wiersz dla użytkownika
     * @param integer $idUser
     * @return Application_Service_EntityRow
     */
    public function getOsobaRow($idUser)
    {
        $model = new Application_Model_Osoby();
        
        $select = $model->select()
            ->where('id = ?', $idUser);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę oczekujących dokumentów przypisanych do pracownika (osoby)
     * @param integer $idWorker
     * @return Zend_Db_Table_Rowset
     */
    protected function getWorkerPendingDocuments($idWorker)
    {
        $logic = new Logic_Workers();
        
        $data = $logic->getWorkerPendingDocuments($idWorker);
        
        return $data;
    }
    
    /**
     * Pobierz listę gotowych dokumentów przypisanych do pracownika (osoby)
     * @param integer $idWorker
     * @return Zend_Db_Table_Rowset
     */
    protected function getWorkerDocuments($idWorker)
    {
        $logic = new Logic_Workers();
        
        $data = $logic->getWorkerDocuments($idWorker);
        
        return $data;
    }
    
    /**
     * Utwórz zadanie dotyczące dokumentu
     * @param integer $idUser
     * @param integer $idDocument
     */
    protected function createTask($idUser, $idDocument)
    {
        $logic = new Logic_Documents();
        $tasksService = Application_Service_Tasks::getInstance();
        
        $row = $logic->getDocumentPendingRow($idDocument);
        
        if ($row instanceof Application_Service_EntityRow) {
            $tasksService->eventNextRoundTaskCreate([
                'documenttemplate_id' => $row->id,
            ], $idUser);
        }
    }
}
