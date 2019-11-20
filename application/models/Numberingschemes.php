<?php

class Application_Model_Numberingschemes extends Muzyka_DataModel
{
    protected $_name = 'numberingschemes';
    private $id;
    private $name;

    public function getOne($id)
    {
        $sql = $this->select()
            ->where('id = ?', $id);

        return $this->fetchRow($sql);
    }

    public function getAll()
    {
        return $this->select()
            ->order('name ASC')
            ->query()
            ->fetchAll();
    }

    /**
     * @param $data
     * @return mixed
     * @throws Application_SubscriptionOverLimitException
     * @throws Exception
     */
    public function save($data)
    {
        /** @var object $row */
        if (!(int)$data['id']) {
            $row = $this->createRow();
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->getOne($data['id']);
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $historyCompare = clone $row;

        $row->name = mb_strtoupper($data['name']);
        $row->scheme = $data['scheme'];
        $row->type = $data['type'] * 1;
        $id = $row->save();

        $this->getRepository()->eventObjectChange($row, $historyCompare);

        $documentsService = Application_Service_Documents::getInstance();
        foreach ($this->getTemplates($id) as $template) {
            $documentsService->resetForTemplate($template->id, true);
        }

        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        return $id;
    }

    /**
     * @param $id
     * @throws Zend_Db_Table_Row_Exception
     * @throws Exception
     */
    public function remove($id)
    {
        /** @var object $row */
        $row = $this->getOne($id);
        if (!($row instanceof Zend_Db_Table_Row)) {
            throw new Exception('Rekord nie istnieje lub zostal skasowany');
        }

        $documentsService = Application_Service_Documents::getInstance();
        foreach ($this->getTemplates($id) as $template) {
            $documentsService->resetForTemplate($template->id);
        }

        $templatesRepository = Application_Service_Utilities::getModel('Documenttemplates');
        $templatesRepository->update(['numberingscheme_id' => 0], ['numberingscheme_id = ?' => $id]);

        $row->delete();
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
    }

    /**
     * @param $id
     * @return Zend_Db_Table_Rowset_Abstract|object[]
     * @throws Exception
     */
    protected function getTemplates($id)
    {
        $templatesRepository = Application_Service_Utilities::getModel('Documenttemplates');
        return $templatesRepository->fetchAll(['numberingscheme_id = ?' => $id]);
    }
}