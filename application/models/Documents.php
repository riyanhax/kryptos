<?php

class Application_Model_Documents extends Muzyka_DataModel
{
    protected $_name = 'documents';
    protected $_base_name = 'd';
    protected $_base_order = 'd.id ASC';

    private $id;
    private $name;

    private $created_at;
    private $date;
    private $osoba_id;
    private $active;
    private $documenttemplate_id;
    private $number;
    private $numbertxt;

    public $is_recalled;
    public $recall_reason;
    public $recall_date;
    public $recall_author;

    public $injections = [
        'attachments' => ['DocumentsAttachments', 'id', 'getList', ['da.document_id IN (?)' => null], 'document_id', 'attachments', true],
        //'signature' => ['StorageTasks', 'id', 'getSignatures', ['st.object_id IN (?)' => null], 'storage_task_object_id', 'signature', false],
        'signature' => ['StorageTasks', 'id', 'getSignatures', ['st.type = 2 AND st.object_id IN (?)' => null], 'storage_task_object_id', 'signature', false],
    ];


    public $memoProperties = array(
        'id',
        'osoba_id',
        'documenttemplate_id',
        'active',
        'is_recalled',
    );

    public function getAll()
    {
        return $this->select()
            ->order('name ASC')
            ->query()
            ->fetchAll();
    }

    public function getList($conditions = array(), $limit = null, $order = null)
    {
        $repositoryService = Application_Service_Repository::getInstance();
        $versionedObjects = $repositoryService->getVersionedObjects();
        $status_array = array(Application_Service_Documents::VERSION_PERMISSIBLE);
        $select = $this->_db->select()
            ->from(array('d' => $this->_name), array('*', 'd.worker_id as d.worker_id', 'has_archive' => sprintf('EXISTS (SELECT IF(da.id IS NOT NULL, 1, 0) FROM documents da WHERE da.documenttemplate_id = d.documenttemplate_id AND d.id <> da.id AND da.active = %d)', Application_Service_Documents::VERSION_ARCHIVE)))
            ->joinLeft(array('dt' => 'documenttemplates'), 'dt.id = d.documenttemplate_id', array('template_name' => 'dt.name', 'template_type' => 'dt.type'))
            //->joinLeft(array('dro' => 'documents_repo_objects'), 'dro.document_id = d.id AND object_id = '. (int) $versionedObjects['osoba.imie']['id'], array())
            //->joinLeft(array('roi' => 'repo_osoba_imie'), 'roi.id = dro.version_id', array('osoba_imie' => 'roi.imie'))
            //->joinLeft(array('dro2' => 'documents_repo_objects'), 'dro2.document_id = d.id AND dro2.object_id = '. (int) $versionedObjects['osoba.nazwisko']['id'], array())
            //->joinLeft(array('roz' => 'repo_osoba_nazwisko'), 'roz.id = dro2.version_id', array('osoba_nazwisko' => 'roz.nazwisko'))
            //->joinLeft(array('dro3' => 'documents_repo_objects'), 'dro3.document_id = d.id AND dro3.object_id = '. (int) $versionedObjects['osoba.stanowisko']['id'], array())
            //->joinLeft(array('ros' => 'repo_osoba_stanowisko'), 'ros.id = dro3.version_id', array('osoba_stanowisko' => 'ros.stanowisko'))
            //->joinLeft(array('dro4' => 'documents_repo_objects'), 'dro4.document_id = d.id AND dro4.object_id = '. (int) $versionedObjects['osoba.login']['id'], array('droid' => 'dro4.id'))
            //->joinLeft(array('rol' => 'repo_osoba_login'), 'rol.id = dro4.version_id', array('osoba_login' => 'rol.login_do_systemu'))
            //->joinLeft(array('osb' => 'osoby'), 'osb.id = d.osoba_id', array('osoba_imie1' => 'osb.imie', 'osoba_stanowisko1' => 'osb.stanowisko', 'osoba_nazwisko1' => 'osb.nazwisko'))
            // ->joinInner(array('re' => 'registry_entries'), 're.worker_id = d.worker_id', array('re_id' => 're.id', 're.worker_id'))
            ->joinInner(array('re' => 'registry_entries'), 're.id = d.worker_id', array('re_id' => 're.id', 're.worker_id'))
            ->joinInner(array('reev' => 'registry_entries_entities_varchar'), 'reev.entry_id = re.id', array('worker_name' => 'GROUP_CONCAT(reev.value)'))
            ->where('d.active NOT IN (?)', $status_array)
            ->group(array('d.id'));
        if($conditions)

        if ($order) {
            $select->order($order);
        } else {
            $select->order('name ASC');
        }

        if ($limit) {
            $select->limit($limit);
        }

        $this->addConditions($select, $conditions);

        //vdie((string)$select);
        //echo $select->__toString(); exit;

        $results = $this->getListFromSelect($select, $conditions);

        $this->addMemoObjects($results);

        return $results;
    }

