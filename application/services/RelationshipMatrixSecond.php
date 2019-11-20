<?php

class Application_Service_RelationshipMatrixSecond
{
    const RELATIONSHIP_VALUES_COUNT = 3;

    /** @var self */
    protected static $instance = null;

    /** @var array */
    protected $entities = null;

    public static function composeId(array $valueIds)
    {
        sort($valueIds);
        return implode('-', $valueIds);
    }

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

    public function getItemsTree($serializedValues, array $excludes = [], $multiformDataStr = null)
    {
        $out = [];
        $multiformData = $this->decodeMultiformData($multiformDataStr);
        
        $topRegistryId = !empty($multiformData['registry']) ? $multiformData['registry'] : null;
        $registry2Id = !empty($multiformData['registry2']) ? $multiformData['registry2'] : null;
        $registry3Id = !empty($multiformData['registry3']) ? $multiformData['registry3'] : null;

        $relations = $this->getItemsList($serializedValues, $excludes, $multiformDataStr);
        
        // echo json_encode($relations);
        // die();
        foreach ($relations as $rPos => $relation) {
            foreach ($relation as $item) {
                if ($item['registry_id'] == $topRegistryId) {
                    $out[ $item['id'] ]['title'] = $item['title'];
                    foreach ($relation as $item2) {
                        if ($item2['registry_id'] == $registry2Id) {
                            $out[ $item['id'] ]['children'][ $item2['id'] ]['title'] = $item2['title'];
                            foreach ($relation as $item3) {
                                if ($item3['registry_id'] == $registry3Id) {
                                    $out[ $item['id'] ]['children'][ $item2['id'] ]['children'][ $item3['id'] ] = $item3['title'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $out;
    }

    public function getItemsList($serializedValues, array $excludes = [], $multiformDataStr = null)
    {
        $out = [];
        $multiformData = $this->decodeMultiformData($multiformDataStr);
        foreach (array_unique(explode(',', $serializedValues)) as $i => $valueIdsStr) {
            $values = array_unique(explode('-', trim($valueIdsStr), self::RELATIONSHIP_VALUES_COUNT));
            if (count($values) < self::RELATIONSHIP_VALUES_COUNT) {
                continue;
            }
            $out[$i] = [];
            foreach ($values as $valueId) {
                foreach ($this->getEntities($multiformData) as $registryId => $registryInfo) {
                    foreach ($registryInfo['values'] as $registryValueId => $registryValueTitle) {
                        if ($registryValueId != $valueId || in_array($valueId, $excludes)) {
                            continue;
                        }
                        $out[$i][] = [
                            'id' => $registryValueId,
                            'registry_id' => $registryId,
                            'title' => $registryValueTitle,
                        ];
                    }
                }
            }
        }
        return $out;
    }

    protected function getEntities($multiformData = [])
    {
        if ($this->entities === null) {
            try {
                $service = new Application_Service_RegistryEntries();
                $registryIds = $stringifyParams = [];
                if (!empty($multiformData['registry']) && !empty($multiformData['registryStringify'])) {
                    $registryIds[] = $multiformData['registry'];
                    $stringifyParams[$multiformData['registry']] = $multiformData['registryStringify'];
                }
                if (!empty($multiformData['registry2']) && !empty($multiformData['registry2Stringify'])) {
                    $registryIds[] = $multiformData['registry2'];
                    $stringifyParams[$multiformData['registry2']] = $multiformData['registry2Stringify'];
                }
                if (!empty($multiformData['registry3']) && !empty($multiformData['registry3Stringify'])) {
                    $registryIds[] = $multiformData['registry3'];
                    $stringifyParams[$multiformData['registry3']] = $multiformData['registry3Stringify'];
                }
                $this->entities = $service->getAllEntities($registryIds, $stringifyParams);
            } catch (Exception $e) {
                $this->entities = [];
            }
        }
        return $this->entities;
    }

    protected function decodeMultiformData($dataStr)
    {
        return !empty($dataStr) ? json_decode($dataStr, true) : [];
    }
}
