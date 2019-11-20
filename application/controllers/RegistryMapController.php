<?php

class RegistryMapController extends Muzyka_Admin
{

    protected $registryModel;

    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;

    /** @var Application_Service_Registry */
    protected $registryService;
    protected $baseUrl = '/registry-map';

    public function init()
    {
        parent::init();

        // $this->disableLayout();
         $this->_helper->_layout->setLayout('map');
      

        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
        $this->dictionaryModel = Application_Service_Utilities::getModel('Dictionary');
        $this->registryService = Application_Service_Registry::getInstance(); 
        Zend_Layout::getMvcInstance()->assign('section', 'Rejestr adresów');
        $this->view->baseUrl = $this->baseUrl;
    }

    public function indexAction()
    {

       $registryId = $this->registryModel->getRegistryIdByName('Address');

       $registry = $this->registryModel->getOne($registryId, true);

        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
        //$this->view->paginator = $paginator;
       // $this->view->registry = $registry;
      $locations = array();
      $loc_addr = array();
     
      foreach($paginator as $d)
      {
                foreach($registry->entities as $entity)
                {
                    $address = preg_replace('/\s+/', '+', $d->entityToString($entity->id));
                   // die($d->entityToString($entity->id));
                   
                    $jsonData = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key=AIzaSyBlJLKIqc3Xed6WiD18kE-n4U67r83rzEQ');
                  
                    
                   $output= json_decode($jsonData);
                  
                
                      @$lat = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                      @$lng = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

                      $loc_addr[] = array($d->entityToString($entity->id));
                     
                      $locations[] = array($lat,$lng); 
               }
      } 

              $layout = Zend_Layout::getMvcInstance();
              $view = $layout->getView();
              $view->locations = $locations;
              $view->loc_addr = $loc_addr;
    }
}
