<?php

class Application_Model_NotificationsControl extends Muzyka_DataModel
{
    private $id;
    private $user_id;
    private $task_email;
    private $task_sms;
    private $activity_email;
    private $activity_sms;
    private $tickets_email;
    private $tickets_sms;
    private $web_push;
    private $last_web_push;

    const TYPE_TASK = 'task';
    const TYPE_ACTIVITY = 'activity';
    const TYPE_TICKET = 'ticket';

    protected $_name = "notification_control";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
    
    public function getAllById($id)
    {
        $sql = $this->select()
            ->where('user_id = ?',$id);
        return $this->fetchRow($sql);
    }

    public function create($id)
    {
        $row = $this->createRow();
        $row->user_id = $id;
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        
        $row->save();
    }

    public function update($data, $user_id)
    {
	    $qur = "UPDATE `notification_control` SET task_email=". $data['task_email'] .", task_sms=". $data['task_sms'] .", activity_email=". 		$data['activity_email'] .", activity_sms=". $data['activity_sms'] .", tickets_email=". $data['tickets_email'] .", tickets_sms=". 		$data['tickets_sms'] ." WHERE user_id=" .$user_id;
        $this->_db->query(sprintf($qur));
    }

    public function updateLast($last, $user_id){
	    $qur = "UPDATE `notification_control` SET last_web_push=".$last." WHERE user_id=" .$user_id;
	    $this->_db->query(sprintf($qur));
    }
}
