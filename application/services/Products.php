<?php

/**
 * @todo create table for products
 * Class Application_Service_Products
 */
class Application_Service_Products
{
    /** @var self */
    protected static $_instance = null;
    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    const PRODUCT_KRYPTOS_BASIC_ID        = 1;
    const PRODUCT_KRYPTOS_STANDARD_ID     = 2;
    const PRODUCT_KRYPTOS_PROFESSIONAL_ID = 3;
    const PRODUCT_KRYPTOS_ENTERPRISE_ID   = 4;
    const PRODUCT_KRYPTOS_MINI_IND_ID     = 5;
    const PRODUCT_KRYPTOS_STANDARD_IND_ID = 6;
    const PRODUCT_KRYPTOS_ADMIN_IND_ID    = 7;

    const PRODUCT_KRYPTOS_BASIC_NAME        = 'BASIC';
    const PRODUCT_KRYPTOS_STANDARD_NAME     = 'STANDARD';
    const PRODUCT_KRYPTOS_PROFESSIONAL_NAME = 'PROFESSIONAL';
    const PRODUCT_KRYPTOS_ENTERPRISE_NAME   = 'ENTERPRISE';

    const PRODUCT_KRYPTOS_MINI_INDIVIDUAL_NAME		= 'MINI';
    const PRODUCT_KRYPTOS_STANDIND_INDIVIDUAL_NAME	= 'STANDARD';
    const PRODUCT_KRYPTOS_ADMIN_INDIVIDUAL_NAME		= 'ADMIN';

    const KRYPTOS_VERSION_BASIC        = 'BASIC';
    const KRYPTOS_VERSION_STANDARD     = 'STANDARD';
    const KRYPTOS_VERSION_PROFESSIONAL = 'PROFESSIONAL';
    const KRYPTOS_VERSION_ENTERPRISE   = 'ENTERPRISE';

    const PRICE_CURRENCY = 'INR';

    /**
     * @param $productId
     * @return null|string
     */
    public static function getName($productId) {
        switch ($productId) {
            case self::PRODUCT_KRYPTOS_BASIC_ID:
                return self::PRODUCT_KRYPTOS_BASIC_NAME;
            case self::PRODUCT_KRYPTOS_STANDARD_ID:
                return self::PRODUCT_KRYPTOS_STANDARD_NAME;
            case self::PRODUCT_KRYPTOS_PROFESSIONAL_ID:
                return self::PRODUCT_KRYPTOS_PROFESSIONAL_NAME;
            case self::PRODUCT_KRYPTOS_ENTERPRISE_ID:
                return self::PRODUCT_KRYPTOS_ENTERPRISE_NAME;
            default:
                return null;
        }

    }

    /**
     * @param $productId
     * @return float|null
     */
    public static function getPrice($productIdOrVersion) {
        return is_numeric($productIdOrVersion) ? self::getPriceByProductId($productIdOrVersion) : self::getPriceByVersion($productIdOrVersion);
    }

    public static function getPriceByProductId($productId) {
	$license_info = Application_Service_Utilities::getModel('LicenseInfo');
       	return $license_info->getPriceByProductId($productId);
    }

    public static function getPriceByVersion($version) {
        switch ($version) {
            case self::KRYPTOS_VERSION_BASIC:
                return self::PRODUCT_KRYPTOS_BASIC_PRICE;
            case self::KRYPTOS_VERSION_STANDARD:
                return self::PRODUCT_KRYPTOS_STANDARD_PRICE;
            case self::KRYPTOS_VERSION_PROFESSIONAL:
                return self::PRODUCT_KRYPTOS_PROFESSIONAL_PRICE;
            case self::KRYPTOS_VERSION_ENTERPRISE:
                return self::PRODUCT_KRYPTOS_ENTERPRISE_PRICE;
            default:
                return null;
        }

    }

    public static function getIdByVersion($version) {
        switch ($version) {
            case self::KRYPTOS_VERSION_BASIC:
                return self::PRODUCT_KRYPTOS_BASIC_ID;
            case self::KRYPTOS_VERSION_STANDARD:
                return self::PRODUCT_KRYPTOS_STANDARD_ID;
            case self::KRYPTOS_VERSION_PROFESSIONAL:
                return self::PRODUCT_KRYPTOS_PROFESSIONAL_ID;
            case self::KRYPTOS_VERSION_ENTERPRISE:
                return self::PRODUCT_KRYPTOS_ENTERPRISE_ID;
            default:
                return null;
        }

    }

    public static function getHigherVersions($version) {
        switch ($version) {
            case self::KRYPTOS_VERSION_BASIC:
                return [
                    self::KRYPTOS_VERSION_STANDARD,
                    self::KRYPTOS_VERSION_PROFESSIONAL,
                    self::KRYPTOS_VERSION_ENTERPRISE,
                ];
            case self::KRYPTOS_VERSION_STANDARD:
                return [
                    self::KRYPTOS_VERSION_PROFESSIONAL,
                    self::KRYPTOS_VERSION_ENTERPRISE,
                ];
            case self::KRYPTOS_VERSION_PROFESSIONAL:
                return [
                    self::KRYPTOS_VERSION_ENTERPRISE,
                ];
            default:
                return [];
        }
    }

}
