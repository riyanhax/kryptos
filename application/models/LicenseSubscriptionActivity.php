<?php

class Application_Model_LicenseSubscriptionActivity extends Muzyka_DataModel
{
    const TYPE_CREATE = 1;
    const TYPE_APPROVE = 2;
    const TYPE_EXPIRE = 3;
    const TYPE_CANCEL = 4;

    protected $_name = 'license_subscription_activity';
}
