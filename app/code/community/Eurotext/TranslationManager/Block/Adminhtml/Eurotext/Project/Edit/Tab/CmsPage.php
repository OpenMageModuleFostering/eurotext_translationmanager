<?php

/**
 * @method int[] getSelectedCmsPages()
 * @method setSelectedCmsPages(int[] $ids)
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_CmsPage
    extends Mage_Adminhtml_Block_Cms_Page_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('cmspage_filter');
    }

    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'pages',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'pages',
                'values'           => $this->getSelectedCmsPages(),
                'align'            => 'center',
                'index'            => 'page_id',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        parent::_prepareColumns();

        $this->removeColumn('page_actions');

        return $this;
    }

    public function _getSelectedCmsPages()
    {
        $products = $this->getSelectedCmsPages();
        if (!is_array($products)) {
            $products = $this->getSelectedCmsPagesFromDatabase();
        }

        return $products;
    }

    private function getSelectedCmsPagesFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getPages();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/cmspagesgrid', ['_current' => true]);
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
        return $this->__('CMS Pages');
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
