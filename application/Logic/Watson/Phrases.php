<?php
class Logic_Watson_Phrases
{
    public function setPhraseDisplayed($phrase)
    {
        $row = $this->getPhraseRow($phrase);
        $identity = Base_Auth::getInstance()->getIdentity();
        $model = new Application_Model_WatsonUserPhrasesDisplayed();
        
        if (!empty($phrase) && !empty($row)) {
            $model->createRow([
                'id_watson_init_phrase' => $row->id,
                'id_user' => $identity->id,
            ])->save();
        }
    }
    
    public function isPhraseDisplayed($phrase)
    {
        $displayed = false;
        $row = $this->getPhraseRow($phrase);
        $identity = Base_Auth::getInstance()->getIdentity();
        $model = new Application_Model_WatsonUserPhrasesDisplayed();
        
        if (!empty($row)) {
            $select = $model->select()
                ->where('id_watson_init_phrase = ?', $row->id)
                ->where('id_user = ?', $identity->id)
                ->where('ghost IS NOT TRUE');
            
            $rowDisplayed = $model->fetchRow($select);
            
            $displayed = !empty($rowDisplayed);
        }
        
        return $displayed;
    }
    
    public function getPhraseRow($phrase)
    {
        $row = null;
        
        if (!empty($phrase)) {
            $model = new Application_Model_WatsonInitPhrases();

            $select = $model->select()
                ->where('phrase = ?', $phrase)
                ->where('ghost IS NOT TRUE');

            $row = $model->fetchRow($select);
        }
        
        return $row;
    }
}
