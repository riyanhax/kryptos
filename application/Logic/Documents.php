<?php
class Logic_Documents extends Logic_Abstract
{
    /**
     * @param integer $id
     * @return Application_Service_EntityRow
     */
    public function getDocumentPendingRow($id)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('id = ?', $id);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * @param integer $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDocumentRow($id)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('id = ?', $id);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    public function isDocumentAllowed($idDocument, $idUser = null)
    {
        $allowed = true;
        
        if (empty($idUser)) {
            $identity = Base_Auth::getInstance()->getIdentity();
            $idUser = $identity->id;
        }
        
        $logicUser = new Logic_Users();
        
        $rowDocument = $this->getDocumentRow($idDocument);
        $rowOsoba = $logicUser->getOsobaRow($idUser);
        $rowUser = $logicUser->getUserRow($idUser);
        
        if ($rowOsoba->assigned_worker_id !== $rowDocument->worker_id
            && (empty($rowUser->isAdmin) || empty($rowUser->isSuperAdmin))) {
            $allowed = false;
        }
        
        return $allowed;
    }
    
    /**
     * Pobierz listę aktywnych dokumentów przypisanych do danego wpisu rejestru
     * @param integer $idRegistryEntry
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRegistryEntryActiveDocumentsData($idRegistryEntry)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('registry_entry_id = ?', $idRegistryEntry)
            ->where('active IS TRUE');
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function archivizeRegistryEntryActiveDocuments($idRegistryEntry)
    {
        $data = $this->getRegistryEntryActiveDocumentsData($idRegistryEntry);
        
        foreach ($data as $row) {
            $this->archvizeDocument($row->id);
        }
    }
    
    /**
     * Pobierz listę oczekujących dokumentów przypisanych do danego wpisu rejestru
     * @param integer $idRegistryEntry
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRegistryEntryPendingDocumentsData($idRegistryEntry)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('registry_entry_id = ?', $idRegistryEntry);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function archvizeDocument($id)
    {
        $row = $this->getDocumentRow($id);
        
        if (empty($row)) {
            throw new Exception('Dokument o podanym id nie istnieje');
        }
        
        $row->setFromArray([
            'active' => '0',
            'archived_at' => new Zend_Db_Expr("NOW()"),
        ])->save();
    }
    
    public function removePendingDocument($id)
    {
        $row = $this->getDocumentPendingRow($id);
        
        if (empty($row)) {
            throw new Exception('Dokument oczekujący o podanym id nie istnieje');
        }
        
        $row->setFromArray([
            'status' => '0',
        ])->save();
    }
    
    public function getRegistryDocumentsTemplates($idRegistry)
    {
        $model = new Application_Model_Documenttemplates();
        
        $select = $model->select()
            ->where('registry_id = ?', $idRegistry);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function getDocumentsPendingByTemplateIdData($idDocumentTemplate)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * @param integer $idDocumentTemplate
     * @param integer $idWorker
     * @return Zend_Db_Table_Rowset
     */
    public function getWorkerDocumentsPendingByTemplateIdData($idDocumentTemplate, $idWorker)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate)
            ->where('worker_id = ?', $idWorker);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function removeWorkerDocumentsPendingByTemplateId($idDocumentTemplate, $idWorker)
    {
        $data = $this->getWorkerDocumentsPendingByTemplateIdData($idDocumentTemplate, $idWorker);
        
        foreach ($data as $row) {
            /* @var $row Zend_Db_Table_Row */
            $row->delete();
        }
    }
    
    public function getDocumentsByTemplateIdData($idDocumentTemplate)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function createRegistryDocumentsPending($idWorker, $idRegistry, $idRegistryEntry)
    {
        $service = Application_Service_Documents::getInstance();
        $data = $this->getRegistryDocumentsTemplates($idRegistry);
        
        foreach ($data as $row) {
            if (!$this->isWorkerAssignedToTemplate($idWorker, $row->id)) {
                $this->assignWorkerToTemplate($idWorker, $row->id);
            }
            
            if ($this->hasWorkerPendingDocumentByTemplateId($row->id, $idWorker)) {
                $this->removeWorkerDocumentsPendingByTemplateId($row->id, $idWorker);
            }
            
            $service->create($row->id, $idWorker, $idRegistryEntry);
        }
    }
    
    public function recallDocument($id, $recallReason = null)
    {
        $identity = Base_Auth::getInstance()->getIdentity();
        
        $row = $this->getDocumentRow($id);
        
        if (empty($row)) {
            throw new Exception('Brak dokumentu o podanym id');
        }
        
        $row->setFromArray([
            'active' => '0',
            'is_recalled' => '1',
            'recall_date' => new Zend_Db_Expr("NOW()"),
            'recall_author' => $identity->id,
            'recall_reason' => $recallReason,
        ])->save();
    }
    
    public function isWorkerAssignedToTemplate($idWorker, $idTemplate)
    {
        $model = new Application_Model_Documenttemplatesosoby();
        
        $select = $model->select()
            ->where('worker_id = ?', $idWorker)
            ->where('documenttemplate_id = ?', $idTemplate);
        
        $row = $model->fetchRow($select);
        
        return !empty($row);
    }
    
    public function assignWorkerToTemplate($idWorker, $idTemplate)
    {
        $model = new Application_Model_Documenttemplatesosoby();
        
        $model->createRow([
            'worker_id' => $idWorker,
            'documenttemplate_id' => $idTemplate,
        ])->save();
    }
    
    public function hasWorkerPendingDocumentByTemplateId($idDocumentTemplate, $idWorker)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate)
            ->where('worker_id = ?', $idWorker);
        
        $row = $model->fetchRow($select);
        
        return !empty($row);
    }
    
    public function getWorkerPendingDocumentByTemplateId($idDocumentTemplate, $idWorker)
    {
        $model = new Application_Model_DocumentsPending();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate)
            ->where('worker_id = ?', $idWorker);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę statusów uprawnień przypisanych do danego dokumentu oczekującego
     * @param integer $idDocumentPending
     * @return Zend_Db_Table_Rowset
     */
    public function getDocumentPendingPermissionStatusData($idDocumentPending)
    {
        $model = new Application_Model_PermissionStatus();
        
        $select = $model->select()
            ->where('document_pending_id = ?', $idDocumentPending);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * @param integer $idWorker
     * @param integer $idDocumentTemplate
     * @return Zend_Db_Table_Rowset
     */
    public function getWorkerActiveDocumentsData($idWorker, $idDocumentTemplate)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('documenttemplate_id = ?', $idDocumentTemplate)
            ->where('worker_id = ?', $idWorker)
            ->where('active IS TRUE');
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function recallWorkerActiveDocumentsByTemplate($idWorker, $idDocumentTemplate)
    {
        $data = $this->getWorkerActiveDocumentsData($idWorker, $idDocumentTemplate);
        
        foreach ($data as $row) {
            $this->recallDocument($row->id, 'Dokument wycofany z powodu aktualizacji');
        }
    }
    
    /**
     * Sprawdź czy pracownik o podanym id posiada aktywny dokument o podanym id schematu
     * @param integer $idWorker
     * @param integer $idDocumentTemplate
     * @return boolean
     */
    public function hasWorkerActiveDocumentByTemplateId($idWorker, $idDocumentTemplate)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('worker_id = ?', $idWorker)
            ->where('documenttemplate_id = ?', $idDocumentTemplate)
            ->where('active IS TRUE');
        
        $row = $model->fetchRow($select);
        
        return !empty($row);
    }
    
    /**
     * Pobierz wiersz dokumentu na podstawie id dokumentu oczekującego
     * @param integer $idDocumentPending
     * @return Zend_Db_Table_Row
     */
    public function getDocumentByPendingIdRow($idDocumentPending)
    {
        $model = new Application_Model_Documents();
        
        $select = $model->select()
            ->where('id_document_pending = ?', $idDocumentPending);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę pracowników przypisanych do danego szablonu dokumentu
     * @param integer $idDocumentTemplate
     * @return Zend_Db_Table_Rowset
     */
    public function getDocumentTemplateWorkersData($idDocumentTemplate)
    {
        if (!empty($idDocumentTemplate)) {
            $model = new Application_Model_Documenttemplatesosoby();

            $select = $model->select()
                ->where('documenttemplate_id = ?', $idDocumentTemplate);

            $data = $model->fetchAll($select);
        }
        
        return $data;
    }
    
    public function isNumberingSchemaAssignedToOtherTemplate($idDocumentTemplate, $idNumberingSchema)
    {
        $return = false;
        
        if ($idNumberingSchema === 'brak') {
            $idNumberingSchema = null;
        }
        
        if (!empty($idNumberingSchema)) {
            $model = new Application_Model_Documenttemplates();
            
            $select = $model->select()
                ->where('numberingscheme_id = ?', $idNumberingSchema)
                ->where('active IS TRUE');
            
            if (!empty($idDocumentTemplate)) {
                $select->where('id != ?', $idDocumentTemplate);
            }
            
            $row = $model->fetchRow($select);
            
            $return = !empty($row);
        }
        
        return $return;
    }
}
