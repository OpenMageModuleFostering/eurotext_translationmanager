<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('edit_form');
        $this->setTitle($this->__('Project Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            [
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