    /**
     * @return Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract|object
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->requestObject($data['id']);
            $row->setFromArray($data);
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }

    public function remove($id)
    {
        $row = $this->getOne(['d.id = ?', $id]);
        if (!($row instanceof Zend_Db_Table_Row)) {
            throw new Exception('Rekord nie istnieje lub zostal skasowany');
        }
        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
    }

    public function removeOnly($id)
    {
        $row = $this->requestObject($id);
        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
    }

    public function getLatestDocument($osobaId, $documenttemplateId)
    {
        return $this->fetchRow($this->select()
            ->where('osoba_id = ?', $osobaId)
            ->where('documenttemplate_id = ?', $documenttemplateId)
            ->where('active != ?', Application_Service_Documents::VERSION_ARCHIVE));
    }

    public function getLatestDocuments($workerId, $documenttemplateIds)
    {
        return $this->fetchAll($this->select()
            ->where('worker_id IN (?)', $workerId)
            ->where('documenttemplate_id IN (?)', $documenttemplateIds)
            ->where('active != ?', Application_Service_Documents::VERSION_ARCHIVE));
    }

    public function getActiveByUsers($osobyIds)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('id'))
            ->where('osoba_id IN (?)', $osobyIds)
            ->where('active != ?', Application_Service_Documents::VERSION_ARCHIVE);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllForTypeahead()
    {
        return $this->_db->select()
            ->from(['d' => $this->_name], ['id', 'name' => 'CONCAT_WS(\', \', d.numbertxt, CONCAT_WS(\' \', o.nazwisko, o.imie), dc.name)'])
            ->joinLeft(['o' => 'osoby'], 'd.osoba_id = o.id', [])
            ->joinLeft(['dc' => 'documenttemplates'], 'd.documenttemplate_id = dc.id', [])
            ->order('name ASC')
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resultsFilter(&$results) {
        foreach ($results as &$result) {
            $result['display_name'] = $result['numbertxt'];
        }
    }

    public function saveRegistryData($data)
    {
        if (empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->requestObject($data['id']);
            $row->setFromArray($data);
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }

    public function getLatestRegistryDocuments($workerId, $documenttemplateIds)
    {
        return $this->fetchAll($this->select()
            ->from($this->_name, array('id'))
            ->where('worker_id IN (?)', $workerId)
            ->where('documenttemplate_id IN (?)', $documenttemplateIds)
            ->order('id DESC')
            ->limit(1));
    }

    public function getNewContent($id)
    {
        return $this->fetchAll($this->select()
            ->from($this->_name, array('new_content'))
            ->where('id = ?', $id)
            ->order('id DESC'));
    }
    public function getRegisryEntryID($id)
    {
       $result = $this->_db->query(sprintf('SELECT `registry_entry_id` FROM `documents` where `id` = "'.$id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }
    public function getActiveDocumentByRegistryEntryId($registry_entry_id) 
    {
        return $this->fetchRow($this->select()
            ->where('active = ?', 1)
            ->where('registry_entry_id = ?', $registry_entry_id));
    }
    public function getActiveDocumentsByWorkerId($worker_id) 
    {
        return $this->fetchAll($this->select()
            ->where('active = ?', 1)
            ->where('worker_id = ?', $worker_id));
    }
    public function updateStatusArchiveByWorkerId($worker_id) {
        $this->_db->query(sprintf('UPDATE documents SET `active` = 0, `` WHERE `worker_id` = %d', $worker_id));
    }

    public function updateStatusArchiveAndarchivedDateByWorkerId($worker_id, $archivedDate)
    {
        $qur = "UPDATE `documents` SET `archived_at`='". $archivedDate ."', `active` = 0 WHERE `worker_id`='" .$worker_id."'";

        $this->_db->query(sprintf($qur));
    }
    public function getDocumentActiveByWorkerIdAndDocumentTemplateId($workerId, $documenttemplateId)
    {
        return $this->fetchRow($this->select()
            ->where('active = ?', 1)
            ->where('worker_id = ?', $workerId)
            ->where('documenttemplate_id = ?', $documenttemplateId)
        );
    }
    public function getDocumentByDocumentId($id) {
        return $this->fetchRow($this->select()
            ->where('id = ?', $id)
        );
    }
    public function getDocumentActiveByRegistryEntryId($registry_entry_id)
    {
        return $this->fetchRow($this->select()
            ->where('active = ?', 1)
            ->where('registry_entry_id = ?', $registry_entry_id));
    }
    public function updateActiveFieldByRegistryEntryID($id)
    {
        $this->_db->query(sprintf('UPDATE documents SET `active` = 2 WHERE `id` = %d', $id));
    }

    public function updateStatusArchive($id,$archivedDate) {
        $qur = "UPDATE `documents` SET `archived_at`='". $archivedDate ."', `active` = 0 WHERE `id`='" .$id."'";

        $this->_db->query(sprintf($qur));
        // $this->_db->query(sprintf('UPDATE documents SET `active` = 0 AND `archived_at` = %d WHERE `id` = %d', $archivedDate, $id));
    }
}
