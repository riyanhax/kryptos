<?php

class Application_Model_RegistryAssignees extends Muzyka_DataModel
{
    protected $_name = "registry_assignees";
    protected $_base_name = 'ra';
    protected $_base_order = 'ra.id ASC';

    public $id;
    public $registry_id;
    public $user_id;
    public $user_permissions_id;
    public $registry_role_id;
    public $created_at;
    public $updated_at;

    public $injections = [
        'user' => ['Osoby', 'user_id', 'getList', ['o.id IN (?)' => null], 'id', 'user', false],
        'registry' => ['Registry', 'registry_id', 'getList', ['r.id IN (?)' => null], 'id', 'registry', false],
        'role' => ['RegistryRoles', 'registry_role_id', 'getList', ['rr.id IN (?)' => null], 'id', 'role', false],
    ];

    /**
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            unset($data['id']);
            $row = $this->createRow($data);
            $row->created_at = date('Y-m-d H:i:s');

        } else {
            $row = $this->requestObject($data['id']);
            $row->setFromArray($data);
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $row;
    }

    public function resultsFilter(&$results)
    {
    }

    public function getRegistryAssignees($registry_id)
    {
       $result = $this->_db->query(sprintf('SELECT * FROM `registry_assignees` where `registry_id` = "'.$registry_id.'"'))->fetchAll(PDO::FETCH_ASSOC);
       return $result;
    }

    public function getDataByUserID($user_id){

        $assigneesrows = $this->select()->where('user_id = ?', $user_id)->where('registry_id != ?', '0')->query()->fetchAll();
        if (count($assigneesrows) > 0) {
            $items = [];
            foreach ($assigneesrows as $assigneesrow) {
                $row = $this->getone($assigneesrow['id']);

                $item['registry_id'] = $row->registry_id;

                $permArr = explode('/', $row->user_permissions_id);

                $perm = null;
                foreach($permArr as $key=>$val){
                    $perm[$val] = 1;
                }
                $item['perms'] = $perm;
                array_push($items, $item);
            }
        }

        return $items;

    }
    public function saveRowWithUserPermissionID($user_id, $permissions)
    {
        
        $assigneesrows = $this->select()->where('user_id = ?', $user_id)->where('registry_id != ?', '0')->query()->fetchAll();


        if (count($assigneesrows) > 0) {
            foreach ($assigneesrows as $assigneesrow) {
                // $sql = $this->select()
                //     ->where('id = ?', $assigneesrow['id']);

                $row = $this->getone($assigneesrow['id']);
                $row->registry_id = '0';
                $row->user_permissions_id = '';
                $row->save();

                //var_dump($row);
                //echo '<br>______<br>';
            }
        }
        // var_dump($assigneesrows);
        // exit;

        foreach ($permissions as $key => $permission) {

            if ($permission == 1) {
                $dataArr = explode('/', $key);

                if ($dataArr[2]) {
                    // echo '<br>______<br>';
                    // echo $key;
                    // echo '<br>______<br>';
                    // var_dump($dataArr);
                    // echo '<br>______<br>';

                    $sql = $this->select()
                        ->where('user_id = ?', $user_id)->where('registry_id = ?', $dataArr[1]);
                    $row = $this->fetchRow($sql);

                    if(empty($row)){
                        $row = $this->createRow();
                        $row->registry_id = $dataArr[1];
                        $row->user_id = $user_id;
                        $row->user_permissions_id = $dataArr[2];
                    }
                    else{
                        $perms = $row->user_permissions_id;
                        $row->user_permissions_id = $perms . "/" . $dataArr[2];
                    }
                    $row->save();

                }
            }

        }
    }
}
