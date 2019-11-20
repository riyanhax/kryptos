<?php

class Application_Model_StorageDocuments extends Muzyka_DataModel
{
    const TYPE_SIMPLE = 1;
    const TYPE_DOCUMENT = 2;
    const TYPE_SZKOLENIE = 3;

    protected $_name = "storage_documents";

    private $id;
    private $documenttemplate_id;
    private $user_id;
    private $status;
    private $number;
    private $title;
    private $html_content;
    private $confirmation_date;
    private $created_at;
    private $updated_at;

    public function getAllByIds($ids)
    {
        $sql = $this->select()
            ->where('id IN (?)', $ids);

        return $this->fetchAll($sql);
    }

    public function getOne($id)
    {
        $sql = $this->select()
            ->where('id = ?', $id);

        return $this->fetchRow($sql);
    }

    public function getTypes()
    {
        return array(
            1 =>
            array('id' => 1, 'name' => 'Pobranie'),
            array('id' => 2, 'name' => 'Udostępnienie'),
            array('id' => 3, 'name' => 'Powierzenie'),
        );
    }

    public function save($data)
    {
        if (!(int)$data['id']) {
            $row = $this->createRow();
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->getOne($data['id']);
            $row->updated_at = date('Y-m-d H:i:s');
            if (!($row instanceof Zend_Db_Table_Row)) {
                throw new Exception('Zmiana rekordu zakonczona niepowiedzenie. Rekord zostal usuniety');
            }
        }

        $row->id = (int) $data['id'];
        $row->documenttemplate_id = (int) $data['documenttemplate_id'];
        $row->user_id = (int) $data['user_id'];
        $row->status = (int) $data['status'];
        $row->number = (string) $data['number'];
        $row->title = $this->escapeName($data['title']);
        $row->html_content = (string) $data['html_content'];
        $row->confirmation_date = (string) $data['confirmation_date'];

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $id;
    }

    public function createNewRow()
    {
        $row = $this->createRow();
        $id = $row->save();

        return $id;
    }

    public function remove($id)
    {
        $row = $this->getOne($id);
        if (!($row instanceof Zend_Db_Table_Row)) {
            throw new Exception('Rekord nie istnieje lub zostal skasowany');
        }

        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
    }
}
