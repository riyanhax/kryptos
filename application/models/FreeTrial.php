<?php

class Application_Model_FreeTrial extends Muzyka_DataModel
{
    protected $_name = 'free_trials';

    const STATUS_PENDING = 0;
    const STATUS_ACTIVATED = 1;

    /**
     * @param $email
     * @return Zend_Db_Table_Row_Abstract|object|null
     */
    public function findByEmail($email)
    {
        return $this->fetchRow(
            $this->select()
                ->where('email = ?',$email)
        );

    }

    /**
     * @param Zend_Db_Table_Row_Abstract|object $freeTrial
     * @param $status
     */
    public function updateStatus($freeTrial, $status) {
        $freeTrial->status = $status;
        if ($freeTrial->id) {
            $freeTrial->updated_at = date('Y-m-d H:i:s');
        }
        $freeTrial->save();
    }
    /* Vipin code starts */
    public function getList(){
       $trials = $this->select()
        ->where('status = ?', 0)
        ->query()
        ->fetchAll();

        return $trials;

    }
    /* Vipin code end */
}
