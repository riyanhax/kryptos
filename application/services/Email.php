<?php

class Application_Service_Email
{
    /** @var self */
    protected static $_instance = null;
    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    /** @var Zend_Mail_Transport_Smtp $smtp */
    protected $smtp;

    protected $transports = [];
    protected $senders;

    private function __construct()
    {
        self::$_instance = $this;

        $apiinfoModel = Application_Service_Utilities::getModel('ApiConfiguration');
        $apidata = $apiinfoModel->getApiconfigAction('1');
        $data = $apidata[0];

         $this->senders = [
        
        1 => [
            'server' => $data['apiurl'],
            'from_name' => $data['accesskey'],
            'from_email' =>  $data['username'],
            'smtp_config' => [
                'username'  =>  $data['username'],
                'password'  =>  $data['password'],
                'ssl'       => 'tls',
                'auth'      => 'login'                
            ]
        ],
    ];    
    }

     public function init()
    {
        parent::init();

       

     return $this->senders;
    }

  
    /**
     * @param $senderId
     * @return Zend_Mail_Transport_Smtp
     * @throws Exception
     */
    protected function getTransport($senderId)
    {
        if (isset($this->transports[$senderId])) {
            return $this->transports[$senderId];
        }

        if (!array_key_exists($senderId, $this->senders)) {
            Throw new Exception('Invalid email sender', 500);
        }

        $config = $this->senders[$senderId];
        $transport = new Zend_Mail_Transport_Smtp($config['server'], $config['smtp_config']);        
        $this->transports[$senderId] = $transport;

        return $transport;
    }

    public function send($data)
    {
        Application_Service_Utilities::requireKeys($data, ['recipient_address', 'title', 'text', 'sender_id']);

        $mail = new Zend_Mail('UTF-8');
        $transport = $this->getTransport($data['sender_id']);
        $senderConfig = $this->senders[$data['sender_id']];

        $mail->setFrom($senderConfig['from_email'], $senderConfig['from_name']);
        $mail->setSubject($data['title']);
        $mail->setBodyHtml($data['text']);
        $mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);

        
        if (is_array($data['recipient_address'])) {
            $mail->addTo($data['recipient_address'][0], $data['recipient_address'][1]);
        } else {
            $mail->addTo($data['recipient_address']);
        }
        
        try {
            $mail->send($transport);
        } catch (Exception $e) {
            vdie($e);
            Throw new Exception('E-mail send error', 500, $e);
        }
    }
}