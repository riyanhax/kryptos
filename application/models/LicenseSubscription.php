<?php

class Application_Model_LicenseSubscription extends Muzyka_DataModel
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVATED = 1;
    const STATUS_EXPIRED = 2;

    protected $_name = 'license_subscriptions';

    public $injections = [
        'license' => ['License', 'license_id', 'getList', ['l.id IN (?)' => null], 'id', 'license', false],
        'osoby' => ['Osoby', 'osoby_id', 'getList', ['o.id IN (?)' => null], 'id', 'osoba', false],
    ];

    public $autoloadInjections = ['license', 'osoby'];

    /**
     * @param int $osobaId
     * @return int
     */
    public function getCountByOsobaId($osobaId) {
        return $this->fetchAll(
            $this->select()
                ->where('osoby_id = ?', $osobaId)
        )->count();
    }
    
    public function getSubscriptionById($id)
    {
        $data = $this->select()
            ->where('id = ?', $id)
            ->query()
            ->fetch();
        return $data;
    }
    public function getEndDateByOsobyId($osoby_id)
    {
        $data = $this->select()
            ->where('osoby_id = ?', $osoby_id)
            ->query()
            ->fetch();
        return $data['end_date'];
    }

    public function updateData($data, $id)
    {
        $arr = array(
            'expert_count' => $data['expert_count'],
            'pro_count' => $data['pro_count'],
            'mini_count' => $data['mini_count'],
            'subscription_price' => $data['subscription_price']
        );
        
        $this->update($arr, $id);
    }
    public function insertData($data)
    {
        $dataArr = array(
            'license_id' => $data['license_id'],
            'osoby_id' => $data['user_id'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'created_at' => date('Y-m-d H:i:s'),
            'expert_count' => $data['expert_count'],
            'pro_count' => $data['pro_count'],
            'mini_count' => $data['mini_count'],
            'subscription_price' => $data['subscription_price'],
            'session_id' => $data['session_id']
        );
        $this->insert($dataArr);
    }

    public function updateSubscription($data, $session_id)
    {
        $dataArr = array(
            'status' => $data['status']
        );
        $this->update($dataArr, $session_id);
    }
    public function getSubscriptionBySessionId($session_id)
    {
        $select = $this->select()
                ->where('session_id =?', $session_id)
                ->query()
                ->fetch();
        return $select;
    }
    
    public function getUsetById($user_id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $getUserById = $db->select()->from('users')->where('id =?', $user_id);
        $getUser = $db->fetchRow($getUserById);
        return $getUser;
    }
}
