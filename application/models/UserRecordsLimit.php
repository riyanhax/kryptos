<?php

class Application_Model_UserRecordsLimit extends Muzyka_DataModel
{
    private $id;
    private $count_mini;
    private $count_pro;
    private $count_expert;

    protected $_name = "user_records_limit";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getLimitByType($type)
    {
        $sql = $this->select()
            ->where('type = ?', $type);
        return $this->fetchRow($sql);
    }
    // public function update($type, $limit)
    // {
    //     $sql = $this->select('1')
    //         ->where('type = ?', $type);
    
    //     $type_exist = $this->fetchRow($sql);
    //     if($type_exist) {        
    //         $qur = "UPDATE `user_records_limit` SET `count`='". $limit ."' WHERE `type`='" .$type."'";
    //         $this->_db->query(sprintf($qur));
    //     }
    // }

    // comagom code start 2019.3.21
    public function updateLimitInfoByType($type,$limit)
    {
        $sql = $this->select('1')
            ->where('type = ?', $type);
    
        $type_exist = $this->fetchRow($sql);
        if($type_exist) {        
            $qur = "UPDATE `user_records_limit` SET `limit_info`='". $limit ."' WHERE `type`='" .$type."'";
            $this->_db->query(sprintf($qur));
        }
    }
    // comagom code end 2019.3.21
}
