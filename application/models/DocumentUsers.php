<?php

class Application_Model_DocumentUsers extends Muzyka_DataModel
{
    protected $_name = "document_users";

    public $id;
    public $document_id;
    public $user_id;
    public $created_at;
    public $updated_at;


    public function saveDocumentUsers($data)
    {
        if(!empty($data['document_id'])) {

            $documentId = $data['document_id'];
            $this->delete(array("document_id = ?" => $documentId));
            
            if (!empty($data['document_users'])) {
                foreach ($data['document_users'] as $userId => $isSelected) {
                    if ($isSelected === '1') {
                        $this->save(array(
                            'document_id' => $documentId,
                            'user_id' => $userId,
                        ));
                    }
                }
            }
        }
    }

    public function save($data) {
        if (empty($data['id'])) {
            $row = $this->createRow();
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->get($data['id']);
            $row->updated_at = date('Y-m-d H:i:s');
            if (!($row instanceof Zend_Db_Table_Row)) {
                throw new Exception('Zmiana rekordu zakonczona niepowiedzenie. Rekord zostal usuniety');
            }
        }

        $row->document_id = (int) $data['document_id'];
        $row->user_id = (int) $data['user_id'];

        $id = $row->save();

        $this->addLog($this->_name, $row->toArray(), __METHOD__);

        return $id;
    }

    public function getAll($conditions = array())
    {
        $select = $this->getBaseQuery($conditions);

        return $select->query()->fetchAll(PDO::FETCH_ASSOC);
    }
}
