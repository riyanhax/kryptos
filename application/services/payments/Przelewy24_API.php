<?php
    /*$config = new Zend_Config_Ini(__DIR__ . '/../configs/subscriptions.ini');
    $getConfig = $config->get('przelewy24');*/
    
    /*define('PRZELEWY24_MERCHANT_ID', '58551');
    define('PRZELEWY24_CRC', '92c1e9f12d85fbe8');
    // sandbox - ?rodowisko testowe, secure - ?rodowisko produkcyjne
    define('PRZELEWY24_TYPE', 'sandbox');

    class Przelewy24_API
    {
        public function CreateToken($p24_amount = null, $p24_description = null, $p24_email = null, $p24_url_return = null, $p24_url_status = null, $p24_address = null)
        {
            $p24_session_id = uniqid();
            $_SESSION['p24_session_id'] = $p24_session_id;  
            $headers[] = 'p24_merchant_id=' . PRZELEWY24_MERCHANT_ID;
            $headers[] = 'p24_pos_id=' . PRZELEWY24_MERCHANT_ID;
            $headers[] = 'p24_crc=' . PRZELEWY24_CRC;
            $headers[] = 'p24_session_id=' . $p24_session_id;
            $headers[] = 'p24_amount=' . $p24_amount;
            $headers[] = 'p24_currency=PLN';
            $headers[] = 'p24_description=' . $p24_description;
            $headers[] = 'p24_country=PL';
            $headers[] = 'p24_url_return=' . urlencode($p24_url_return);
            $headers[] = 'p24_url_status=' . urlencode($p24_url_status);
            $headers[] = 'p24_api_version=3.2';
            $headers[] = 'p24_sign=' . md5($p24_session_id . '|' . PRZELEWY24_MERCHANT_ID . '|' . $p24_amount . '|PLN|' . PRZELEWY24_CRC);
            $headers[] = 'p24_email=' . $p24_email;

            $oCURL = curl_init();
            curl_setopt($oCURL, CURLOPT_POST, 1);
            curl_setopt($oCURL, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
            curl_setopt($oCURL, CURLOPT_POSTFIELDS, implode('&', $headers));
            curl_setopt($oCURL, CURLOPT_URL, 'https://' . PRZELEWY24_TYPE . '.przelewy24.pl/trnRegister');
            curl_setopt($oCURL, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($oCURL, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($oCURL, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCURL, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($oCURL);
            curl_close($oCURL);
            parse_str($response, $output);
            return isset($output['token']) ? $output['token'] : 0;
        }

        public function Pay($p24_amount = null, $p24_description = null, $p24_email = null, $p24_url_return = null, $p24_url_status = null)
        {
            $token = $this->CreateToken($p24_amount, $p24_description, $p24_email, $p24_url_return, $p24_url_status);
            return 'https://' . PRZELEWY24_TYPE . '.przelewy24.pl/trnRequest/' . $token;
        }

        public function Verify($data = null)
        {
            $headers[] = 'p24_merchant_id=' . $data['p24_merchant_id'];
            $headers[] = 'p24_pos_id=' . $data['p24_pos_id'];
            $headers[] = 'p24_session_id=' . $data['p24_session_id'];
            $headers[] = 'p24_amount=' . $data['p24_amount'];
            $headers[] = 'p24_currency=PLN';
            $headers[] = 'p24_order_id=' . $data['p24_order_id'];
            $headers[] = 'p24_sign=' . md5($data['p24_session_id'] . '|' . $data['p24_order_id'] . '|' . $data['p24_amount'] . '|PLN|' . PRZELEWY24_CRC);

            $oCURL = curl_init();
            curl_setopt($oCURL, CURLOPT_POST, 1);
            curl_setopt($oCURL, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
            curl_setopt($oCURL, CURLOPT_POSTFIELDS, implode('&', $headers));
            curl_setopt($oCURL, CURLOPT_URL, 'https://' . PRZELEWY24_TYPE . '.przelewy24.pl/trnVerify');
            curl_setopt($oCURL, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($oCURL, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($oCURL, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCURL, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($oCURL);
            curl_close($oCURL);

            parse_str($response, $output);
            return ($output['error'] == '0') ? true : false;
        }
    }*/




/**
 * Przelewy24 comunication class
 * 
 * @author DialCom24 Sp. z o.o.
 * @copyright DialCom24 Sp. z o.o.
 * @version 1.1
 * @since 2014-04-29
 */

/**
 * 
 * Communication protol version
 * @var double
 */
define("P24_VERSION", "3.2");

class Przelewy24 {
    /**
     * Live system URL address
     * @var string
     */    
    private $hostLive        =    "https://secure.przelewy24.pl/";
    /**
     * Sandbox system URL address
     * @var string
     */    
    private $hostSandbox     =    "https://sandbox.przelewy24.pl/";
    /**
     * Use Live (false) or Sandbox (true) enviroment
     * @var bool
     */    
    private $testMode        =    false;
    /**
     * Merchant id
     * @var int
     */
    private $merchantId      =    0;
    /**
     * Merchant posId
     * @var int
     */    
    private $posId           =    0;
    /**
     * Salt to create a control sum (from P24 panel)
     * @var string
     */    
    private $salt            =    "";
    /**
     * Array of POST data
     * @var array
     */    
    private $postData        =    array();
    
