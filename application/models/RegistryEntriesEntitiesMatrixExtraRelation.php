<?php

/**
 * @property Application_Model_RegistryEntriesEntitiesMatrixExtraRelationItem[] $items
 */
class Application_Model_RegistryEntriesEntitiesMatrixExtraRelation extends Application_Model_RegistryEntriesEntitiesRelation
{
    const RELATION_TYPE = 4;

    public $injections = [
        'items' => ['RegistryEntriesEntitiesMatrixExtraRelationItem', 'id', 'getList', ['eevi.relation_id IN (?)' => null], 'relation_id', 'items', true],
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
        return Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixExtraRelationItem');
    }

    /**
     * @inheritdoc
     */
    protected function parseEntryIds($value)
    {
        $values = array_filter(explode('-', $value, 3));
        if (!is_array($values) || count($values) !== 3 || empty($values[0]) || empty($values[1]) || empty($values[2])) {
            throw new Zend_Validate_Exception('Incorrect matrix entity value');
        }
        return $values;
    }
}
