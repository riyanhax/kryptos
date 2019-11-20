<?php
class Logic_Permissions extends Logic_Abstract
{
    const STATUS_WAIT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 3;
    
    public function getPermissionStatusRow($id)
    {
        $model = new Application_Model_PermissionStatus();
        
        $select = $model->select()
            ->where('id = ?', $id);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    /**
     * Pobierz listę statusów uprawnienia na podstawie id_registry_entry 
     * @param integer $idRegistryEntry
     * @return Zend_Db_Table_Rowset
     */
    public function getPermissionStatusByRegistryEntryData($idRegistryEntry)
    {
        $model = new Application_Model_PermissionStatus();
        
        $select = $model->select()
            ->where('registry_entry_id = ?', $idRegistryEntry);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    public function activatePermissions($idDocumentPending)
    {
        $logic = new Logic_Documents();
        $row = $logic->getDocumentPendingRow($idDocumentPending);
        $dataStatus = $logic->getDocumentPendingPermissionStatusData($idDocumentPending);
        
        foreach ($dataStatus as $rowStatus) {
            $rowStatus->setFromArray([
                'status' => self::STATUS_ACTIVE,
                'parent_id' => $row->registry_entry_id,
                'registry_entry_id' => $row->registry_entry_id,
            ])->save();
        }
    }
    
    public function activatePermissionsByRegistryEntryId($idRegistryEntry)
    {
        $data = $this->getPermissionStatusByRegistryEntryData($idRegistryEntry);
        
        foreach ($data as $row) {
            $row->setFromArray([
                'status' => self::STATUS_ACTIVE,
            ])->save();
        }
    }
    
    /**
     * Zmień status wszystkich uprawnień dla danego wpisu rejestru
     * @param integer $idRegistryEntry
     * @param integer $idStatus
     */
    public function changePermissionsStatus($idRegistryEntry, $idStatus, $data = [])
    {
        $dataPermissions = $this->getPermissionStatusByRegistryEntryData($idRegistryEntry);
        
        foreach ($dataPermissions as $row) {
            $row->setFromArray(array_merge($data, [
                'status' => $idStatus,
            ]))->save();
        }
    }
}
