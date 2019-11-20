<?php
class Base_Logger_Logger
{
    protected $drivers;
    
    /**
     * @return Base_Logger_Driver_Abstract[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    public function setDrivers($drivers = [])
    {
        foreach ($drivers as $driver) {
            $this->addDriver($driver);
        }
    }
    
    public function addDriver(Base_Logger_Driver_Abstract $driver)
    {
        $this->drivers[] = $driver;
    }
    
    public function logMessage($message, $additionalInfo = [])
    {
        $drivers = $this->getDrivers();
        
        foreach ($drivers as $driver) {
            $driver->logMessage($message, $additionalInfo);
        }
    }
}
