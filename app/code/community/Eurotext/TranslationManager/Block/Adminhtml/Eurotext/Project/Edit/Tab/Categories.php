<?php

/**
 * @method int[] getSelectedCategories()
 * @method setSelectedCategories(int[] $ids)
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Categories
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->setId('categoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('category_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addAttributeToSelect(['name', 'is_active']);
        $collection->addFieldToFilter('path', ['neq' => '1']);
        $collection->setLoadProductCount(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'categories',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'categories',
                'values'           => $this->_getSelectedCategories(),
                'align'            => 'center',
                'index'            => 'entity_id',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : ''
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => $this->__('ID'),
                'width'  => '50px',
                'type'   => 'number',
                'index'  => 'entity_id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => $this->__('Name'),
                'index'  => 'name',
            ]
        );

        $sourceBoolean = Mage::getSingleton('eav/entity_attribute_source_boolean');
        $this->addColumn(
            'is_active',
            [
                'header'  => $this->__('Active'),
                'width'   => '70px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => $sourceBoolean->getOptionArray(),
            ]
        );

        $this->addColumn(
            'product_count',
            [
                'header'   => $this->__('Product Count'),
                'width'    => '30px',
                'index'    => 'product_count',
                'type'     => 'int',
                'sortable' => false,
                'filter'   => false,
            ]
        );

        return parent::_prepareColumns();
    }

    public function _getSelectedCategories()
    {
        $products = $this->getSelectedCategories();
        if (!is_array($products)) {
            $products = $this->getSelectedCategoriesFromDatabase();
        }

        return $products;
    }

    private function getSelectedCategoriesFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getCategories();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Categories');
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

    /**
     * @param Varien_Object $item
     * @return bool
     */
    public function getMultipleRows($item)
    {
        return false;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/categoriesgrid', ['_current' => true]);
    }

}
