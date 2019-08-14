<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_HelpController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('eurotext_translationmanager/help');
    }
}
