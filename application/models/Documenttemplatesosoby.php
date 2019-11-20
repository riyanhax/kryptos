<?php
class Application_Model_Documenttemplatesosoby extends Muzyka_DataModel
{
    protected $_name = "documenttemplatesosoby";

    public function getOne($id) {
        $sql = $this->select()
            ->where('osoba_id = ?', $id);

        return $this->fetchRow($sql);
    }

    public function getWorkerIds($template_id, $checkPendings = true) {
        $workerIds = array();
        $sql = $this->select()
            ->from('documenttemplatesosoby', ['worker_id'])
            ->where('documenttemplatesosoby.documenttemplate_id = ?', $template_id);
        if ($checkPendings) {
            $sql->join('documents_pending','documenttemplatesosoby.documenttemplate_id = documents_pending.documenttemplate_id AND `documenttemplatesosoby`.`worker_id`= documents_pending.worker_id', [])
                ->where('documents_pending.status<> ?', Application_Model_DocumentsPending::STATUS_REMOVED);
        }
        $result = $this->fetchAll($sql);

        if ($result) {
            foreach ($result as $id) {
                $workerIds[] = $id['worker_id'];
            }
        }
        return $workerIds;
    }

    public function save($data)
    {
        $row = $this->createRow($data);

        $id = $row->save();
        return $row;
    }

    public function getDocumentTemplateOsoby($workerId, $documentTemplateId) {
        $workerIds = array();
        $sql = $this->select()
            ->where('worker_id = ?', $workerId)
            ->where('documenttemplate_id = ?', $documentTemplateId);
        $result = $this->fetchAll($sql);

        if ($result) {
            foreach ($result as $id) {
                $workerIds[] = $id['worker_id'];
            }
        }
        return $workerIds;
    }

    public function getAllDocumentTemplatesIds($workerId) {
        $templatesIDs = array();
        $sql = $this->select()
                    ->where('worker_id = ?',$workerId);
        $result = $this->fetchAll($sql);
        if($result){
            foreach ($result as $value) {
                $templatesIDs[] = $value['documenttemplate_id'];
            }
        }
        return $templatesIDs;
    }
}
