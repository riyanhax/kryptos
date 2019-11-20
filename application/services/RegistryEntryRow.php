<?php

/**
 * @property int|null $id
 * @property int|null $registry_id
 * @property string|null $title
 * @property int|null $worker_id
 * @property object[] $entities
 */
class Application_Service_RegistryEntryRow extends Application_Service_EntityRow
{
    public function __toString()
    {
        return $this->title;
    }

    public function entityToString($entityId)
    {
        // echo $entityId;
        if (empty($this->entities[$entityId])) {
            // echo "came to here";
            return '';
        }
        // echo json_encode($this->entities[$entityId]);
        // echo "came to here0000";

        /** @var object[] $data */
        $data = Application_Service_Utilities::forceArray($this->entities[$entityId]);
        $result = [];

        foreach ($data as $row) {
            $result[] = $row->__toString();
        }

        return implode(', ', $result);
    }

    public function getEntityId($entityId)
    {
        if (empty($this->entities[$entityId])) {
            return '';
        }
        return $this->entities[$entityId]->entry_id;
    }

    /**
     * @return int[]
     */
    public function getDocumentWorkerIds($documentTemplateId = false)
    {
        if ($this->worker_id) {
            return [$this->worker_id];
        }
        try {
            /** @var Application_Model_Documenttemplates $templatesRepository */
            $templatesRepository = Application_Service_Utilities::getModel('Documenttemplates');
            /** @var Application_Model_Documenttemplatesosoby $workersRepository */
            $workersRepository = Application_Service_Utilities::getModel('Documenttemplatesosoby');
            if ($documentTemplateId) {
                return $workersRepository->getWorkerIds(
                    $documentTemplateId,
                    false
                );
            } else {
                return $workersRepository->getWorkerIds(
                    $templatesRepository->getActiveDocumentTemplateId($this->registry_id),
                    false
                );
            }
        } catch (Exception $e) {
            return [];
        }
    }
}
