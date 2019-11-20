<?php

class Application_Model_GoogleEvents extends Muzyka_DataModel
{
    private $id;
    private $user_id;
    private $summary;
    private $description;
    private $start_time;
    private $end_time;
    private $attendees;
    private $status;

    protected $_name = "google_events";
    protected $_base_name = 'o';
    protected $_base_order = 'o.id DESC';
  //need to review  
    public function getAllById($id)
    {
        $sql = $this->select()
            ->where('user_id = ? AND status = 0',$id);

        $data = $this->fetchAll($sql);
	return $data->toArray();
    }

    public function save($data)
    {
        $row = $this->createRow();
        $row->user_id = $data['user_id'];
        $row->start_time = $data['sdate'];
        $row->end_time = $data['edate'];
        $row->summary = $data['summary'];
        $row->description = $data['description'];
        $row->attendees = $data['attendees'];
        $row->status = 0;
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        
        $row->save();
    }

    public function markAsDone($id)
    {
	$qur = "UPDATE `google_events` SET status = 1 WHERE id=" .$id;
        $this->_db->query(sprintf($qur));
	return true;
    }

    public function getNewEntries($id)
    {
	$sql = $this->select()
            ->where('id >= ?',$id);

        $data = $this->fetchAll($sql);
	return $data->toArray();
    }
}
