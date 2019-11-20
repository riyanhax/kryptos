<?php
class Logic_Registry extends Logic_Abstract
{
    const REGISTRY_PERMISSIONS = 175;
    
    public static $entryEntitesDependencies = [
        Application_Model_RegistryEntriesEntitiesDate::class,
        Application_Model_RegistryEntriesEntitiesDateTime::class,
        Application_Model_RegistryEntriesEntitiesInt::class,
        Application_Model_RegistryEntriesEntitiesText::class,
        Application_Model_RegistryEntriesEntitiesVarchar::class,
    ];
    
    /**
     * @param integer $id
     * @return Application_Service_RegistryEntryRow
     */
    public function getRegistryEntryRow($id)
    {
        $model = new Application_Model_RegistryEntries();
        
        $select = $model->select()
            ->where('id = ?', $id);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę wartości przypisanych do danego registry_entry.
     * Wynikiem jest tablica, gdzie każdy rekord zawiera klucz model z nazwą modelu w postaci stringa
     * oraz klucz data z listą wartości, które przechowuje dana tabela
     * @param integer $idRegistryEntry
     * @return array
     */
    public function getRegistryEntitiesData($idRegistryEntry)
    {
        $return = [];
        $models = self::$entryEntitesDependencies;
        
        foreach ($models as $model) {
            $data = $this->getRegistryEntityData($idRegistryEntry, $model);
            
            $return[] = [
                'model' => $model,
                'data' => $data,
            ];
        }
        
        return $return;
    }
    
    public function removeDependencyEntities($idRegistryEntry)
    {
        $data = $this->getRegistryEntitiesData($idRegistryEntry);
        
        foreach ($data as $row) {
            $modelName = $row['model'];
            $dataEntities = $row['data'];
            
            if ($dataEntities->count()) {
                foreach ($dataEntities as $rowEntity) {
                    $this->removeRegistryEntityRow($rowEntity->id, $modelName);
                }
            }
        }
    }
    
    /**
     * Zarchwizuj aktywne dokumenty podpięte pod ten rejestr
     * @param integer $idRegistryEntry
     */
    public function archivizeDependencyDocuments($idRegistryEntry)
    {
        $logic = new Logic_Documents();
        
        $data = $logic->getRegistryEntryActiveDocumentsData($idRegistryEntry);
        
        foreach ($data as $row) {
            $logic->archvizeDocument($row->id);
        }
        
    }
    
    /**
     * Usuń oczekujące dokumenty przypisane do danego wpisu rejestru
     * @param integer $idRegistryEntry
     */
    public function removeDependencyPendingDocuments($idRegistryEntry)
    {
        $logic = new Logic_Documents();
        
        $data = $logic->getRegistryEntryPendingDocumentsData($idRegistryEntry);
        
        foreach ($data as $row) {
            $logic->removePendingDocument($row->id);
        }
    }
    
    public function removeRegistryEntryRow($idRegistryEntry)
    {
        $model = new Application_Model_RegistryEntries();
        
        $model->update(['ghost' => '1'], ['id = ?' => $idRegistryEntry]);
    }
    
    /**
     * Sprawdź czy pracownik ma już wpis w registry_entries dla danego rejestru
     * @param integer $idRegistry
     * @param integer $idWorker
     * @return boolean
     */
    public function hasWorkerRegistryEntry($idRegistry, $idWorker)
    {
        $row = null;
        
        if (!empty($idRegistry) && !empty($idWorker)) {
            $model = new Application_Model_RegistryEntries();

            $select = $model->select()
                ->where('registry_id = ?', $idRegistry)
                ->where('worker_id = ?', $idWorker)
                ->where('NOT ghost');

            $row = $model->fetchRow($select);
        }
        
        return !empty($row);
    }
    
    /**
     * Pobierz dane przypisane do wpisu rejestru (registry_entries) o podanym w pierwszym parametrze id
     * oraz dla nazwy modelu podanego w drugim parametrze (modele różnią się typem przechowywanych danych - zgodnie z nazwą)
     * @param integer $idRegistryEntry
     * @param string $modelName
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Exception
     */
    protected function getRegistryEntityData($idRegistryEntry, $modelName)
    {
        if (!class_exists($modelName)) {
            throw new Exception(sprintf('Klasa modelu %s nie istnieje', $modelName));
        }
        
        $model = new $modelName();
        
        if (!$model instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Klasa modelu musi dziedziczyć po Zend_Db_Table_Abstract');
        }
        
        $select = $model->select()
            ->where('entry_id = ?', $idRegistryEntry);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    protected function removeRegistryEntityRow($id, $modelName)
    {
        if (!class_exists($modelName)) {
            throw new Exception(sprintf('Klasa modelu %s nie istnieje', $modelName));
        }
        
        $model = new $modelName();
        
        if (!$model instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Klasa modelu musi dziedziczyć po Zend_Db_Table_Abstract');
        }
        
        $model->update(['ghost' => '1'], ['id = ?' => $id]);
    }
}
