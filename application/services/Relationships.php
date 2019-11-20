<?php

class Application_Service_Relationships
{
    /** @var self */
    protected static $instance = null;

    /** @var array */
    protected $entities = null;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getItemsList($serializedValues, $multiformDataStr = null, $exclude_registry_ids = array())
    {
        $multiformData = !empty($multiformDataStr) ? json_decode($multiformDataStr, true) : [];
        $out = [];
        foreach (array_unique(explode(',', $serializedValues)) as $i => $valueId) {
            if (empty($valueId)) {
                continue;
            }
            foreach ($this->getEntities($multiformData, $valueId, $exclude_registry_ids) as $registryId => $registryInfo) {
                foreach ($registryInfo['values'] as $registryValueId => $registryValueTitle) {
                    if ($registryValueId != $valueId) {
                        continue;
                    }
                    $out[] = [
                        'id' => $registryValueId,
                        'registry_id' => $registryId,
                        'title' => $registryValueTitle,
                    ];
                }
            }
        }
        return $out;
    }

    protected function getEntities($multiformData = [], $entryID, $exclude_registry_ids = array())
    {
        // if ($this->entities === null) {
            try {
                
                $service = new Application_Service_RegistryEntries();
                $registryIds = $stringifyParams = [];

                if (!empty($multiformData['registry']) && !empty($multiformData['registryStringify'])) {
                    $registryIds[] = $multiformData['registry'];
                    $stringifyParams[$multiformData['registry']] = $multiformData['registryStringify'];
                }

                if (!empty($multiformData['registries'])) {
                    foreach(array_unique(explode(',', $multiformData['registries'])) as $value) {
                        $registryIds[] = $value;
                    }
                }

                $registryIds = array_diff($registryIds, $exclude_registry_ids);

                $this->entities = $service->getAllEntities($registryIds, $stringifyParams, $entryID);

            } catch (Exception $e) {
                $this->entities = [];
            }
        // }
        return $this->entities;
    }

}