    /**
     * 
     * Obcject constructor. Set initial parameters
     * @param int $merchantId
     * @param int $posId
     * @param string $salt
     * @param bool $testMode
     */
    public function __construct($merchantId, $posId, $salt, $testMode = false) {

        $this->merchantId    = (int) $merchantId;
        $this->posId         = (int) $posId;
        $this->salt          = $salt;

        if($this->merchantId === 0)
            $this->merchantId = $this->posId;
        
        if($testMode) {
            $this->hostLive = $this->hostSandbox;
        }
        
        $this->addValue("p24_merchant_id", $merchantId);
        $this->addValue("p24_pos_id", $this->posId);
        $this->addValue("p24_api_version", P24_VERSION);
        
        return true;        
    }
    /**
     * 
     * Returns host URL
     */
    public function getHost() {
        return $this->hostLive;
    }

    /**
     * 
     * Add value do post request
     * @param string $name Argument name
     * @param mixed $value Argument value
     * @todo Add postData validation
     */
    public function addValue($name, $value) {
        
        $this->postData[$name] = $value;
        
    }
    
    /**
     * 
     * Function is testing a connection with P24 server
     * @return array Array(INT Error, Array Data), where data 
     */
    public function testConnection() {
        
        $crc = md5($this->posId."|".$this->salt);

        $ARG["p24_merchant_id"] = $this->merchantId;
        
        $ARG["p24_pos_id"] = $this->posId;
        
        $ARG["p24_sign"] = $crc;

        $RES = $this->callUrl("testConnection",$ARG);
        return $RES;
    }
    
    /**
     * 
     * Prepare a transaction request
     * @param bool $redirect Set true to redirect to Przelewy24 after transaction registration
     * @return array array(INT Error code, STRING Token)
     */
    public function trnRegister($redirect = false) {

        $crc = md5($this->postData["p24_session_id"]."|".$this->posId."|".$this->postData["p24_amount"]."|".$this->postData["p24_currency"]."|".$this->salt) ;

        $this->addValue("p24_sign", $crc);
        
        $RES = $this->callUrl("trnRegister",$this->postData);
        if($RES["error"] == "0") {
            
            $token = $RES["token"];
            
        } else {
            
            return $RES;
            
        }
        if($redirect) {
            $this->trnRequest($token);
            
        }
        
        return array("error"=>0, "token"=>$token);
        
        
    }
    
    /**
     * Redirects or returns URL to a P24 payment screen
     * @param string $token Token
     * @param bool $redirect If set to true redirects to P24 payment screen. If set to false function returns URL to redirect to P24 payment screen
     * @return string URL to P24 payment screen
     */
    public function trnRequest($token, $redirect = true) {

        if($redirect) {
            header("Location:" . $this->hostLive."trnRequest/".$token);
            return "";
        } else {
            return $this->hostLive."trnRequest/".$token; 
        } 
        
    }
    
    /**
     * 
     * Function verify received from P24 system transaction's result.
     * @return array
     */
    public function trnVerify() {
        
        $crc = md5($this->postData["p24_session_id"]."|".$this->postData["p24_order_id"]."|".$this->postData["p24_amount"]."|".$this->postData["p24_currency"]."|".$this->salt) ;
        
        $this->addValue("p24_sign", $crc);        
        
        $RES = $this->callUrl("trnVerify",$this->postData);
        
        return $RES;
    
    }

    /**
     * 
     * Function contect to P24 system
     * @param string $function Method name
     * @param array $ARG POST parameters
     * @return array array(INT Error code, ARRAY Result)
     */
    private function callUrl($function, $ARG) {
        
        if(!in_array($function, array("trnRegister","trnRequest","trnVerify","testConnection"))) {
            
            return array("error"=>201,"errorMessage"=>"class:Method not exists");
            
        }
        
        $REQ = array();
        
        foreach($ARG as $k=>$v) $REQ[] = $k."=".urlencode($v);

        $url = $this->hostLive.$function;
        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        if($ch = curl_init()) {
        
            if(count($REQ)) {
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$REQ));
            }
            
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if($result = curl_exec ($ch)) {
                $INFO = curl_getinfo($ch);
                curl_close ($ch);
                
                if($INFO["http_code"]!=200) {
                    
                    return array("error"=>200,"errorMessage"=>"call:Page load error (".$INFO["http_code"].")");
                    
                } else {

                    $RES     = array();
                    $X       = explode("&", $result);
                
                    foreach($X as $val) {
                        
                        $Y           = explode("=", $val);
                        $RES[trim($Y[0])] = urldecode(trim($Y[1]));
                    }
                    if(!isset($RES["error"])) return array("error"=>999,"errorMessage"=>"call:Unknown error");
                    return $RES;

                }
                
                
            } else {
                curl_close ($ch);
                return array("error"=>203,"errorMessage"=>"call:Curl exec error");
                
            }
            
        } else {
            
            return array("error"=>202,"errorMessage"=>"call:Curl init error");
        
        }
        
        
        
    }

}

?>