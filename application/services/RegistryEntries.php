<?php

class Application_Service_RegistryEntries
{
    const CACHE_PREFIX = 'registry_values_';
    const BUILDING_REGISTERY = "Buildings";
    const PLACE_REGISTERY = "Places";

    /**
     * @var Application_Model_Registry
     */
    protected $registryRepository;

    /**
     * @var Application_Model_RegistryEntries
     */
    protected $registryEntriesRepository;

    /**
     * @var Application_Service_Registry
     */
    protected $serviceRegistry;

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    /**
     * @var boolean
     */
    protected $allValuesMode;

    /**
     * RegistryEntries constructor.
     * @param bool $allValuesMode
     * @throws Zend_Cache_Exception
     */
    public function __construct($allValuesMode = false)
    {
        $this->allValuesMode = $allValuesMode;
        // TODO: add DI
        $this->cache = Zend_Cache::factory(
            'Core',
            'File',
            array(
                'automatic_serialization' => true
            ),
            array(
                'cache_dir' => ROOT_PATH . '/cache'
            )
        );
        $this->registryRepository = Application_Service_Utilities::getModel('Registry');
        $this->registryEntriesRepository = Application_Service_Utilities::getModel('RegistryEntries');
        $this->serviceRegistry = Application_Service_Registry::getInstance();
    }


    /**
     * @param integer $registryId
     * @param array|string $fieldEntityIds
     * @return array
     * @throws Exception
     */
    public function getRegistryEntities($registryId, $fieldEntityIds = [], $entryID = 0)
    {
        //error_log(print_r("start ====>", true));
        /** @var object $registry */
        $registry = $this->registryRepository->getFull($registryId, true);
        $config = Zend_Registry::get('config');
        
        if (!$registry) {
            return array();
        }
        if (is_string($fieldEntityIds)) {
            $fieldEntityIds = array_filter(explode(',', $fieldEntityIds));
        }
        if (!$fieldEntityIds) {
            $fieldEntityIds = $this->getDefaultStringifyParams($registry);
        }
        // $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId]);
        
        //error_log(print_r("registryId ===>". $registryId, true));
        // $this->serviceRegistry->entriesGetEntities($paginator, $entryID);

        
        //////////////////////////////////////////////////////////////////////////////
        // Caching ...... to improve page loading speed.
        // caching file name is determined by Registry ID and Entry ID.

        // comagom code start 2019.4.2 : Hello, Jack. your code is good. but, when user change place registry or building registry. after that, It is not implemented on permission registry when user add or edit permission entry.
        // So that, I just inserted my idea into your code. I am sorry.
        $cache_building_place_name = 'registryEntriesTable_'.Self::BUILDING_REGISTERY.'_'. Self::PLACE_REGISTERY .'_flag';
        $cache_file_name = 'getregistryentities_paginator_'.$registryId."_".$entryID;
        // if($registryId == "175") {
           
        //     if(($cached_building_place_flag_Info = $this->cache->load($cache_building_place_name)) === false ) {
        //         $cached_building_place_flag_Info = "empty";
        //     }
            
        //     if($cached_building_place_flag_Info != "empty") {
        //         // If building registry or place registry was changed.
        //         if($cached_building_place_flag_Info['Buildings'] == "false" && $cached_building_place_flag_Info['Places'] == "false") {
        //             if ( ($cached_paginator = $this->cache->load($cache_file_name)) === false ) {
        //                 $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId]);        
        //                 //error_log(print_r("registryId ===>". $registryId, true));
        //                 $this->serviceRegistry->entriesGetEntities($paginator, $entryID);
            
        //                 $paginator_data_custom=[];
        //                 foreach ($paginator as $d) {
        //                     $paginator_data_custom[] = (object)array('entities'=>$d->entities, 'entities_named' => $d->entities_named);
        //                 }
            
        //                 $this->cache->save($paginator, $cache_file_name);
        //                 $this->cache->save($paginator_data_custom, $cache_file_name.'_custom');
        //             }
        //             else {
        //                 $paginator = $cached_paginator;
        //                 $paginator_data_custom = $this->cache->load($cache_file_name.'_custom');
            
        //                 $d_idx = 0;
        //                 foreach ($paginator as $d) {
        //                     $d->entities = $paginator_data_custom[$d_idx]->entities;
        //                     $d->entities_named = $paginator_data_custom[$d_idx]->entities_named;
        //                     $d_idx++;
        //                 }
        //             }
        //         } else {
        //             $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId]);
        //             $this->serviceRegistry->entriesGetEntities($paginator, $entryID);
        //             $paginator_data_custom=[];
        //             foreach ($paginator as $d) {
        //                 $paginator_data_custom[] = (object)array('entities'=>$d->entities, 'entities_named' => $d->entities_named);
        //             }

        //             $this->cache->save($paginator, $cache_file_name);
        //             $this->cache->save($paginator_data_custom, $cache_file_name.'_custom');

        //             $cacheData = array(
        //                 "Buildings" => "false",
        //                 "Places" => "false",
        //             );
        //             $this->cache->save($cacheData, $cache_building_place_name);
        //         }
                
        //     } else {
        //         if ( ($cached_paginator = $this->cache->load($cache_file_name)) === false ) {
        //             $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId]);        
        //             //error_log(print_r("registryId ===>". $registryId, true));
        //             $this->serviceRegistry->entriesGetEntities($paginator, $entryID);
        
        //             $paginator_data_custom=[];
        //             foreach ($paginator as $d) {
        //                 $paginator_data_custom[] = (object)array('entities'=>$d->entities, 'entities_named' => $d->entities_named);
        //             }
        
        //             $this->cache->save($paginator, $cache_file_name);
        //             $this->cache->save($paginator_data_custom, $cache_file_name.'_custom');
        //         }
        //         else {
        //             $paginator = $cached_paginator;
        //             $paginator_data_custom = $this->cache->load($cache_file_name.'_custom');
        
        //             $d_idx = 0;
        //             foreach ($paginator as $d) {
        //                 $d->entities = $paginator_data_custom[$d_idx]->entities;
        //                 $d->entities_named = $paginator_data_custom[$d_idx]->entities_named;
        //                 $d_idx++;
        //             }
        //         }
        //     }
        // } else {
        if ($config->production->cache->enabled) {
            $cached_paginator = $this->cache->load($cache_file_name);
        } else {
            $cached_paginator = false;
        }

        if ($cached_paginator === false) {
            $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId]);
            //error_log(print_r("registryId ===>". $registryId, true));
            $this->serviceRegistry->entriesGetEntities($paginator, $entryID);

