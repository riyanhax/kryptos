<?php

class PaymentsController extends Muzyka_Admin
{
    /** @var Application_Model_Payments */
    protected $paymentsModel;

    /** @var Application_Service_Payments */
    protected $paymentsService;

    protected $baseUrl = '/payments';

    public function init()
    {
        parent::init();
        $this->view->section = 'Lista uprawnień';
        Zend_Layout::getMvcInstance()->assign('section', 'Lista uprawnień');
        $this->view->baseUrl = $this->baseUrl;

        $this->paymentsModel = Application_Service_Utilities::getModel('Payments');
        $this->paymentsService = Application_Service_Payments::getInstance();
    }

    public static function getPermissionsSettings() {
        $baseIssetCheck = array(
            'function' => 'issetAccess',
            'params' => array('id'),
            'permissions' => array(
                1 => array('perm/permissions/create'),
                2 => array('perm/permissions/update'),
            ),
        );

        $settings = array(
            'modules' => array(
                'permissions' => array(
                    'label' => 'Pracownicy/Uprawnienia',
                    'permissions' => array(
                        array(
                            'id' => 'create',
                            'label' => 'Tworzenie wpisów',
                        ),
                        array(
                            'id' => 'update',
                            'label' => 'Edycja wpisów',
                        ),
                        array(
                            'id' => 'remove',
                            'label' => 'Usuwanie wpisów',
                        ),
                    ),
                ),
            ),
            'nodes' => array(
                'permissions' => array(
                    '_default' => array(
                        'permissions' => array('user/superadmin'),
                    ),

                    'index' => array(
                        'permissions' => array('perm/permissions'),
                    ),

                    'save' => array(
                        'getPermissions' => array(
                            $baseIssetCheck,
                        ),
                    ),
                    'update' => array(
                        'getPermissions' => array(
                            $baseIssetCheck,
                        ),
                    ),

                    'remove' => array(
                        'permissions' => array('perm/permissions/remove'),
                    ),

                ),
                'payments' => array(
                    'history' => array(
                        'permissions' => array('user/admin'),
                    ),
                    /* Vipin code starts */
                    'increase-users' => array(
                        'permissions' => array('user/admin'),
                    ),
                    'submit-increase-users' => array(
                        'permissions' => array('user/admin'),
                    ),
                    'paypal-callback' => array(
                        'permissions' => array('user/admin'),
                    ),
                    /* Vipin code end */
                ),
            )
        );

        return $settings;
    }

    public function historyAction()
    {
        $this->setDetailedSection('Historia płatności');
        $this->view->paginator = $this->paymentsModel->getList(['status = ?' => Application_Model_Payments::PAYMENT_STATUS_PAID], null, 'id DESC');
    }

    public function indexAction()
    {
        $this->redirect('/payments/history');
    }

    public function buyAction()
    {
        $this->setDialogAction(array(
            'id' => 'messages-response',
            'title' => 'Buy Kryptos',
        ));
    }

