<?php

class Application_Model_RegistryEntriesEntitiesMatrixExtraRelationItem extends Application_Model_RegistryEntriesEntitiesRelationItem
{
    public $injections = [
        'relation' => ['RegistryEntriesEntitiesMatrixExtraRelation', 'entry_id', 'getList', ['eev.id IN (?)' => null], 'id', 'relation', false],
    ];
}