            $paginator_data_custom = [];
            foreach ($paginator as $d) {
                $paginator_data_custom[] = (object) array('entities' => $d->entities, 'entities_named' => $d->entities_named);
            }

            $this->cache->save($paginator, $cache_file_name);
            $this->cache->save($paginator_data_custom, $cache_file_name . '_custom');
        } else {
            $paginator = $cached_paginator;
            $paginator_data_custom = $this->cache->load($cache_file_name . '_custom');

            $d_idx = 0;
            foreach ($paginator as $d) {
                $d->entities = $paginator_data_custom[$d_idx]->entities;
                $d->entities_named = $paginator_data_custom[$d_idx]->entities_named;
                $d_idx++;
            }
        }
        // }
        
        // comagom code end 2019.4.2
        
        

        //////////////////////////////////////////////////////////////////////////////
  
        $out = array();
        //error_log(print_r("end ===>", true));
        /** @var object $d */
        foreach ($paginator as $d) {
            foreach ($fieldEntityIds as $fieldEntityId) {
                $entityId = $d->getEntityId($fieldEntityId);
                if (!$entityId) {
                    continue;
                }
                $value = $d->entityToString($fieldEntityId);
                $out[$entityId] = isset($out[$entityId]) ? ($out[$entityId] . ' ' . $value) : $value;
            }
        }
        return $out;
    }

    /**
     * @param array $registryIds
     * @param array $stringifyParams
     * @return array
     * @throws Exception
     */
    public function getAllEntities(array $registryIds = [], $stringifyParams = [], $entryID = 0)
    {
        $cacheKey = $this->getCacheKey($registryIds, $stringifyParams);
        if($result = $this->cache->load($cacheKey)) {
            return $result;
        }
        $result = [];

        // $registryIds = array_diff($registryIds, $exclude_registry_ids);
        $conditions = array_filter(['id' => $registryIds]);
        
        foreach ($this->registryRepository->findBy($conditions) as $registry) {
            // findBy returns all rows if the registryIds is empty, so we need to exclude again
            // if (in_array($registry['id'], $exclude_registry_ids)) {
            //     continue;
            // }

            
            $registryId = $registry['id'];
            $stringify = isset($stringifyParams[$registryId]) ? $stringifyParams[$registryId] : [];
            if (!$values = $this->getRegistryEntities($registryId, $stringify, $entryID)) {
                continue;
            }
            $result[$registryId] =  [
                'title' => $registry['title'],
                'values' => $values,
            ];
        }
//        $this->cache->save($result, $cacheKey);
        return $result;
    }

    /**
     * @param array $registryIds
     * @return array
     * @throws Exception
     */
    public function getAllEntitiesAsArray(array $registryIds = [])
    {
        $cacheKey = $this->getCacheKey($registryIds) . '_extra';
        $result = $this->cache->load($cacheKey);
        
        if($result) {
            return $result;
        } else {
            $result = [];
        }
        
        $conditions = array_filter(['id' => $registryIds]);
        
        foreach ($this->registryRepository->findBy($conditions) as $registryData) {
            $registryId = $registryData['id'];
            $registryFields = $this->getEntityFields($registryId);
            /** @var Application_Service_RegistryEntryRow[] $paginator */
            $paginator = $this->registryEntriesRepository->getList(['registry_id = ?' => $registryId, 'NOT ghost']);
            $this->serviceRegistry->entriesGetEntities($paginator);
            $values = [];
            foreach ($paginator as $d) {
                foreach (array_keys($registryFields) as $fieldEntityId) {
                    $entityId = $d->getEntityId($fieldEntityId);
                    
                    if(!$entityId) {
                        continue;
                    }
                    
                    $values[$entityId][$fieldEntityId] = $d->entityToString($fieldEntityId);
                }
            }
            
            $result[$registryId] =  [
                'title' => $registryData['title'],
                'fields' => $registryFields,
                'values' => $values,
            ];
        }
//        $this->cache->save($result, $cacheKey);
        return $result;
    }

    /**
     * @param $entryId
     * @return array
     * @throws Exception
     */
    public function getEntryAsArray($entryId)
    {
        $out = [];
        /** @var Application_Service_RegistryEntryRow[]|object $paginator */
        $paginator = $this->registryEntriesRepository->getList(['id = ?' => $entryId]);
        $this->serviceRegistry->entriesGetEntities($paginator);
        foreach ($paginator as $entry) {
            /** @var object $registry */
            $registry = $this->registryRepository->getOne($entry->registry_id, true);
            $registry->loadData('entities');
            foreach ($registry->entities as $entity) {
                $out[] = [
                    'id' => $entity->id,
                    'system_name' => $entity->entity->system_name,
                    'config' => (array) $entity->config_data,
                    'title' => $entity->title,
                    'value' => $entry->entityToString($entity->id),
                ];
            }
        }
        return $out;
    }

    /**
     * @param $entryId
     * @return string
     * @throws Exception
     */
    public function getEntryAsString($entryId)
    {
        return implode(' ', array_filter(array_map(function($entryField){
            if (!in_array($entryField['system_name'], ['varchar'])) {
                return null;
            }
            return $entryField['value'];
        }, $this->getEntryAsArray($entryId))));
    }

    /**
     * @param $registryId
     * @param bool $all
     * @return array
     * @throws Exception
     */
    protected function getEntityFields($registryId)
    {
        $out = [];
        /** @var Application_Service_EntityRow|object $registry */
        $registry = $this->registryRepository->getOne($registryId, true);
        $registry->loadData('entities');
        /** @var Application_Service_EntityRow|object $entity */
        foreach ($registry->entities as $entity) {
            if (!$this->allValuesMode && $entity->entity->system_name !== 'varchar' && $entity->entity->system_name !== 'text-ckeditor' ) {
                continue;
            }
            $out[$entity->id] = $entity->title;
        }
        return $out;
    }

    /**
     * @param object $registry
     * @return array
     */
    protected function getDefaultStringifyParams($registry)
    {
        return array_filter(array_map(function($entity){
            if (!$entity->stringify) {
                return null;
            }
            return $entity->id;
        }, $registry->entities));
    }

    /**
     * @param array $registryIds
     * @param array $stringifyParams
     * @return string
     */
    protected function getCacheKey(array $registryIds, array $stringifyParams = [])
    {
        if ($stringifyParams) {
            return self::CACHE_PREFIX . md5(join('_', $registryIds).json_encode($stringifyParams));
        }
        return self::CACHE_PREFIX . ($registryIds ? join('_', $registryIds) : ($stringifyParams===false?'array':'all'));
    }
}
