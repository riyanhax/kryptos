<?php
abstract class Base_Logger_Driver_Abstract
{
    abstract public function logMessage($message, $additionalInfo = []);
}
