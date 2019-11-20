<?php

/**
 * @property Application_Model_RegistryEntriesEntitiesSingleRelationItem[] $items
 */
class Application_Model_RegistryEntriesEntitiesSingleRelation extends Application_Model_RegistryEntriesEntitiesRelation
{
    const RELATION_TYPE = 1;

    public $injections = [
        'items' => ['RegistryEntriesEntitiesSingleRelationItem', 'id', 'getList', ['eevi.relation_id IN (?)' => null], 'relation_id', 'items', true],
    ];

    /**
     * @inheritdoc
     */
    public function getList($conditions = [], $limit = null, $order = null)
    {
        $conditions['relation_type'] = self::RELATION_TYPE;
        return parent::getList($conditions, $limit, $order);
    }

    public function save($data)
    {
        $data['relation_type'] = self::RELATION_TYPE;
        return parent::save($data);
    }

    /**
     * @inheritdoc
     */
    protected function getItemsRepository()
    {
        return Application_Service_Utilities::getModel('RegistryEntriesEntitiesSingleRelationItem');
    }

    /**
     * @inheritdoc
     */
    protected function parseEntryIds($value)
    {
        $values = parent::parseEntryIds($value);
        if (count($value) !== 1 || empty($value[0])) {
            throw new Zend_Validate_Exception('Incorrect select entity value');
        }
        return $values;
    }
}
