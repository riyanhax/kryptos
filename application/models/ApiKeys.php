<?php

class Application_Model_ApiKeys extends Muzyka_DataModel
{
    private $id;
    private $name;
    private $value;
    private $additional;

    protected $_name = "api_keys";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getAllByName()
    {
        $data = $this->fetchAll($this->select());
	return $data->toArray();
    }

    public function getAllByType($type)
    {
	    $sql = $this->select()
            ->where('name = ?',$type);            
        return $this->fetchRow($sql);
    }


    public function update($data)
    {
	$qur = "UPDATE `api_keys` SET Value='". $data['sms_value'] ."', additional='". $data['sms_from'] ."' WHERE name='smsapi'";
        $this->_db->query(sprintf($qur));
	$qur = "UPDATE `api_keys` SET Value='". $data['mail_value'] ."', additional='". $data['mail_from'] ."' WHERE name='email'";
        $this->_db->query(sprintf($qur));

	return true;
    }
}
