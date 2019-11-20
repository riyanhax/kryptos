<?php

/**
 * Created by PhpStorm.
 * User: storm
 * Date: 3/15/2018
 * Time: 1:14 PM
 */
class Application_Model_RegistryMenu extends Muzyka_DataModel
{
    protected $_name = "registery_menu";

    public $id;
    public $registery_id;
    public $label;
    public $path;
    public $icon;
    public $rel;
    public $parent_id;
    public $activate;

    public function getMenuList()
    {
        $where = $this->getAdapter()->quoteInto('activate = ?', 0);
        $list_data = $this->fetchAll($where);
        return $list_data;
    }

    public function getParentIdById($menuId)
    {
        $menuModel = $this->getAdapter()
            ->select()
            ->from($this->_name)
            ->where('id=?', (int)$menuId)
            ->query()
            ->fetch();

        if ($menuModel) {
            $parent_id = null;
            if ($menuModel['parent_id'] == null) {
                $parent_id = $menuModel['id'];
            } else {
                $parent_id = $menuModel['parent_id'];
            }
            return $parent_id;
        } else {
            return null;
        }
    }

    public function addNewRow($data)
    {
        $row = $this->createRow($data);
        $row->save();
        return $row;
    }

    public function updateRowData($data)
    {
        $id = $data['id'];
        $row = $this->validateExists($this->findOne($id));

        $row->label = $data['label'];
        $row->path = $data['path'];
        $row->icon = $data['icon'];
        $row->rel = $data['rel'];
        $row->parent_id = $data['parent_id'];
//        $row->activate_routes = $data['activate_routes'];
        $row->save();
        return $row;
    }

    public function updateParentData($parent_id, $id)
    {
        $row = $this->validateExists($this->findOne($id));

        $row->parent_id = $parent_id;
        $row->save();
        return $row;
    }

    public function deleteRow($path)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('path = ?', $path);
        $this->delete($where);
    }

    public function removeData($data, $option)
    {
        if ($option == 'parentchild') {
            foreach ($data as $item) {
                $id = $item['id'];
                $where = array();
                $where[] = $this->getAdapter()->quoteInto('id = ?', $id);
                $this->delete($where);
                $wheres = $this->getAdapter()->quoteInto('parent_id = ?', $id);
                $this->delete($wheres);
            }
            return true;
        } else if ($option == 'parent') {
            foreach ($data as $item) {
                $id = $item['id'];
                $where = array();
                $where[] = $this->getAdapter()->quoteInto('id = ?', $id);
                $this->delete($where);

                $parent_row = array(
                    'parent_id' => NULL,
                );
                $wheres = $this->getAdapter()->quoteInto('parent_id = ?', $id);
                $this->update($parent_row, $wheres);
            }
            return true;
        } else {
            foreach ($data as $item) {
                $id = $item['id'];
                $where = array();
                $where[] = $this->getAdapter()->quoteInto('id = ?', $id);
                $this->delete($where);
            }
            return true;
        }
    }


public function getlabel($id){

	

	      $sql = $this->select()
            ->from(array('m' => $this->_name))
            ->where('registery_id= ?', $id);

        $row = $this->fetchRow($sql);
	   
	   
       

        $this->validateExists($row);
      
       return $row;
		
		//return $data; 
}
    public function registryData($lang, $is_registry=false){
        foreach ($lang as $items) {
            $parent_row = array(
                'activate' => 1,
            );
            $where = $this->getAdapter()->quoteInto(($is_registry?'registery_id = ?':'id = ?'), $items);
            $this->update($parent_row, $where);
        }
        return true;

    }

}