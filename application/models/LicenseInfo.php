<?php

class Application_Model_LicenseInfo extends Muzyka_DataModel
{
    private $id;
    private $name;
    private $type;
    private $cost;
    private $mini_count;
    private $standard_count;
    private $admin_count;

    protected $_name = 'license_info';

    const STATUS_INACTIVE    = 0;
    const STATUS_ACTIVATED   = 1;

    public function getPriceByProductId($id) {

        $sql = $this->select()
            ->where('id = ?',$id);
	$row = $this->fetchRow($sql);
	return $row['cost'];
    }

    public function getInfoByProductId($id) {

        $sql = $this->select()
            ->where('id = ?',$id);
	$row = $this->fetchRow($sql);
	return $row;
    }

    /* Vipin code starts */
    public function getLicenseCost()
    {
        $getAll = $this->select()
            ->query()
            ->fetchAll();
        return $getAll;
    }

    public function updateCount($licenseInfoCost, $licenseInfoId)
    {
        $data = array(
            'cost' => $licenseInfoCost,
        );
        $this->update($data, $licenseInfoId);
    }
    /* Vipin code end */
}
