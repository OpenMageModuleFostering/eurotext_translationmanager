<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'eurotext_translationmanager';
        $this->_controller = 'adminhtml_eurotext_project';
        $this->_headerText = $this->__('Projects');
        
        parent::__construct();
    }
}
