<?php

class Application_Model_AdminLink extends Muzyka_DataModel
{
    private $id;
    private $osoby_login;
    private $superadmin_login;
    private $type;
    private $count_mini;
    private $count_pro;
    private $count_expert;

    protected $_name = "admin_link";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getByLogin($id)
    {
        $sql = $this->select()
            ->where('osoby_login = ?',$id);
        return $this->fetchRow($sql);
    }

    public function getAll()
    {
	
    }

    public function create($admin, $osoby, $type)
    {
        $sql = $this->select('type')
            ->where('osoby_login = ?', $osoby);
	
	$data = $this->fetchRow($sql);

	if(empty($data))
	{
            $row = $this->createRow();
            $row->osoby_login = $osoby;
	    $row->superadmin_login = $admin;
	    $row->type = $type;
            $this->addLog($this->_name, $row->toArray(), __METHOD__);
            $row->save();
	}else{
	    $qur = "UPDATE `admin_link` SET `type`='". $type ."' WHERE `osoby_login`='" .$osoby."'";
            $this->_db->query(sprintf($qur));
	}
	return;
    }

    public function update($data, $user_id)
    {
	
    }

    public function updateUser(){}

    public function getTypeByLogin($id)
    {
        $sql = $this->select('type')
            ->where('osoby_login = ?', $id);
	
        return $this->fetchRow($sql);
    }

    public function getAllAdmin()
    {
        $sql = $this->select('DISTINCT superadmin_login');
        $data = $this->fetchAll($sql)->toArray();
        $adminUser = array();

        foreach ($data as $d) {
            $adminUser[] = $d['superadmin_login'];
        }
        return $adminUser;
    }

    public function getAllByType($type)
    {
        $sql = $this->select()
            ->where('type = ?', $type);
        $data = $this->fetchAll($sql)->toArray();
    

        $subOsoby = array();

        foreach ($data as $d) {
            $subOsoby[] = $d['osoby_login'];
        }
        return $subOsoby;
    }
    // comagom code start
    public function getUsersCountByAdmin($admin_id) {
        $result = $this->_db->query(sprintf('SELECT `type`, count(`type`) as `count` FROM `admin_link` WHERE `superadmin_login` = %d group by `type`', $admin_id))->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    // comagom code end
    public function isEmployee($id, $login)
    {
        if(!Application_Service_Authorization::isSuperAdmin() && $login)
    	{
    	    $sql = $this->select()
                	->where('osoby_login = ?', $login)
    	    	->where('superadmin_login = ?', $id);

    	    $data = $this->fetchRow($sql);
    	    if(empty($data))
    	    {
    	    	return false;
    	    }
        }
	    return true;
    }

    public function getUsersByAdmin($admin, $type, $osoby_login='')
    {
	$sql = $this->select()
            ->where('type = ?', $type)
	    ->where('superadmin_login = ?', $admin);
        if($osoby_login)
        {
            $sql->where('type != ?', $osoby_login);
        }

	$data = $this->fetchAll($sql)->toArray();
	$subOsoby = array();

	foreach($data as $d)
	{
	    $subOsoby[] = $d['osoby_login'];
	}
	return $subOsoby;
    }
}
