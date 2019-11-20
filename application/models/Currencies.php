<?php

class Application_Model_Currencies extends Muzyka_DataModel
{
    protected $_name = 'currencies';

    /**
     * @param $code
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     */
    public function getByCode($code)
    {
        return $this->select()->where("code=?", $code)->query()->fetch();
    }

    /**
     * @param $code
     * @return null
     * @throws Zend_Db_Statement_Exception
     */
    public function getIdByCode($code)
    {
        $row = $this->getByCode($code);
        return !empty($row['id']) ? $row['id'] : null;
    }

    /**
     * @param $id
     * @return null
     */
    public function getCodeById($id)
    {
        $row = $this->get($id);
        return !empty($row['code']) ? $row['code'] : null;
    }

}
