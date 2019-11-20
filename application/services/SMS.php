<?php

class Application_Service_SMS
{
    /** @var self */
    protected static $_instance = null;
    private function __clone() {}
    public static function getInstance() { return null === self::$_instance ? new self : self::$_instance; }

    private function __construct()
    {
        self::$_instance = $this;
        
    }

     public function smsInfo()
    {   

        $apiinfoModel = Application_Service_Utilities::getModel('ApiConfiguration');
        $apisms = $apiinfoModel->getApiconfigAction('2');
        $data = $apisms[0];

                $access_token = $data['accesskey']; 
                       //sms api access token
        
        $params = array(
            'to'            => $numbers,                                            //destination number  
            'from'          => 'Test',//$api['additional'],                                             //sender name has to be active  
            'message'       => $data["text"],       
            'encoding'      => 'utf-8'                                      //message content
            );
        
        if ($access_token&&$params['to']&&$params['message']&&$params['from']) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $data['apiurl']);
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, $params);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer $access_token"
            ));

            $content = curl_exec($c);
            $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

            curl_close($c);            
        }

        
    } 

    public function send($data)
    {
        $apis = Application_Service_Utilities::getModel('ApiKeys');
        $api = $apis->getAllByType('smsapi');

        $osoby = Application_Service_Utilities::getModel('Osoby');
        
        $numbers = $data['recipient_address'];

        $access_token = $api['Value'];          //sms api access token
        
        $params = array(
            'to'            => $numbers,         	  								//destination number  
            'from'          => 'Test',//$api['additional'],                								//sender name has to be active  
            'message'       => $data["text"],    	
            'encoding'      => 'utf-8'		  								//message content
            );
        
        if ($access_token&&$params['to']&&$params['message']&&$params['from']) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, 'https://api.smsapi.pl/sms.do');
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, $params);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer $access_token"
            ));

            $content = curl_exec($c);
            $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

            curl_close($c);            
        }
    }

    
}