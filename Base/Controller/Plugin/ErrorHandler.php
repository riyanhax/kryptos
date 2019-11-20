<?php
class Base_Controller_Plugin_ErrorHandler extends Zend_Controller_Plugin_ErrorHandler
{
    /**
     * @var Base_Logger_Logger
     */
    protected $logger;
    
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->initLogger();
    }
    
    /**
     * @return Base_Logger_Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(Base_Logger_Logger $logger)
    {
        $this->logger = $logger;
    }
    
    protected function _handleError(\Zend_Controller_Request_Abstract $request)
    {
        $response = $this->getResponse();
        
        if ($response->isException()) {
            $exceptions = $response->getException();
            
            foreach ($exceptions as $exception) {
                $this->logMessage($exception, [
                    'controller' => $request->getControllerName(),
                    'action' => $request->getActionName(),
                    'url' => $request->getRequestUri(),
                    'server_name' => $request->getServer('HTTP_HOST'),
                    'mail_subject' => 'Na serwerze ' . $request->getServer('HTTP_HOST') . ' wystąpił błąd!',
                ]);
            }
        }
        
        parent::_handleError($request);
    }
    
    protected function logMessage($message, $additionalInfo = [])
    {
        $logger = $this->getLogger();
        $logger->logMessage($message, $additionalInfo);
    }
    
    protected function initLogger()
    {
        $config = Zend_Registry::get('config');
        
        if (!$config->production->errorMail->recipients instanceof Zend_Config) {
            throw new Exception('There is no config provided for error mails. Check your application.ini');
        }
        
        $mail = new Base_Logger_Driver_Mail($config->production->errorMail->recipients->toArray());
        $mail->setSubject('Wystąpił błąd!');
        $mail->setMailSchema('notifications/templates/email/server_error.html');
        
        $db = new Base_Logger_Driver_Db(new Application_Model_ErrorLog());
        
        $logger = new Base_Logger_Logger();
        $logger->addDriver($mail);
        $logger->addDriver($db);
        
        $this->setLogger($logger);
    }
}
