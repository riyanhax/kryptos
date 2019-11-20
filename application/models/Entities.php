<?php

class Application_Model_Entities extends Muzyka_DataModel
{
    const ID_VARCHAR = 1;
    const ID_TEXT_AREA = 2;
    const ID_DATE = 4;
    const ID_DATETIME = 5;
    const ID_EMPLOYEE = 6;
    const ID_DOCUMENT = 7;
    const ID_ROOM = 9;
    const ID_ENTITY = 10;
    const ID_COLLECTION = 11;
    const ID_BUILDING = 12;
    const ID_FILE = 13;
    const ID_CONSENT = 14;
    const ID_SURVEY = 15;
    const ID_DATAGRID = 16;
    const ID_CLASSIFICATION = 17;
    const ID_GROUP_ASSET = 18;
    const ID_ADDITIONAL_SECURITY = 19;
    const ID_RELATION_MATRIX = 20;
    const ID_RELATION_MATRIX_MULTIPLE = 21;
    const ID_RELATION_MATRIX_DYNAMIC = 22;
    const ID_RELATION_SELECT = 23;
    const ID_NUMBER = 24;
    const ID_BUTTON = 25;
    const ID_AUTO_COMPLETE = 26;
    const ID_CHECKBOX_GROUP = 27;
    const ID_HIDDEN = 28;
    const ID_HEADER = 29;
    const ID_PARAGRAPH = 30;
    const ID_RADIO_GROUP = 31;
    const ID_RATING = 32;
    const ID_SIGNATURE = 33;
    const ID_SMART_RADIO = 34;
    const ID_RELATION_MATRIX_EXTRA = 35;
    const ID_HYPERLINK = 36;
    const ID_SMART_MULTI_SELECT = 37;
    const ID_MORE_INFO = 38;

    protected $_name = "entities";
    protected $_base_name = 'e';
    protected $_base_order = 'e.id ASC';

    public $id;
    public $title;
    public $author_id;
    public $config;
    public $created_at;
    public $updated_at;

    /**
     * @param $data
     * @return self|Zend_Db_Table_Row|Zend_Db_Table_Row_Abstract
     * @throws Application_SubscriptionOverLimitException
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

    public function getAllForTypeahead()
    {
        return $this->getSelect(null, ['id', 'title'])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resultsFilter(&$results)
    {
        foreach ($results as &$result) {
            $result['config_data'] = json_decode($result['config']);
        }
    }

    public function getOne($id)
    {
        $sql = $this->select()
            ->where('id = ?', $id);

        return $this->fetchRow($sql)->toArray();
    }
}
