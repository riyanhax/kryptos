<?php

class Application_Model_TypeRights extends Muzyka_DataModel
{
    private $id;
    private $type;
    private $rights;

    protected $_name = "type_rights";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getBytype($type)
    {
        $sql = $this->select()
                    ->where('type = ?',$type);
        $data = $this->fetchRow($sql);
	    return $data['rights'];
    }

    public function update($typ, $right)
    {
	    $qur = "UPDATE `type_rights` SET rights='". $right ."' WHERE type='". $typ ."'";
        $this->_db->query(sprintf($qur));
    }
}
