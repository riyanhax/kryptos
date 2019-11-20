<?php

class Application_Model_RegistryFilter extends Muzyka_DataModel
{
    protected $_name = "registry_filters";
    protected $_base_name = 'f';
    protected $_base_order = 'f.id ASC';

    public $id;
    public $meta_content;
    public $user_id;
    public $filter_score;
    public $filter_name;
    public $created_at;
    public $updated_at;

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
   
    public function getOne($id)
    {
        $sql = $this->select()
            ->where('id = ?', $id);

        return $this->fetchRow($sql)->toArray();
    }

    public function fetchAll()
    {
        $sql = $this->select();
        $results = $this->_fetch($sql);
		// echo"<pre>";print_r($results);die;
        $response = array();
        if(count($results) > 0 )
        {
            $count = 0;
            foreach($results as $result)
            {
                $response[$count]['id'] = $result['id'];
                $response[$count]['filter_name'] = $result['filter_name'];
                $response[$count]['filter_score'] = $result['filter_score'];
                $response[$count]['conditions']  = unserialize($result['meta_content']);
                $count++;
            }
        }
        return $response;
    }
}
