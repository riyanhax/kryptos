<?php
class Base_Acl extends Zend_Acl
{
    const DEFAULT_ROLE = 'guest';
    
    const MODULE_ROUTE = 'default';
    
    protected static $instance;
    
    /**
     * @return Base_Acl
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new Base_Acl();
        }
        
        return self::$instance;
    }
    
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * @param integer $idRole
     * @return string
     */
    public function getRoleName($idRole)
    {
        $roleName = self::DEFAULT_ROLE;
        
        if (!empty($idRole)) {
            $model = new Application_Model_Roles();

            $select = $model->select()
                ->where('id = ?', $idRole);

            $row = $model->fetchRow($select);

            $roleName = $row->code;
        }
        
        return $roleName;
    }
    
    /**
     * Get role row by the given code
     * @param string $code
     * @return Application_Service_EntityRow
     * @throws Exception
     */
    public function getRoleByCode($code)
    {
        if (empty($code)) {
            throw new Exception('Role name cannot be empty');
        }
        
        $model = new Application_Model_Roles();
        
        $select = $model->select()
            ->where('code = ?', $code)
            ->where('NOT ghost');
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    public function init()
    {
        $dataResources = $this->getResourcesData();
        $dataRoles = $this->getRolesData();
        
        foreach ($dataResources as $rowResource) {
            $name = $this->getResourceName($rowResource->module, $rowResource->resource, $rowResource->privilege);
            
            if (!$this->has($name)) {
                $this->addResource($name);
            }
        }
        
        foreach ($dataRoles as $rowRole) {
            if (!$this->hasRole($rowRole->code)) {
                $this->addRole($rowRole->code, $rowRole->code_parent);
            }
            
            $dataRoleResources = $this->getRoleResourcesData($rowRole->id);
            
            foreach ($dataRoleResources as $rowRoleResource) {
                $resourceName = $this->getResourceName($rowRoleResource->module, $rowRoleResource->resource, $rowRoleResource->privilege);
                $this->allow($rowRole->code, $resourceName);
            }
        }
    }
    
    /**
     * Generate resource name based on given params
     * @param string $module
     * @param string $resource
     * @param string $privilege
     * @return string
     */
    public function getResourceName($module, $resource, $privilege)
    {
        $data = implode('.', [
            $module,
            $resource,
            $privilege,
        ]);
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        $allowed = false;
        
        if ($this->has($resource)) {
            $allowed = parent::isAllowed($role, $resource, $privilege);
        }
        
        return $allowed;
    }
    
    public function normalizeName($name)
    {
        $normalized = str_replace(['-'], [''], $name);
        
        return $normalized;
    }
    
    /**
     * Get list of default roles
     * @return Application_Service_EntityRowset
     */
    protected function getRolesData()
    {
        $model = new Application_Model_Roles();
        
        $select = $model->select()
            ->setIntegrityCheck(false)
            ->from(['r' => 'roles'])
            ->joinLeft(['rp' => 'roles'], 'rp.id = r.id_role_parent', ['code_parent' => 'code'])
            ->where('r.ghost IS NOT TRUE')
            ->order(new Zend_Db_Expr('r.id_role_parent IS NULL DESC, r.id_role_parent ASC'));
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * Get list of resources
     * @return Application_Service_EntityRowset
     */
    protected function getResourcesData()
    {
        $model = new Application_Model_Resources();
        
        $select = $model->select()
            ->where('ghost IS NOT TRUE');
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
    
    /**
     * Get list of role resources
     * @param integer $idRole
     * @return Application_Service_EntityRowset
     */
    protected function getRoleResourcesData($idRole)
    {
        $model = new Application_Model_RoleResources();
        
        $select = $model->select()
            ->setIntegrityCheck(false)
            ->from(['rr' => 'role_resources'])
            ->join(['r' => 'resources'], 'r.id = rr.id_resource', ['module', 'resource', 'privilege'])
            ->where('rr.ghost IS NOT TRUE')
            ->where('r.ghost IS NOT TRUE')
            ->where('rr.id_role = ?', $idRole);
        
        $data = $model->fetchAll($select);
        
        return $data;
    }
}
