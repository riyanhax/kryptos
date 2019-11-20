<?php

/**
 * Created by PhpStorm.
 * User: storm
 * Date: 3/15/2018
 * Time: 1:14 PM
 */
class Application_Model_Menu extends Muzyka_DataModel
{
    protected $_name = "menu";

    public $id;
    public $label;
    public $path;
    public $icon;
    public $rel;
    public $parent_id;
    public $activate_routes;

    public function getMenuList()
    {

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


 public function updateOrder($order, $name)
    {
		
		$data=$this->fetchAll(array('label = ?' => $name));
		
		//if($data[0]['parent_id']==''){
			foreach ($data as $key =>$val){
				 $id= $val['id'];
				$row = $this->validateExists($this->findOne($id));
				$row->odr = $order;
				$row->save();
       
		}
		 return 1; 
    }
    public function updateRowData($data)
    {
        $id = $data['id'];
        $row = $this->validateExists($this->findOne($id));

        $row->label = $data['label'];
        $row->path = $data['path'];
        $row->icon = $data['icon'];
        $row->rel = $data['rel'];
        $row->parent_id = $row['parent_id'];
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

    /*menu button by rahul*/

    public function moveupdown($child_id, $parent_id)
    {
        if ($child_id && $parent_id) {
            $childRowset = $this->fetchAll(array('parent_id = ?' => $child_id));
            $parentRowset = $this->fetchAll(array('parent_id = ?' => $parent_id));
            if (count($childRowset) > 0) {
                foreach ($childRowset as $item) {
                    $ids = $item['id'];
                    $parent_row = array(
                        'parent_id' => $parent_id,
                    );
                    $where = $this->getAdapter()->quoteInto('id = ?', $ids);

                    $this->update($parent_row, $where);
                }

                if (count($parentRowset) > 0) {
                    foreach ($parentRowset as $items) {
                        $parentRowset_ids = $items['id'];
                        $parent_row = array(
                            'parent_id' => $child_id,
                        );
                        $where = $this->getAdapter()->quoteInto('id = ?', $parentRowset_ids);
                        $this->update($parent_row, $where);
                    }
                }

                $multi_row = array(
                    'id' => 0,
                );
                $where = $this->getAdapter()->quoteInto('id = ?', $child_id);
                $this->update($multi_row, $where);

                if ($parent_id) {
                    $change_p_id = array(
                        'id' => $child_id,
                    );
                    $where = $this->getAdapter()->quoteInto('id = ?', $parent_id);
                    $this->update($change_p_id, $where);

                    $change_c_id = array(
                        'id' => $parent_id,
                    );
                    $where = $this->getAdapter()->quoteInto('id = ?', 0);
                    $this->update($change_c_id, $where);
                }
            } else {
                if (count($parentRowset) > 0) {
                    foreach ($parentRowset as $items) {
                        $parentRowset_ids = $items['id'];
                        $parent_row = array(
                            'parent_id' => $child_id,
                        );
                        $where = $this->getAdapter()->quoteInto('id = ?', $parentRowset_ids);
                        $this->update($parent_row, $where);
                    }
                }
                $single_row = array(
                    'id' => 0,
                );
                $where = $this->getAdapter()->quoteInto('id = ?', $child_id);
                $this->update($single_row, $where);
                if ($parent_id) {
                    $change_p_id = array(
                        'id' => $child_id,
                    );
                    $where = $this->getAdapter()->quoteInto('id = ?', $parent_id);
                    $this->update($change_p_id, $where);

                    $change_c_id = array(
                        'id' => $parent_id,
                    );
                    $where = $this->getAdapter()->quoteInto('id = ?', 0);
                    $this->update($change_c_id, $where);
                }

            }
        }
        return true;
    }

    public function movein($in_id, $parent_id)
    {
        if ($in_id && $parent_id) {
            $childRowset = $this->fetchAll(array('parent_id = ?' => $in_id));
            /*foreach ($childRowset as $item) {
                $ids = $item['id'];
                echo "<pre>";print_r($ids);
                $parent_row = array(
                    'parent_id' => $parent_id,
                );
                $where = $this->getAdapter()->quoteInto('id = ?', $ids);
                $this->update($parent_row, $where);
            }*/
            $change_in_id = array(
                'parent_id' => $parent_id,
            );
            $where = $this->getAdapter()->quoteInto('id = ?', $in_id);
            $this->update($change_in_id, $where);
        }
        return true;
    }

    public function moveout($out_id, $parent_id)
    {
        if ($out_id && $parent_id) {
            $content_out = $this->fetchAll(array('id = ?' => $parent_id));
            foreach ($content_out as $out_value) {
                $where = $this->getAdapter()->quoteInto('id = ?', $out_id);
                $change_out_id = array(
                    'parent_id' => $out_value->parent_id,
                );
                $this->update($change_out_id, $where);
            }

        }
        return true;
    }

    /*end menu button by rahul*/

}