    public function submitBuyAction() {
        if (!$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            die('The page you requested cannot be found.');
        }

	$purchaseType = $this->getParam('purchase_type');
        $productId = $this->getParam('kryptos_version');
        $miniUser = (int)$this->getParam('mini_user');
	$standardUser = (int)$this->getParam('standard_user');
        $years = (float)$this->getParam('contract_type');
        $paymentMethod = $this->getParam('payment_method');
    $amount = 0;

    if($purchaseType == 'individual'){
        $miniID = Application_Service_Products::PRODUCT_KRYPTOS_MINI_IND_ID;
        $standardID = Application_Service_Products::PRODUCT_KRYPTOS_STANDARD_IND_ID;
        $adminID = Application_Service_Products::PRODUCT_KRYPTOS_ADMIN_IND_ID;

        $amount = Application_Service_Products::getPrice($miniID) * $miniUser * $years * 12;
        $amount = $amount + Application_Service_Products::getPrice($standardID) * $standardUser * $years * 12;
        $amount = $amount + Application_Service_Products::getPrice($adminID) * $years * 12;
    }
    else{
            $amount = Application_Service_Products::getPriceByProductId($productId) * $years * 12;
        $licenseInfo = Application_Service_Utilities::getModel('licenseInfo');
        $info = $licenseInfo->getInfoByProductId($productId);
        $miniUser = $info['mini_count'];
        $standardUser = $info['standard_count'];
    }
        $currencyCode = Application_Service_Products::PRICE_CURRENCY;
        $data = [
            'description'     => Application_Service_Products::getName($productId) . ' ' . $years . ' year(s)',
            'payment_purpose' => Application_Model_Payments::PAYMENT_PURPOSE_BUY,
            'purpose_data'    => [
                'url'        => $_SERVER['HTTP_HOST'],
                'version'    => Application_Service_Products::getName($productId),
                'mini_user' => $miniUser,
        'standard_user' => $standardUser,
                'months'     => $years * 12,
            ],
        ];

        if ($this->validate($paymentMethod, $amount, $currencyCode)) {
            $redirectUrl = $this->paymentsService->getRedirectUrl($paymentMethod, $amount, $currencyCode, $data);
            $this->redirect($redirectUrl);
        }
    }

    public function increaseUsersAction()
    {
        $this->setDialogAction([
            'id'    => 'messages-response',
            'title' => 'Dodaj więcej użytkowników',
        ]);
        /* Vipin code starts */
        $db = Zend_Db_Table::getDefaultAdapter();
        $license = $db->select()->from('license_info');
        $get = $db->fetchAll($license);
        $this->view->paginator = $get;
        /* Vipin code end */
    }

    public function submitIncreaseUsersAction()
    {
        if (!$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            die('The page you requested cannot be found.');
        }

        $mini_user = (int)$this->getParam('mini_user');
        $standard_user = (int)$this->getParam('pro_user');
        $expert_user = (int)$this->getParam('expert_user');

        $paymentMethod = $this->getParam('payment_method');

        $license = Application_Service_Licenses::getInstance()->getLicense();
        if (empty($license)) {
            throw new Exception('You must buy Kryptos first');
        }

    $miniID = Application_Service_Products::PRODUCT_KRYPTOS_MINI_IND_ID;
    $standardID = Application_Service_Products::PRODUCT_KRYPTOS_STANDARD_IND_ID;
        // $amount = 5;//Application_Service_Products::getPrice($miniID) * $mini_user * $license['months'];
    //$amount = 5;//$amount + Application_Service_Products::getPrice($standardID) * $standard_User * $license['months'];

    /* Vipin code starts */
    $proCost = (int)$this->getParam('pro_user_amount') * $standard_user;
    $miniCost = (int)$this->getParam('mini_user_amount') * $mini_user;
    $expertCost = (int)$this->getParam('expert_user_amount') * $expert_user;

    $amount = $proCost+$miniCost+$expertCost;
    /* Vipin code end */
    
        $currencyCode = Application_Service_Products::PRICE_CURRENCY;
        $data = [
            'description'     => 'Increase ' . $mini_user . ' mini users and ' . $standard_user . ' standard users.',
            'payment_purpose' => Application_Model_Payments::PAYMENT_PURPOSE_INCREASE_USER_LIMIT,
            'purpose_data'    => [
                'mini_user' => $mini_user,
        'standard_user' => $standard_user,
        'admin_user' => $expert_user,
            ],
        ];

        if ($this->validate($paymentMethod, $amount, $currencyCode)) {
            $redirectUrl = $this->paymentsService->getRedirectUrl($paymentMethod, $amount, $currencyCode, $data);
            $this->redirect($redirectUrl);
        }
        else
        {
            $this->redirect('/home');
        }
    }

