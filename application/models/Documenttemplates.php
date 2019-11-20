<?php

class Application_Model_Documenttemplates extends Muzyka_DataModel
{
    protected $_name = 'documenttemplates';
    private $id;
    private $name;

    const TEMPLATE_DELETED = 3;

    /**
     * @inheritdoc
     */
    public function getOne($id)
    {
        $sql = $this->select()
            ->where('id = ?', $id);

        return $this->fetchRow($sql);
    }

    public function getRegistry($workerId, $documenttemplateIds)
    {
        return $this->fetchAll($this->select()
            ->from($this->_name, array('id'))
            ->where('worker_id IN (?)', $workerId)
            ->where('documenttemplate_id IN (?)', $documenttemplateIds)
            ->order('id DESC')
            ->limit(1));
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->select()
            ->order('name ASC')
            ->query()
            ->fetchAll();
    }

    /**
     * @param array $conditions
     * @return array
     */
    public function getAllForTypeahead($conditions = [])
    {
        $select = $this->_db->select()
            ->from(array('dt' => $this->_name), array('id', 'name'))
            ->order('name ASC');

        $this->addConditions($select, $conditions);

        return $select->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $data
     * @return mixed
     * @throws Application_SubscriptionOverLimitException
     * @throws Exception
     */
    public function save($data)
    {
        $logicDocuments = new Logic_Documents();
        /** @var object $row */
        if (!(int)$data['id']) {
            $row = $this->createRow();
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->getOne($data['id']);
            $row->updated_at = date('Y-m-d H:i:s');
        }
        
        $dataWorkers = $logicDocuments->getDocumentTemplateWorkersData($row->id);
        $skipWorkers = [];
        $hasChanged = false;
        
        if ($row->content !== $data['content']) {
            $hasChanged = true;
        }
        
        if (!$hasChanged) {
            foreach ($dataWorkers as $rowWorker) {
                $skipWorkers[] = $rowWorker->worker_id;
            }
        }
        
        $historyCompare = clone $row;

        $row->name = mb_strtoupper($data['name']);
        $row->type = $data['type'] * 1;
        $row->numberingscheme_id = $data['numberingscheme_id'] * 1;
        $row->content = $data['content'];
        $row->active = $data['active'] * 1;
        $row->icon = $data['icon'];
        $row->registry_id = (int) $data['registry_id'];
        $row->signature_required = (int) $data['signature_required'];
        $row->assign_new_workers = $data['assign_new_workers'];
        $id = $row->save();

        $this->getRepository()->eventObjectChange($row, $historyCompare);

        $documenttemplatesosoby = Application_Service_Utilities::getModel('Documenttemplatesosoby');
        $documenttemplatesosoby->delete(array('documenttemplate_id = ?' => $id));
        $t_options = json_decode($data['persons']);
        
        if (is_object($t_options->t_personsdata)) {
            foreach ($t_options->t_personsdata AS $option) {
                $iden = str_replace('id', '', $option);
                $t_data = array(
                    'documenttemplate_id' => $id,
                    'worker_id' => $iden,
                );
                $documenttemplatesosoby->insert($t_data);
            }
        }
        
        $tasksModel = Application_Service_Utilities::getModel('Tasks');
        /** @var object $task */
        $task = $tasksModel->getOne([
            'type = ?' => Application_Service_Tasks::TYPE_DOCUMENT,
            'object_id = ?' => $row->id,
        ]);

        Application_Service_Documents::getInstance()
            ->resetForTemplate($id, !empty($data['recreate_worker_documents']), [], $skipWorkers);

        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        return $id;


    }

    /**
     * @param $id
     * @throws Exception
     */
    public function remove($id)
    {
        $row = $this->getOne($id);
        if (!($row instanceof Zend_Db_Table_Row)) {
            throw new Exception('Rekord nie istnieje lub zostal skasowany');
        }

        Application_Service_Documents::getInstance()
            ->resetForTemplate($id);

        $history = clone $row;

        $this->update([
            'active' => Application_Model_Documenttemplates::TEMPLATE_DELETED,
        ], ['id = ?' => $id]);

        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        $this->getRepository()->eventObjectChange($this->createRow(), $history);
    }

    /**
     * @param $registryId
     * @return int
     */
    public function getDocumentTemplateId($registryId)
    {
        $id = 0;
        $sql = $this->select()
            ->where('registry_id = ?', $registryId);
        /** @var object $result */
        $result = $this->fetchRow($sql);
        if ($result) {
            $id = $result->id;
        }
        return $id;
    }
    public function isActive($templateId)
    {
        $active = 0;
        $sql = $this->select()
            ->where('id = ?', $templateId);
        /** @var object $result */
        $result = $this->fetchRow($sql);
        if ($result) {
            $active = $result->active;
        }
        return $active;
    }

    /**
     * @param $registryId
     * @return int
     */
    public function getActiveDocumentTemplateId($registryId)
    {
        $id = 0;
        $sql = $this->select()
            ->where('registry_id = ?', $registryId)
            ->where('active = ?', 1);
        /** @var object $result */
        $result = $this->fetchRow($sql);
        if ($result) {
            $id = $result->id;
        }
        return $id;
    }

    public function getActiveDocumentTemplateIdForNewAssigny()
    {
        $idsArr = array();
        $sql = $this->select()
                    ->where('active = ?',1)
                    ->where('assign_new_workers',1);
        $result = $this->fetchAll($sql);
        if($result) {
            foreach ($result as $value) {
                $idsArr[] = $value->id;
            }
        }
        return $idsArr;
    }
}
