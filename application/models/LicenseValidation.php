<?php

class Application_Model_LicenseValidation extends Muzyka_DataModel
{
    private $id;
    private $osoby_id;
    private $license_type;
    private $date_of_expiry;
    private $pro;
    private $mini;

    protected $_name = "license_validation";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function save($osoby)
    {
        $row = $this->createRow();
        $row->osoby_id = $osoby;
	$row->license_type = 'trial';
	$row->date_of_expiry = date('Y-m-d', strtotime("+14 days"));
	$row->pro = 1;
	$row->mini = 1;
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        $row->save();
	return;
    }

    public function update($data, $user_id)
    {
	
    }

    public function isValidated($id)
    {
	$sql = $this->select()
            ->where('osoby_id = ?',$id);
        $data = $this->fetchRow($sql);

	if(!empty($data))
	{
	    if(strtotime($data['date_of_expiry']) >= strtotime(date("Y-m-d")))
	    {
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}
	else
	{
	    return true;
	}
    }

    public function countUserTypeById($id, $type)
    {
	$sql = $this->select($type)
            ->where('osoby_id = ?',$id);
        return $this->fetchAll($sql)->count();
    }
}