    public function depositBalanceAction()
    {
        $this->setDialogAction([
            'id'    => 'messages-response',
            'title' => 'Deposit balance',
        ]);
    }

    public function submitDepositBalanceAction()
    {
        if (!$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            die('The page you requested cannot be found.');
        }

        $amount = (float)$this->getParam('amount');
        $paymentMethod = $this->getParam('payment_method');

        $license = Application_Service_Licenses::getInstance()->getLicense();
        if (empty($license)) {
            throw new Exception('You must buy Kryptos first');
        }

        $currencyCode = Application_Service_Products::PRICE_CURRENCY;
        $data = [
            'description'     => 'Deposit ' . $amount . ' ' . $currencyCode ,
            'payment_purpose' => Application_Model_Payments::PAYMENT_PURPOSE_DEPOSIT_BALANCE,
            'purpose_data'    => [
                'amount' => $amount,
                'currency_code' => $currencyCode
            ],
        ];

        if ($this->validate($paymentMethod, $amount, $currencyCode)) {
            $redirectUrl = $this->paymentsService->getRedirectUrl($paymentMethod, $amount, $currencyCode, $data);
            $this->redirect($redirectUrl);
        }
    }

    public function upgradeVersionAction()
    {
        $this->setDialogAction([
            'id'    => 'messages-response',
            'title' => 'Upgrade version',
        ]);

        $license = Application_Service_Licenses::getInstance()->getLicense();
        
        $higherVersions = Application_Service_Products::getHigherVersions($license['version']);
        $versions = [];
        foreach ($higherVersions as $higherVersion) {
            $versions[] = [
                'version' => $higherVersion,
        'id'      => Application_Service_Products::getIdByVersion($higherVersion),
            ];
        }
        $this->view->higherVersions = $versions;
    }

    public function submitUpgradeVersionAction()
    {
        if (!$this->getRequest()->isPost()) {
            header('HTTP/1.0 404 Not Found');
            die('The page you requested cannot be found.');
        }

        $version = $this->getParam('version');
        $paymentMethod = $this->getParam('payment_method');

        $license = Application_Service_Licenses::getInstance()->getLicense();
        if (empty($license)) {
            throw new Exception('You must buy Kryptos first');
        }

        $amount = Application_Service_Products::getPrice($version) * $license['user_limit'] * $license['months'];
        $currencyCode = Application_Service_Products::PRICE_CURRENCY;
        $data = [
            'description'     => "Upgrade version from {$license['version']} to {$version}",
            'payment_purpose' => Application_Model_Payments::PAYMENT_PURPOSE_UPGRADE_VERSION,
            'purpose_data'    => [
                'version' => $version,
            ],
        ];

        if ($this->validate($paymentMethod, $amount, $currencyCode)) {
            $redirectUrl = $this->paymentsService->getRedirectUrl($paymentMethod, $amount, $currencyCode, $data);
            $this->redirect($redirectUrl);
        }
    }

    public function paypalCallbackAction()
    {
        $paymentHash = $this->getParam('hash');
        $paymentParams = [
            'payment_id' => $this->getParam('paymentId'),
            'payer_id'   => $this->getParam('PayerID'),
        ];
        try {
            $success = $this->paymentsService->pay($paymentHash, $paymentParams);
        } catch (Exception $e) {
            $success = false;
        }

        if ($success) {
            $this->flashMessage('success', 'You have successfully paid! Thank you.');
        } else {
            $this->flashMessage('error', 'You have failed to pay!');
        }

        $this->redirect('/home');
    }

    public function paypalCancelAction() {
        $paymentHash = $this->getParam('hash');
        $this->redirect('/home');
    }

    protected function validate($paymentMethod, $amount, $currencyCode) {
        if (!$this->paymentsService->isAcceptedPaymentMethod($paymentMethod)) {
            //throw new Exception('This payment method has been declined.');
            return false;
        }

        if ($amount <=0 ) {
            return false;
        }

        return true;
    }
}
