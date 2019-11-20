<?php

class Application_Service_Entities
{
    const CACHE_PREFIX = 'registry_values_render_';

    /**
     * @var Zend_Cache_Core
     */
     public $cache;


    /** @var self */
    protected static $_instance = null;

    public $view;
    public $template;
    public $attributes;

    private function __clone() {}
    public static function getInstance() {
       // echo "fdf";exit();
     return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance;
      }
    
    function __construct()
    {
        
        // $this->cache = Zend_Cache::factory(
        //     'Core',
        //     'File',
        //     array(
        //         'automatic_serialization' => true
        //     ),
        //     array(
        //         'cache_dir' => ROOT_PATH . '/tempcache'
        //     )
        // ); 

        $this->view = new stdClass();
        $this->template = '';
    }

    public static function getEntityParams($registryEntity, $data)
    {
        vd('render start');
        $config = $registryEntity->entity->config_data;
        vd($config, $registryEntity);
        if (!$config->widget && !$config->element) {
            throw new Exception('Error', 500);
        }

        $service = self::getInstance();
        $id = sprintf('element_%s', $registryEntity->id);

        $entityData = $data->entities[$registryEntity->id];
		//$options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['registry_id' => $registryEntity->id]);
		//print_r($options);exit;
        //TODO dump code
        $config = isset($registryEntity->entity->config_data->element) ? $registryEntity->entity->config_data->element : $registryEntity->entity->config_data->widget;



        $configArray = json_decode(json_encode($config), true);
        $registeryConfig = json_decode($registryEntity->config);

        $attributes = array_replace_recursive([
            'name' => $id,
            'widget' => [
                'process' => $config->processUrl,
                'dialUrl' => $config->dialUrl,
                'source' => $config->sourceUrl,
            ],
            'label' => $registryEntity->title,
            'multiple' => $registryEntity->is_multiple,
            'dataValue' => ['dataNextFocus' =>  (isset($registryEntity->config_data->noNextFocus))? '0' : '1'],
            'tab' => $registryEntity->config{7},
			'colNo'=>$registeryConfig->column_cnt
        ], $configArray);

        if ($config->options) {

            $attributes['widget']['options'] = Application_Service_Utilities::getModel($config->options->model)->{$config->options->function}();
            $attributes['widget']['enableJs'] = true;
            $attributes['widget']['storage'] = $id;
        }
        $multiformData = self::decodeMultiformData($registryEntity);
        $attributes['multiformName'] = $multiformData->name;
        foreach (['enableIf', 'visibleIf'] as $expressionKey) {
            if (!isset($multiformData->$expressionKey)) {
                continue;
            }
            $attributes['widget'][$expressionKey] = $multiformData->$expressionKey;
        }

        if ($registryEntity->entity->config_data->type === 'datagrid') {
            $attributes['registry_id'] = $registryEntity->config_data->registry_id;
        } else if ($registryEntity->entity->config_data->type === 'entry') {

            $options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['registry_id' => $registryEntity->config_data->registry_id]);
            $attributes['options'] = [];

            if (isset($registryEntity->config_data->label_schema)) {

                Application_Service_Registry::getInstance()->entriesGetEntities($options);
				$labelName = strtolower(str_replace(" ", "_", $registryEntity->config_data->label_schema));

                foreach ($options as $option) {
					$attributes['options'][] = [
                        'id' => $option['id'],
                        //'name' => Application_Service_Utilities::stempl($registryEntity->config_data->label_schema, $option['entities_named']),
						'name' => $option['entities_named'][$labelName]['value']
                    ];
                }
            } else {

                foreach ($options as $option) {
                    $attributes['options'][] = [
                        'id' => $option['id'],
                        'name' => $option['name'],
                    ];
                }
            }
        } elseif ($registryEntity->entity->config_data->type === 'file') {

            if ($attributes['multiple']) {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id')]);
            } else {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => [$entityData->value]]);
            }
        }else if ( !empty($multiformData) && $multiformData->type == 'select') {
            $attributes['options'] = [];
            $service = new Application_Service_RegistryEntries();
            if (!empty($registryEntity->config_data->registry_id)) {
                $attributes['options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry_id,
                    $multiformData->registryStringify ?: []
                );
            }
        }else if ( !empty($multiformData) && in_array($multiformData->type, ['checkbox-group', 'radio-group'])) {
            $attributes['options'] = [];
            $attributes['inline'] = (isset($multiformData->inline)) ? $multiformData->inline: 0;
			$attributes['toggle'] = (isset($multiformData->toggle)) ? $multiformData->toggle : 0;
			$attributes['multiple'] = $multiformData->type === 'checkbox-group';
			foreach ($multiformData->values as $option) {
				$attributes['options'][] = [
					'id' => $option->value,
					'name' => $option->label,
					'selected' => !empty($option->selected),
				];
			}
        }

        $service->view->element = $attributes;
        $service->view->data = $entityData;

        if ($attributes['multiple']) {

            $attributes['value'] = Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id');

        } else {

            if ($entityData instanceof Application_Service_EntityRow) {
                $attributes['value'] = $entityData->value;
            } else {
                $attributes['value'] = $entityData;
            }
        }

        if($attributes['tag'] == 'bs.datagrid' && !empty($attributes['value']))
        {
            $attributes['value'] = unserialize($attributes['value']);
            $options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['id IN (?)' => implode(',', $attributes['value'])]);
            foreach($attributes['value'] as $registryEntryId){
                $row = Application_Service_Utilities::getModel('RegistryEntries')->getOne([
                    'id' => $registryEntryId,
                    ], true);
                $entities = Application_Service_Registry::getInstance()->entryGetEntities($row);



                foreach ($row->entities as $key=>$val) {
                    if(!isset($entries[$val->entity->registry->id])){
                        $registryEntities = Application_Service_Utilities::getModel('RegistryEntities')->getList(['registry_id = ?' => $val->entity->registry->id]);
                        foreach($registryEntities as $entities){

                            $entries[$val->entity->registry->id][$registryEntryId][$entities->system_name]['title'] = $entities->title;
                        }
                        $registryData[$val->entity->registry->id] = $val->entity->registry->title;
                    }
                    $entries[$val->entity->registry->id][$registryEntryId][$val->entity->system_name]['value'] = $val->value;
                }

            }

            $attributes['options'] = $entries;
        }
        if ($attributes['tag'] === 'bs.header') {
            foreach (['subtype', 'position'] as $key) {
                if(isset($multiformData->$key)) {
                    $attributes[$key] = $multiformData->$key;
                }
            }
        }
        if ($attributes['tag'] === 'bs.number') {
            foreach (['min', 'max', 'step'] as $key) {
                if(isset($multiformData->$key)) {
                    $attributes[$key] = $multiformData->$key;
                }
            }
        }
        if (in_array($attributes['tag'], ['bs.relationshipMatrix', 'bs.relationshipMatrixMultiple', 'bs.relationshipMatrixDynamic', 'bs.relationshipMatrixExtra'])) {
            $attributes['row_options'] = $attributes['col_options'] = $attributes['item_options'] = $attributes['all_options'] = [];
            $service = new Application_Service_RegistryEntries();
            if (!empty($registryEntity->config_data->registry_id)) {
                $attributes['row_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry_id,
                    $multiformData->egistryStringify ?: []
                );
            }
            if (!empty($registryEntity->config_data->registry2_id)) {
                $attributes['col_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry2_id,
                    $multiformData->registry2Stringify ?: []
                );
            }
            if (!empty($registryEntity->config_data->registry3_id)) {
                $attributes['item_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry3_id,
                    $multiformData->registry3Stringify ?: []
                );
            }
            if ($attributes['tag'] === 'bs.relationshipMatrixDynamic') {
                $attributes['all_options'] = json_encode($service->getAllEntities());
            }
        }
        if (in_array($attributes['tag'], ['bs.smartRadioGroup', 'bs.smartMultiSelect'])) {
            $registryIds = !empty($multiformData->registries) ? explode(',', $multiformData->registries) : [];
            $service = new Application_Service_RegistryEntries();
            $attributes['editable'] = !empty($multiformData->editable);
            try {
                if (empty($registryIds)) {
                    $attributes['all_options'] = array();
                } else {
                    $attributes['all_options'] = $service->getAllEntitiesAsArray($registryIds);
                }
                
            } catch (Exception $e) {
                $attributes['all_options'] = [];
            }
        }
        if ($attributes['tag'] === 'bs.hyperlink') {
            $attributes['url'] = $multiformData->url;
        }
        if ($attributes['tag'] === 'bs.moreInfo') {
            $attributes['description'] = $multiformData->description;
        }


        $params = [
            'tag' => $attributes['tag'],
            'attributes' => $attributes,
        ];
        vc('renderElement', $registryEntity, $params, $service);

        return $params;
    }

    public function render($registryEntity, $data)
    {
        vd('render start');
        $config = $registryEntity->entity->config_data;
        vd($config, $registryEntity);
        if (!$config->widget && !$config->element) {
            throw new Exception('Error', 500);
        }
        $cache = Zend_Cache::factory(
            'Core',
            'File',
            array(
                'automatic_serialization' => true
            ),
            array(
                'cache_dir' => ROOT_PATH . '/cache'
            )
        );
        // $cache_file_name = "renderHtmlContent_".$registryEntity->id;
        // if ( ($cached_renderHtmlContent = $cache->load($cache_file_name)) === false ) {
        //     $renderHtmlContent = self::renderHtml($registryEntity, $data);
        //     $cache->save($renderHtmlContent, $cache_file_name);
        // } else {
        //     $renderHtmlContent = $cached_renderHtmlContent;
        // }
        return self::renderHtml($registryEntity, $data);
        // return $renderHtmlContent;
    }

    public static function renderText($registryEntity, $data)
    {


        vd('render start');
        $service = self::getInstance();
        $config = $registryEntity->entity->config_data;
        vd($config, $registryEntity);


        return self::renderHtmlText($registryEntity, $data);

    }

    public static function renderElement($registryEntity, $data)
    {
        $service = self::getInstance();
        $config = $registryEntity->entity->config_data;
        $id = sprintf('element_%s', $registryEntity->id);
        $entityData = $data->entities[$registryEntity->id];

        $element = [
            'name' => $id,
            'id' => $id,
            'class' => $config->element->class,
            'source' => $config->element->sourceUrl,
            'label' => $registryEntity->title,
            'options' => null,
            'enableJs' => false,
            'multiple' => $registryEntity->is_multiple
        ];

        switch ($config->element->tag) {
            case "textarea":
                $service->template = 'bs.textarea';
                break;
            case "input":
                $service->template = 'bs.varchar';
                switch ($config->element->type) {
                    case "text":
                        break;
                    case "date":
                        $service->attributes['datepicker'] = true;
                        break;
                    case "datetime":
                        $service->attributes['datepicker'] = [
                            'type' => 'datetime',
                        ];
                        break;
                    default:
                        Throw new Exception('Unsupported', 500);
                }
                $service->template = 'bs.varchar';
                break;
            default:
                vdie($config, $registryEntity, $data);
                Throw new Exception('Unsupported', 500);
        }

        $service->view->data = $entityData;
        $service->view->element = $element;
    }

    public static function renderWidget($registryEntity, $data)
    {
        $config = $registryEntity->entity->config_data;
        $id = sprintf('element_%s', $registryEntity->id);
        $entityData = $data->entities[$registryEntity->id];

        $element = [
            'name' => $id,
            'id' => $id,
            'class' => $config->widget->class,
            'source' => $config->widget->sourceUrl,
            'process' => $config->widget->processUrl,
            'dialUrl' => $config->widget->dialUrl,
            'label' => $registryEntity->title,
            'options' => null,
            'enableJs' => false,
            'multiple' => 0
        ];

        if ($config->widget->options) {
            $element['options'] = Application_Service_Utilities::getModel($config->widget->options->model)->{$config->widget->options->function}();
            $element['enableJs'] = true;
            $element['storage'] = $id;
        }

        $service = self::getInstance();
        $service->view->element = $element;
        $service->view->data = $entityData;
        $service->template = 'widget.typeahead-full';
    }

    public static function renderHtml($registryEntity, $data)
    {
        $service = self::getInstance();
        $id = sprintf('element_%s', $registryEntity->id);

        $entityData = $data->entities[$registryEntity->id];
		//$options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['registry_id' => $registryEntity->id]);
		//print_r($options);exit;
        //TODO dump code
        $config = isset($registryEntity->entity->config_data->element) ? $registryEntity->entity->config_data->element : $registryEntity->entity->config_data->widget;



        $configArray = json_decode(json_encode($config), true);
        $registeryConfig = json_decode($registryEntity->config);

        $attributes = array_replace_recursive([
            'name' => $id,
            'widget' => [
                'process' => $config->processUrl,
                'dialUrl' => $config->dialUrl,
                'source' => $config->sourceUrl,
            ],
            'label' => $registryEntity->title,
            'multiple' => $registryEntity->is_multiple,
            'dataValue' => ['dataNextFocus' =>  (isset($registryEntity->config_data->noNextFocus))? '0' : '1'],
            'tab' => $registryEntity->config{7},
			'colNo'=>$registeryConfig->column_cnt
        ], $configArray);

        if ($config->options) {

            $attributes['widget']['options'] = Application_Service_Utilities::getModel($config->options->model)->{$config->options->function}();
            $attributes['widget']['enableJs'] = true;
            $attributes['widget']['storage'] = $id;
        }
        $multiformData = self::decodeMultiformData($registryEntity);
        
        $attributes['multiformName'] = $multiformData->name;
        foreach (['enableIf', 'visibleIf'] as $expressionKey) {
            if (!isset($multiformData->$expressionKey)) {
                continue;
            }
            $attributes['widget'][$expressionKey] = $multiformData->$expressionKey;
        }

        if ($registryEntity->entity->config_data->type === 'datagrid') {
            $attributes['registry_id'] = $registryEntity->config_data->registry_id;
        } else if ($registryEntity->entity->config_data->type === 'entry') {

            $options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['registry_id' => $registryEntity->config_data->registry_id]);
            $attributes['options'] = [];

            if (isset($registryEntity->config_data->label_schema)) {

                Application_Service_Registry::getInstance()->entriesGetEntities($options);
				$labelName = strtolower(str_replace(" ", "_", $registryEntity->config_data->label_schema));

                foreach ($options as $option) {
					$attributes['options'][] = [
                        'id' => $option['id'],
                        //'name' => Application_Service_Utilities::stempl($registryEntity->config_data->label_schema, $option['entities_named']),
						'name' => $option['entities_named'][$labelName]['value']
                    ];
                }
            } else {

                foreach ($options as $option) {
                    $attributes['options'][] = [
                        'id' => $option['id'],
                        'name' => $option['name'],
                    ];
                }
            }
        } elseif ($registryEntity->entity->config_data->type === 'file') {

            if ($attributes['multiple']) {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id')]);
            } else {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => [$entityData->value]]);
            }
        }else if ( !empty($multiformData) && $multiformData->type == 'select') {
            $attributes['options'] = [];
            $service = new Application_Service_RegistryEntries();
            if (!empty($registryEntity->config_data->registry_id)) {
                $attributes['options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry_id,
                    $multiformData->registryStringify ?: []
                );
            }
        }else if ( !empty($multiformData) && in_array($multiformData->type, ['checkbox-group', 'radio-group'])) {
            $attributes['options'] = [];
            $attributes['inline'] = (isset($multiformData->inline)) ? $multiformData->inline: 0;
			$attributes['toggle'] = (isset($multiformData->toggle)) ? $multiformData->toggle : 0;
			$attributes['multiple'] = $multiformData->type === 'checkbox-group';
			foreach ($multiformData->values as $option) {
				$attributes['options'][] = [
					'id' => $option->value,
					'name' => $option->label,
					'selected' => !empty($option->selected),
				];
			}
        }

        $service->view->element = $attributes;
        $service->view->data = $entityData;

        if ($attributes['multiple']) {

            $attributes['value'] = Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id');

        } else {

            if ($entityData instanceof Application_Service_EntityRow) {
                $attributes['value'] = $entityData->value;
            } else {
                $attributes['value'] = $entityData;
            }
        }

        if($attributes['tag'] == 'bs.datagrid' && !empty($attributes['value']))
        {
            $attributes['value'] = unserialize($attributes['value']);
            $options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['id IN (?)' => implode(',', $attributes['value'])]);
            foreach($attributes['value'] as $registryEntryId){
                $row = Application_Service_Utilities::getModel('RegistryEntries')->getOne([
                    'id' => $registryEntryId,
                    ], true);
                $entities = Application_Service_Registry::getInstance()->entryGetEntities($row);



                foreach ($row->entities as $key=>$val) {
                    if(!isset($entries[$val->entity->registry->id])){
                        $registryEntities = Application_Service_Utilities::getModel('RegistryEntities')->getList(['registry_id = ?' => $val->entity->registry->id]);
                        foreach($registryEntities as $entities){

                            $entries[$val->entity->registry->id][$registryEntryId][$entities->system_name]['title'] = $entities->title;
                        }
                        $registryData[$val->entity->registry->id] = $val->entity->registry->title;
                    }
                    $entries[$val->entity->registry->id][$registryEntryId][$val->entity->system_name]['value'] = $val->value;
                }

            }

            $attributes['options'] = $entries;
        }
        if ($attributes['tag'] === 'bs.header') {
            foreach (['subtype', 'position'] as $key) {
                if(isset($multiformData->$key)) {
                    $attributes[$key] = $multiformData->$key;
                }
            }
        }
        if ($attributes['tag'] === 'bs.number') {
            foreach (['min', 'max', 'step'] as $key) {
                if(isset($multiformData->$key)) {
                    $attributes[$key] = $multiformData->$key;
                }
            }
        }
        
        if (in_array($attributes['tag'], ['bs.relationshipMatrix', 'bs.relationshipMatrixMultiple', 'bs.relationshipMatrixDynamic', 'bs.relationshipMatrixExtra'])) {
            $attributes['row_options'] = $attributes['col_options'] = $attributes['item_options'] = $attributes['all_options'] = [];
            $service = new Application_Service_RegistryEntries();
            if (!empty($registryEntity->config_data->registry_id)) {
                $attributes['row_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry_id,
                    $multiformData->egistryStringify ?: []
                );
            }
            if (!empty($registryEntity->config_data->registry2_id)) {
                $attributes['col_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry2_id,
                    $multiformData->registry2Stringify ?: []
                );
            }
            if (!empty($registryEntity->config_data->registry3_id)) {
                $attributes['item_options'] = $service->getRegistryEntities(
                    $registryEntity->config_data->registry3_id,
                    $multiformData->registry3Stringify ?: []
                );
            }
            if ($attributes['tag'] === 'bs.relationshipMatrixDynamic') {
                $attributes['all_options'] = json_encode($service->getAllEntities());
            }
        }
        if (in_array($attributes['tag'], ['bs.smartRadioGroup', 'bs.smartMultiSelect'])) {
            $registryIds = !empty($multiformData->registries) ? explode(',', $multiformData->registries) : [];
            $service = new Application_Service_RegistryEntries();
            $attributes['editable'] = !empty($multiformData->editable);
            try {
                if (empty($registryIds)) {
                    $attributes['all_options'] = array();
                } else {
                    $attributes['all_options'] = $service->getAllEntitiesAsArray($registryIds);
                }
                
            } catch (Exception $e) {
                $attributes['all_options'] = [];
            }
        }
        if ($attributes['tag'] === 'bs.hyperlink') {
            $attributes['url'] = $multiformData->url;
        }
        if ($attributes['tag'] === 'bs.moreInfo') {
            $attributes['description'] = $multiformData->description;
        }


        $params = [
            'tag' => $attributes['tag'],
            'attributes' => $attributes,
        ];
        vc('renderElement', $registryEntity, $params, $service);

        return Application_Service_HtmlHelper::renderElement($params);
    }

    /**
     * @param $registryEntity
     * @return object|null
     */
    public static function decodeMultiformData($registryEntity)
    {
        if (!empty($registryEntity->multiform_data)) {
            return json_decode($registryEntity->multiform_data);
        }
        return null;
    }

    public static function renderHtmlText($registryEntity, $data)
    {

        $service = self::getInstance();
        $id = sprintf('element_%s', $registryEntity->id);
        $entityData = $data->entities[$registryEntity->id];

        //TODO dump code
        $config = isset($registryEntity->entity->config_data->element) ? $registryEntity->entity->config_data->element : $registryEntity->entity->config_data->widget;

     //  print_r($config);exit();

        $configArray = json_decode(json_encode($config), true);

        $attributes = array_replace_recursive([
            'name' => $id,
            'widget' => [
                'process' => $config->processUrl,
                'dialUrl' => $config->dialUrl,
                'source' => $config->sourceUrl,
            ],
            'label' => $registryEntity->title,
            'multiple' => $registryEntity->is_multiple,
            'tab' => $registryEntity->config{7}
        ], $configArray);

        if ($config->options) {

            $attributes['widget']['options'] = Application_Service_Utilities::getModel($config->options->model)->{$config->options->function}();
            $attributes['widget']['enableJs'] = true;
            $attributes['widget']['storage'] = $id;
        }


        if ($registryEntity->entity->config_data->type === 'entry') {
            $options = Application_Service_Utilities::getModel('RegistryEntries')->getList(['registry_id' => $registryEntity->config_data->registry_id]);
            $attributes['options'] = [];

            if ($registryEntity->config_data->label_schema) {
                Application_Service_Registry::getInstance()->entriesGetEntities($options);
                foreach ($options as $option) {
                    $attributes['options'][] = [
                        'id' => $option['id'],
                        'name' => Application_Service_Utilities::stempl($registryEntity->config_data->label_schema, $option['entities_named']),
                    ];
                }
            } else {
                foreach ($options as $option) {
                    $attributes['options'][] = [
                        'id' => $option['id'],
                        'name' => $option['name'],
                    ];
                }
            }
        } elseif ($registryEntity->entity->config_data->type === 'file') {

            if ($attributes['multiple']) {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id')]);
            } else {
                $attributes['current_files'] = Application_Service_Utilities::getModel('Files')->getList(['id IN (?)' => [$entityData->value]]);
            }
        }

        // ??
        //echo "<pre>";  print_r( $attributes);
        $service->view->element = $attributes;
        $service->view->data = $entityData;

        if ($attributes['multiple']) {
            $attributes['value'] = Application_Service_Utilities::getIndexedBy($entityData, 'value', 'id');

        } else {
            if ($entityData instanceof Application_Service_EntityRow) {
                $attributes['value'] = $entityData->value;
            } else {
                $attributes['value'] = $entityData;
            }
        }


        $params = [
            'tag' => $attributes['tag'],
            'attributes' => $attributes,
        ];
        //echo "<pre>";
        //print_r($params);

        vc('renderElement', $registryEntity, $params, $service);


        //self::renderView($params);
        return Application_Service_HtmlHelper::renderElementText($params);
    }

    public function renderView($params) {
        //print_r($attribute);
        /*echo "<pre>";
        print_r($params['attributes']['label']);
        print_r($params['attributes']['value']);*/
        echo '<div class="row">';
            echo '<div class="col-sm-4">';
                echo '<label>' . $params['attributes']['label'] . '</label>';
            echo '</div>';
            echo '<div class="col-sm-8">';
            if(is_array($params['attributes']['value'])) {
                foreach ($params['attributes']['value'] as $value) {
                    echo '<p>' . $value . '&nbsp;&nbsp;&nbsp;</p>';
                }
            } else {
                echo '<p>' . $params['attributes']['value'] . '&nbsp;&nbsp;</p>';
            }
            echo '</div>';
        echo '</div>';
    }

}
