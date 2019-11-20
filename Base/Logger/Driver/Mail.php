<?php
class Base_Logger_Driver_Mail extends Base_Logger_Driver_Abstract
{
    protected $recipients = [];
    
    protected $subject;
    
    protected $mailSchema = null;
    
    public function __construct($recipients = [])
    {
        $this->setRecipients($recipients);
    }
    
    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setRecipients($recipients = [])
    {
        foreach ($recipients as $recipient) {
            $this->addRecipient($recipient);
        }
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;
    }
    
    public function getMailSchema()
    {
        return $this->mailSchema;
    }

    public function setMailSchema($mailSchema)
    {
        $this->mailSchema = $mailSchema;
    }
    
    public function logMessage($message, $additionalInfo = [])
    {
        $recipients = $this->getRecipients();
        
        foreach ($recipients as $recipient) {
            $emailContent = $this->createMessage($message, $additionalInfo);
            
            if (!empty($additionalInfo['mail_subject'])) {
                $this->setSubject($additionalInfo['mail_subject']);
            }
            
            $this->sendEmail($recipient, $emailContent);
        }
    }
    
    /**
     * @return Application_Service_Email
     */
    protected function getHandler()
    {
        $handler = Application_Service_Email::GetInstance();
        
        return $handler;
    }
    
    protected function sendEmail($email, $message, $subject = null)
    {
        if (empty($subject)) {
            $subject = $this->getSubject();
        }
        
        $handler = $this->getHandler();
        
        $data = [
            'text' => $message,
            'sender_id' => '1',
            'recipient_address' => $email,
            'title' => $subject,
        ];
        
        try {
            $handler->send($data);
        } catch (Exception $e) {
            // wystąpił błąd wysyłki maila
            // nie powinno to mieć wpływu na działanie loggera, więc kontynuujemy mimo tego
        }
    }
    
    protected function createMessage($message, $additionalInfo = [])
    {
        $schema = $this->getMailSchema();
        
        $data = [
            'error_message' => $message,
            'error_date' => date('Y-m-d H:i:s'),
        ];
        
        if ($message instanceof Exception) {
            $data = [
                'error_message' => $message->getMessage(),
                'error_code' => $message->getCode(),
                'stack_trace' => $message->getTraceAsString(),
                'error_file' => $message->getFile(),
                'error_line' => $message->getLine(),
                'error_date' => date('Y-m-d H:i:s'),
            ];
        }
        
        $text = Application_Service_Utilities::renderView($schema, array_merge($data, $additionalInfo));
        
        return $text;
    }
}
