<?php

use Application_Service_Licenses as LicenseService;
use Application_Service_LicenseSubscriptions as LicenseSubscriptionsService;
use Application_Service_Osoby as PersonService;
use Application_Service_Exception_NotFoundException as NotFoundException;

class LicenseSubscriptionsController extends Muzyka_Admin
{
    /** @var LicenseService */
    protected $licenseService;

    /** @var LicenseSubscriptionsService */
    protected $subscriptionsService;

    /** @var PersonService */
    protected $personsService;

    /**
     * @throws Exception
     */
    public function init()
    {
        parent::init();
        $this->licenseService = LicenseService::getInstance();
        $this->subscriptionsService = LicenseSubscriptionsService::getInstance();
        $this->personsService = PersonService::getInstance();
        $this->setSection('Licencje');
    }

    /**
     * License list subscriptions
     * @throws Exception
     */
    public function indexAction()
    {
        $this->setDetailedSection('Lista subskrypcji');
        $this->view->paginator = $this->subscriptionsService->getList();
    }

    /**
     * View license subscription
     * @throws NotFoundException
     */
    public function viewAction()
    {
        $this->setDetailedSection('Informacje o subskrypcji');
        $this->view->data = $this->subscriptionsService->get(
            $id = $this->getRequest()->getParam('id', 0)
        );
        $this->view->trialPeriodUnits = $this->licenseService->getTrialPeriodsUnits();
        $this->view->userTypes = $this->personsService->getUserTypes();
        $this->view->activityLog = $this->subscriptionsService->getActivity($id);
        $this->view->activityTypes = $this->subscriptionsService->getActivityTypes();
    }
    
    public function updateHistoryAction()
    {
        $request = $this->getRequest();
        if($request->getPost('submit_type'))
        {
            try{
                $data = $request->getPost();
                $id = $request->getPost('hidden_id');
                $LicenseSubcriptionRepository = Application_Service_Utilities::getModel('LicenseSubscription');
                $where = array('id = ?' => $id);
                $LicenseSubcriptionRepository->updateData($data, $where);
                $this->flashMessage('success', 'Data Saved');
                
            }catch(\Exception $ex){
                $err_msg = $ex->getMessage();
                $this->flashMessage('danger', $err_msg);
            }
            $this->redirect('/licenses/manage-license-history');
        }
    }
}
