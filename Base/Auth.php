<?php
class Base_Auth extends Zend_Auth
{
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function setIdentity($identity)
    {
        $this->getStorage()->write($identity);
    }
}
