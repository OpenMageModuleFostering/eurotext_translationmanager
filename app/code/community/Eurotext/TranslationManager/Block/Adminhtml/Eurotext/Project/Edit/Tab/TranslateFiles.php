<?php

/**
 * @method string[] getSelectedTranslateFiles()
 * @method setSelectedTranslateFiles(string[] $files)
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TranslateFiles
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->setId('translateFilesGrid');
        $this->setDefaultSort('basename');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('transactionFiles_filter');
    }

    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'translateFiles',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'translateFiles',
                'values'           => $this->_getSelectedTranslateFiles(),
                'align'            => 'center',
                'index'            => 'relativeToApp',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        $this->addColumn(
            'relativeToApp',
            [
                'header'    => Mage::helper('cms')->__('Filename'),
                'align'     => 'left',
                'index'     => 'relativeToApp',
                'use_index' => true,
            ]
        );

        $this->addColumn(
            'basename',
            [
                'header' => Mage::helper('cms')->__('Basename'),
                'align'  => 'left',
                'index'  => 'basename',
            ]
        );

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('eurotext_translationmanager/resource_csv_collection');
        $renderer = Mage::getModel('eurotext_translationmanager/renderer_filesystem_relativeToAppDirectory');
        $collection->addRenderer($renderer);

        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');
        $srcLocale = $project->getStoreviewSrcLocale();
        $store = $project->getStoreviewSrc();
        $collection->setTargetPathByLanguageAndStore($srcLocale, $store);
        $localePath = dirname(
            Mage::getModel('core/design_package')
                ->setStore($project->getStoreviewSrc())
                ->getLocaleFileName('translate.csv')
        );

        $collection->addTargetDir(Mage::getBaseDir('locale') . DS . $srcLocale);
        if (is_dir($localePath)) {
            $collection->addTargetDir($localePath);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return string[]
     */
    public function _getSelectedTranslateFiles()
    {
        $translateFiles = $this->getSelectedTranslateFiles();
        if (!is_array($translateFiles)) {
            $translateFiles = $this->getSelectedTranslateFilesFromDatabase();
        }

        return $translateFiles;
    }

    /**
     * @return string[]
     */
    private function getSelectedTranslateFilesFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getTranslationFiles();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/translatefilesgrid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Translation files');
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
