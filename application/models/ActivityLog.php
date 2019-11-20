<?php

class Application_Model_ActivityLog extends Muzyka_DataModel
{
    protected $_name = "activity_log";
    protected $_base_name = 'actl';

    public function save($data)
    {
            $row = $this->createRow();
            print_r($data);
            //die;

            $row->act_id = $data['parameter']['actid'];
            $row->registryid= $data['parameter']['registry_id'];
            $row->value = json_encode($data['regselect']);
            $row->created_date = date('Y-m-d H:i:s');

            $id = $row->save();
            return $id;
    }

    public function getRegistryEntitiesByName($registry_name , $config_name)
    {
        $arr = array();
        $qur = "select VALUE FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE system_name='".$config_name."' AND registry_id in (SELECT `id` FROM `registry` WHERE title='".$registry_name."'))";
        //echo $qur;
         $result = $this->_db->query($qur)->fetchAll(PDO::FETCH_ASSOC);
          $i=0;
        foreach ($result as $data) {
                $arr[$i]["id"]=$arr[$i]["text"] = $data['VALUE'];
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
}