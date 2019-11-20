<?php

class Application_Service_Subscription_Factory
{
    /** @var self */
    protected static $_instance;

    /** @var Zend_Config */
    protected $config;

    /** @var Zend_Http_Client */
    protected $httpClient;

    /**
     * @return self
     */
    public static function getInstance() {
        if (!self::$_instance) {
            try {
                $config = new Zend_Config_Ini(__DIR__ . '/../../configs/subscriptions.ini');
            } catch (Zend_Config_Exception $e) {
                $config = new Zend_Config(['subscriptions' => ['adapter' => 'dummy'], 'dummy' => []]);
            }
            self::$_instance = new self(
                $config,
                new Zend_Http_Client()
            );
        }
        return self::$_instance;
    }

    /**
     * @param Zend_Config $config
     * @param Zend_Http_Client $httpClient
     */
    public function __construct(Zend_Config $config, Zend_Http_Client $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    /**
     * @return Application_Service_Subscription_Adapter_AdapterInterface
     * @throws Zend_Config_Exception
     */
    public function createAdapter()
    {
        $adapter = $this->config
            ->get('subscriptions')
            ->get('adapter');
        $adapterConfig = $this->config
            ->get($adapter);
        $adapterClass = 'Application_Service_Subscription_Adapter_' . ucfirst($adapter) . 'Adapter';
        if (!class_exists($adapterClass)) {
            throw new Zend_Config_Exception('Subscription adapter not found: '.var_export($adapter, true));
        }
        return new $adapterClass($this->httpClient, $adapterConfig);
    }
}
