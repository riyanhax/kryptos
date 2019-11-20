<?php

class Application_Model_Trigger extends Muzyka_DataModel
{
    /*CREATE TABLE `mateuszz`.`trigger` ( `id` INT NOT NULL AUTO_INCREMENT COMMENT 'pk' , `registry_id` INT(11) NOT NULL COMMENT 'Registry ID' ,`trigger_name` VARCHAR(100) NULL , `cond` INT NOT NULL , `disable` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;*/

	protected $_name = "registry_trigger";
    protected $_base_name = 'reg_tri';

    public function save($data)
    {
            $arr = $data['parameter'];
    	    $row = $this->createRow();
            $row->id = '';
            $row->registry_id = $arr['registry_id'];
            $row->trigger_name = $arr['trigger_name'];
            $row->cond = $arr['entity_id'];
            $row->disable = json_encode($data['todisable']);

            $id = $row->save();
            return $row;
   }

   public function getDisableEntities($registryId)
   {
         $arr = array();
        $outputArray= array();
          $result = $this->_db->query(sprintf("SELECT `disable` FROM `registry_trigger` WHERE `registry_id`=".$registryId))->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $data) {
                array_push($arr, $data['disable']);    
        }
        foreach($arr as $ary => $value){
            foreach (json_decode($value) as $pasta) {
            array_push($outputArray, $pasta);
            }
        }
       //print_r(array_unique($outputArray));
       return array_unique($outputArray);
   }
   
   public function getAllTrigger($registryId)
   {
        $outputArray= $loop = array();
        $result = $this->_db->query(sprintf("SELECT * FROM `registry_trigger` WHERE `registry_id`=".$registryId))->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $res)
        {
            $outputArray['id'] = $res[id];
            $outputArray['cond'] = $this->getEntityName($res['cond']);
            $outputArray['trigger_name'] = $res['trigger_name'];
            $dis = array();
            foreach(json_decode($res['disable']) as $des)
            {
                 $dis[] = $this->getEntityName($des);
            }
            
             $outputArray['disable'] = $dis;
             array_push($loop, $outputArray);
        }
        return $loop;

   }
   
   public function getEntityName($id)
   {
      $query = "SELECT `title` FROM `entities` WHERE `id`=".$id;
      $result = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC); 
      return $result[0]['title'];
   }
}
