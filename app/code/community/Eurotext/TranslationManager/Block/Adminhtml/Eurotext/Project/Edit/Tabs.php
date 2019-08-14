<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('project_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('eurotext_translationmanager')->__('Manage project'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', [
            'label'   => $this->__('Project Information'),
            'title'   => $this->__('Project Information'),
            'content' => $this->getLayout()->createBlock('eurotext_translationmanager/adminhtml_eurotext_project_edit_tab_main')->toHtml(),
        ]);


        $this->addTab(
            'products_section',
            [
                'label' => $this->__('Products'),
                'title' => $this->__('Products'),
                'url'   => $this->getUrl('*/*/productsTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'categories_section',
            [
                'label' => $this->__('Categories'),
                'title' => $this->__('Categories'),
                'url'   => $this->getUrl('*/*/categoriesTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'cms_block_section',
            [
                'label' => $this->__('CMS Blocks'),
                'title' => $this->__('CMS Blocks'),
                'url'   => $this->getUrl('*/*/cmsBlocksTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'cms_page_section',
            [
                'label' => $this->__('CMS Pages'),
                'title' => $this->__('CMS Pages'),
                'url'   => $this->getUrl('*/*/cmsPagesTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'transaction_email_section',
            [
                'label' => $this->__('Transactional Emails'),
                'title' => $this->__('Transactional Emails'),
                'url'   => $this->getUrl('*/*/transactionEmailFilesTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'translate_file_section',
            [
                'label' => $this->__('Translation files'),
                'title' => $this->__('Translation files'),
                'url'   => $this->getUrl('*/*/translateFilesTab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_beforeToHtml();
    }
}
