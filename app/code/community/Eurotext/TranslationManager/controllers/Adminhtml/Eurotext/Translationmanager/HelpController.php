<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_HelpController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('eurotext_translationmanager/help');
    }


    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}