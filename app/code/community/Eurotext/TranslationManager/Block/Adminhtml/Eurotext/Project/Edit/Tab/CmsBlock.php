<?php

/**
 * @method setSelectedCmsBlocks(int[] $ids)
 * @method int[] getSelectedCmsBlocks()
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_CmsBlock
    extends Mage_Adminhtml_Block_Cms_Block_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('cmsblock_filter');
    }

    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'blocks',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'blocks',
                'values'           => $this->_getSelectedCmsBlocks(),
                'align'            => 'center',
                'index'            => 'block_id',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        return parent::_prepareColumns();
    }

    public function _getSelectedCmsBlocks()
    {
        $products = $this->getSelectedCmsBlocks();
        if (!is_array($products)) {
            $products = $this->getSelectedCmsBlocksFromDatabase();
        }

        return $products;
    }

    private function getSelectedCmsBlocksFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getBlocks();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/cmsblocksgrid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('CMS Blocks');
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
