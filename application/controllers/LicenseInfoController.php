<?php
class LicenseInfoController extends Muzyka_Admin
{
	public function init()
	{
		parent::init();
		$this->setSection('Manage Counts Cost');
	}

	public function indexAction()
	{
		$LicenseInfo = Application_Service_Utilities::getModel('LicenseInfo');
        $getLicenseInfo = $LicenseInfo->getLicenseCost();
        $this->view->getLicenseInfo = $getLicenseInfo;
	}

	public function updateCountsAction()
	{
		
		$LicenseInfo = Application_Service_Utilities::getModel('LicenseInfo');
		$request = $this->getRequest();
		
		if($request->getPost('update_counts') )
		{
			try{
				for($i=0; $i<count($request->getPost('license_cost_id')); $i++){
					$licenseInfoId = $request->getPost('license_cost_id')[$i];
					$licenseInfoCost = $request->getPost('license_cost')[$i];
					$where = array('id =?' => $licenseInfoId);
					$LicenseInfo->updateCount($licenseInfoCost, $where);
				}
				$this->flashMessage('success', 'Data Saved');
			}catch(\Exception $ex){
				$err_msg = $ex->getMessage();
                $this->flashMessage('danger', $err_msg);
			}
			$this->_redirect('/license-info');
		}
	}
}
?>
