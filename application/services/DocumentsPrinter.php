<?php

class Application_Service_DocumentsPrinter
{
    const CACHE_PREFIX = 'registry_values_';
    const WORKERS_REGISTRY_TITLE = 'Pracownicy';

    /** Singleton */
    protected static $_instance = null;
    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance; }
    public static function reloadInstance() { return self::$_instance = new self(); }


    /**
     * @var Zend_Cache_Core
     */
    protected $cache;


    /** @var Application_Model_Documents */
    protected $documentsModel;

    /** @var Application_Model_DocumentsPending */
    protected $documentsPendingModel;

    /** @var Application_Model_Documenttemplates */
    protected $documenttemplatesModel;

    /** @var Application_Model_Osoby */
    protected $osobyModel;

    /** @var Application_Model_Documenttemplatesosoby */
    protected $documenttemplatesosobyModel;

    /** @var Application_Model_DocumentsRepoObjects */
    protected $documentsRepoObjectsModel;

    /** @var Application_Service_RepositoryRetreiver */
    protected $repositoryRetreiver;

    /** @var Application_Model_Registry */
    protected $registryModel;

    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;

    /** @var Application_Service_RegistryEntries */
    protected $registryEntries;

    /** @var Application_Model_Settings */
    protected $settingsModel;

    public function __construct()
    {
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

        $this->documentsModel = Application_Service_Utilities::getModel('Documents');
        $this->documentsPendingModel = Application_Service_Utilities::getModel('DocumentsPending');
        $this->documenttemplatesModel = Application_Service_Utilities::getModel('Documenttemplates');
        $this->osobyModel = Application_Service_Utilities::getModel('Osoby');
        $this->documenttemplatesosobyModel = Application_Service_Utilities::getModel('Documenttemplatesosoby');
        $this->documentsRepoObjectsModel = Application_Service_Utilities::getModel('DocumentsRepoObjects');
        $this->repositoryRetreiver = Application_Service_RepositoryRetreiver::reloadInstance();
// -----------------------------------------COMAGOM CODE START---------------------------------------
        $this->registryEntities = Application_Service_Utilities::getModel('registryEntities');
        $this->entitiesModel = Application_Service_Utilities::getModel('Entities');
// -----------------------------------------COMAGOM CODE END-----------------------------------------
        $this->settingsModel = Application_Service_Utilities::getModel('Settings');
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
        $this->registryModel = Application_Service_Utilities::getModel('Registry');
        $this->registryEntries = new Application_Service_RegistryEntries(true);
        $this->registryEntriesEntitiesVarchar = Application_Service_Utilities::getModel('RegistryEntriesEntitiesVarchar');
        $this->numberingschemes = Application_Service_Utilities::getModel('Numberingschemes');

        $this->templateVariables = array(
            'imie' => array('osoba.imie'),
            'nazwisko' => array('osoba.nazwisko'),
            'stanowisko' => array('osoba.stanowisko'),
            'login_do_systemu' => array('osoba.login'),
            'nazwa_firmy' => array('???'),
            'zbiory' => array('zbiory.nazwa', 'upowaznienie'),
            'pomieszczenia' => array('klucz', 'pomieszczenie.nazwa', 'budynek.nazwa'),
            'dokument' => array('object.dokument'),
        );
    }

    public function getTemplateVariables()
    {
        return $this->templateVariables;
    }

    function printDocuments($ids = array())
    {
        // TODO wczytywnie t_setting z objectow
        $settings = Application_Service_Utilities::getModel('Settings');
        $setting_values = $settings->getAll();
        //$t_setting = $this->settingsModel->fetchRow('id = 1');
        $t_setting = $setting_values['0']['value'];
        $city = $setting_values['1']['value'];
        $company_address = $setting_values['2']['value'] .', '.$setting_values['4']['value'].', '.$setting_values['3']['value'];
        // echo json_encode($ids)
        // exit;
        if (!empty($ids)) {
            $documents = $this->documentsModel->find($ids)->toArray();
        } else {
            $documents = $this->documentsModel->fetchAll(array('active != ?' => Application_Service_Documents::VERSION_ARCHIVE))->toArray();
            foreach ($documents as $document) {
                $ids[] = $document['id'];
            }
        }

        if (empty($documents)) {
            return null;
        }

        $objectsByDocument = array();
        $documentObjects = $this->documentsRepoObjectsModel->findByDocument($ids);
        foreach ($documentObjects as $object) {
            if (!isset($objectsByDocument[$object['document_id']])) {
                $objectsByDocument[$object['document_id']] = array();
            }
            $objectsByDocument[$object['document_id']][] = $object->toArray();
        }

        $this->repositoryRetreiver->loadByVersion($documentObjects);
        $rr = $this->repositoryRetreiver;
        /** @var Application_Model_Documenttemplates $templateRepository */
        $templateRepository = Application_Service_Utilities::getModel('Documenttemplates');
        $dateupdate = date('Y-m-d');

        $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();

        foreach ($documents as &$document) {
            $osobaId = $document['osoba_id'];
            $workerId = $document['worker_id'];
            $szablonId = $document['documenttemplate_id'];

            $date = $document['date'];
            $number = $document['number'];
            $numbertxt = $document['numbertxt'];
            $newnum = preg_replace("/[^A-Za-z0-9 ]/", '', $numbertxt);
// -------------------------------COMAGOM CODE START------------------------------------------------
            

            $registry_id = $this->registryEntriesModel->getRegystrIdByRegistryEntryId($document['registry_entry_id']);

            if (!empty($registry_id[0]['registry_id'])) {
                $registry = $this->registryModel->requestObject($registry_id[0]['registry_id']);


                $registry->entities = $this->registryEntities->getEntitiesByRegistryId($registry_id[0]['registry_id']);


                $paginator = $this->registryEntriesModel->getEntriesByRegistryIdAndId($registry_id[0]['registry_id'],$document['registry_entry_id']);
                if (!is_array($paginator)) {
                    $paginator = [$paginator];
                }

                $this->registryEntriesModel->loadData('author', $paginator);
                Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
                $tempDataMatrixOne = array();
                $tempDataMatrixTwo = array();
                foreach ($registry->entities as $key => $value) {
                        $tempArray = $this->entitiesModel->getOne($value['entity_id']);
                        if($value['title'] == 'Czynności przetwarzania') {
                            $item['entity']['system_name'] = $tempArray['system_name'];
                            $item['config_data'] = $value['config'];
                            $item['multiform_data'] = $value['multiform_data'];
                            $item['id'] = $value['id'];
                            array_push($tempDataMatrixOne, $item);
                        } else if($value['title'] == 'Obszary') {
                            $item['entity']['system_name'] = $tempArray['system_name'];
                            $item['config_data'] = $value['config'];
                            $item['multiform_data'] = $value['multiform_data'];
                            $item['id'] = $value['id'];
                            array_push($tempDataMatrixTwo, $item);
                        }

                }

                $dataMatrixOne = array();

                foreach ($paginator as $key => $d) {
                    foreach ($tempDataMatrixOne as $key => $entity) {
                        $dataMatrixOne = Application_Service_RelationshipMatrix::getInstance()->getItemsTree($d->entityToString($entity['id']),[$d->id],$entity['multiform_data']);

                    }
                }
                $dataMatrixTwo = array();
                foreach ($paginator as $key => $d) {
                    foreach ($tempDataMatrixTwo as $key => $entity) {
                        $dataMatrixTwo = Application_Service_RelationshipMatrixSecond::getInstance()->getItemsTree($d->entityToString($entity['id']),[$d->id],$entity['multiform_data']);
                    }
                }
                if (!is_array($dataMatrixOne)) {
                    $dataMatrixOne = [$dataMatrixOne];
                }
                if (!is_array($dataMatrixTwo)) {
                    $dataMatrixTwo = [$dataMatrixTwo];
                }
                $html_matrix_one = "";
                foreach ($dataMatrixOne as $key => $item) {
                    $html_matrix_one .= $item['title'] . ":"; $firstInGroup = true;
                    foreach ($item['children'] as $key => $item2) {
                        if(!$firstInGroup) $html_matrix_one .= ","; $html_matrix_one .= $item2['title'];
                        $firstInGroup = false;
                        $html_matrix_one .= "<br>";
                        if(count($item2['children'])) {
                            implode(', ', $item2['children']);
                        }
                    }

                }
                $html_matrix_two .= "<br>";
                foreach ($dataMatrixTwo as $key => $item) {
                    $html_matrix_two .= $item['title'] . ":"; $firstInGroup = true;
                    foreach ($item['children'] as $key => $item2) {
                        if(!$firstInGroup) $html_matrix_two .= ","; $html_matrix_two .= $item2['title'];
                        $firstInGroup = false;
                        $html_matrix_two .= "<br>";
                        if(count($item2['children'])) {
                            implode(', ', $item2['children']);
                        }
                    }

                }

    // -------------------------------COMAGOM CODE END  ------------------------------------------------
                if($html_matrix_one == "") {
                    //$html_matrix_one = "Czynności przetwarzania empty or Please tell me what should i show when matrix is empty?";
                    $html_matrix_one = "-";
                }
                if($html_matrix_two == "") {
                    //$html_matrix_two = "Obszary empty or Please tell me what should i show when matrix is empty?";
                    $html_matrix_two = "-";
                }
            }
            
            $content = $templateRepository->get($szablonId)['content'];
            $content = str_replace('{Czynności przetwarzania}', $html_matrix_one, $content);
            $content = str_replace('{Obszary}', $html_matrix_two, $content);
            $content = str_replace('{imie}', $rr->fetch('osoba.imie', array('osoby_id' => $osobaId))['imie'], $content);
            $content = str_replace('{nazwisko}', $rr->fetch('osoba.nazwisko', array('osoby_id' => $osobaId))['nazwisko'], $content);
            $content = str_replace('{login_do_systemu}', $rr->fetch('osoba.login', array('osoby_id' => $osobaId))['login_do_systemu'], $content);
            $content = str_replace('{stanowisko}', $rr->fetch('osoba.stanowisko', array('osoby_id' => $osobaId))['stanowisko'], $content);
            $content = str_replace('{data}', sprintf('<span class="nowrap">%s</span>', $date), $content);
            $content = str_replace('{nr}', $numbertxt, $content);
            $content = str_replace('{nazwa_firmy}', $t_setting, $content);
            $content = str_replace('{company_name}', $t_setting, $content);
            $content = str_replace('{company_address}', $company_address, $content);
            $content = str_replace('{city}', $city, $content);
            $content = str_replace('{date}', $dateupdate, $content);
            $content = str_replace('{zbiory}', $this->getZbiory(), $content);
            $content = str_replace('{pomieszczenia}', $this->getPomieszczenia(), $content);
            $content = str_replace('{formularz}', $this->getDocumentFormSummary($document), $content);
            $content = str_replace('{barcode}', '<barcode code="' . $newnum . '" type="C39" height="2" text="1" /><br />' . $newnum . '', $content);
            $content = str_replace('{formularz}', $this->getDocumentFormSummary($document), $content);
            $content = str_replace('{name}',$workers[$workerId]['imie'],$content);
            $content = str_replace('{surname}',$workers[$workerId]['nazwisko'],$content);
            if (!empty($objectsByDocument[$document['id']]) && $documentFind = Application_Service_Utilities::arrayFind($objectsByDocument[$document['id']], 'object_id', 14)) {
                $content = str_replace('{dokument.numer}', $rr->fetch('object.document', array('id' => $documentFind[0]['version_id']))['numbertxt'], $content);
            }
            // $document['content'] = $this->replaceWorkerMacroses($content, $workerId);
            $document['content'] = $content;
        }

        return $documents;
    }

    function printPendingDocuments($ids = array(), $nrTagReplace = false)
    {
        // TODO wczytywnie t_setting z objectow
        
        $settings = Application_Service_Utilities::getModel('Settings');
        $setting_values = $settings->getAll();
        //$t_setting = $this->settingsModel->fetchRow('id = 1');
        $t_setting = $setting_values['0']['value'];
        $city = $setting_values['1']['value'];
        $company_address = $setting_values['2']['value'] .', '.$setting_values['4']['value'].', '.$setting_values['3']['value'];
      
        if (!empty($ids)) {
            $documents = $this->documentsPendingModel->find($ids)->toArray();
        } else {
            $documents = $this->documentsPendingModel->fetchAll(array('status IN (?)' => [Application_Model_DocumentsPending::STATUS_PENDING, Application_Model_DocumentsPending::STATUS_ACCEPTED]))->toArray();
            foreach ($documents as $document) {
                $ids[] = $document['id'];
            }
        }

        if (empty($documents)) {
            return null;
        }

        $documenttemplateIds = Application_Service_Utilities::getValues($documents, 'documenttemplate_id');
        $osobyIds = Application_Service_Utilities::getValues($documents, 'worker_id');

        $t_documenttemplates = $this->documenttemplatesModel->fetchAll(['id IN (?)' => $documenttemplateIds]);

        $numberingschemeIds = array();
        $documenttemplateIds = array();
        foreach ($t_documenttemplates as $documenttemplate) {
            $documenttemplateIds[] = $documenttemplate->id;
            $numberingschemeIds[] = $documenttemplate->numberingscheme_id;
        }

        $objectsRepository = new Application_Service_RepositoryObjects();
        $rr = $objectsRepository->prepareRetreiver($osobyIds, $documenttemplateIds, $numberingschemeIds);
 
        $workers = Application_Service_DocumentsPrinter::getInstance()->getWorkersList();

        foreach ($documents as &$document) {
            $workerId = $document['worker_id'];
            $szablonId = $document['documenttemplate_id'];

            

            $registryInfo = $this->getRegistryInfo($szablonId);

            $dateupdate = date('Y-m-d');
            $templateNumberingScheme = $this->documenttemplatesModel->getOne($szablonId);

            $numberingscheme = $this->numberingschemes->getOne($templateNumberingScheme->numberingscheme_id);

            $numberingSchemeData = $this->getNumberingSchemeData($numberingscheme['type'], $dateupdate);
            $number = $this->getMaxNumber($szablonId, $numberingSchemeData['start_date'], $numberingSchemeData['end_date']);

            $numbertxt = $numberingscheme['scheme'];
            $numbertxt = str_ireplace('[nr]', $number, $numbertxt);
            $numbertxt = str_ireplace('[yyyy]', date('Y', strtotime($dateupdate)), $numbertxt);
            $numbertxt = str_ireplace('[kw]', $numberingSchemeData['kw'], $numbertxt);
            $numbertxt = str_ireplace('[mm]', date('m', strtotime($dateupdate)), $numbertxt);
            $numbertxt = str_ireplace('[dd]', date('d', strtotime($dateupdate)), $numbertxt);
            $newnum = preg_replace("/[^A-Za-z0-9 ]/", '', $numbertxt);
            $date = $document['date'];

            $content = $rr->fetch('documenttemplate', array('documenttemplate_id' => $szablonId))['content'];
            $content = str_replace('{data}', sprintf('<span class="nowrap">%s</span>', $date), $content);
            /* Ankit code change to not to replace nr tag for pending docs start */
            if ($nrTagReplace) {
                $content = str_replace('{nr}', $numbertxt, $content);    
            }
            /* Ankit code change to not to replace nr tag for pending docs close */
// -------------------------------COMAGOM CODE START------------------------------------------------
            

            $registry_id = $this->registryEntriesModel->getRegystrIdByRegistryEntryId($document['registry_entry_id']);
            if($registry_id[0]['registry_id']) {
                $registry = $this->registryModel->requestObject($registry_id[0]['registry_id']);

            
                $registry->entities = $this->registryEntities->getEntitiesByRegistryId($registry_id[0]['registry_id']);
            
                $paginator = $this->registryEntriesModel->getEntriesByRegistryIdAndId($registry_id[0]['registry_id'],$document['registry_entry_id']);
                if (!is_array($paginator)) {
                    $paginator = [$paginator];
                }

                $this->registryEntriesModel->loadData('author', $paginator);
                Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
                $tempDataMatrixOne = array();
                $tempDataMatrixTwo = array();
                foreach ($registry->entities as $key => $value) {
                        $tempArray = $this->entitiesModel->getOne($value['entity_id']);
                        if($value['title'] == 'Czynności przetwarzania') {
                            $item['entity']['system_name'] = $tempArray['system_name'];
                            $item['config_data'] = $value['config'];
                            $item['multiform_data'] = $value['multiform_data'];
                            $item['id'] = $value['id'];
                            array_push($tempDataMatrixOne, $item);
                        } else if($value['title'] == 'Obszary') {
                            $item['entity']['system_name'] = $tempArray['system_name'];
                            $item['config_data'] = $value['config'];
                            $item['multiform_data'] = $value['multiform_data'];
                            $item['id'] = $value['id'];
                            array_push($tempDataMatrixTwo, $item);
                        }
                        
                }

                $dataMatrixOne = array();

                foreach ($paginator as $key => $d) {
                    foreach ($tempDataMatrixOne as $key => $entity) {
                        $dataMatrixOne = Application_Service_RelationshipMatrix::getInstance()->getItemsTree($d->entityToString($entity['id']),[$d->id],$entity['multiform_data']);
                        
                    }
                }
                $dataMatrixTwo = array();
                foreach ($paginator as $key => $d) {
                    foreach ($tempDataMatrixTwo as $key => $entity) {
                        $dataMatrixTwo = Application_Service_RelationshipMatrixSecond::getInstance()->getItemsTree($d->entityToString($entity['id']),[$d->id],$entity['multiform_data']);
                    }
                }
                if (!is_array($dataMatrixOne)) {
                    $dataMatrixOne = [$dataMatrixOne];
                }
                if (!is_array($dataMatrixTwo)) {
                    $dataMatrixTwo = [$dataMatrixTwo];
                }
                $html_matrix_one = "";
                foreach ($dataMatrixOne as $key => $item) {
                    $html_matrix_one .= $item['title'] . ":"; $firstInGroup = true;
                    foreach ($item['children'] as $key => $item2) {
                        if(!$firstInGroup) $html_matrix_one .= ","; $html_matrix_one .= $item2['title'];
                        $firstInGroup = false;
                        $html_matrix_one .= "<br>";
                        if(count($item2['children'])) {
                            implode(', ', $item2['children']);
                        }
                    }
                    
                }
                $html_matrix_two .= "";
                foreach ($dataMatrixTwo as $key => $item) {
                    $html_matrix_two .= $item['title'] . ":"; $firstInGroup = true;
                    foreach ($item['children'] as $key => $item2) {
                        if(!$firstInGroup) $html_matrix_two .= ","; $html_matrix_two .= $item2['title'];
                        $firstInGroup = false;
                        $html_matrix_two .= "<br>";
                        if(count($item2['children'])) {
                            implode(', ', $item2['children']);
                        }
                    }
                    
                }
            }
            
            
// -------------------------------COMAGOM CODE END  ------------------------------------------------
            if($html_matrix_one == "") {
                //$html_matrix_one = "Czynności przetwarzania empty or Please tell me what should i show when matrix is empty?";
                $html_matrix_one = "-";
            }
            if($html_matrix_two == "") {
                //$html_matrix_two = "Obszary empty or Please tell me what should i show when matrix is empty?";
                $html_matrix_two = "-";
            }
            
            if (strpos($content, '{Czynności przetwarzania}') !== false) {
                $content = str_replace('{Czynności przetwarzania}', $html_matrix_one, $content);
            }
            
            if (strpos($content, '{Obszary}') !== false) {
                $content = str_replace('{Obszary}', $html_matrix_two, $content);
            }
            
            if (strpos($content, '{nazwa_firmy}') !== false) {
                $content = str_replace('{nazwa_firmy}', $t_setting, $content);
            }
            
            if (strpos($content, '{company_name}') !== false) {
                $content = str_replace('{company_name}', $t_setting, $content);
            }
            
            if (strpos($content, '{company_address}') !== false) {
                $content = str_replace('{company_address}', $company_address, $content);
            }
            
            if (strpos($content, '{city}') !== false) {
                $content = str_replace('{city}', $city, $content);
            }
            
            if (strpos($content, '{date}') !== false) {
                $content = str_replace('{date}', $dateupdate, $content);
            }
            
            if (strpos($content, '{zbiory}') !== false) {
                $content = str_replace('{zbiory}', $this->getZbiory(), $content);
            }
            
            if (strpos($content, '{pomieszczenia}') !== false) {
                $content = str_replace('{pomieszczenia}', $this->getPomieszczenia(), $content);
            }
            
            if (strpos($content, '{formularz}') !== false || 1==1) {
                $content = str_replace('{formularz}', $this->getDocumentFormSummary($document), $content);
            }
            
            if (strpos($content, '{barcode}') !== false) {
                $content = str_replace('{barcode}', '<barcode code="' . $newnum . '" type="C39" height="2" text="1" /><br />' . $newnum . '', $content);
            }
            
            if (strpos($content, '{name}') !== false) {
                $content = str_replace('{name}',$workers[$workerId]['imie'],$content);
            }
            
            if (strpos($content, '{surname}') !== false) {
                $content = str_replace('{surname}',$workers[$workerId]['nazwisko'],$content);
            }
            // $content = str_replace('{'.$registryInfo['title'].'.permissions}', $this->getPermissions($workerId, $registryInfo['registry_id']), $content);
            // $content = $this->replaceWorkerMacroses($content, $workerId);
            // if ($registryEntryInfo = $this->getRegistryEntryInfo($document['registry_entry_id'])) {
            //     $registryTitle = $registryInfo['title'];
            //     foreach ($registryEntryInfo  as $entryField => $entryValue) {
            //         $content = str_replace('{'.$registryTitle.'.'.$entryField.'}', $entryValue, $content);
            //     }
            // }
            $document['content'] = $content;
            // return $documents;
        }
        return $documents;
    }

    private function getZbiory()
    {
        $rr = $this->repositoryRetreiver;
        $objects = $rr->fetchCategorized();

        if (empty($objects['upowaznienie'])) {
            return '';
        }

        $t_zbiory_names = array();
        foreach ($objects['upowaznienie'] as $upowaznienie) {
            if ($upowaznienie['czytanie'] == 0 && $upowaznienie['pozyskiwanie'] == 0 && $upowaznienie['wprowadzanie'] == 0 && $upowaznienie['modyfikacja'] == 0 && $upowaznienie['usuwanie'] == 0) {
                continue;
            }

            $t_zbiory_names[$upowaznienie['zbiory_id']] = $objects['zbior.nazwa'][$upowaznienie['zbiory_id']]['nazwa'] . ' ( ';
            if ($upowaznienie['czytanie'] == 1) {
                $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' C ';
            }
            if ($upowaznienie['pozyskiwanie'] == 1) {
                $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' P ';
            }
            if ($upowaznienie['wprowadzanie'] == 1) {
                $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' W ';
            }
            if ($upowaznienie['modyfikacja'] == 1) {
                $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' M ';
            }
            if ($upowaznienie['usuwanie'] == 1) {
                $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' U ';
            }
            $t_zbiory_names[$upowaznienie['zbiory_id']] .= ' ) ';
        }

        $zbiorynames = '<ul>';
        foreach ($t_zbiory_names AS $zbioryname) {
            $zbiorynames .= '<li>' . $zbioryname . '</li>';
        }
        $zbiorynames .= '</ul>';

        return $zbiorynames;
    }

    private function getPomieszczenia()
    {
        $rr = $this->repositoryRetreiver;
        $objects = $rr->fetchCategorized();

        if (empty($objects['klucz'])) {
            return '';
        }

        $roomsnames = '<ul>';
        foreach ($objects['budynek.nazwa'] AS $budynek) {
            $roomsnames .= '<li>' . $budynek['nazwa'] . '<ul>';
            foreach ($objects['pomieszczenie.nazwa'] AS $pomieszczenie) {
                if ($pomieszczenie['budynki_id'] === $budynek['budynki_id']) {
                    $roomsnames .= '<li>' . $pomieszczenie['nazwa'] .' '.$pomieszczenie['nr'].' '. $pomieszczenie['wydzial'] . '</li>';
                }
            }
            $roomsnames .= '</ul></li>';
        }
        $roomsnames .= '</ul>';

        return $roomsnames;
    }

    public function getDocumentBinaryData($documentId)
    {
        $content = $this->getDocumentPreview($documentId);
        $paginator = [['content' => $content]];

        require_once('mpdf60/mpdf.php');

        $mpdf = new mPDF('', 'A4', '', '', '0', '0', '0', '0', '', '', 'P');
        $mpdf->WriteHTML(Application_Service_Utilities::renderView('documents/print-pdf.html', compact('paginator')));

        $pdfBinary = $mpdf->Output('', 'S');

        return $pdfBinary;
    }

    public function getDocumentPreview($documentId)
    {
        $documentsPrinterService = Application_Service_DocumentsPrinter::getInstance();
        $print = $documentsPrinterService->printDocuments([$documentId]);

        if (!empty($print)) {
            return $print[0]['content'];
        }

        return 'Brak dokumentu';
    }

    public function getPendingDocumentPreview($documentId)
    {
        $documentsPrinterService = Application_Service_DocumentsPrinter::getInstance();
        $print = $documentsPrinterService->printPendingDocuments(array($documentId));

        if (!empty($print)) {
            return $print[0]['content'];
        }

        return 'Brak dokumentu';
    }

    private function getDocumentFormSummary($document)
    {
        $summary = '';
        /** @var Application_Model_Registry $registryModel */
        $registryModel = Application_Service_Utilities::getModel('Registry');
        $registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');

        $documenttemplateFormRegistry = $registryModel->getFull([
            'type_id = ?' => Application_Service_RegistryConst::REGISTRY_TYPE_DOCUMENTTEMPLATE_FORM,
            'object_id = ?' => $document['documenttemplate_id'],
        ]);

        if ($documenttemplateFormRegistry && $documenttemplateFormRegistry->entities_named['document']->id) {
            $select = $registryEntriesModel->getSelect()
                ->joinLeft(['po' => 'registry_entries_entities_int'], 'po.entry_id = re.id AND po.registry_entity_id = ' . $documenttemplateFormRegistry->entities_named['document']->id, [])
                ->where('po.value = ?', $document['id']);

            $result = $registryEntriesModel->getListFromSelect($select);
            if (!empty($result)) {
                $result = $result[0];
                Application_Service_Registry::getInstance()->entryGetEntities($result);

                $disabledEntities = [];
                foreach ($documenttemplateFormRegistry->entities as $entity) {
                    if (in_array($entity->system_name, ['employee', 'document'])) {
                        $disabledEntities[] = $entity->id;
                    }
                }

                $entitiesToPrint = [];
                foreach ($result->entities as $entity) {
                    if (in_array($entity->registry_entity_id, $disabledEntities)) {
                        continue;
                    }
                    $entitiesToPrint[] = $entity;
                }

                $summary = '<table class="document-user-form-table">';
                foreach ($documenttemplateFormRegistry->entities as $registryEntity) {
                    if (in_array($registryEntity->system_name, ['employee', 'document'])) {
                        continue;
                    }

                    $entity = Application_Service_Utilities::arrayFindOne($result->entities, 'registry_entity_id', $registryEntity->id);

                    if ($entity) {
                        $summary .= '<tr><td>'.$registryEntity->title.'</td><td>'.($entity ? 'tak' : 'nie').'</td></tr>';
                    }
                }
                $summary .= '</table>';
            }
        }

        return $summary;
    }

    public function getName($id)
    {
        $worker = $this->getRegistryEntryInfo($id);
        if ($worker) {
            return $worker['imie'] . ' ' . $worker['nazwisko'];
        }
        return null;
    }

    public function getPermissions($workerId, $registryId)
    {
        $text = "";
        //$registryId = 126;
        $registry = $this->registryModel->getOne($registryId, true);
        $registry->loadData('entities');

        $paginator = $this->registryEntriesModel->getList(['registry_id = ?' => $registryId ]);
        $this->registryEntriesModel->loadData('author', $paginator);
        Application_Service_Registry::getInstance()->entriesGetEntities($paginator);

        foreach ($paginator as $d) {
            if($d->worker_id == $workerId){
                foreach ($registry->entities as $entity) {
                    if (in_array($entity->entity->system_name, array('relationshipMatrix', 'relationshipMatrixMultiple', 'relationshipMatrixDynamic'))) {
                        $values = $d->entityToString($entity->id);
                        $config = (array)$entity->config_data;
                        $excludes = array($d->id);
                        $multiformData = $entity->multiform_data;

                        $relations = Application_Service_RelationshipMatrix::getInstance()->getItemsList($values,$excludes,$multiformData);
                        //print_r($relations);
                        $processedGroups = array();
                        foreach ($relations as $relation) {
                            foreach ($relation as $entity) {
                                if ($entity['registry_id'] == $config['registry_id']) {
                                    if (!in_array($entity['id'], $processedGroups)) {
                                        $firstInGroup = true;
                                        $processedGroups[] = $entity['id'];
                                        $text .= "<br>". $entity['title']. ":<br>";
                                        foreach ($relations as $relation2) {
                                            if ($relation2[0]['id'] == $entity['id'] && $relation2[1]['registry_id'] == $config['registry2_id']) {
                                                if (!$firstInGroup) {
                                                    $text .= ", ";
                                                }
                                                $text .= $relation2[1]['title'];
                                                $firstInGroup = false;

                                            } elseif ( $relation2[0]['registry_id'] == $config['registry2_id'] && $relation2[1]['id'] == $entity['id']) {
                                                if (!$firstInGroup) {
                                                    $text .= " ,";
                                                }
                                                $text .= $relation2[0]['title'];

                                                $firstInGroup = false;
                                            }

                                        }

                                    }

                                }

                            }

                        }
                    }
                }
            }
        }
        return $text;
    }

    public function getRegistryInfo($documentTemplateId)
    {
        $registryInfo = array();
        $documentTemplate = $this->documenttemplatesModel->fetchAll(['id IN (?)' => $documentTemplateId]);

        if ($documentTemplate) {
            if ($documentTemplate[0]->registry_id) {
                $registryId = $documentTemplate[0]->registry_id;
                $registryInfo['registry_id'] = $registryId;

                $registry = $this->registryModel->getRegistryById($registryId);
                if ($registry) {
                    $registryInfo['title'] = $registry['title'];
                }
            }
        }
        return $registryInfo;
    }
    public function getActiveWorkersList(array $excludes = [])
    {
        $workersRegistry = $this->registryModel->getRegistryByTitle(self::WORKERS_REGISTRY_TITLE);
        $workers = array();

        if ($workersRegistry) {
            $registry = $this->registryModel->getOne($workersRegistry['id'], true);
            $registry->loadData('entities');
            $query = ['registry_id = ?' => $workersRegistry['id'],'status_of_worker = ?' => 0 ];
            if ($excludes) {
                $query ['id NOT IN (?)'] = $excludes;
            }
            $paginator = $this->registryEntriesModel->getList($query);
            $this->registryEntriesModel->loadData('author', $paginator);
            Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
            $allowedFields = [
                'imie',
                'nazwisko',
                'identyfikator',
                'e_mail',
                'rola',
                'forma_wspolpracy',
                'stanowisko',
                'data_podpisania_umowy_o_prace',
                'telefon',
                'dzial',
            ];
            if (count($paginator) > 0){
                foreach ($paginator as $d) {
                    foreach ($registry->entities as $entity) {
                        if (!in_array($entity->system_name, $allowedFields)){
                            continue;
                        }
                        $workers[$d->id][$entity->system_name] = $d->entityToString($entity->id);
                    }
                }
            }
        }
        return $workers;
    }
    public function getWorkersList(array $excludes = [])
    {
        $workersRegistry = $this->registryModel->getRegistryByTitle(self::WORKERS_REGISTRY_TITLE);
        $workers = array();

        if ($workersRegistry) {
            $registry = $this->registryModel->getOne($workersRegistry['id'], true);
            $registry->loadData('entities');
            $query = ['registry_id = ?' => $workersRegistry['id']];
            if ($excludes) {
                $query ['id NOT IN (?)'] = $excludes;
            }
            $paginator = $this->registryEntriesModel->getList($query);
            $this->registryEntriesModel->loadData('author', $paginator);
            Application_Service_Registry::getInstance()->entriesGetEntities($paginator);
            $allowedFields = [
                'imie',
                'nazwisko',
                'identyfikator',
                'e_mail',
                'rola',
                'forma_wspolpracy',
                'stanowisko',
                'data_podpisania_umowy_o_prace',
                'telefon',
                'dzial',
            ];
            if (count($paginator) > 0){
                foreach ($paginator as $d) {
                    foreach ($registry->entities as $entity) {
                        if (!in_array($entity->system_name, $allowedFields)){
                            continue;
                        }
                        $workers[$d->id][$entity->system_name] = $d->entityToString($entity->id);
                    }
                }
            }
        }
        return $workers;
    }

    /**
     * @param string $entryId
     * @param bool $stringify
     * @return array
     * @throws Exception
     */
    // Ankit code changes for access this function in Osoby controller
    public function getRegistryEntryInfo($entryId, $stringify = false, $getOnlySmartRadioGroupInfo = false)
    {
        $out = ['id' => $entryId];
        if (!$getOnlySmartRadioGroupInfo) {
            foreach ($this->registryEntries->getEntryAsArray($entryId) as $entity) {
                $tag = strtolower($this->sanitizeTag($entity['title']));
                $tagValue = '';
                switch ($entity['system_name']) {
                    case 'relationshipMatrix':
                    case 'relationshipMatrixMultiple':
                    case 'relationshipMatrixDynamic':
                    case 'relationshipMatrixExtra':
                        $values = $this->parseMatrixValue($entity['value']);
                        $registryValues = !empty($entity['config']['registry_id']) ? $this->parseMatrixRegisterValues($entity['config']['registry_id']) : [];
                        $registry2Values = !empty($entity['config']['registry2_id']) ? $this->parseMatrixRegisterValues($entity['config']['registry2_id']) : [];
                        $registry3Values = !empty($entity['config']['registry3_id']) ? $this->parseMatrixRegisterValues($entity['config']['registry3_id']) : [];
                        foreach($values as $valueGroup) {
                            foreach($registryValues as $registryValueId => $registryValue) {
                                if (!in_array($registryValueId, $valueGroup)) {
                                    continue;
                                }
                                $tagValue .= '<div>';
                                $tagValue .= $registryValue . ':';
                                $tagValue .= '<ul>';
                                foreach($registry2Values as $registry2ValueId => $registry2Value) {
                                    if (!in_array($registry2ValueId, $valueGroup)) {
                                        continue;
                                    }
                                    $values3 = [];
                                    foreach($registry3Values as $registry3ValueId => $registry3Value) {
                                        if (!in_array($registry3ValueId, $valueGroup)) {
                                            continue;
                                        }
                                        $values3 []= $registry3Value;
                                    }
                                    $tagValue .= '<li>' . $registry2Value;
                                    if($values3) {
                                        $tagValue .= ' (' . implode(', ', $values3) .')';
                                    }
                                    $tagValue .= '</li>';
                                }
                                $tagValue .= '</ul>';
                                $tagValue .= '</div>';
                            }
                        }
                        break;
                    case 'smartRadioGroup':
                        $service = new Application_Service_RegistryEntries();
                        $tagValue = implode('<br>', array_map(function ($value) use($service) {
                            return $service->getEntryAsString($value[0]);
                        }, $this->parseMatrixValue($entity['value'])));
                        break;
                    default:
                        $tagValue = $entity['value'];
                        break;
                }
                $out [$tag] = $tagValue;
            }
        } else {
            $service = new Application_Service_RegistryEntries();
            $tagValue = implode('<br>', array_map(function ($value) use($service) {
                return $service->getEntryAsString($value[0]);
            }, $this->parseMatrixValue($entryId)));
            return $tagValue;
        }
        return $out;
    }

    protected function parseMatrixValue($value) {
        return array_map(function($value){
            return array_map(function($value){
                return $value;
            }, explode('-', trim($value)));
        }, explode(',', $value));
    }

    /**
     * @param $registryId
     * @return array
     * @throws Exception
     */
    protected function parseMatrixRegisterValues($registryId) {
        $registries = $this->registryEntries->getAllEntitiesAsArray([$registryId]);
        if (!empty($registries[$registryId]['values'])) {
            return array_map(function ($value){
                return implode(' ', $value);
            }, $registries[$registryId]['values']);
        }
        return [];
    }

    protected function sanitizeTag($tag)
    {
        $tag = str_replace([' ', '-'], '_', $tag);
        $tag = str_replace(['(', ')', ':'], '', $tag);
        return $tag;
    }

    /**
     * @param $numberingSchemeType string
     * @param $dateupdate string
     * @return array
     */
    private function getNumberingSchemeData($numberingSchemeType, $dateupdate)
    {
        $month = (int) date('m', strtotime($dateupdate));
        $year = (int) date('Y', strtotime($dateupdate));

        switch ($numberingSchemeType) {
            case 1:
                $start_date = $dateupdate;
                $end_date = date('Y-m-d', (strtotime($dateupdate) + (60 * 60 * 24)));
                break;
            case 2:
                $start_date = date('Y-m-d', strtotime('first day of ' . date('F Y', strtotime($dateupdate))));
                $end_date = date('Y-m-d', strtotime('last day of ' . date('F Y', strtotime($dateupdate))) + (60 * 60 * 24));
                break;
            case 3:
                if ($month <= 3) {
                    $start_date = $year . '-01-01';
                    $end_date = $year . '-04-01';
                } else if ($month <= 6) {
                    $start_date = $year . '-04-01';
                    $end_date = $year . '-07-01';
                } else if ($month <= 9) {
                    $start_date = $year . '-07-01';
                    $end_date = $year . '-10-01';
                } else if ($month <= 12) {
                    $start_date = $year . '-10-01';
                    $end_date = ($year + 1) . '-01-01';
                }
                break;
            case 4:
                $start_date = $year . '-01-01';
                $end_date = ($year + 1) . '-01-01';
                break;
        }

        if ($month <= 3) {
            $kw = 1;
        } else if ($month <= 6) {
            $kw = 2;
        } else if ($month <= 9) {
            $kw = 3;
        } else if ($month <= 12) {
            $kw = 4;
        }

        return compact('start_date', 'end_date', 'kw');
    }

    private function getMaxNumber($templateId, $startDate, $endDate)
    {
        $number = 1;
        $lastNumber = $this->documentsModel->getAdapter()->select()
            ->from(array('d' => 'documents'), array('max_number' => 'MAX(number)'))
            //->where('d.countingactive = ?', 1)
            ->where('d.documenttemplate_id = ?', $templateId)
            ->where('d.date >= ?', $startDate)
            ->where('d.date < ?', $endDate)
            ->query()
            ->fetchColumn();

        if ($lastNumber) {
            $number = (int) $lastNumber + 1;
        }

        return $number;
    }

    public function isDocSigned($user_id, $document_id)
    {
        if(!$user_id) return false;
        $userSignup = Application_Service_Utilities::getModel('UserSignatures');
        $sign_id = $userSignup->getAdapter()->select()
            ->from(array('us' => 'user_signatures'), array('id' => 'us.id'))
            ->joinInner(['st' => 'storage_tasks'], 'st.id = us.resource_id', [])
            ->where('us.user_id = ?', $user_id)
            ->where('st.status = ?', 1)
            ->where('st.object_id = ?', $document_id)
            ->query()
            ->fetchColumn();

        if (!$sign_id) {
            return false;
        }

        return $sign_id;
    }

    protected function replaceWorkerMacroses($content, $workerId)
    {
        if (!$worker = $this->getRegistryEntryInfo($workerId)) {
            return $content;
        }
        foreach ($worker as $workerField => $workerValue) {
            $workerField = strtr($workerField, ['Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
                'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
                'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
                'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
                'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
                'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
                'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ę' => 'e', 'þ' => 'b',
                'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r']);
            $content = str_replace('{' . self::WORKERS_REGISTRY_TITLE . '.' . $workerField . '}', $workerValue, $content);
            $content = str_replace('{' . $workerField . '}', $workerValue, $content);
        }
        //replace tag for {name}
        if (isset($worker['imie']) && !empty($worker['imie'])) {
            $content = str_replace('{name}', $worker['imie'], $content);
        }
        //replace tag for {surname}
        if (isset($worker['nazwisko']) && !empty($worker['nazwisko'])) {
            $content = str_replace('{surname}', $worker['nazwisko'], $content);
        }
        //replace tag for {job}
        if (isset($worker['stanowisko']) && !empty($worker['stanowisko'])) {
            $content = str_replace('{job}', $worker['stanowisko'], $content);
        }
        return $content;
    }
}
