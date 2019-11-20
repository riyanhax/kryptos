<?php
class Base_Controller_Plugin_Watson extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $row = $this->getWatsonInitPhrase($request);
        
        $view = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('view');
        
        if (!empty($row)) {
            $view->watson_init_phrase = $row->phrase;
        }
    }
    
    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return Application_Service_EntityRow
     */
    protected function getWatsonInitPhrase(Zend_Controller_Request_Abstract $request)
    {
        $identity = Base_Auth::getInstance()->getIdentity();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        
        $model = new Application_Model_WatsonPhraseDisplay();
        
        $select = $model->select()
            ->setIntegrityCheck(false)
            ->from(['wpd' => 'watson_phrase_display'])
            ->join(['wip' => 'watson_init_phrases'], 'wip.id = wpd.id_watson_init_phrase', ['phrase'])
            ->where('wpd.controller = ?', $controllerName)
            ->where('wpd.action = ?', $actionName)
            ->where('wpd.ghost IS NOT TRUE')
            ->where('wip.ghost IS NOT TRUE');
        
        if ($identity->isAdmin) {
            $select->where('wpd.is_admin IS TRUE');
        } else {
            $select->where('wpd.is_admin IS FALSE');
        }
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
}
