<?php
class Logic_Workers extends Logic_Abstract
{
    /**
     * Pobierz listę dokumentów w trakcie przypisanych do pracownika
     * @param integer $idWorker
     * @return Zend_Db_Table_Rowset
     */
    public function getWorkerPendingDocuments($idWorker)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select();
        
        if (is_array($idWorker)) {
            $select->where('worker_id IN (?)', $idWorker);
        } else {
            $select->where('worker_id = ?', $idWorker);
        }
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * Pobierz listę dokumentów pracownika
     * @param integer $idWorker
     * @return Zend_Db_Table_Rowset
     */
    public function getWorkerDocuments($idWorker)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select();
        
        if (is_array($idWorker)) {
            $select->where('worker_id IN (?)', $idWorker);
        } else {
            $select->where('worker_id = ?', $idWorker);
        }
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
}
