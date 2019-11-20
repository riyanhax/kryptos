<?php

class Application_Model_DocumentsPending extends Muzyka_DataModel
{
    protected $_name = "documents_pending";
    protected $_base_name = 'dp';
    protected $_base_order = 'dp.created_at ASC';

    const STATUS_REMOVED = 0;
    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_CREATED = 3;

    public $id;
    public $user_id;
    public $documenttemplate_id;
    public $document_id;
    public $registry_entry_id;
    public $status;
    public $created_at;
    public $updated_at;

    public function getBaseQuery($conditions = [], $limit = null, $order = null)
    {
        $select = $this->getSelect('dp')
            //->where('re.worker_id !=0')
            //->joinInner(['o' => 'osoby'], 'dp.user_id = o.id', ['imie', 'nazwisko', 'stanowisko'])
            ->joinInner(['dt' => 'documenttemplates'], 'dt.id = dp.documenttemplate_id', ['template_name' => 'dt.name', 'template_type' => 'dt.type'])
            ->joinLeft(['re' => 'registry_entries'], 're.worker_id = dp.worker_id AND re.registry_id=dt.registry_id', ['re_id' => 're.id'])
            ->joinLeft(['reev' => 'registry_entries_entities_varchar'], 'reev.entry_id = re.id', ['worker_name' => 'reev.value']);

        $this->addBase($select, $conditions, $limit, $order);

        return $select;
    }

    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
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
        $row = $this->requestObject($id);

        $row->status = self::STATUS_REMOVED;
        $row->updated_at = date('Y-m-d H:i:s');
        $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);
    }

    public function disable($id)
    {
        $row = $this->getOne($id);
        if ($row instanceof Zend_Db_Table_Row) {
            $row->enabled = false;
            $row->save();
        }
    }

    public function enable($id)
    {
        $row = $this->getOne($id);
        if ($row instanceof Zend_Db_Table_Row) {
            $row->enabled = true;
            $row->save();
        }
    }

    public function getActiveByUsers($osobyIds)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('id'))
            ->where('user_id IN (?)', $osobyIds)
            ->where('status != ?', [Application_Model_DocumentsPending::STATUS_PENDING, Application_Model_DocumentsPending::STATUS_ACCEPTED]);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPendingDocumentId($workerId, $documentTemplateId)
    {
        $pendingDocumentId = 0;

        $sql = $this->select()
            ->from($this->_name, array('id'))
            ->where('worker_id = ?', $workerId)
            ->where('documenttemplate_id = ?', $documentTemplateId)
            ->where('status IN (?)', [Application_Model_DocumentsPending::STATUS_PENDING, Application_Model_DocumentsPending::STATUS_ACCEPTED]);

        $rows = $this->fetchAll($sql);

        if (count($rows) > 0) {
            $pendingDocumentId = $rows[0]->id;
        }
        return $pendingDocumentId;
    }

    public function getLatestPendingDocument($workerId, $documentTemplateId)
    {
        $sql = $this->select()
            ->from($this->_name, array('id', 'status'))
            ->where('worker_id = ?', $workerId)
            ->where('documenttemplate_id = ?', $documentTemplateId)
            ->where('status != ?', Application_Model_DocumentsPending::STATUS_REMOVED)
            ->order('id DESC')
            ->limit(1);

        $rows = $this->fetchAll($sql);

        if (count($rows) > 0) {
            return $rows;
        } else {
            return array();
        }
    }
    
    /* Ankit code changes to get document Pending List */
    public function getPendingDocumentList($conditions = array()) {    
        $status_array = [Application_Model_DocumentsPending::STATUS_ACCEPTED, Application_Model_DocumentsPending::STATUS_PENDING];
        $select = $this->_db->select()
            ->from(array('dp' => $this->_name), array('*'))
            ->joinInner(array('dt' => 'documenttemplates'), 'dt.id = dp.documenttemplate_id', array('template_name' => 'dt.name', 'template_type' => 'dt.type'))
            ->joinInner(array('re' => 'registry_entries'), 're.id = dp.worker_id', array('re_id' => 're.id'))
            ->joinInner(array('reev' => 'registry_entries_entities_varchar'), 'reev.entry_id = re.id', array('worker_name' => 'GROUP_CONCAT(reev.value)'))
            ->where('dp.status IN (?)', $status_array)
            ->group(array('dp.id'));    
        
        $select->order('dp.id DESC');
        //echo $select->__toString();die;
        $results = $this->getListFromSelect($select);
        return $results;
    }
    /* Ankit code changes to get document Pending List */

    public function getAllActivePendingDocumentIds($workerId = [], $documentTemplateIds = [])
    {   
        // Returns only document Ids 
        $sql = $this->select()
            ->from($this->_name, array('id'))
            ->where('status != ?', Application_Model_DocumentsPending::STATUS_PENDING);
        if(count($documentTemplateIds)){
            $sql->where('documenttemplate_id IN (?)', $documentTemplateIds);
        }
        if(count($workerId)){
            $sql->where('worker_id IN (?)', $workerId);
        }
        $rows = $this->fetchAll($sql);

        if (count($rows) > 0) {
            return $rows;
        } else {
            return array();
        }
    }
}
