<?php
class Logic_Watson_Response
{
    protected $rawData;
    
    protected $toDisplay;
    
    public function __construct($rawData)
    {
        $this->setRawData($rawData);
    }
    
    public function getRawData()
    {
        return $this->rawData;
    }

    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
    }
    
    public function getToDisplay()
    {
        return $this->toDisplay;
    }

    public function setToDisplay($toDisplay)
    {
        $this->toDisplay = $toDisplay;
    }
        
    public function getData()
    {
        $rawData = $this->getRawData();
        $data = json_decode($rawData, true);
        
        return $data;
    }
    
    public function getText()
    {
        $data = $this->getData();
        
        return $data['output']['text'][0];
    }
    
    public function getContext()
    {
        $data = $this->getData();
        
        return $data['context'];
    }
    
    public function getConversationId()
    {
        $data = $this->getData();
        
        return $data['context']['conversation_id'];
    }
    
    public function getErrorMessage()
    {
        $data = $this->getData();
        
        return $data['error'];
    }
    
    public function isError()
    {
        $data = $this->getData();
        
        return !empty($data['error']);
    }
}
