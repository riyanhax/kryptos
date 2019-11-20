<?php

use Application_Model_Role as RoleRepository;
use Application_Model_Osoby as PersonsRepository;
use Application_Model_Osobydorole as PersonRolesRepository;
use Application_Model_Klucze as KeysRepository;
use Application_Model_Pomieszczenia as RoomsRepository;

class Application_Service_Osoby
{
    /** @var self */
    protected static $_instance;

    /** @var PersonsRepository */
    protected $personsRepository;

    /** @var PersonRolesRepository */
    protected $personRolesRepository;

    /** @var KeysRepository */
    protected $keysRepository;

    /** @var RoomsRepository */
    protected $roomsRepository;

    /** @var RoleRepository */
    protected $roleRepository;

    /** @var array */
    protected $duplicateRoleWarning;

    /**
     * @return self
     * @throws Exception
     */
    public static function getInstance() {
        if (!self::$_instance) {
            /** @var PersonsRepository $personsRepository */
            $personsRepository = Application_Service_Utilities::getModel('Osoby');
            /** @var PersonRolesRepository $personRolesRepository */
            $personRolesRepository = Application_Service_Utilities::getModel('Osobydorole');
            /** @var KeysRepository $keysRepository */
            $keysRepository = Application_Service_Utilities::getModel('Klucze');
            /** @var RoomsRepository $roomsRepository */
            $roomsRepository = Application_Service_Utilities::getModel('Pomieszczenia');
            /** @var RoleRepository $roleRepository */
            $roleRepository = Application_Service_Utilities::getModel('Role');
            self::$_instance = new self(
                $personsRepository,
                $personRolesRepository,
                $keysRepository,
                $roomsRepository,
                $roleRepository
            );
        }
        return self::$_instance;
    }

    /**
     * @param PersonsRepository $personsRepository
     * @param PersonRolesRepository $personRolesRepository
     * @param KeysRepository $keysRepository
     * @param RoomsRepository $roomsRepository
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        PersonsRepository $personsRepository,
        PersonRolesRepository $personRolesRepository,
        KeysRepository $keysRepository,
        RoomsRepository $roomsRepository,
        RoleRepository $roleRepository
    ) {
        $this->personsRepository = $personsRepository;
        $this->personRolesRepository = $personRolesRepository;
        $this->keysRepository = $keysRepository;
        $this->roomsRepository = $roomsRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param $id
     * @return DbRow|object
     * @throws NotFoundException
     * @throws Exception
     */
    public function get($id) {
        if ($license = $this->personsRepository->getOne($id)) {
            return $license;
        }
        throw new NotFoundException('License subscription not found: '.$id);
    }
    /**
     * @param string $email
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function getByEmail($email)
    {
        return $this->personsRepository->getUserByEmail($email);
    }
    
    /**
     * @param Zend_Db_Table_Row $person
     * @param string [$newPassword]
     */
    public function setPassword($person, $newPassword = null)
    {
        //$this->userRepository->savePassword($person, $newPassword);
    }

    /**
     * @return array
     */
    public function getUserTypes()
    {
        return [
            PersonsRepository::USER_TYPE_EXPERT => "EKSPERT",
            PersonsRepository::USER_TYPE_PRO => "ZAWODOWIEC",
            PersonsRepository::USER_TYPE_MINI => "MINI"
        ];
    }
    
