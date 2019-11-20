<?php

require_once(APPLICATION_PATH . '/../library/simplehtmldom/simple_html_dom.php');

class RegistryFormBuilderController extends Muzyka_Admin
{

    protected $html;

    public function init()
    {
        parent::init();
        $this->registry = Application_Service_Utilities::getModel('Registry');

        // $this->disableLayout();
         $this->_helper->_layout->setLayout('formbuilder');
         
    }

    public function indexAction()
    {
        $id = $this->getParam('id');
        $this->view->id = $id;
    }

    public function saveAction()
    {   
        $array['registry_id'] = $this->getParam('id');
        $data = $this->getRequest()->getPost();
        $dom = new simple_html_dom();
        $dom_inner = new simple_html_dom();
        // Load HTML from a string
        $dom->load($data['value']);
        $params = array();
        $inputs = $dom->find('div[class="control-group"]'); 
            $flag = 0;
            foreach ($inputs as $input)
            {
                $dom_inner->load($input->innertext);

                $label = $dom_inner->find('label[class="control-label"]');
                $field = $dom_inner->find('div[class="controls"]');
                $array['title'] = $label[0]->innertext; 
                $array['id'] = '';
                $array['is_multiple'] = 0;
                $array['default_value'] = '';
             
                switch($field[0]->first_child()->tag)
                {
                    case 'input': 
                                  switch ($field[0]->first_child()->type)
                                  {
                                    case 'text':  
                                          $array['default_value'] =  $field[0]->placeholder;
                                          $array['entity_id'] = 1;
                                      break;
                                    
                                    case 'password':
                                         $array['entity_id'] = 1;
                                      break;
                                    
                                    case 'radio':
                                         $array['entity_id'] = 1;
                                      break;

                                    case 'checkbox':
                                         $array['entity_id'] = 14;
                                      break;

                                    case 'file':
                                         $array['entity_id'] = 13;
                                      break;

                                    default:
                                         $array['entity_id'] = 1;
                                      break;
                                  }

                    case 'textarea':
                            $array['entity_id'] = 2;
                            break;

                    case 'select':
                                  if(isset($field[0]->first_child()->multiple))
                                    { 
                                      $array['is_multiple'] = 1;
                                    }
                                  try
                                    {
                $reg_id = $this->registry->getRegistryIdByName(trim($field[0]->first_child()->innertext));
                                    }
                                  catch(exception $e)
                                    {
                                      $reg_id = null;
                                    }

                            $array['config'] = array(
                                                      'registry_id' => $reg_id,
                                                      "label_schema" => ""
                                                    );  
                            $array['entity_id'] = 4;
                            break;

                    default:
                            $array['entity_id'] = 1;
                             break;
                }
               
                  if($flag === 0)
                  {
                    $array['set_primary'] = 1;
                    $flag = 1;
                  }
                  else
                  {
                     $array['set_primary'] = 0;
                  }
                 

                  array_push($params,$array);
            } 
             // file_put_contents(ROOT_PATH . '/translations/temp.ini', json_encode($params));die;
            foreach ($params as $data) 
            {
            try {
                $this->db->beginTransaction();

                $mode = empty($data['id'])
                    ? 'create'
                    : 'update';
                $param = Application_Service_Utilities::getModel('RegistryEntities')->save($data);

                Application_Service_Events::getInstance()->trigger(sprintf('registry.param.%s', $mode), $param);

                $this->db->commit();
                $status = true;
                $message = 'Zapisano parametr';
                }
                catch (Exception $e)
                {

                }
            }

      //  file_put_contents(ROOT_PATH . '/translations/temp.ini', json_encode($param));

    }

}