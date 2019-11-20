<?php

class Application_Service_Documents
{
    const CACHE_PREFIX = 'registry_values_';
    const VERSION_ARCHIVE = 0;
    const VERSION_OBLIGATORY = 1;
    const VERSION_PERMISSIBLE = 2;
    const VERSION_OUTDATED = 3;




    /**
     * @var Zend_Cache_Core
     */
    protected $cache;


    /** Singleton */
    protected static $_instance = null;

    private function __clone() {}
    
    /**
     * @return Application_Service_Documents
     */
    public static function getInstance() { return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance; }

//------------------COMAGOM CODE START------------------------

    /** @var Application_Model_PermissionStatus */
    protected $permissionStatusModel;

//------------------COMAGOM CODE END--------------------------

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

    /** @var Application_Model_Numberingschemes */
    protected $numberingschemesModel;

    /** @var Application_Model_Klucze */
    protected $kluczeModel;

    /** @var Application_Model_Upowaznienia */
    protected $upowaznieniaModel;

    /** @var Application_Model_Settings */
    protected $settingsModel;

    /** @var Application_Model_Budynki */
    protected $budynkiModel;

    /** @var Application_Model_Pomieszczenia */
    protected $pomieszczeniaModel;

    /** @var Application_Model_Zbiory */
    protected $zbioryModel;

    /** @var array */
    protected $typeVariableDependencies;

    /** @var Application_Model_DocumentsRepoObjects */
    protected $documentsRepoObjectsModel;

    /** @var Application_Model_RegistryEntries */
    protected $registryEntriesModel;

    /** @var Application_Service_DocumentsPrinter */
    protected $documentsPrinter;

    /** @var Zend_Db_Adapter_Pdo_Mysql */
    protected $db;

    protected $outdatedDocumentsCounter = 0;

    /**
     * Application_Service_Documents constructor.
     * @throws Exception
     */
    public function __construct()
    {
//-------------------------------------COMAGOM CODE START------------------------------------------
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
        $this->permissionStatusModel = Application_Service_Utilities::getModel('PermissionStatus');
//-------------------------------------COMAGOM CODE END------------------------------------------
        $this->documentsModel = Application_Service_Utilities::getModel('Documents');
        $this->documentsPendingModel = Application_Service_Utilities::getModel('DocumentsPending');
        $this->documenttemplatesModel = Application_Service_Utilities::getModel('Documenttemplates');
        $this->osobyModel = Application_Service_Utilities::getModel('Osoby');
        $this->documenttemplatesosobyModel = Application_Service_Utilities::getModel('Documenttemplatesosoby');
        $this->documentsRepoObjectsModel = Application_Service_Utilities::getModel('DocumentsRepoObjects');
        $this->numberingschemesModel = Application_Service_Utilities::getModel('Numberingschemes');

        $this->kluczeModel = Application_Service_Utilities::getModel('Klucze');
        $this->upowaznieniaModel = Application_Service_Utilities::getModel('Upowaznienia');
        $this->settingsModel = Application_Service_Utilities::getModel('Settings');
        $this->budynkiModel = Application_Service_Utilities::getModel('Budynki');
        $this->pomieszczeniaModel = Application_Service_Utilities::getModel('Pomieszczenia');
        $this->zbioryModel = Application_Service_Utilities::getModel('Zbiory');
        $this->registryEntriesModel = Application_Service_Utilities::getModel('RegistryEntries');
        $this->documentsPrinter = Application_Service_DocumentsPrinter::getInstance();

        $this->db = $this->zbioryModel->getAdapter();

        $this->typeVariableDependencies = array(
            'Application_Model_ZbioryHistory' => array('zbiory'),
            'Application_Model_UpowaznieniaHistory' => array('zbiory'),
            'Application_Model_BudynkiHistory' => array('pomieszczenia'),
            'Application_Model_PomieszczeniaHistory' => array('pomieszczenia'),
            'Application_Model_KluczeHistory' => array('pomieszczenia'),
        );
    }

    /**
     * Function outdates documents after new repository version
     *
     * @param $updatedObjects []
     */
    public function eventOperationComplete()
    {
        $db = (new Application_Service_RepositoryModel())->getAdapter();

        $updateVersionsQuery = $db->query('update documents d LEFT JOIN (SELECT d.id, MAX(dro.version_status) version FROM documents d left join documents_repo_objects dro on d.id = dro.document_id WHERE d.active IN (1,2,3) GROUP BY d.id) jv ON jv.id = d.id SET d.active = jv.version');
        $updatedDocumentsCounter = $updateVersionsQuery->rowCount();

        if ($updatedDocumentsCounter > 0) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger')->addMessage(Application_Service_Utilities::getFlashMessage('Zmieniono (' . $updatedDocumentsCounter . ') wersje dokumentów!', 'danger'));
        }

