<?php

class RegistryMultiFormBuilderController extends Muzyka_Admin
{
    /** @var Application_Model_Registry */
    protected $registryModel;
    /** @var Application_Model_Osoby */
    protected $osoby;
    public function init()
    {
        parent::init();


        $this->_helper->_layout->setLayout('multiformbuilder');
        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->osoby = Application_Service_Utilities::getModel('Osoby');
    }

    public function indexAction()
    {

        $id = $this->getParam('id');
        $registry = $this->registryModel->getFull($id, true);
        $entities = array();
        $tab_names = array();

        foreach ($registry->entities as $key => $entity) {
          $tab = 0;
          if($entity->config != "" && !empty(json_decode($entity->config)))
          {
            $config = json_decode($entity->config);
            if(isset($config->tab))
            {
              $tab = $config->tab;
            }
          }
          $tab_name = 'Step '.($tab + 1);
          if($entity->config != "" && !empty(json_decode($entity->config)))
          {
            $config = json_decode($entity->config);
            if(isset($config->tab_name))
            {
              $tab_name = $config->tab_name;
            }
          }
          if(isset($tab_names[$tab]) && !empty($tab_names[$tab]))
          {
            if($tab_names[$tab] != $tab_name && $tab_name != 'Step '.($tab + 1))
            {
              $tab_names[$tab] = $tab_name;
            }
          }
          else
          {
            $tab_names[$tab] = $tab_name;
          }
          switch ($entity->entity->system_name) {
            case 'varchar':
                $field = $this->initField($entity, 'text', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]) + [
                    'stringify' => $entity->stringify
                ];
                $entities[$tab][$field['column']][] = $field;
                break;
            case 'employees':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'employees', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                  ]);
                  break;
              case 'classification':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'classification', [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                  ]);
                  break;
              case 'groupassets':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'groupassets', [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                  ]);
                  break;
              case 'additionalsecurity':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'additionalsecurity', [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                  ]);
                  break;
              case 'relationshipMatrix':
              case 'relationshipMatrixMultiple':
              case 'relationshipMatrixDynamic':
              case 'relationshipMatrixExtra':
                  $entities[$tab][$entity->column][] = $this->initField($entity, $entity->entity->system_name, [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                      'registry',
                      'registry2',
                      'registry3',
                      'registryStringify',
                      'registry2Stringify',
                      'registry3Stringify',
                  ]);
                  break;
            case 'documents':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'documents', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
              break;
            case 'date':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'date', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
              break;
            case 'datetime':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'datetime', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
                break;
            case 'zbiory':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'zbiory', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
                break;
            case 'surveys':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'surveys', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
                break;
            case 'consent':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'consent', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
                break;
            case 'datagrid':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'datagrid', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                    'registry',
                ]);
                break;
            case 'files':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'file', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'multiple',
                    'role',
                ]);
                break;
            case 'text-ckeditor':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'textarea', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                ]);
                break;
            case 'select':
            case 'registry-entries':
                $entities[$tab][$entity->column][] = $this->initField($entity, 'select', [
                    'required',
                    'description',
                    'placeholder',
                    'className',
                    'access',
                    'subtype',
                    'maxlength',
                    'role',
                    'multiple',
                    'labelScheme',
                    'multiple',
                    'registry',
                    'registryStringify',
                ]);
                break;
              case 'number':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'number', [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'role',
                      'min',
                      'max',
                      'step',
                  ]);
                  break;
              case 'header':
                  $entities[$tab][$entity->column][] = $this->initField($entity, 'header', [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                      'position',
                  ]);
                  break;
              case 'smartRadioGroup':
              case 'smartMultiSelect':
                  $entities[$tab][$entity->column][] = $this->initField($entity, $entity->entity->system_name, [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'maxlength',
                      'role',
                      'registries',
                      'useRelations',
                      'editable',
                  ]);
                  break;
              case 'checkboxGroup':
              case 'radioGroup':
                  $type = strtolower(preg_replace('/([A-Z])/', '-$1',$entity->entity->system_name));
                  $entities[$tab][$entity->column][] = $this->initField($entity, $type, [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                      'inline',
                      'values',
                  ]);
                  break;
              case 'hyperlink':
                  $entities[$tab][$entity->column][] = $this->initField($entity, $entity->entity->system_name, [
                      'label',
                      'url',
                  ]);
                  break;
              case 'button':
              case 'autocomplete':
              case 'hidden':
              case 'paragraph':
              case 'moreInfo':
              case 'rating':
              case 'signature':
                  $entities[$tab][$entity->column][] = $this->initField($entity, $entity->entity->system_name, [
                      'required',
                      'description',
                      'placeholder',
                      'className',
                      'access',
                      'subtype',
                      'maxlength',
                      'role',
                  ]);
                  break;
          }
        }

        // var_dump($entities);

        $entities_tmp = array();

        foreach ($entities as $index => $tab) {
          $entities_tmp[$index] = json_encode(array_values($tab));
        }

        $registries = array_map(function($registry){
            return [
                'value' => $registry->id,
                'label' => htmlentities($registry->title, ENT_QUOTES, 'utf-8', FALSE),
                'data-fields' => json_encode(array_filter(array_map(function ($entity){
                    if (
                        empty($entity->entity->config_data->type) ||
                        $entity->entity->config_data->type !== 'string'
                    ) {
                        return null;
                    }
                    return [
                        'id' => $entity->id,
                        'title' => htmlentities($entity->title, ENT_QUOTES, 'utf-8', FALSE),
                        'stringify' => (boolean)$entity->stringify,
                    ];
                }, $registry->entities))),
            ];
        }, $this->registryModel->getListFull());
        array_unshift($registries, ['label' => 'Select', 'value' => '']);
        $this->view->entities = $entities_tmp;
        $this->view->registries_json = html_entity_decode(json_encode($registries));

        $this->view->tab_names = $tab_names;
        $this->view->id = $id;

    }

    protected function initField ($entity, $type,  array $keys = []) {
        $commonTriggerKeys = ['enableIf', 'visibleIf'];
        $field = [
            'id' => $entity->id,
            'type' => $type,
            'label' => addslashes($entity->title),
            'value' => addslashes($entity->default_value),
            'name' => addslashes($entity->name),
            'column' => $entity->column,
            'system_name' => $entity->system_name,
            'setPrimary' => $entity->set_primary
        ];
        if($multiform_data = Application_Service_Entities::decodeMultiformData($entity)){
            foreach ($keys as $key) {
                if(isset($multiform_data->$key)) {
                    $field[$key] = $multiform_data->$key;
                }
            }
            foreach ($commonTriggerKeys as $key) {
                $field[$key] = addslashes($multiform_data->$key);
            }
        }
        return $field;
    }

    public function saveAction()
    {

      $registryId = $this->getParam('id');
      $params = array();

      $param1 = $this->_request->getParam('data');
      $tab_names = $this->_request->getParam('names');
	    $columnCnt = $this->_request->getParam('columnNumber');
      $tab_names = array_map("strip_tags", $tab_names);
      /** @var Application_Model_RegistryEntities $registryEntitiesRepository */
      $registryEntitiesRepository = Application_Service_Utilities::getModel('RegistryEntities');
      $user_id = Application_Service_Authorization::getInstance()->getUserId();
      $module_id_val = $this->registryModel->getRegistryById($registryId);
      $user_name = $this->osoby->requestObject($user_id);
      $this->registryActionModel = Application_Service_Utilities::getModel('RegistryAction');

      /*
      var_dump($param1);
      $flipped_array =  array_flip($param1);
      $array_data = array_keys($flipped_array);
      $filtered_array =  array_filter($array_data);
      $tabs_arr = array_values($filtered_array);
      */
      $existing_ids = array(0);
      $fieldCounter = 0;
      foreach($param1 as $tabkey => $tabval_str) {
        $tabval = json_decode($tabval_str, true);
        foreach($tabval as $column  => $value) {
          $keyval = $tabkey;
          $someArray = $value;
          foreach ($someArray as $key => $value) {
            $array = array();
            $array['title'] =  strip_tags($value["label"]);
            $array['is_multiple'] = !empty($value['multiple'])?1:0;
            $array['default_value'] = !empty($value['value'])?$value['value']:'';
            $array['set_primary'] = !empty($value['setPrimary'])?1:0;
            $array['registry_id'] = $registryId;
            $array['multiform_data'] = json_encode($value);
            $array['column'] = $column?$column:0;
            $array['name'] = $value['name'];
            $array['order'] = ++$fieldCounter;


             if(isset($value['id']) && !empty($value['id']) && $value['id'] > 0) {
              $array['id'] = $value['id'];
              $existing_ids[] = $value['id'];
             } else {
              $array['id'] = '';
              if ($id = $registryEntitiesRepository->getIdByUniqueName($array['registry_id'], $array['name']?:'')) {
                 $existing_ids[] = $array['id'] = $id;
              }
             }
            switch($value["type"])
            {
                case 'text': {
                  switch ($value["subtype"])
                  {
                      case 'text':
                            $array['entity_id'] = Application_Model_Entities::ID_VARCHAR;
                            $array['stringify'] = (int) !empty($value['stringify']);
                            $array['config'] = array(
                                  "tab" => $keyval,
                                  "tab_name" => $tab_names[$keyval],
								                  "column_cnt" => $columnCnt
                                );
                        break;

                      case 'password':
                           $array['entity_id'] = Application_Model_Entities::ID_VARCHAR;
                           $array['config'] = array(
                                  "tab" => $keyval,
                                  "tab_name" => $tab_names[$keyval],
								                  "column_cnt" => $columnCnt
                                );
                        break;
                    }
                    break;
                }
                case 'autocomplete':
                    $array['entity_id'] = Application_Model_Entities::ID_AUTO_COMPLETE;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'button':
                    $array['entity_id'] = Application_Model_Entities::ID_BUTTON;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'checkbox-group':
                    $array['entity_id'] = Application_Model_Entities::ID_CHECKBOX_GROUP;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'header':
                    $array['entity_id'] = Application_Model_Entities::ID_HEADER;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'hidden':
                    $array['entity_id'] = Application_Model_Entities::ID_HIDDEN;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'number':
                    $array['entity_id'] = Application_Model_Entities::ID_NUMBER;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "min" => !empty($value['min'])?$value['min']:0,
                        "max" => !empty($value['max'])?$value['max']:'',
                        "step" => !empty($value['step'])?$value['step']:1,
                    );
                    break;
                case 'paragraph':
                    $array['entity_id'] = Application_Model_Entities::ID_PARAGRAPH;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'moreInfo':
                    $array['entity_id'] = Application_Model_Entities::ID_MORE_INFO;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'radio-group':
                    $array['entity_id'] = Application_Model_Entities::ID_RADIO_GROUP;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'rating':
                    $array['entity_id'] = Application_Model_Entities::ID_RATING;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'signature':
                    $array['entity_id'] = Application_Model_Entities::ID_SIGNATURE;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'smartRadioGroup':
                    $array['entity_id'] = Application_Model_Entities::ID_SMART_RADIO;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'smartMultiSelect':
                    $array['entity_id'] = Application_Model_Entities::ID_SMART_MULTI_SELECT;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        "column_cnt" => $columnCnt
                    );
                    break;
                case 'employees':
                                $array['entity_id'] = Application_Model_Entities::ID_EMPLOYEE;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
										                  "column_cnt" => $columnCnt
                                    );
                                break;
                case 'classification':
                                $array['entity_id'] = Application_Model_Entities::ID_CLASSIFICATION;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
                                      "noNextFocus" => true,
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'groupassets':
                                $array['entity_id'] = Application_Model_Entities::ID_CLASSIFICATION;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
                                      "noNextFocus" => true,
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'additionalsecurity':
                                $array['entity_id'] = Application_Model_Entities::ID_ADDITIONAL_SECURITY;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
                                      "noNextFocus" => true,
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'relationshipMatrix':
                case 'relationshipMatrixMultiple':
                    $array['entity_id'] = $value["type"]==='relationshipMatrix'
                        ? Application_Model_Entities::ID_RELATION_MATRIX
                        : Application_Model_Entities::ID_RELATION_MATRIX_MULTIPLE;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        'registry_id' => $value['registry'],
                        'registry_stringify' => $value['registryStringify'],
                        'registry2_id' => $value['registry2'],
                        'registry2_stringify' => $value['registry2Stringify'],
                    );
                    break;
                case 'relationshipMatrixDynamic':
                    $array['entity_id'] = Application_Model_Entities::ID_RELATION_MATRIX_DYNAMIC;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                    );
                    break;
                case 'relationshipMatrixExtra':
                    $array['entity_id'] = Application_Model_Entities::ID_RELATION_MATRIX_EXTRA;
                    $array['config'] = array(
                        "tab" => $keyval,
                        "tab_name" => $tab_names[$keyval],
                        'registry_id' => $value['registry'],
                        'registry_stringify' => $value['registryStringify'],
                        'registry2_id' => $value['registry2'],
                        'registry2_stringify' => $value['registry2Stringify'],
                        'registry3_id' => $value['registry3'],
                        'registry3_stringify' => $value['registry3Stringify'],
                    );
                    break;
                case 'documents':
                                $array['entity_id'] = Application_Model_Entities::ID_DOCUMENT;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'date':
                case 'date1':
                                $array['entity_id'] = Application_Model_Entities::ID_DATE;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'datetime':
                                $array['entity_id'] = Application_Model_Entities::ID_DATETIME;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'zbiory':
                                $array['entity_id'] = Application_Model_Entities::ID_COLLECTION;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'consent':
                                $array['entity_id'] = Application_Model_Entities::ID_CONSENT;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'surveys':
                                $array['entity_id'] = Application_Model_Entities::ID_SURVEY;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'datagrid':
                                $array['entity_id'] = Application_Model_Entities::ID_DATAGRID;
                                $array['config'] = array(
                                      "tab" => $keyval,
                                      "tab_name" => $tab_names[$keyval],
                                      'registry_id' => $value['registry'],
								                      "column_cnt" => $columnCnt
                                    );
                                break;
                case 'file':
                                switch ($value["subtype"])
                                {
                                  case 'file':
                                        $array['entity_id'] = Application_Model_Entities::ID_FILE;
                                        $array['config'] = array(
                                              "tab" => $keyval,
                                              "tab_name" => $tab_names[$keyval],
								  "column_cnt" => $columnCnt
                                            );
                                    break;

                                }
                                break;
                case 'textarea':

                  switch ($value["subtype"]) {
                    case 'textarea':
                          $array['entity_id'] = Application_Model_Entities::ID_TEXT_AREA;
                          $array['config'] = array(
                                "tab" => $keyval,
                                "tab_name" => $tab_names[$keyval],
								                "column_cnt" => $columnCnt
                              );
                      break;

                    default:
                         $array['entity_id'] = Application_Model_Entities::ID_TEXT_AREA;
                         $array['config'] = array(
                                "tab" => $keyval,
                                "tab_name" => $tab_names[$keyval],
								                "column_cnt" => $columnCnt
                              );
                      break;
                  }
                  break;
              case 'select':
                $array['entity_id'] = Application_Model_Entities::ID_RELATION_SELECT;
                if($value["multiple"]==true){
                  $array['is_multiple'] = 1;
                }

                $array['config'] = array(
                  "tab" => $keyval,
                  "tab_name" => $tab_names[$keyval],
                  'registry_id' => $value['registry'],
                  'registry_stringify' => $value['registryStringify'],
                );
                break;
              case 'hyperlink':
                  $array['entity_id'] = Application_Model_Entities::ID_HYPERLINK;
                  $array['config'] = [
                      "tab" => $keyval,
                      "tab_name" => $tab_names[$keyval],
                      'label' => $value['label'],
                      'url' => $value['url'],
                  ];
                  break;
              default:
                $array['entity_id'] = Application_Model_Entities::ID_VARCHAR;
                $array['config'] = array(
                  "tab" => $keyval,
                  "tab_name" => $tab_names[$keyval],
				  "column_cnt" => $columnCnt
                );
                break;
              }

              array_push($params,$array);
            }
          }
        }

      $removed_entities = $registryEntitiesRepository->getRemovedEntities($registryId, $existing_ids);

      foreach ($removed_entities as $entity_row) {
        $row = $registryEntitiesRepository->getOne([
          'id' => $entity_row['id'],
          'registry_id' => $registryId,
        ], true);
        $registryEntitiesRepository->removeEntity($row);
        $count_array['user_id'] = $user_id;
        $count_array['module_id']  = $entity_row['registry_id'];
        $count_array['controller'] = 'RegistryMultiFormBuilder';
        $count_array['action'] = 'usuń pole';
        $count_array['field'] = 'pole';
        $count_array['action_name'] = $entity_row['title'];
        $count_array['previous_value'] = '';
        $count_array['new_value'] = $entity_row['default_value'];
        $count_array['module_id_value'] = $module_id_val['title'];
        $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
       
        $this->registryActionModel->save($count_array);
      }
      $this->registryModel = Application_Service_Utilities::getModel('Registry');
      foreach ($params as $data)
      {
        $registry = $this->registryModel->getFull($data['registry_id'], true);
        foreach($registry->entities  as $key_id => $val) {     
          if($val->id == $data['id']) {
            if($val->title !=  $data['title']) {
              $count_array['user_id'] = $user_id;
              $count_array['module_id']  = $data['registry_id'];
              $count_array['controller'] = 'RegistryMultiFormBuilder';
              $count_array['action'] = 'field update';
              $count_array['field'] = 'pole';
              $count_array['action_name'] = $data['title'];
              $count_array['previous_value'] = '';
              $count_array['new_value'] = $data['default_value'];
              $count_array['module_id_value'] = $module_id_val['title'];
              $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
              $this->registryActionModel->save($count_array);
            }
          }
        }
        try {
            $this->db->beginTransaction();

            $mode = empty($data['id'])
                ? 'create'
                : 'update';
            $data['config_data'] = json_encode($data['config']);
            $param = $registryEntitiesRepository->save($data);
            if($data['id'] == "") {
              $count_array['user_id'] = $user_id;
              $count_array['module_id']  = $data['registry_id'];
              $count_array['controller'] = 'RegistryMultiFormBuilder';
              $count_array['action'] = 'Utwórz pole';
              $count_array['field'] = 'pole';
              $count_array['action_name'] = $data['title'];
              $count_array['previous_value'] = '';
              $count_array['new_value'] = $data['default_value'];
              $count_array['module_id_value'] = $module_id_val['title'];
              $count_array['user_id_value'] = $user_name['imie'] . ' ' . $user_name['nazwisko'];
              $this->registryActionModel->save($count_array);
            }
            Application_Service_Events::getInstance()->trigger(sprintf('registry.param.%s', $mode), $param);

                    $this->db->commit();
                    $status = true;
                    $message = 'Zapisano parametr';
                    }
                    catch (Exception $e)
                    {
                        //var_dump($e->getMessage());
                    }
                }
     exit();

    }




}
