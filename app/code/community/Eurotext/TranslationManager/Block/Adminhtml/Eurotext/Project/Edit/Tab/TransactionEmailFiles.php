<?php

/**
 * @method string[] getSelectedTransactionEmailFiles()
 * @method setSelectedTransactionEmailFiles(string[] $files)
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TransactionEmailFiles
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionEmailFilesGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('transactionEmails_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('eurotext_translationmanager/emailtemplate_filesystem_collection');
        $renderer = Mage::getModel('eurotext_translationmanager/renderer_filesystem_relativeToLocaleTemplateDirectory');
        $collection->addRenderer($renderer);

        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');
        $collection->setLanguage($project->getStoreviewSrcLocale());
        $this->setCollection($collection);

        /** @var self $collection */
        $collection = parent::_prepareCollection();

        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'translateFiles',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'transactionEmails',
                'values'           => $this->_getSelectedTransactionEmailFiles(),
                'align'            => 'center',
                'index'            => 'relativeToLocaleTemplate',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        $this->addColumn(
            'relativeToLocaleTemplate',
            [
                'header'    => Mage::helper('cms')->__('Filename'),
                'align'     => 'left',
                'index'     => 'relativeToLocaleTemplate',
                'use_index' => true,
            ]
        );

        parent::_prepareColumns();

        $this->removeColumn('action');

        return $this;
    }

    /**
     * @return string[]
     */
    public function _getSelectedTransactionEmailFiles()
    {
        $translateFiles = $this->getSelectedTransactionEmailFiles();
        if (!is_array($translateFiles)) {
            $translateFiles = $this->getSelectedTransactionEmailFilesFromDatabase();
        }

        return $translateFiles;
    }

    /**
     * @return string[]
     */
    private function getSelectedTransactionEmailFilesFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getTransactionEmailFiles();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/transactionEmailFilesGrid', ['_current' => true]);
    }

    /**
     * @param $row
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Transaction Email Files');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }
}
