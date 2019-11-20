<?php

class Application_Service_Registry
{
    /** @var self */
    protected static $_instance = null;

    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    /**
     * @param Application_Service_EntityRow $entry
     * @param $entryData
     * @throws Exception
     */
    public function entrySave($entry, $entryData)
    {
	
		 $registry_id=$entryData['registry_id'];
		 $element_755=$entryData['element_755'];
		 $conditions = array_filter(['id' => $registry_id,'new_value'=>'["'.$text.'"]' ]);
	/* $data =	$this->checkentryexist($registry_id,$element_755);
	print_r($data);
	exit; */
	/* 	print_r($data);
		die; */
		
        $entry_data = Application_Service_Utilities::getModel('RegistryEntries')->save($entry);
        $entry->loadData(['registry']);

        foreach ($entry->registry->entities as $registryEntity) {
            $entity = $registryEntity->entity;
            $configData = $entity->config_data;
            $fieldName = sprintf('element_%s', $registryEntity->id);
            $values = $entryData[$fieldName];
            $uniqueIndex = [
                'entry_id = ?' => $entry->id,
                'registry_entity_id = ?' => $registryEntity->id
            ];
            $uniqueIndexFields = [
                'entry_id' => $entry->id,
                'registry_entity_id' => $registryEntity->id
            ];
            if(in_array($configData->type, ['button', 'header', 'paragraph', 'moreInfo'])){
                continue;
            }
            switch ($configData->type) {
                case "number":
                case "int":
                case "rating":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "smartRadioGroup":
                case "smartMultiSelect":
                    $radioConfig = Application_Service_Entities::decodeMultiformData($registryEntity);
                    /** @var Application_Model_RegistryEntriesEntitiesRadioRelation | Application_Model_RegistryEntriesEntitiesInt $model */
                    $model = !empty($radioConfig->useRelations)
                        ? Application_Service_Utilities::getModel('RegistryEntriesEntitiesRadioRelation')
                        : Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "signature":
                case "text":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesText');
                    break;
                case "hidden":
                case "radioGroup":
                case "checkboxGroup":
                case "autocomplete":
                case "string":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesVarchar');
                    break;
                case "date":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesDate');
                    break;
                case "datetime":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesDateTime');
                    break;
                case "file":
                    $filesService = Application_Service_Files::getInstance();
                    $fieldNameUploaded = $fieldName . '_uploaded';
                    $uploadedValues = $entryData[$fieldNameUploaded];
                    if ($registryEntity->is_multiple) {
                        $values = [];
                    }

                    if (!empty($uploadedValues)) {
                        $uploadedValues = json_decode($uploadedValues, true);
                        $fileNames = array();
                        if (!empty($uploadedValues)) {
                            foreach ($uploadedValues as $file) {
                                $fileUri = sprintf('uploads/default/%s', $file['uploadedUri']);

                                $params = array();
                                if($entry->registry->system_name != ''){
                                    $params['subdirectory'] = $entry->registry->system_name;
                                }

                                $file = $filesService->create(Application_Service_Files::TYPE_REGISTRY_ATTACHMENT, $fileUri, $file['name'], null, $params);

                                $fileNames[] = $file['name'];
                                if ($registryEntity->is_multiple) {
                                    $values[] = $file->id;
                                } else {
                                    $values = $file->id;
                                }
                            }
                        }

                        $entry->title = implode (", ", $fileNames);
					
                        Application_Service_Utilities::getModel('RegistryEntries')->save($entry);
                    }

                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "dictionary":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "entry":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "checkbox":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "datagrid":
                    $values = serialize($values);
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesText');
                    break;
                case "select":
                    /** @var Application_Model_RegistryEntriesEntitiesSingleRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesSingleRelation');
                    break;
                case "relationshipMatrixDynamic":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixDynamicRelation');
                    break;
                case "relationshipMatrix":
                case "relationshipMatrixMultiple":
                    /** @var Application_Model_RegistryEntriesEntitiesMatrixRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixRelation');
                    break;
                case "relationshipMatrixExtra":
                    /** @var Application_Model_RegistryEntriesEntitiesMatrixExtraRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixExtraRelation');
                    break;
                default:
                    Throw new Exception('Invalid entity type ' . $configData->type);
            }

            $model->replaceEntries($uniqueIndexFields, $values);
        }
    //    return $entry_data;
//        vdie($entry, $entryData);
    }
	
	
/*  public function checkentryexist($registry_id,$text){
	 $conditions = array_filter(['id' => $registry_id,'new_value'=>'["'.$text.'"]' ]);

	   $select = $this->getAdapter()->select()
	    ->from('registry_action')
		 ->where($conditions);
		 print_r($select->query()->fetchAll(PDO::FETCH_ASSOC));exit;
		 
}  */
    /**
     * @param $entries
     * @throws Exception
     */
    public function entriesGetEntities($entries, $entryID = 0)
    {
        $length = sizeof($entries);

        //error_log(print_r("size of entries ===>". $length, true));
        //error_log(print_r("entryID ===>". $entryID, true));
        Application_Service_Utilities::getModel('RegistryEntries')->loadData(['registry'], $entries);
        if ($entryID > 0) {
            foreach ($entries as $entry) {
                if ($entryID == $entry->id) {
                    $this->entryGetEntities($entry);
                    break;
                }
            }
        } else {
            foreach ($entries as $entry) {
                $this->entryGetEntities($entry);
            }
        }
        
    }

    /**
     * @param Application_Service_EntityRow $entry
     * @throws Exception
     */
    public function entryGetEntities($entry)
    {
        /** @var Application_Service_EntityRow | object $entry */
        $entry->loadData(['registry']);

        $entry->entities_named = [];
        $entry->entities = [];
        
        $length = sizeof($entry->registry->entities);

        //error_log(print_r("size of ===>". $length, true));
        foreach ($entry->registry->entities as $registryEntity) {
            $entity = $registryEntity->entity;
            $configData = $entity->config_data;
            $fieldName = sprintf('element_%s', $registryEntity->id);
            $uniqueIndex = [
                'entry_id' => $entry->id,
                'registry_entity_id' => $registryEntity->id,
                'NOT ghost',
            ];
            /** @var Muzyka_DataModel $model */

            $configDataTypes = array('button', 'header', 'paragraph', 'number', 'checkboxGroup', 'select', 'button');
            if(in_array($configData->type, $configDataTypes)){
                continue;
            }

            switch ($configData->type) {
                case 'number':
                case "int":
                case "rating":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "smartMultiSelect":
                case "smartRadioGroup":
                    $radioConfig = Application_Service_Entities::decodeMultiformData($registryEntity);
                    /** @var Application_Model_RegistryEntriesEntitiesRadioRelation | Application_Model_RegistryEntriesEntitiesInt $model */
                    $model = !empty($radioConfig->useRelations)
                        ? Application_Service_Utilities::getModel('RegistryEntriesEntitiesRadioRelation')
                        : Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    $registryEntity->is_multiple = true;
                    break;
                case "signature":
                case "text":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesText');
                    break;
                case "string":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesVarchar');
                    break;
                case "date":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesDate');
                    break;
                case "datetime":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesDateTime');
                    break;
                case "file":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "dictionary":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "entry":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "checkbox":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesInt');
                    break;
                case "datagrid":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesText');
                    break;
                case "select":
                    /** @var Application_Model_RegistryEntriesEntitiesSingleRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesSingleRelation');
                    break;
                case "relationshipMatrixDynamic":
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixDynamicRelation');
                    break;
                case "relationshipMatrix":
                case "relationshipMatrixMultiple":
                    /** @var Application_Model_RegistryEntriesEntitiesMatrixRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixRelation');
                    break;
                case "relationshipMatrixExtra":
                    /** @var Application_Model_RegistryEntriesEntitiesMatrixExtraRelation $model */
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesMatrixExtraRelation');
                    break;
                case 'hyperlink':
                case 'button':
                case 'autocomplete':
                case 'radioGroup':
                case 'checkboxGroup':
                case 'hidden':
                case 'header':
                case 'paragraph':
                case 'moreInfo':
                    $model = Application_Service_Utilities::getModel('RegistryEntriesEntitiesVarchar');
                    break;
                default:
                    Throw new Exception('Invalid entity type ' . $configData->type);
            }

            $list = $model->getList($uniqueIndex);
            
            if (empty($list)) {
                continue;
            }

            if ($configData->baseModel) {
                $baseModel = Application_Service_Utilities::getModel($configData->baseModel);
                $baseModel->injectObjectsCustom('value', 'base_object', 'id', [$baseModel->getBaseName() . '.id IN (?)' => null], $list, 'getList');
            }

            if ($model instanceof Application_Model_RegistryEntriesEntitiesMatrixRelation) {
                foreach ($list as &$listItem) {
                    $values = array();
                    foreach ($listItem->items as $relationItem) {
                        $values []= $relationItem->entry_id;
                    }
                    $listItem['value'] = Application_Service_RelationshipMatrix::composeId($values);
                }
                $registryEntity->is_multiple = true;
            }

            if ($model instanceof Application_Model_RegistryEntriesEntitiesMatrixExtraRelation) {
                foreach ($list as &$listItem) {
                    $values = array();
                    foreach ($listItem->items as $relationItem) {
                        if ($relationItem->entry_id === $entry->id) {
                            continue;
                        }
                        $values []= $relationItem->entry_id;
                    }
                    $listItem['value'] = Application_Service_RelationshipMatrix::composeId($values);
                }
                $registryEntity->is_multiple = true;
            }

            if (
                $model instanceof Application_Model_RegistryEntriesEntitiesSingleRelation ||
                $model instanceof Application_Model_RegistryEntriesEntitiesRadioRelation
            ) {
                foreach ($list as &$listItem) {
                    foreach ($listItem->items as $relationItem) {
                        if (!empty($listItem['value']) && $relationItem->entry_id === $entry->id) {
                            continue;
                        }
                        $listItem['value'] = $relationItem->entry_id;
                    }
                }
                $registryEntity->is_multiple = true;
            }

            foreach ($list as &$listItem) {
                $listItem['entity'] = $entry->registry->entities_indexed[$listItem['registry_entity_id']];
            }

            if ($registryEntity->is_multiple) {
                $entry->entities[$registryEntity->id] = $list;
                if ($registryEntity->system_name) {
                    $entry->entities_named[$registryEntity->system_name] = $list;
                }
            } else {
                $entry->entities[$registryEntity->id] = isset($list[0]) ? $list[0] : null;


                if ($registryEntity->system_name) {
                    $entry->entities_named[$registryEntity->system_name] = isset($list[0]) ? $list[0] : null;
                }
            }

            if($configData->type == "file"){
                $entry->entities[$registryEntity->id]->value = $entry->title;
            }
        }
    }

    function getTemplateReport()
    {

    }

    public function getEntityId($registryName, $entityName)
    {
        return Application_Service_Utilities::getModel('RegistryEntities')->getOneByName($registryName, $entityName);
    }

    public function addAssignee($registry, $user, $role, $permissionIds = false)
    {
        $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
        $registryUserPermissionsModel = Application_Service_Utilities::getModel('RegistryUserPermissions');
        $assigneeData = [
            'registry_id' => $registry->id,
            'user_id' => $user->id,
        ];
        $existedAssignee = $registryAssigneesModel->getOne($assigneeData);

        if ($existedAssignee) {
            return false;
        }

        $assigneeData['registry_role_id'] = $role->id;

        $assignee = $registryAssigneesModel->save($assigneeData);

        Application_Service_Events::getInstance()->trigger('registry.assignee.add', $assignee);


        $userPermissionData = [
            'registry_id' => $registry->id,
            'user_id' => $user->id,
        ];
        /*
        $existedUserPermission = $registryUserPermissionsModel->getOne($userPermissionData);
        if ($existedUserPermission) {
            return false;
        }
        */

        if ($permissionIds) {
            foreach ($permissionIds as $permissionId => $val) {
                if ($val == 1) {
                    $userPermissionData = [
                        'registry_id' => $registry->id,
                        'user_id' => $user->id,
                        'registry_permission_id' => $permissionId,
                    ];
                    $existedUserPermission = $registryUserPermissionsModel->getOne($userPermissionData);
                    if (!$existedUserPermission) {
                        $registryUserPermissionsModel->save($userPermissionData);
                    }
                }
            }
        }



        return true;
    }

    public function removeAssignee($registry, $assigneeId)
    {

        $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
        $registryUserPermissionsModel = Application_Service_Utilities::getModel('RegistryUserPermissions');
        $assigneeData = [
            'registry_id' => $registry->id,
            'id' => $assigneeId,
        ];
        $existedAssignee = $registryAssigneesModel->getOne($assigneeData);

        if (!$existedAssignee) {
            return false;
        }

        $deleteParams = [
            'registry_id = ?' => $existedAssignee->registry_id,
            'user_id = ?' => $existedAssignee->user_id
        ];

        $registryUserPermissionsModel->delete($deleteParams);

        $assignee = clone $existedAssignee;
        $existedAssignee->delete();

        Application_Service_Events::getInstance()->trigger('registry.assignee.remove', $assignee);

        return true;
    }

    public function removeAssigneeGroup($registry, $assigneeGroupId)
    {

        $registryAssigneesGroupModel = Application_Service_Utilities::getModel('RegistryAssigneesGroup');
        $registryUserGroupPermissionsModel = Application_Service_Utilities::getModel('RegistryUserGroupPermissions');
        $assigneeData = [
            'registry_id' => $registry->id,
            'id' => $assigneeGroupId,
        ];
        $existedAssignee = $registryAssigneesGroupModel->getOne($assigneeData);


        if (!$existedAssignee) {
            return false;
        }

        $deleteParams = [
            'registry_id = ?' => $existedAssignee->registry_id,
            'group_id = ?' => $existedAssignee->group_id
        ];
        $registryUserGroupPermissionsModel->delete($deleteParams);

        $assignee = clone $existedAssignee;
        $existedAssignee->delete();

        Application_Service_Events::getInstance()->trigger('registry.assignee_group.remove', $assignee);

        return true;
    }

    public function removeRole($registry, $roleId)
    {
        $registryRolesModel = Application_Service_Utilities::getModel('RegistryRoles');
        $roleData = [
            'registry_id' => $registry->id,
            'id' => $roleId,
        ];
        $existedRole = $registryRolesModel->getOne($roleData);

        if (!$existedRole) {
            return false;
        }

        $role = clone $existedRole;
        $existedRole->delete();

        Application_Service_Events::getInstance()->trigger('registry.role.remove', $role);

        return true;
    }

    public function removeDocumentTemplate($registry, $documentTemplateId)
    {
        $registryModel = Application_Service_Utilities::getModel('RegistryDocumentsTemplates');
        $data = [
            'registry_id' => $registry->id,
            'id' => $documentTemplateId,
        ];
        $existedObject = $registryModel->getOne($data);

        if (!$existedObject) {
            return false;
        }

        $object = clone $existedObject;
        $existedObject->delete();

        Application_Service_Events::getInstance()->trigger('registry.document_template.remove', $object);

        return true;
    }

    public function removePermission($registry, $permissionId)
    {
        $registryPermissionsModel = Application_Service_Utilities::getModel('RegistryPermissions');
        $permissionData = [
            'registry_id' => $registry->id,
            'id' => $permissionId,
        ];
        $existedPermission = $registryPermissionsModel->getOne($permissionData);

        if (!$existedPermission) {
            return false;
        }

        $permission = clone $existedPermission;
        $existedPermission->delete();

        Application_Service_Events::getInstance()->trigger('registry.permission.remove', $permission);

        return true;
    }

    public function entryCreateDocument($entryId, $documentTemplateId)
    {
        $dateString = date('Y-m-d');
        $entry = Application_Service_Utilities::getModel('RegistryEntries')->getFull($entryId, true);
        $this->entryGetEntities($entry);
        $registry = Application_Service_Utilities::getModel('Registry')->getFull($entry->registry_id, true);
        $documentTemplate = Application_Service_Utilities::arrayFindOne($registry['documents_templates'], 'id', $documentTemplateId);

        $documentData = Application_Service_Utilities::stempl($documentTemplate->template->data, $entry->entities_named);

        $number = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getNextNumberIncrement($documentTemplate->numbering_scheme, $dateString);

        Application_Service_Utilities::getModel('RegistryEntriesDocuments')->save([
            'entry_id' => $entry->id,
            'document_template_id' => $documentTemplate->id,
            'author_id' => $documentTemplate->default_author_id,
            'number' => Application_Service_Utilities::getDocumentNumber($documentTemplate->numbering_scheme, $dateString, $number),
            'numbering_scheme_ordinal' => $number,
            'data' => $documentData,
        ]);

        return true;
    }

    public function entryUpdateDocuments($entryId)
    {
        $documents = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getListFull(['entry_id' => $entryId]);
        Application_Service_Utilities::getModel('RegistryDocumentsTemplates')->loadData(['template'], Application_Service_Utilities::getValues($documents, 'document_template'));

        foreach ($documents as $document) {
            $documentData = Application_Service_Utilities::stempl($document->document_template->template->data, $document->entry->entities_named);
            $document->data = $documentData;
            $document->save();
        }

        return true;
    }

    public function entryUpdateDocument($documentId)
    {
        $document = Application_Service_Utilities::getModel('RegistryEntriesDocuments')->getFull($documentId, true);
        $documentData = Application_Service_Utilities::stempl($document->document_template->template->data, $document->entry->entities_named);
        $document->data = $documentData;
        $document->save();

        return true;
    }


    public function addRoleToUserGroup($registry, $group, $role, $permissionIds = false)
    {
        $registryGroupAssigneesModel = Application_Service_Utilities::getModel('RegistryAssigneesGroup');
        $registryUserGroupPermissionsModel = Application_Service_Utilities::getModel('RegistryUserGroupPermissions');

        $assigneeGroupData = [
            'registry_id' => $registry->id,
            'group_id' => $group->id,
        ];
        $existedAssignee = $registryGroupAssigneesModel->getOne($assigneeGroupData);

        if ($existedAssignee) {
            return false;
        }

        $assigneeGroupData['registry_role_id'] = $role->id;

        $assigneeGroup = $registryGroupAssigneesModel->save($assigneeGroupData);

        Application_Service_Events::getInstance()->trigger('registry.assignee_group.add', $assigneeGroup);


        $userGroupPermissionData = [
            'registry_id' => $registry->id,
            'group_id' => $group->id,
        ];

        $existedUserPermission = $registryUserGroupPermissionsModel->getOne($userGroupPermissionData);
        if ($existedUserPermission) {
            return false;
        }

        if ($permissionIds) {
            foreach ($permissionIds as $permissionId => $val) {
                if ($val == 1) {
                    $userGroupPermissionData = [
                        'registry_id' => $registry->id,
                        'group_id' => $group->id,
                        'registry_permission_id' => $permissionId,
                    ];
                    $existedUserPermission = $registryUserGroupPermissionsModel->getOne($userGroupPermissionData);
                    if (!$existedUserPermission) {
                        $registryUserGroupPermissionsModel->save($userGroupPermissionData);
                    }
                }
            }
        }


        return true;
    }

}
