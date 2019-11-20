<?php
class Base_Http_Client extends Zend_Http_Client
{
    public function setHeaders($name, $value = null)
    {
        if (is_string($name)) {
            $normalizedName = strtolower($name);
            
            if (array_key_exists($normalizedName, $this->headers)) {
                return $this;
            }
        }
        
        return parent::setHeaders($name, $value);
    }
}
