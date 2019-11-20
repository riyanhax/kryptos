<?php

/**
 * @method Application_Service_RegistryEntityRow|$this|object createRow(array $data = array())
 * @method Application_Service_RegistryEntityRow|$this|object requestObject(array $data = array())
 * @property Application_Model_RegistryEntriesEntitiesRelationItem[] $items
 */
abstract class Application_Model_RegistryEntriesEntitiesRelation extends Muzyka_DataModel
{
    protected $_name = "registry_entries_entities_relations";
    protected $_base_name = 'eev';
    protected $_base_order = 'eev.id ASC';
    public $_rowClass = 'Application_Service_RegistryEntityRow';

    public $injections = [
        'items' => ['RegistryEntriesEntitiesRelationItem', 'id', 'getList', ['eevi.relation_id IN (?)' => null], 'relation_id', 'items', true],
    ];

    public $autoloadInjections = ['items'];

    public function getList($conditions = array(), $limit = null, $order = null)
    {
        //unset($conditions['registry_entity_id']);
        $select = $this->getBaseQuery($conditions, $limit, $order);
        if (!empty($conditions['entry_id'])) {
            $select->joinInner(['eevi' => 'registry_entries_entities_relation_items'], 'eev.id = eevi.relation_id', []);
        }
        return $this->getListFromSelect($select, $conditions, $limit, $order);
    }

    /**
     * @param array $data
     * @return Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract|object
     * @throws Application_SubscriptionOverLimitException
     * @throws Exception
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = $this->getUpdateTime();
        } else {
            $row = $this->requestObject($data['id']);
            $row->loadData('items');
            foreach ($row->items as $item) {
                $item->delete();
            }
            $row->setFromArray($data);
            $row->updated_at = $this->getUpdateTime();
        }
        $row->save();
        foreach ((array)$data['values'] as $entry_id) {
            $this->getItemsRepository()
                ->createRow([
                    'relation_id' => $row->id,
                    'entry_id' => $entry_id,
                ])->save();
        }
        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }

    /**
     * @param array $uniqueIndex
     * @param array|string $values
     * @throws Application_SubscriptionOverLimitException
     */
    public function replaceEntries($uniqueIndex, $values){
        $newIds = array(-1);
        $entryId = $uniqueIndex['entry_id'];
        unset($uniqueIndex['entry_id']);
        foreach((array)$values as $value) {
            try {
                $uniqueIndex['values'] = $this->parseEntryIds($value);
                $uniqueIndex['values'] []= $entryId;
                $uniqueIndex['id'] = $this->detectEntityId(
                    $uniqueIndex['registry_entity_id'],
                    $uniqueIndex['values']
                );
            } catch (Zend_Validate_Exception $e) {
                $this->addLog($this->_name, $e->getMessage(), __METHOD__);
                continue;
            }
            $newIds []= $relationId = $this->save($uniqueIndex)->id;
        }
        try {
            $this->deleteOldValues($entryId, $uniqueIndex['registry_entity_id'], $newIds);
        } catch (Exception $e) {
            $this->addLog($this->_name, $e->getMessage(), __METHOD__);
        }
    }

    /**
     * @param $entityId
     * @return array
     * @throws Exception
     */
    protected function getAllowedValueIds($entityId)
    {
        /** @var Application_Model_RegistryEntities $repository */
        $repository = Application_Service_Utilities::getModel('RegistryEntities');
        $entryListService = new Application_Service_RegistryEntries();
        /** @var object $matrixConfig */
        $matrixConfig =  $repository->getById($entityId)->config_data;
        $list = $entryListService->getAllEntities(array($matrixConfig->registry_id, $matrixConfig->registry2_id));
        $registry1List = isset($list[$matrixConfig->registry_id]) ? array_keys($list[$matrixConfig->registry_id]['values']) : array();
        $registry2List = isset($list[$matrixConfig->registry2_id]) ? array_keys($list[$matrixConfig->registry2_id]['values']) : array();
        return array_merge($registry1List, $registry2List);
    }

    /**
     * @param $entryId
     * @param $registry_entity_id
     * @param $newIds
     * @throws Zend_Db_Table_Row_Exception
     */
    protected function deleteOldValues($entryId, $registry_entity_id, $newIds)
    {
        if (!$entryId) {
            return;
        }
        foreach ($this->getList(['entry_id'=>$entryId,'registry_entity_id' => $registry_entity_id]) as $oldValue) {
            if (!in_array($oldValue->id, $newIds)) {
                $oldValue->delete();
            }
        }
    }


    /**
     * @param string|array $value
     * @return array
     * @throws Zend_Validate_Exception
     */
    protected function parseEntryIds($value)
    {
        if (is_string($value)) {
            $value = array_filter(explode('-', $value, 2));
        }
        if (!is_array($value)) {
            throw new Zend_Validate_Exception('Incorrect entity value');
        }
        return $value;
    }

    protected  function getUpdateTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @param int $registryEntityId
     * @param int[] $entryIds
     * @return int|null
     */
    protected function detectEntityId($registryEntityId, $entryIds)
    {
        $select = $this->getBaseQuery(['registry_entity_id' => $registryEntityId]);
        foreach ($entryIds as $i=>$entryId) {
            $select->joinInner(['eevi_'.$i => 'registry_entries_entities_relation_items'], 'eev.id = eevi_'.$i.'.relation_id', []);
            $select->where('eevi_'.$i.'.entry_id = ?', $entryId);
        }
        return $select->query()->fetchColumn();
    }

    /**
     * @param int $valueId
     * @param int  $value2Id
     * @return int|null
     */
    protected function getIdByValues($valueId, $value2Id)
    {
        return $this->getBaseQuery()
            ->where('value_id = ?', $valueId)
            ->where('value2_id = ?', $value2Id)
            ->query()
            ->fetchColumn();
    }

    /**
     * @return Application_Model_RegistryEntriesEntitiesRelationItem|Muzyka_DataModel
     * @throws Exception
     */
    protected function getItemsRepository()
    {
        return Application_Service_Utilities::getModel('RegistryEntriesEntitiesRelationItem');
    }
}
