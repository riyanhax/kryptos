<?php

class Application_Model_RegistryEntriesEntitiesRelationItem extends Muzyka_DataModel
{
    protected $_name = "registry_entries_entities_relation_items";
    protected $_base_name = 'eevi';
    protected $_base_order = 'eevi.id ASC';

    public $injections = [
        'relation' => ['RegistryEntriesEntitiesMatrixRelation', 'entry_id', 'getList', ['eev.id IN (?)' => null], 'id', 'relation', false],
    ];
}