        return;


        foreach ($updatedObjects as $updatedObject) {
//            vdie($updatedObjects);
            if (!empty($updatedObject['versions'][Application_Service_Repository::VERSION_OUTDATED])) {
                // look for active documents with field in outdated or permissible version
                $outdatedDocumentIds = $this->documentsModel->getAdapter()->select()
                    ->from(array('d' => 'documents'), array('id'))
                    ->joinInner(array('dro' => 'documents_repo_objects'), 'dro.document_id = d.id', array())
                    ->where('d.active != ?', Application_Service_Documents::VERSION_ARCHIVE)
                    ->where('dro.object_id = ?', $updatedObject['objectId'])
                    ->where('dro.version_id IN (?)', $updatedObject['versions'][Application_Service_Repository::VERSION_OUTDATED])
                    ->query()
                    ->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($outdatedDocumentIds)) {
                    // disable or outdate them
                    $this->documentsModel->update(array('active' => Application_Service_Repository::VERSION_OUTDATED), array('id IN (?)' => $outdatedDocumentIds));

                    $this->increaseOutdatedDocumentsCounter(count($outdatedDocumentIds));
                }
            }

            if (!empty($updatedObject['versions'][Application_Service_Repository::VERSION_PERMISSIBLE])) {
                // look for active documents with field in outdated or permissible version
                $permissibleDocumentIds = $this->documentsModel->getAdapter()->select()
                    ->from(array('d' => 'documents'), array('id'))
                    ->joinInner(array('dro' => 'documents_repo_objects'), 'dro.document_id = d.id', array())
                    ->where('d.active != ?', Application_Service_Documents::VERSION_ARCHIVE)
                    ->where('dro.object_id = ?', $updatedObject['objectId'])
                    ->where('dro.version_id IN (?)', $updatedObject['versions'][Application_Service_Repository::VERSION_PERMISSIBLE])
                    ->query()
                    ->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($permissibleDocumentIds)) {
                    // disable or outdate them
                    $this->documentsModel->update(array('active' => Application_Service_Repository::VERSION_PERMISSIBLE), array('id IN (?)' => $permissibleDocumentIds));
                }
            }

            // look for active documents with field in outdated or permissible version
            $obligatoryDocumentIds = $this->documentsModel->getAdapter()->select()
                ->from(array('d' => 'documents'), array('id'))
                ->joinInner(array('dro' => 'documents_repo_objects'), 'dro.document_id = d.id', array())
                ->where('d.active != ?', Application_Service_Documents::VERSION_ARCHIVE)
                ->where('dro.object_id = ?', $updatedObject['objectId'])
                ->where('dro.version_id = ?', $updatedObject['versions'][Application_Service_Repository::VERSION_OBLIGATORY])
                ->query()
                ->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($obligatoryDocumentIds)) {
                // disable or outdate them
                $this->documentsModel->update(array('active' => Application_Service_Repository::VERSION_OBLIGATORY), array('id IN (?)' => $obligatoryDocumentIds));
            }
        }

        if ($this->outdatedDocumentsCounter > 0) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger')->addMessage($this->controller->showMessage('Dokumenty (' . $this->outdatedDocumentsCounter . ') wymagają aktualizacji!', 'danger'));
        }
    }

    public function getDependedDocumentTemplates($affectedFields)
    {
        $templates = $this->documenttemplatesModel->getAdapter()->select()
            ->from(array('d' => 'documents'))
            ->where('d.active = 1')
            ->where('d.content REGEXP = ?', implode($affectedFields))
            ->query(PDO::FETCH_ASSOC);

        return $templates;
    }

    /**
     * @param array $osobyIds
     * @throws Exception
     */
    public function updatePendingDocuments($osobyIds = [])
    {
        $dateupdate = date('Y-m-d');
        $documentsService = new Application_Service_Documents();
        $paginator = $documentsService->createDocuments($dateupdate, [
            'osobyIds' => $osobyIds
        ], 'get_pending_documents');

        $params = [
            'dp.status IN (?)' => [Application_Model_DocumentsPending::STATUS_PENDING, Application_Model_DocumentsPending::STATUS_ACCEPTED],
        ];
        if (!empty($osobyIds)) {
            $params['user_id IN (?)'] = $osobyIds;
        }

        $currentPendingDocuments = $this->documentsPendingModel->getList($params);
        $createPending = [];

        foreach ($paginator as $pendingData) {
            $pendingUserId = $pendingData['pending_user_id'];
            $pendingDocumenttemplateId = $pendingData['pending_documenttemplate_id'];

            $found = false;
            foreach ($currentPendingDocuments as &$currentPendingDocument) {
                if ($currentPendingDocument['user_id'] == $pendingUserId && $currentPendingDocument['documenttemplate_id'] == $pendingDocumenttemplateId) {
                    $currentPendingDocument['preserve'] = true;
                    $found = true;
                }
            }

            if (!$found) {
                $createPending[] = [
                    'user_id' => $pendingUserId,
                    'documenttemplate_id' => $pendingDocumenttemplateId,
                    'document_id' => null,
                    'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                ];
            }
        }

        foreach ($currentPendingDocuments as $currentPendingDocument) {
            if (empty($currentPendingDocument['preserve'])) {
                $this->documentsPendingModel->remove($currentPendingDocument['id']);
            }
        }

        foreach ($createPending as $pendingDocumentData) {
            $this->documentsPendingModel->save($pendingDocumentData);
        }
    }

    public function getRepository()
    {

    }


    public function createDocuments($dateupdate, $params = array(), $mode = 'actualize', $actualizeContinue = true)
    {

        $newDocumentsCounter = 0;
        $workerWhere = $pendingDocuments = [];
        $repositoryService = Application_Service_Repository::getInstance();
        $tasksService = Application_Service_Tasks::getInstance();
        //predefined tags
        $definedTemplateVariables = $this->documentsPrinter->getTemplateVariables();

        $t_setting = $this->settingsModel->fetchRow('id = 1');
        $companyname = $t_setting->value;

        if (!empty($params['osobyIds'])) {
            $workerWhere['id IN (?)'] = $params['osobyIds'];
        }
        if (!empty($params['documents'])) { 
            //get worker id of pending document i.e registries_entries table id
            $workerWhere['id IN (?)'] = Application_Service_Utilities::getValues($params['documents'], 'worker_id');
        }

        $paramDocumenttemplateIds = !empty($params['documenttemplateIds']) ? $params['documenttemplateIds'] : null;
        $documenttemplatesWhere = [
            'active = ?' => 1,
            'type <> ?' => 4,
        ];
        if ($paramDocumenttemplateIds) {
            $documenttemplatesWhere = ['id IN (?)' => $paramDocumenttemplateIds];
        }

        $t_documenttemplates = $this->documenttemplatesModel->fetchAll($documenttemplatesWhere);

        /** @var object[] $t_worker */
        $t_worker = $this->registryEntriesModel->fetchAll($workerWhere);

        $workerIds = array();
        foreach ($t_worker as $worker) {
            $workerIds[] = $worker->id;
        }

        if (empty($workerIds)) {
            return null;
        }

        $latestDocuments = $documenttemplatesOsoby = $numberingschemeIds = $documenttemplateIds = array();
        foreach ($t_documenttemplates as $documenttemplate) {
            $documenttemplateIds[] = $documenttemplate->id;
            $numberingschemeIds[] = $documenttemplate->numberingscheme_id;
        }

        $objectsRepository = new Application_Service_RepositoryObjects();
        $repositoryRetreiver = $objectsRepository->prepareRetreiver($workerIds, $documenttemplateIds, $numberingschemeIds);

        $wheredocumenttemplateIds = array();
        if(!empty($documenttemplateIds)){
            $wheredocumenttemplateIds = array('documenttemplate_id IN (?)' => $documenttemplateIds);
        }

        if (!empty($documenttemplateIds)) {
            $documenttemplatesOsoby = $this->documenttemplatesosobyModel->fetchAll(array('documenttemplate_id IN (?)' => $documenttemplateIds))->toArray();
            Application_Service_Utilities::indexBy($documenttemplatesOsoby, 'documenttemplate_id', true);
        }

        $latestDocuments = array();
        if(!empty($documenttemplateIds)){
            $latestDocuments = $this->documentsModel->getLatestDocuments($workerIds, $documenttemplateIds);
        }

        if (!empty($documenttemplateIds)) {
            $latestDocuments = $this->documentsModel->getLatestDocuments($workerIds, $documenttemplateIds);
            Application_Service_Utilities::indexBy($latestDocuments, ['documenttemplate_id', 'worker_id']);
        }

        if (!empty($params['documents'])) {
            $documentsToCreate = $params['documents'];
        } else {
            $documentsToCreate = [];
            foreach ($workerIds as $worker) {
                foreach ($documenttemplateIds AS $documenttemplateId) {
                    $documentsToCreate[] = array(
                        'documenttemplate_id' => $documenttemplateId,
                        'worker_id' => $worker->id,
                    );
                }
            }
        }

        $repoDocumentsIds = [];
        foreach ($documentsToCreate as $documentData) {
            if (isset($documentData['document_id'])) {
                $repoDocumentsIds = $documentData['document_id'];
            }
        }

        $repositoryRetreiver->load('object.document', array('id' => $repoDocumentsIds));

        $sourceConfig = [];
        $source = Application_Service_Utilities::getModel('FileSources')->getOne([
            'role' => Application_Model_FileSources::ROLE_DEFAULT_SOURCE,
        ], false);
        if ($source) {
            $sourceConfig = json_decode($source['config'], true);
            $appId = Application_Service_Utilities::getAppId();
            $systemFolder = 'kryptos.' . $appId;
        }
        vd('documentsToCreate', $documentsToCreate);
        foreach ($documentsToCreate AS $documentData) {
            $documenttemplateId = $documentData['documenttemplate_id'];
            $workerId = $documentData['worker_id'] ?: 0;

            $templateWorker = !empty($documenttemplatesOsoby[$documenttemplateId]) ? $documenttemplatesOsoby[$documenttemplateId] : [];

            Application_Service_Utilities::indexBy($templateWorker, 'worker_id');

            if (count($templateWorker) != 0 && !isset($templateWorker[$workerId])) {
                continue;
            }
//Archived
            $latestDocument = isset($latestDocuments[$documenttemplateId][$workerId]) ? $latestDocuments[$documenttemplateId][$workerId] : null;

            // if ($latestDocument) {
            //     /* Ankit existing function changes to apply condition for actulizing documents start */
            //     if (in_array($mode, ['actualize', 'get_pending_documents']) && $actualizeContinue == true && (int) $latestDocument->active === Application_Service_Documents::VERSION_OBLIGATORY) {
            //         continue;
            //     } elseif ($mode === 'actualize') {
            //         $latestDocument->active = Application_Service_Documents::VERSION_ARCHIVE;
            //         $latestDocument->archived_at = date('Y-m-d H:i:s');
            //         $latestDocument->save();
            //     } elseif ($mode === 'replace') {
            //         $this->documentsRepoObjectsModel->delete(['document_id = ?' => $latestDocument->id]);

            //     }
            //     /* Ankit existing function changes to apply condition for actulizing documents close */
            // } elseif ($mode === 'replace') {
            //     continue;
            // }

            // $repoObjects = array_fill_keys($repositoryService->getAllObjectsNames(), array());


// made variables for Active ---------------------------------------------------------------------------------.
            $documenttemplate = $repositoryRetreiver->fetch('documenttemplate', array('documenttemplate_id' => $documenttemplateId));

            // if (empty($documenttemplate)) {
            //     Throw new Exception('No documenttemplate in repository', 500);
            // }
            // $repoObjects['documenttemplate'][] = $documenttemplate['id'];


// made variables for Active ---------------------------------------------------------------------------------.
            $numberingscheme = $repositoryRetreiver->fetch('numberingscheme', array('numberingscheme_id' => $documenttemplate['numberingscheme_id']));

            // if (empty($numberingscheme)) {
            //     Throw new Exception('No numberingscheme in repository', 500);
            // }
            // $repoObjects['numberingscheme'][] = $numberingscheme['id'];

            // $templateVariables = $this->getTemplateVariabledUsedInTemplate($documenttemplate['content'], $definedTemplateVariables);

            // $hasZbiory = null;
            // $hasPomieszczenia = null;
            // foreach ($templateVariables as $templateVariable) {
            //     switch ($templateVariable) {
            //         case "imie":
            //             $repoObjects['osoba.imie'][] = $repositoryRetreiver->fetchVersion('osoba.imie', array('osoby_id' => $workerId));
            //             break;
            //         case "nazwisko":
            //             $repoObjects['osoba.nazwisko'][] = $repositoryRetreiver->fetchVersion('osoba.nazwisko', array('osoby_id' => $workerId));
            //             break;
            //         case "stanowisko":
            //             $repoObjects['osoba.stanowisko'][] = $repositoryRetreiver->fetchVersion('osoba.stanowisko', array('osoby_id' => $workerId));
            //             break;
            //         case "login_do_systemu":
            //             $repoObjects['osoba.login'][] = $repositoryRetreiver->fetchVersion('osoba.login', array('osoby_id' => $workerId));
            //             break;
            //         case "nazwa_firmy":
            //             break;
            //         case "zbiory":
            //             $hasZbiory = false;
            //             $setUpowaznienia = $repositoryRetreiver->fetch('set.upowaznienia', array('subject_id' => $workerId));
            //             if (empty($setUpowaznienia)) {
            //                 break;
            //             }
            //             $repoObjects['set.upowaznienia'][] = $setUpowaznienia['id'];
            //             $dataUpowaznienia = $repositoryRetreiver->fetchAll('upowaznienie', array('osoby_id' => $workerId));
            //             $repoZbior = array();
            //             foreach ($dataUpowaznienia AS $upowaznienie) {
            //                 if ($upowaznienie['czytanie'] || $upowaznienie['pozyskiwanie'] || $upowaznienie['wprowadzanie'] || $upowaznienie['modyfikacja'] || $upowaznienie['usuwanie']) {
            //                     $repoObjects['upowaznienie'][] = $upowaznienie['id'];
            //                     $repoZbior[] = $upowaznienie['zbiory_id'];
            //                     $hasZbiory = true;
            //                 }
            //             }
            //             if ($hasZbiory) {
            //                 $repoObjects['zbior.nazwa'] = array_merge($repoObjects['zbior.nazwa'], $repositoryRetreiver->fetchVersions('zbior.nazwa', array('zbiory_id' => array_unique($repoZbior))));
            //             }
            //             break;
            //         case "pomieszczenia":
            //             $hasPomieszczenia = false;
            //             $setKlucze = $repositoryRetreiver->fetch('set.klucze', array('subject_id' => $workerId));
            //             if (empty($setKlucze)) {
            //                 break;
            //             }
            //             $repoObjects['set.klucze'][] = $setKlucze['id'];
            //             $repoPomieszczenia = array();
            //             $repoBudynek = array();
            //             $dataKlucze = $repositoryRetreiver->fetchAll('klucz', array('osoby_id' => $workerId));
            //             foreach ($dataKlucze AS $klucz) {
            //                 $repoObjects['klucz'][] = $klucz['id'];
            //                 $dataPomieszczenie = $repositoryRetreiver->fetch('pomieszczenie.nazwa', array('pomieszczenia_id' => $klucz['pomieszczenia_id']));
            //                 $repoPomieszczenia[] = $dataPomieszczenie['id'];
            //                 $dataBudynek = $repositoryRetreiver->fetch('budynek.nazwa', array('budynki_id' => $dataPomieszczenie['budynki_id']));
            //                 $repoBudynek[] = $dataBudynek['id'];
            //                 $hasPomieszczenia = true;
            //             }
            //             if ($hasPomieszczenia) {
            //                 $repoObjects['pomieszczenie.nazwa'] = array_merge($repoObjects['pomieszczenie.nazwa'], $repositoryRetreiver->fetchVersions('pomieszczenie.nazwa', array('id' => array_unique($repoPomieszczenia))));
            //                 $repoObjects['budynek.nazwa'] = array_merge($repoObjects['budynek.nazwa'], $repositoryRetreiver->fetchVersions('budynek.nazwa', array('id' => array_unique($repoBudynek))));
            //             }
            //             break;
            //         case "dokument":
            //             $repoObjects['object.document'][] = $repositoryRetreiver->fetchVersion('object.document', array('id' => $documentData['document_id']));
            //             break;
            //     }
            // }

            // if (($hasZbiory === false && $hasPomieszczenia === false)
            //     || ($hasZbiory === null && $hasPomieszczenia === false)
            //     || ($hasZbiory === false && $hasPomieszczenia === null)) {
            //     // skip if had
            //     continue;
            // }

            // if ($mode === 'get_pending_documents') {
            //     $pendingDocuments[] = [
            //         'pending_user_id' => $workerId,
            //         'pending_documenttemplate_id' => $documenttemplateId,
            //     ];
            //     continue;
            // }


// made variables for Active ---------------------------------------------------------------------------------.
             $numberingSchemeData = $this->getNumberingSchemeData($numberingscheme['type'], $dateupdate);
             vd($documenttemplate, $numberingSchemeData, $documenttemplateId, $dateupdate);
             $number = $this->getMaxNumber($documenttemplateId, $numberingSchemeData['start_date'], $numberingSchemeData['end_date']);

             $numbertxt = $numberingscheme['scheme'];
             $numbertxt = str_ireplace('[nr]', $number, $numbertxt);
             $numbertxt = str_ireplace('[yyyy]', date('Y', strtotime($dateupdate)), $numbertxt);
             $numbertxt = str_ireplace('[kw]', $numberingSchemeData['kw'], $numbertxt);
             $numbertxt = str_ireplace('[mm]', date('m', strtotime($dateupdate)), $numbertxt);
             $numbertxt = str_ireplace('[dd]', date('d', strtotime($dateupdate)), $numbertxt);
             $newContent = "";
             if ($latestDocument->id) {
                 $newContent = $this->documentsModel->getNewContent($latestDocument->id);
                 if ($newContent) {
                     $newContent = $newContent[0]['new_content'];
                 }
             }
            /* Ankit Code changes for getting content replaced */
             if (!empty($documentData['id']) && isset($documentData['id'])) {
                 $createContent = $this->documentsPrinter->printPendingDocuments(array($documentData['id']), true);
                 if (isset($createContent['0']['content']) && !empty($createContent['0']['content'])) {
                     $newContent = $createContent['0']['content'];
                 }
             }
            /* Ankit Code changes for getting content replaced */
            $t_ins = array(
                'created_at' => date('Y-m-d H:i:s'),
                'date' => $dateupdate,
                'worker_id' => $workerId,
                'active' => Application_Service_Documents::VERSION_OBLIGATORY,
                'documenttemplate_id' => $documenttemplateId,
                'registry_entry_id' => $documentData['registry_entry_id'],
                'number' => $number,
                'content' => $newContent,
                'numbertxt' => $numbertxt,
                'id_document_pending' => $documentData['id'],
            );
//Active
            if ($mode === 'actualize') {
                //at the same time , VERSION_PERMISSIBLE document become VERSION_ARCHIVE
                $activeDocument_active = $this->documentsModel->getDocumentActiveByWorkerIdAndDocumentTemplateId($documentData['worker_id'],$documentData['documenttemplate_id']);
                if(!empty($activeDocument_active['id']))
                {
                    $archivedDate = date('Y-m-d H:i:s');
                    $this->documentsModel->updateStatusArchive($activeDocument_active['id'],$archivedDate);
                }
                //  document Id which just created in personal document.
                $documentId = $this->documentsModel->insert($t_ins);
                $cur_user_id = $this->osobyModel->getUserIdFromWorkerId($workerId);
                //next round task when new active document 
                if(!empty($cur_user_id)) {
                    $t_ins['id'] = $documentId;
                    //$tasksService->eventNextRoundTaskCreate($t_ins, $cur_user_id);
                }
                
                // $tasksService->eventDocumentCreate($t_ins, $workerId);
                //end

// ------------------------------COMAGOM CODE START-------------------------------------
                //at the same time ,  pending permission become active permission.
                $recordOne = $this->permissionStatusModel->getOneOfPermissionStatus($documentData['registry_entry_id']);
                $recordOne['status'] = 1;
                $this->permissionStatusModel->edit($recordOne['id'],$recordOne);

// ------------------------------COMAGOM CODE END---------------------------------------
            } elseif ($mode === 'replace') {
                $latestDocument->content = $newContent;
                $latestDocument->created_at = date('Y-m-d H:i:s');
                $latestDocument->active = Application_Service_Documents::VERSION_OBLIGATORY;
                $latestDocument->save();
                $t_ins['id'] = $latestDocument->id;
                $documentId = $latestDocument->id;
            }

            if ($source && !Zend_Registry::getInstance()->get('config')->production->dev->disable_ftp_documents_upload) {
                $tempHandle = fopen('php://temp', 'r+');
                $binaryDocument = $this->documentsPrinter->getDocumentBinaryData([$documentId]);
                fwrite($tempHandle, $binaryDocument);
                fseek($tempHandle, 0);
                $ftp = Application_Service_Ftp::getInstance($sourceConfig['host'], null, $sourceConfig['user'], $sourceConfig['pass']);
                $ftp->upload(sprintf('%s/documents/generated/%s.pdf', $systemFolder, Application_Service_Utilities::standarizeName($t_ins['numbertxt'])), $tempHandle, $mode === 'replace');
                fclose($tempHandle);
            }

            $pendingDocument = $this->documentsPendingModel->fetchRow([
                'worker_id = ?' => $workerId,
                'documenttemplate_id = ?' => $documenttemplateId,
                'status IN (?)' => [Application_Model_DocumentsPending::STATUS_ACCEPTED, Application_Model_DocumentsPending::STATUS_PENDING],
            ]);
            if ($pendingDocument) {
                $pendingDocument->status = Application_Model_DocumentsPending::STATUS_CREATED;
                $pendingDocument->document_id = $documentId;
                $pendingDocument->save();
            }

            $repositoryService->documentInsertObjects($documentId, $repoObjects);

            $newDocumentsCounter++;
        }

        if ($mode === 'get_pending_documents') {
            return $pendingDocuments;
        } else {
            return $newDocumentsCounter;
        }
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

    private function getTemplateVariabledUsedInTemplate($documenttemplateContent, $definedTemplateVariables)
    {
        $found = array();

        foreach (array_keys($definedTemplateVariables) as $variableName) {
            if (preg_match('/\{'.preg_quote($variableName) .'[a-z0-9_\.]*\}/i', $documenttemplateContent) === 1) {
                $found[] = $variableName;
            }
        }

        return $found;
    }

    public function increaseOutdatedDocumentsCounter($count)
    {
        $this->outdatedDocumentsCounter += $count;
    }

    public function recallDocument($documentId, $reason, $date = null)
    {
        $document = $this->documentsModel->requestObject($documentId)->toArray();

        if (!$date) {
            $date = date('Y-m-d');
        }

        $document['is_recalled'] = true;
        $document['recall_date'] = $date;
        $document['archived_at'] = $date;
        $document['recall_reason'] = $reason;
        $document['recall_author'] = Application_Service_Authorization::getInstance()->getUserId();
        $document['active'] = self::VERSION_ARCHIVE;

        $this->documentsModel->save($document);

        switch ($document['type']) {
            case 2:
                $kluczeModel = Application_Service_Utilities::getModel('Klucze');
                $kluczeModel->recallUserAuthorization($document['osoba_id']);
                break;
            case 3:
                $upowaznieniaModel = Application_Service_Utilities::getModel('Upowaznienia');
                $upowaznieniaModel->recallUserAuthorization($document['osoba_id']);
                break;
        }

        $documenttemplate = $this->documenttemplatesModel->findOneBy(['type = ?' => 4, 'active' => 1]);

        if ($documenttemplate) {
            $this->createDocuments($date, [
                'documenttemplateIds' => [$documenttemplate['id']],
                'documents' => [[
                    'documenttemplate_id' => $documenttemplate['id'],
                    'user_id' => $document['osoba_id'],
                    'document_id' => $document['id'],
                ]]
            ]);
        }
    }

    public function moveToArchive(array $condition, $applyArchive = true)
    {
        /* Ankit existing function changes to apply condition for moving to archive start  */
        //do not move personal documents to archive by default
        if ($applyArchive) {
            $this->documentsModel->update([
                'active' => Application_Service_Documents::VERSION_ARCHIVE,
                'countingactive' => 0,
            ], $condition);
        }
        /* Ankit existing function changes to apply condition for moving to archive close  */
        $this->documentsPendingModel->update([
            'status' => Application_Model_DocumentsPending::STATUS_REMOVED
        ], $condition);
    }

    public function create($documentTemplateId, $workerId, $registryEntryId = 0, $checkDocument = false, $newWorkers = array())
    {   
       
        /* Ankit existing function changes to apply condition for creating documents  */
        if ($checkDocument) {
            //check any active personal document of current worker of document template exist in active state
            if (!empty($newWorkers) && !in_array($workerId, $newWorkers)) {
                // if new worker has been added 
           
                $checkActiveDocumentExists = $this->documentsModel->getAdapter()->select()
                        ->from(array('d' => 'documents'), array('id'))
                        ->where('d.documenttemplate_id = ?', $documentTemplateId)
                        ->where('d.worker_id = ?', $workerId)
                        ->where('d.active = ?', 1)
                        ->query()
                        ->fetchColumn();
                if ($checkActiveDocumentExists == false) {
                    //if no document of active status exist in personal document then create pending document otherwise do not create
                    $newDocumentPending = $this->documentsPendingModel->save([
                        'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                        'worker_id' => $workerId,
                        'registry_entry_id' => $registryEntryId,
                        'documenttemplate_id' => $documentTemplateId,
                    ]);
                }
            } else {
                $newDocumentPending = $this->documentsPendingModel->save([
                    'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                    'worker_id' => $workerId,
                    'registry_entry_id' => $registryEntryId,
                    'documenttemplate_id' => $documentTemplateId,
                ]);
            }
            
            //No need to create document with outdated status
            //check any document exist in documets table for selected template and worker id
            // $checkDocumentExists = $this->documentsModel->getAdapter()->select()
            //         ->from(array('d' => 'documents'), array('id'))
            //         ->where('d.documenttemplate_id = ?', $documentTemplateId)
            //         ->where('d.worker_id = ?', $workerId)
            //         ->where('d.registry_entry_id = ?', $registryEntryId)
            //         ->where('d.active = ?',1)
            //         ->query()
            //         ->fetchColumn();
       
            // if ($checkDocumentExists == true) {
                //if no document of exists in personal document then create document otherwise do not create
                // $selectedActiveDocument = $this->documentsModel->getActiveDocumentByRegistryEntryId($registryEntryId);

                // $newDocument = $this->documentsModel->updateActiveFieldByRegistryEntryID($selectedActiveDocument['id']);
                // $newDocument = $this->documentsModel->save([
                //     'active' => Application_Service_Documents::VERSION_PERMISSIBLE,
                //     'worker_id' => $workerId,
                //     'documenttemplate_id' => $documentTemplateId,
                // ]);
        
                $pendingDocument = $this->documentsPendingModel->requestObject($newDocumentPending->id)->toArray();
                // $documentContent = $this->documentsPrinter->getPendingDocumentPreview($pendingDocument['id']);
                $cache_file_name = 'pendingPreviewAction'.$pendingDocument['id'].'flag';
                $documentContentFlag = "true";
                $this->cache->save($documentContentFlag, $cache_file_name);
                // if ( ($cached_pendingDocumentContent = $this->cache->load($cache_file_name)) === false ) {
                //     $this->cache->save($documentContent, $cache_file_name);
                // } else {
                //     $this->cache->save($documentContent, $cache_file_name);
                // }
                // $this->documentsModel->save([
                //     'id' => $newDocument->id,
                //     'new_content' => $documentContent,
                //     'content' => $documentContent
                // ]);
                $documentId = $newDocument->id;
                return $pendingDocument;
            // } 
        } else {
            $newDocumentPending = $this->documentsPendingModel->save([
                'status' => Application_Model_DocumentsPending::STATUS_PENDING,
                'worker_id' => $workerId,
                'registry_entry_id' => $registryEntryId,
                'documenttemplate_id' => $documentTemplateId,
            ]);

            // $newDocument = $this->documentsModel->save([
            //     'active' => Application_Service_Documents::VERSION_OUTDATED,
            //     'worker_id' => $workerId,
            //     'registry_entry_id' => $registryEntryId,
            //     'documenttemplate_id' => $documentTemplateId,
            // ]);
    
            $pendingDocument = $this->documentsPendingModel->requestObject($newDocumentPending->id)->toArray();
            // $documentContent = $this->documentsPrinter->getPendingDocumentPreview($pendingDocument['id']);
            $cache_file_name = 'pendingPreviewAction'.$pendingDocument['id'].'flag';
            $documentContentFlag = "true";
            $this->cache->save($documentContentFlag, $cache_file_name);
            // if ( ($cached_pendingDocumentContent = $this->cache->load($cache_file_name)) === false ) {
            //     $this->cache->save($documentContent, $cache_file_name);
            // } else {
            //     $this->cache->save($documentContent, $cache_file_name);
            // }

           /* $this->documentsModel->save([
                'id' => $newDocument->id,
                'active' => Application_Service_Documents::VERSION_OUTDATED,                
                'new_content' => $documentContent,
                'content' => $documentContent
            ]);*/
//------------------------ My CODE START -----------------------
            return $pendingDocument;
//------------------------ My CODE END -----------------------
        }         
        
        /* Ankit existing function changes to apply condition for creating documents close  */
    }

    /**
     * @param $documentTemplateId
     * @param $recreatePendings
     * @throws Exception
     */
    public function resetForTemplate($documentTemplateId, $recreatePendings = false, $newWorkers = array(), $skipWorkers = [])
    {
        $dataToArchive = [
            'documenttemplate_id = ?' => $documentTemplateId,
        ];
        
        if (!empty($skipWorkers)) {
            $dataToArchive['worker_id NOT IN (?)'] = $skipWorkers;
        }
        
        $this->moveToArchive($dataToArchive,false);

        if (!$recreatePendings) {
            return;
        }

        /** @var object $template */
        $template = $this->documenttemplatesModel->getOne($documentTemplateId);
        /** @var Application_Model_RegistryEntries $entriesRepository */
        $entriesRepository = Application_Service_Utilities::getModel('RegistryEntries');
        /** @var Application_Service_RegistryEntryRow[] $entries */
        $entries = $entriesRepository->getList(['registry_id = ?' => $template->registry_id]);
        
        $processedWorkerIds = [];
        $createdDocuments = [];
        
        foreach ($entries as $entry) {
            foreach ($entry->getDocumentWorkerIds($documentTemplateId) as $workerId) {
                if (in_array($workerId, $processedWorkerIds) || in_array($workerId, $skipWorkers)) {
                    continue;
                }
                /* Ankit existing function changes to apply condition for creating document start  */
                // $this->create($documentTemplateId, $workerId, $entry->id, true, $newWorkers);
                $documentId = $this->create($documentTemplateId, $workerId, $entry->id, true, $newWorkers);
                /* Ankit existing function changes to apply condition for creating document close  */
                if ($documentId != 0) {
                    $createdDocuments[] = $documentId;
                }
                $processedWorkerIds []= $workerId;
            }
        }
        
        return $createdDocuments;
    }

    public function loadForm($document)
    {
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

            $registryEntry = $registryEntriesModel->getListFromSelect($select);
            if (!empty($registryEntry)) {
                $registryEntry = $registryEntry[0];
                $registryEntry->loadData(['author', 'registry']);
                Application_Service_Registry::getInstance()->entryGetEntities($registryEntry);

                $document['form'] = $registryEntry;
            }
        }
    }
    
    public function getTemplateName($document_id)
    {
        if(!$document_id) return false;
        $userSignup = Application_Service_Utilities::getModel('Documenttemplates');
        $name = $userSignup->getAdapter()->select()
            ->from(array('dt' => 'documenttemplates'), array('template_name' => 'name'))
            ->joinInner(['d' => 'documents'], 'd.documenttemplate_id = dt.id', [])
            ->where('d.id = ?', $document_id)
            ->query()
            ->fetchColumn();
            
        if (!$name) {
            return false;
        }

        return $name;
    }
}
