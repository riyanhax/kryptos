<?php

use Application_Model_Osoby as PersonModel;

class Application_Model_License extends Muzyka_DataModel
{
    protected $_name = 'licenses';
    protected $_base_name = 'l';

    const PERIOD_YEAR = 1;
    const PERIOD_MONTH = 2;
    const PERIOD_WEEK = 3;
    const PERIOD_DAY = 4;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVATED = 1;
    const STATUS_DELETED = -1;

    const CURRENCY_USD = 'USD';

    /**
     * @param $data
     * @return mixed
     * @throws Application_SubscriptionOverLimitException
     * @throws Exception
     */
    public function save($data)
    {

        /** @var object $row */
        if (empty($data['id'])) {
            $row = $this->createRow();
            $row->created_at = date('Y-m-d H:i:s');
        } else {
            $row = $this->getOne($data['id']);
            $row->updated_at = date('Y-m-d H:i:s');
        }

        $historyCompare = clone $row;

        $row->name = $data['name'] ?: '';
        $row->description = $data['description'] ?: '';
        $row->external_id = $data['external_id'] ?: '';
        $row->period = $data['period'] ?: 1;
        $row->period_unit = $data['period_unit'] ?: self::PERIOD_MONTH;
        $row->trial_period = $data['trial_period'] ?: 14;
        $row->trial_period_unit = $data['trial_period_unit'] ?: self::PERIOD_DAY;
        $row->user_type = $data['user_type'] ?: PersonModel::USER_TYPE_PRO;
        $row->price = $data['price'] ?: 0;
        $row->currency = $data['currency'] ?: 0;
        $row->expert_count = (int) $data['expert_count'];
        $row->pro_count = (int) $data['pro_count'];
        $row->mini_count = (int) $data['mini_count'];
        $row->status = $data['status'] ?: self::STATUS_INACTIVE;
        /* Vipin code starts */
        $row->is_trial = $data['is_trial'] ?: 0;
        /* Vipin code end */
        
        $id = $row->save();

        $this->getRepository()->eventObjectChange($row, $historyCompare);
        $this->addLog($this->_name, $row->toArray(), __METHOD__);
        return $id;
    }

    /**
     * @param $id
     * @throws Exception
     * @return mixed
     */
    public function remove($id)
    {
        /** @var object $license */
        $license = $this->getOne($id);
        $license->status = self::STATUS_DELETED;
        return $license->save();
    }

    /**
     * @return mixed
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function getLicense() {
        return $this->getAdapter()
            ->select()
            ->from('licenses')
            ->order('id DESC')
            ->query()
            ->fetch();
    }

    /**
     * @param $usersCount
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function increaseUserLimit($usersCount)
    {
        $license = $this->getLicense();
        $this->edit($license['id'], [
            'user_limit' => $license['user_limit'] + (int)$usersCount,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param $version
     * @throws Zend_Db_Statement_Exception
     * @deprecated
     */
    public function setVersion($version)
    {
        $license = $this->getLicense();
        $this->edit($license['id'], [
            'version' => $version,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /* Vipin code starts */
    public function getAllLicenses(){
        $db = Zend_Db_Table::getDefaultAdapter();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();
        $currentDate = date('Y-m-d H:i:s');

                $currentLicense = $db->select()->from('license_subscriptions')->where('osoby_id =?', $user_id )->where('end_date >?', $currentDate)->where('status =?', 1)->order('id Desc');

        $check = $db->fetchRow($currentLicense);
        if($check['license_id']){
            $getLicensePrice = $this->select()->where('id =?', $check['license_id'])->query()->fetch();

            $getLicense = $this->select()
                ->where('status =?', 1)
                ->where('is_trial =?', 0)
                ->where('id NOT IN(?)', $check['license_id'])
                ->where('price >?', $getLicensePrice['price'])
                ->query()
                ->fetchAll();
        }else{
            $getLicense = $this->select()
                ->where('status =?', 1)
                ->where('is_trial =?', 0)
                ->query()
                ->fetchAll();
        }

        return $getLicense;
    }

    public function licenseHistory(){
        $db = Zend_Db_Table::getDefaultAdapter();
        $user_id = Application_Service_Authorization::getInstance()->getUserId();

        $license = $db->select()
            ->from(['table1' => 'license_subscriptions'],['*'])
            ->joinInner(['table2' => 'licenses'], 'table2.id = table1.license_id', array('name' => 'table2.name', 'description' => 'table2.description', 'period' => 'table2.period','period_unit'=>'table2.period_unit', 'trial_period' => 'table2.trial_period', 'trial_period_unit' => 'table2.trial_period_unit', 'user_type' => 'table2.user_type', 'price' => 'table2.price', 'currency' => 'table2.currency', 'status' => 'table2.status', 'is_trial' => 'table2.is_trial', 'external_id' => 'table2.external_id', 'license_expert_count' => 'table2.expert_count', 'license_pro_count' => 'table2.pro_count', 'license_mini_count' => 'table2.mini_count', 'license_created_at' => 'table2.created_at'))
            ->where('table1.osoby_id =?', $user_id);
        $getLicense = $db->fetchAll($license);
        return $getLicense;

    }

    public function freeTrial($license_id){
        $freeTrial = $this->select()
            ->where('id =?', $license_id)
            ->where('is_trial =?', 1)
            ->query()
            ->fetch();
        return $freeTrial;
    }
    public function manageHistory()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $user = Application_Service_Authorization::getInstance()->getUser();

        $license = $db->select()
            ->from(['table1' => 'license_subscriptions'],['*'])
            ->joinInner(['table2' => 'licenses'], 'table2.id = table1.license_id', array('name' => 'table2.name', 'description' => 'table2.description', 'period' => 'table2.period','period_unit'=>'table2.period_unit', 'trial_period' => 'table2.trial_period', 'trial_period_unit' => 'table2.trial_period_unit', 'user_type' => 'table2.user_type', 'price' => 'table2.price', 'currency' => 'table2.currency', 'status' => 'table2.status', 'is_trial' => 'table2.is_trial', 'external_id' => 'table2.external_id', 'license_expert_count' => 'table2.expert_count', 'license_pro_count' => 'table2.pro_count', 'license_mini_count' => 'table2.mini_count', 'license_created_at' => 'table2.created_at'))
            ->where('table1.status =?', 1);
        
        if(!$user['isSuperAdmin']){
            $license->where('table1.osoby_id =?', $user['id']);
            $getLicense = $db->fetchRow($license);
        }else{
            $getLicense = $db->fetchAll($license);
        }
        return $getLicense;
    }
    /* Vipin code end */
}
