<?php

use Application_Service_Licenses as LicenseService;
use Application_Service_Osoby as PersonService;
// use Application_Service_Exception_NotFoundException as NotFoundException;

class LicensesController extends Muzyka_Admin
{
    /** @var LicenseService */
    protected $licenseService;

    /** @var PersonService */
    protected $personsService;

    /**
     * @throws Exception
     */
    public function init()
    {
        parent::init();
        $this->licenseService = LicenseService::getInstance();
        $this->personsService = PersonService::getInstance();
        $this->setSection('Licencje');
    }

    public static function getPermissionsSettings() {
        $settings = array(
            'nodes' => array(
                'licenses' => array(
                    '_default' => array(
                        'permissions' => array('user/superadmin'),
                    ),
                    'history' => array(
                        'permissions' => array('user/admin'),
                    ),
                ),
            )
        );

        return $settings;
    }

    public function getTopNavigation($action='')
    {
        $this->setSectionNavigation([
            [
                'label' => 'Licencje',
                'path' => 'javascript:;',
                'icon' => 'fa fa-refresh',
                'rel' => 'licensees',
                'children' => [
                    [
                        'label' => 'Synchronize all with 3rd party service',
                        'path' => '/licenses/synchronize',
                        'icon' => 'icon-align-justify',
                        'rel' => 'admin'
                    ],
                ],
            ],
        ]);
    }

    /**
     * License list
     */
    public function indexAction()
    {
        $this->setDetailedSection('Lista licencji');
        $this->view->paginator = $this->licenseService->getList();
    }

    /**
     * Update license
     * @throws NotFoundException
     */
    public function updateAction()
    {
        if($id = $this->getRequest()->getParam('id', 0)){
            $license = $this->licenseService->get($id);
            $this->setDetailedSection('Edycja licencji');
        } else {
            $license = $this->licenseService->create();
            $this->setDetailedSection('Dodaj nową licencję');
        }
        $this->view->data = $license;
        $this->view->periodUnits = $this->licenseService->getPeriodsUnits();
        $this->view->trialPeriodUnits = $this->licenseService->getTrialPeriodsUnits();
        $this->view->userTypes = $this->personsService->getUserTypes();
        $this->view->currencies = $this->licenseService->getCurrencies();
    }

    /**
     * Save license
     * @throws Exception
     */
    public function saveAction()
    {
        $id = $this->licenseService->save(
            $this->getRequest()->getParams()
        );
        $this->licenseService->synchronize($id);
        $this->redirectToIndex();
    }

    /**
     * Delete license
     * @throws Exception
     */
    public function deleteAction()
    {
        $this->licenseService->remove(
            $id = $this->getRequest()->getParam('id', 0)
        );
        $this->licenseService->synchronize($id);
        $this->redirectToIndex();
    }

    /**
     * License list
     * @throws Exception
     */
    public function synchronizeAction()
    {
        $this->licenseService->synchronizeAll();
        $this->sendMessage('Successfully synchronized');
        $this->redirectToIndex();
    }

    /**
     * @param string $message
     */
    protected function sendMessage($message)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->_helper->getHelper('flashMessenger')
            ->addMessage($this->showMessage($message));
    }

    protected function redirectToIndex()
    {
        $this->redirect('/licenses');
    }

    /* Vipin code starts */
    public function historyAction(){
        $LicenseRepository = Application_Service_Utilities::getModel('License');
        $this->view->paginator = $LicenseRepository->licenseHistory();
    }
    
    public function manageLicenseHistoryAction()
    {
        $LicenseRepository = Application_Service_Utilities::getModel('License');
        $this->view->paginator = $LicenseRepository->manageHistory();
    }

    public function updateHistoryAction()
    {
        $this->setSection('Edit Subscription');
        $req = $this->getRequest();
        $id = $req->getParam('id', 0);

        $LicenseSubcriptionRepository = Application_Service_Utilities::getModel('LicenseSubscription');
        $getData = $LicenseSubcriptionRepository->getSubscriptionById($id);
        
        $this->view->data = $getData;
        $this->view->id = $id;
    }
    /* Vipin code end */
}
