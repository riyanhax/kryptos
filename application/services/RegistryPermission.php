<?php

class Application_Service_RegistryPermission
{
    /** @var self */
    protected static $_instance = null;

    public static function getInstance() { return null === self::$_instance ? (self::$_instance = new self()) : self::$_instance; }

    public function getPermission($registryId)
    {
	$permissionModel = Application_Service_Utilities::getModel('RegistryUserPermissions');
	$user_id = Application_Service_Authorization::getInstance()->getUserId();
	if($permissionModel->getPermission($registryId, $user_id) > 0){
	    return true;
	}
	else{
	    return false;
	}
    }
    
}
?>
