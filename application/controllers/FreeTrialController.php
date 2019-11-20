<?php
use Application_Service_Licenses as LicenseService;
use Application_Service_FreeTrials as FreeTrialsService;
use Application_Service_Osoby as PersonService;
use Application_Service_Exception_NotFoundException as NotFoundException;
class FreeTrialController extends Muzyka_Admin
{
	public function init(){
		parent::init();
		$this->setSection('Free Trial');
	}

	public function indexAction(){
		$TrialRepository = Application_Service_Utilities::getModel('FreeTrial');
		$this->setDetailedSection('Bezpłatna wersja próbna');
       	$this->view->paginator = $TrialRepository->getList();
	}
}
?>