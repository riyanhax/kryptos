<?php

class Application_Model_TypeRightsPermissions extends Muzyka_DataModel
{
    private $id;
    private $type;
    private $rights;

    protected $_name = "type_rights_permissions";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getBytype($type)
    {
        $sql = $this->select()
            ->where('type = ?',$type);
        $data = $this->fetchRow($sql);
	return $data['permissions'];
    }

    public function update($typ, $right)
    {
	$qur = "UPDATE `type_rights_permissions` SET permissions='". $right ."' WHERE type='". $typ ."'";
        $this->_db->query(sprintf($qur));
    }
}
