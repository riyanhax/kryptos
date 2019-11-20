<?php
class Base_Logger_Driver_Db extends Base_Logger_Driver_Abstract
{
    /**
     * @var Base_Db_Table_Abstract
     */
    protected $model;
    
    public function __construct($model)
    {
        $this->setModel($model);
    }
    
    /**
     * @return Base_Db_Table_Abstract
     */
    public function getModel()
    {
        return $this->model;
    }

    public function setModel(Base_Db_Table_Abstract $model)
    {
        $this->model = $model;
    }
    
    public function logMessage($message, $additionalInfo = [])
    {
        $identity = Base_Auth::getInstance()->getIdentity();
        $model = $this->getModel();
        
        $data = [
            'error_message' => $message,
        ];
        
        if ($message instanceof Exception) {
            $data = [
                'error_message' => $message->getMessage(),
                'error_code' => $message->getCode(),
                'stack_trace' => $message->getTraceAsString(),
                'error_file' => $message->getFile(),
                'error_line' => $message->getLine(),
            ];
        }
        
        $model->createRow(array_merge($additionalInfo, $data, [
            'created_by' => $identity->id,
        ]))->save();
    }
}