    /**
     * @param $data
     * @return null
     * @throws Exception
     */
    public function save($req)
    {
        
        $aPost = $req->getPost();
        $this->rightsModel = Application_Service_Utilities::getModel('TypeRights');
        $this->rightsPermissionsModel = Application_Service_Utilities::getModel('TypeRightsPermissions');
        $registryUserPermissionsModel = Application_Service_Utilities::getModel('RegistryUserPermissions');

        try {
            if(!empty($req->getPost())){
                $accType = $req->getPost('empType', '');
                $userId = $req->getPost('id','');
                $roles = $req->getPost('role', '');
                $pageName = $req->getPost('page_name', 'create');
                $proposalRole = $req->getPost('proposal_role', '');
                $roles = $req->getPost('role', '');
                $rights = $req->getPost('rights', false);
                $rightsPermissions = $req->getPost('rightsPermissions', false);
                $password = $req->getPost('password', '');
                $passwordRepeat = $req->getPost('password_repeat', '');
                $isAdmin = $req->getPost('isAdmin', 0);
                $new_pass1 = $password;
                $new_pass2 = $passwordRepeat;
                $data = $req->getPost();
            }
            else{
            $accType = $req->getParam('empType', '');
            $userId = $req->getParam('id','');
            $roles = $req->getParam('role', '');
            $pageName = $req->getParam('page_name', 'update');
            $proposalRole = $req->getParam('proposal_role', '');
            $roles = $req->getParam('role', '');
            $rights = $req->getParam('rights', false);
            $rightsPermissions = $req->getParam('rightsPermissions', false);
            $password = $req->getParam('password', '');
            $passwordRepeat = $req->getParam('password_repeat', '');
            $isAdmin = $req->getParam('isAdmin', 0);
            $new_pass1 = $password;
            $new_pass2 = $passwordRepeat;
            $data = $req->getParams();
            }
            
            $osoba = null;
            $usersModel = Application_Service_Utilities::getModel('Users');

            $isBasicForm = in_array($pageName, ['create', 'update']);
            if ($pageName === 'update') {
                $osoba = $this->personsRepository->getOne($userId);
                if (!($osoba instanceof Zend_Db_Table_Row)) {
                    throw new Exception('Podany rekord nie istnieje');
                }
            } elseif ($pageName === 'create') {
                $data['login_do_systemu'] = $this->personsRepository->generateUserLogin($data);
            } elseif ($pageName === 'proposal') {
                $data['type'] = PersonsRepository::TYPE_EMPLOYEE_DRAFT;
            }

            if ($isBasicForm || in_array($proposalRole, ['abi', 'lad', 'create'])) {
                $roleAlreadyTaken = $this->validateRole($roles, $userId, $this->specialRoles);
                if (!empty($roleAlreadyTaken)) {
                    list($osobaRole, $rolaId) = $this->duplicateRoleWarning;
                    $rola = $this->roleRepository->get($rolaId);
                    $rolaNazwa = $rola['nazwa'];
                    $osoba = $this->personsRepository->get($osobaRole['osoby_id']);
                    $login = $osoba['login_do_systemu'];

                    $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage(sprintf('Rolę %s posiada użytkownik %s. W dokumentacji ODO może być tylko jeden użytkownik o takiej roli. By zmienić obecnego %s skorzystaj z funkcji Wyznacz %s', $rolaNazwa, $login, $rolaNazwa, $rolaNazwa), 'danger'));
                    $this->_redirect($_SERVER ['HTTP_REFERER']);
                }

                $roleAlreadyTaken = $this->validateRole($roles, $userId, $this->switchRoles);
                if (!empty($roleAlreadyTaken)) {
                    foreach ($roleAlreadyTaken as $roleId) {
                        $this->personRolesRepository->delete(['role_id = ?' => $roleId]);
                    }
                }

                if ($rights) {
                    foreach ($rights as $rel => $right) {
                        $items[$rel] = (int)!empty($right);
                    }
                    $data['rights'] = json_encode($items);

                }else{
                    
                    if (empty($accType)) {
                        $accType = 'mini';
                    }
                    $data['rights'] = $this->rightsModel->getBytype($accType);
                }

                $items = [];
                if ($rightsPermissions) {
                    foreach ($rightsPermissions as $rel => $right) {
                        $items[$rel] = (int)!empty($right);
                    }
                    $data['rightsPermissions'] = json_encode($items);
                }else{
                     if (empty($accType)) {
                        $accType = 'mini';
                    }
                    $data['rightsPermissions'] = $this->rightsPermissionsModel->getBytype($accType);
                }

                $userId = $this->personsRepository->save($data);
                // Adding users to registry to maintain edit registry users permisions
                if(!empty($data['rightsPermissions']))
                {
                    $rightsPermissions = json_decode($data['rightsPermissions'], true);
                    foreach ($rightsPermissions as $registry => $perm) {
                        if(substr_count($registry, '/') > 1 && $perm)
                        {
                            $rights = explode('/', $registry);
                            

                            $row = Application_Service_Utilities::getModel('RegistryPermissions')->getOne(['registry_id' => $rights[1], 'system_name' => $rights[2]], true);
                            if($row)
                            {
                                $userPermissionData = [
                                    'registry_id' => $rights[1],
                                    'user_id' => $userId,
                                    'registry_permission_id' => $row->id,
                                ];
                                $existedUserPermission = $registryUserPermissionsModel->getOne($userPermissionData);
                                if (!$existedUserPermission) {
                                    $registryUserPermissionsModel->save($userPermissionData);
                                }
                            }
                        }
                        $assignees = array();
                        $assignees['registry_id'] = (int) $rights[1];
                        $assignees['user_id'] = $userId;
                        
                        //insert into model
                        if($assignees['registry_id'] > 0)
                        {
                            $registryAssigneesModel = Application_Service_Utilities::getModel('RegistryAssignees');
                            $existedUserAssignees = $registryAssigneesModel->getOne($assignees);
                            if (!$existedUserAssignees) {
                                $assignees['created_at'] = date('Y-m-d H:i:s');
                                $assignees['updated_at'] = NULL;
                                $registryAssigneesModel->save($assignees);
                            }
                        }
                    }

                    
                }

                $this->clearRoles($userId);
                $this->saveRoles($roles, $userId);
                Application_Service_Utilities::getModel('OsobyGroups')->saveUserGroups($userId, $data['groups']);
            }

            if (!$osoba) {
                $osoba = $this->personsRepository->getOne($userId);
            }

            if ($isBasicForm) {
                if ($new_pass1 != '' && $new_pass2 != '') {
                    $passwordErrors = [];

                    if ($new_pass1 !== $new_pass2) {
                        $passwordErrors[] = 'Hasła powinni być takie same';
                    }
                    if (strlen($new_pass1) < 10) {
                        $passwordErrors[] = 'Minimalna długość hasła do 10 znaków';
                    }
                    if (strlen($new_pass1) > 15) {
                        $passwordErrors[] = 'Maksymalna długość hasła do 15 znaków';
                    }
                    if (preg_match('/[0-9]+/', $new_pass1) == 0) {
                        $passwordErrors[] = 'Wymagana jest przynajmniej jedna cyfra';
                    }
                    if (preg_match('/[A-ZĘÓĄŚŁŻŹĆŃ]+/', $new_pass1) == 0) {
                        $passwordErrors[] = 'Wymagana jest przynajmniej jedna wielka litera';
                    }
                    if (preg_match('/[a-zęóąśłżźćń]+/', $new_pass1) == 0) {
                        $passwordErrors[] = 'Wymagana jest przynajmniej jedna mała litera';
                    }
                    if (preg_match('/[[:punct:]]+/', $new_pass1) == 0) {
                        $passwordErrors[] = 'Wymagana jest przynajmniej jeden znak interpunkcyjny';
                    }

                    if (!empty($passwordErrors)) {
                        $this->flashMessage('error', implode('<br>', $passwordErrors));
                        $this->_redirect($_SERVER ['HTTP_REFERER']);
                    }
                }

                if (!empty($password)) {
                    if ($password !== $passwordRepeat) {
                        $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Hasła powinny być takie same', 'danger'));
                        $this->_redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                
                $usersModel->savePassword($osoba, $password, $isAdmin);
//                update email id user table
                if(!empty($req->getPost())){
                    if($isAdmin){
                        $emailupdate = array(
                    'email' => $osoba->email,
                    'company_confirmation'=>1,
                    'id_role'=>3
                );
                    }else{
                    
                    $emailupdate = array(
                    'email' => $osoba->email,
                    'company_confirmation'=>1
                    );}
                }else{
                $emailupdate = array(
                    'email' => $osoba->email,
                );
                }
                $where = $usersModel->getAdapter()->quoteInto('id = ?', $userId);

                $usersModel->update($emailupdate, $where);

                //end update email id user table

                
            }

            /*if ($isBasicForm || in_array($proposalRole, ['abi', 'lad'])) {
                $this->savePermissions($this->getParam('permissions'), $userId);

                $klucze = $data['klucze'] ?: '';
                $klucze = $this->_getSelectedValues($klucze);

                $this->saveKlucze($klucze, $userId);

                $zbiory = Application_Service_Utilities::getModel('Zbiory');
                $modelUpowaznienia = Application_Service_Utilities::getModel('Upowaznienia');

                $upowaznieniaRequest = $data['upowaznienia'] ?: [];

                foreach ($upowaznieniaRequest as $zbior_id => $ur) {
                    $zbior = $zbiory->getOne($zbior_id);
                    $t_upowaznienie = $modelUpowaznienia->fetchRow(sprintf('osoby_id = %d AND zbiory_id = %d', $osoba->id, $zbior->id));
                    if ($t_upowaznienie) {
                        $ur['id'] = $t_upowaznienie->id;
                    }

                    $modelUpowaznienia->save($ur, $osoba, $zbior);
                }
            }*/
            if ($isBasicForm && $pageName === 'create') {
                Application_Service_Tasks::getInstance()->eventUserCreate($userId);
            }

        } catch (Zend_Db_Exception $e) {
            /* @var $e Zend_Db_Statement_Exception */
            throw new Exception('Nie udał sie zapis do bazy' . $e->getMessage(), 500, $e);
        } catch(Application_SubscriptionOverLimitException $x){
            $this->_redirect('subscription/limit');
        } catch (Exception $e) {
            throw new Exception('Próba zapisu danych nie powiodła się' . $e->getMessage(), 500, $e);
        }

        return $osoba;
    }


    public function clearRoles($userId)
    {
        $userRoles = $this->personRolesRepository->getRolesByUser($userId);

        if (!($userRoles instanceof Zend_Db_Table_Row)) {
            foreach ($userRoles as $userRole) {
                $userRole->delete();
            }
        }
    }

    public function clearKlucze($userId, $checkedKlucze = null, $wycofanieFlaga = false)
    {
        $osobyKlucze = $this->keysRepository->getUserKlucze($userId);
        if (!($osobyKlucze instanceof Zend_Db_Table_Rowset)) {
            return;
        }
        foreach ($osobyKlucze as $klucze) {
            $id = $klucze->delete();
        }

        return $wycofanie;
    }

    public function saveRoles($roles, $id)
    {
        if (!is_array($roles)) {
            return;
        }
        foreach ($roles as $role) {
            $this->personRolesRepository->save($role, $id);
        }
    }

    public function saveKlucze($klucze, $id)
    {
        if (!is_array($klucze)) {
            $klucze = array();
        }

        $pomieszczenia = $this->roomsRepository->getAll();

        foreach ($pomieszczenia as $pomieszczenie) {
            $existing = $this->keysRepository->fetchRow(sprintf('osoba_id = %d AND pomieszczenia_id = %d', $id, $pomieszczenie->id));

            if (!in_array($pomieszczenie->id, $klucze)) {
                // not selected
                if ($existing) {
                    $this->keysRepository->removeElement($existing);
                }
            } else {
                // selected
                if (!$existing) {
                    $this->keysRepository->save($pomieszczenie->toArray(), $id);
                }
            }
        }
    }


    //
    private function validateDuplicationRole($roleId, $userId)
    {
        $this->duplicateRoleWarning = null;
        $person = $this->personRolesRepository->findUserWithRole($roleId);
        if (!($person instanceof Zend_Db_Table_Row)) {
            return true;
        }
        $person = $person->toArray();

        if ($userId !== $person ['osoby_id']) {
            $this->duplicateRoleWarning = array(
                $person,
                $roleId
            );
        }

        return $userId === $person ['osoby_id'];
    }

    private function validateRole($userRole, $userId, $rolesData)
    {
        $roleAlreadyTaken = [];
        if (is_array($userRole)) {
            $roles = array_intersect($rolesData, $userRole);
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    if (!$this->validateDuplicationRole($role, $userId)) {
                        $roleAlreadyTaken[] = $role;
                        break;
                    }
                }
            }
        }
        return $roleAlreadyTaken;
    }
}

