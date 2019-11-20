<?php

/**
 * @property Application_Model_RegistryEntriesEntitiesMatrixRelationItem[] $items
 */
class Application_Model_RegistryEntriesEntitiesMatrixRelation extends Application_Model_RegistryEntriesEntitiesRelation
{
    const RELATION_TYPE = 2;

    public $injections = [
        'items' => ['RegistryEntriesEntitiesMatrixRelationItem', 'id', 'getList', ['eevi.relation_id IN (?)' => null], 'relation_id', 'items', true],
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
        return Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixRelationItem');
    }

    /**
     * @inheritdoc
     */
    protected function parseEntryIds($value)
    {
        $values = parent::parseEntryIds($value);
        if (count($values) !== 2 || empty($values[0]) || empty($values[1])) {
            throw new Zend_Validate_Exception('Incorrect matrix entity value');
        }
        return $values;
    }
}
