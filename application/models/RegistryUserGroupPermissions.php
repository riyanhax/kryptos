<?php

class Application_Model_RegistryUserGroupPermissions extends Muzyka_DataModel
{
    protected $_name = "registry_user_group_permissions";
    protected $_base_name = 'rugp';
    protected $_base_order = 'rugp.id ASC';

    public $id;
    public $registry_id;
    public $user_id;
    public $registry_permission_id;
    public $created_at;
    public $updated_at;

    public $injections = [
        'user' => ['Osoby', 'user_id', 'getList', ['o.id IN (?)' => null], 'id', 'user', false],
        'registry' => ['Registry', 'registry_id', 'getList', ['r.id IN (?)' => null], 'id', 'registry', false],
        'permission' => ['RegistryPermissions', 'registry_permission_id', 'getList', ['rp.id IN (?)' => null], 'id', 'permission', false],
    ];

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

    public function resultsFilter(&$results)
    {
    }
}
