<?php

class ErrController extends Muzyka_Action
{
    protected $baseUrl = '/err';

    public function init()
    {
        parent::init();
        $this->view->baseUrl = $this->baseUrl;
    }
    
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
    }
    
    public function site404Action()
    {
        diee('tu');
    }
    
    public function notallowedAction()
    {
        $container = new Zend_Session_Namespace('acl');
        
        $this->view->message = $container->message;
    }
}