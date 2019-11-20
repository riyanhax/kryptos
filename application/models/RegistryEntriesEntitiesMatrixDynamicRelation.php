<?php

class Application_Model_RegistryEntriesEntitiesMatrixDynamicRelation extends Application_Model_RegistryEntriesEntitiesMatrixRelation
{
    protected function parseEntryIds($value)
    {
        return parent::parseEntryIds(array_values($value));
    }

    public function getList($conditions = array(), $limit = null, $order = null)
    {
        $select = $this->getBaseQuery($conditions, $limit, $order);
        if (!empty($conditions['entry_id'])) {
            $select->joinInner(['eevi' => 'registry_entries_entities_relation_items'], 'eev.id = eevi.relation_id', []);
        }
        return $this->getListFromSelect($select, $conditions, $limit, $order);
    }

    /**
     * @inheritdoc
     */
    protected function deleteOldValues($entryId, $registry_entity_id, $newIds)
    {
        if (!$entryId) {
            return;
        }
        foreach ($this->getList(['entry_id'=>$entryId, 'registry_entity_id'=>$registry_entity_id]) as $oldValue) {
            if (!in_array($oldValue->id, $newIds)) {
                $oldValue->delete();
            }
        }
    }
}
