<?php

class Application_Model_Activity extends Muzyka_DataModel
{
    protected $_name = "acitivity_details";
    protected $_base_name = 'act';

    public function save($data)
    {
            if(isset($data['activityid']))
            {
                $row = $this->getFull([
                      'id' => $data['activityid']
                    ], true);
            }
            else
            {
               $row = $this->createRow();
            }

            $row->activity_type_id = $data['options'];
            $row->user_id= $data['user'];
            $row->status = $data['done'];
            $row->subject = $data['activity_name'];
            $row->notes = $data['notes'];
            $row->deal = $data['linked'];
            $row->contact_person = $data['people'];
            $row->email = '';
            $row->phone = '';
            $row->assigned_user_id = $data['user'];
            $row->organization = $data['organization'];
            $row->due_date = $data['date'];
            $row->time = $data['time'];
            $row->created_date = date('Y-m-d H:i:s');
            $row->duration = $data['duration'];

            $id = $row->save();
            return $id;
    }

    public function getRegistryEntitiesById($registryId , $config_name)
    {
        $arr = array();
        $qur = "select VALUE FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE system_name='".$config_name."' AND registry_id=".$registryId.")";
        //echo $qur;
         $result = $this->_db->query($qur)->fetchAll(PDO::FETCH_ASSOC);
          $i=0;
        foreach ($result as $data) {
                $arr[$i]["value"]=$arr[$i]["text"] = $data['VALUE'];
                $i++;
        }
        return $arr;
    }

    public function getRegistryIdByName($name)
    {
          $qur = "SELECT `id` FROM `registry` WHERE `title` = " . $name;
    
         $result = $this->_db->query($qur)->fetchAll(PDO::FETCH_ASSOC);
         return $result[0]['id'];
    }

    public function getActivity($name)
    {
         $act = array();
         $icon = array();


     $actname = $this->_db->query(sprintf("select value FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE title='Name' and registry_id in (SELECT `id` FROM `registry` WHERE title = '$name'))"))->fetchAll(PDO::FETCH_ASSOC);
         foreach ($actname as $data)
          {
                array_push($act, $data['value']);
          }

     $iconinfo = $this->_db->query(sprintf("select value FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE title='icon' and registry_id in (SELECT `id` FROM `registry` WHERE title = '$name'))"))->fetchAll(PDO::FETCH_ASSOC);
        foreach ($iconinfo as $data)
        {
                array_push($icon, $data['value']);
        }

        $activity = array_combine($act, $icon);
        return $activity;
    }

    public function update($data)
    {
      $qur = "UPDATE `acitivity_details` SET `". $data['name'] ."`='". $data['value'] ."' WHERE `id`=" .$data['pk'];
        $this->_db->query(sprintf($qur));
    }

    public function getOsobyData()
    {
       $arr = array();
        $qur = "SELECT `nazwisko` FROM `osoby`";
        //echo $qur;
         $result = $this->_db->query($qur)->fetchAll(PDO::FETCH_ASSOC);
          $i=0;
        foreach ($result as $data) {
                $arr[$i]["value"]=$arr[$i]["text"] = $data['nazwisko'];
                $i++;
        }
        return $arr;
    }

    public function getActlistData($name)
    {
        $arr = array();
          $result = $this->_db->query(sprintf("select VALUE FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE system_name='name' AND registry_id in (SELECT `id` FROM `registry` WHERE title='".$name."'))"))->fetchAll(PDO::FETCH_ASSOC);
          $i=0;
        foreach ($result as $data) {
                $arr[$i]["value"]=$arr[$i]["text"] = $data['VALUE'];
                $i++;
        }
     return $arr;
    }

	public function remove($id)
    {
        $row = $this->validateExists($this->getOne($id));
        $history = clone $row;

        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        $this->getRepository()->eventObjectRemove($history);
    }
}
