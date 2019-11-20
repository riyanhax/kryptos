<?php
class Logic_Watson extends Logic_Abstract
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    
    const MESSAGE_HELLO = 'welcome_default';
    
    protected $apiKey = 'O7fiLAX0eFatgGF7V-k7QXd2rEj0kwPV9SSPKWc3O76Z';
    
    protected $workspace = 'fdb38f02-0e43-4ce7-963e-1f0e3015d434';
    
    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }
        
    /**
     * @param string $message
     * @param array $context
     * @return Logic_Watson_Response
     */
    public function sendMessage($message, $context = [])
    {
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setCurlOption(CURLOPT_FOLLOWLOCATION, true);
        
        $data = [
            'input' => [
                'text' => $message,
            ],
        ];
        
        if (!empty($context)) {
            $data['context'] = $context;
        }
        
        $adapter->setCurlOption(CURLOPT_USERNAME, 'Apikey');
        $adapter->setCurlOption(CURLOPT_PASSWORD, $this->getApiKey());
        
        $client = new Base_Http_Client();
        $client->setAdapter($adapter);
        $client->setRawData(json_encode($data));
        $client->setHeaders('Content-Type', 'application/json');
        $client->setUri($this->getUri());
        
        $response = $client->request(self::METHOD_POST);
        
        return new Logic_Watson_Response($response->getBody());
    }
    
    /**
     * @param string $message
     * @param array $context
     * @return Logic_Watson_Response
     */
    public function getResponse($message, $context = [])
    {
        $logic = new Logic_Watson_Phrases();
        
        if ($logic->isPhraseDisplayed($message)) {
            // fraza została już wyświetlona użytkownikowi
            // wyświetlamy standardową wiadomość powitalną
            $message = self::MESSAGE_HELLO;
        }
        
        $response = $this->sendMessage($message, $context);
        
        $logic->setPhraseDisplayed($message);
        
        return $response;
    }
    
    protected function getUri()
    {
        $workspace = $this->getWorkspace();
        
        $uri = 'https://gateway-fra.watsonplatform.net/assistant/api/v1/workspaces/' . $workspace . '/message?version=2019-02-28';
        
        return $uri;
    }
}
