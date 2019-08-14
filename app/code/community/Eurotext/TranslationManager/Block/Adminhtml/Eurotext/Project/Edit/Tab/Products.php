<?php

/**
 * @method int[] getSelectedProducts()
 * @method setSelectedProducts(int[] $ids)
 * @method Mage_Catalog_Model_Resource_Product_Collection getCollection()
 * @method setSelected(int[] $ids)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    /**
     * @var string
     */
    protected $_massactionBlockName = 'eurotext_translationmanager/adminhtml_eurotext_project_edit_tab_grid_massaction';

    /**
     * @var string
     */
    protected $_massactionIdField = 'products';

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = $this->getChildHtml('add_bulk_skus');
        $html .= parent::getMainButtonsHtml();

        return $html;
    }

    /**
     * @return int[]
     */
    public function _getSelectedProducts()
    {
        $products = $this->getSelectedProducts();
        if (!is_array($products)) {
            $products = $this->getSelectedProductsFromDatabase();
        }

        return $products;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
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
        return $this->__('Products');
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
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'add_bulk_skus',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    [
                        'label'   => Mage::helper('adminhtml')->__('Add Bulk Skus'),
                        'onclick' => 'Effect.toggle(\'bulk_sku\', \'blind\')',
                        'class'   => 'go'
                    ]
                )
        );

        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products $thisBlock */
        $thisBlock = parent::_prepareLayout();

        return $thisBlock;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->_rssLists = [];
        $this->removeColumn('action');
        $this->removeColumn('websites');
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->removeColumn('qty');
            $this->addColumn(
                'is_in_stock',
                [
                    'header'  => $this->__('Stock Availability'),
                    'width'   => '50px',
                    'type'    => 'options',
                    'index'   => 'is_in_stock',
                    'options' => [
                        Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK     => $this->__('In Stock'),
                        Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK => $this->__('Out of Stock'),
                    ]
                ]
            );
        }
        $this->removeColumn('price');

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->createCollection();

        $store = $this->_getStore();
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );

            $collection->joinField(
                'is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->removeItem('status');
        $this->getMassactionBlock()->removeItem('attributes');
        $this->getMassactionBlock()->setFormFieldName('products');

        return $this;
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }

            return $this;
        }

        parent::_addColumnFilterToCollection($column);

        return $this;
    }

    /**
     * @return int[]
     */
    private function getSelectedProductsFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getProducts();
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function createCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();

        $category = Mage::getModel('catalog/category')->load($this->getRequest()->getParam('category_id'));
        if (!$category->isObjectNew()) {
            if (!$category->getIsAnchor()) {
                $collection = $category->getProductCollection();

                return $collection;
            } else {
                $categoryCollection = Mage::getResourceModel('catalog/category_collection')
                    ->addPathFilter("^{$category->getPath()}/[0-9]*$");
                $categories = $categoryCollection->getAllIds();
                $categories[] = $category->getId();
                $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id')
                    ->addAttributeToFilter('category_id', ['in' => $categories]);

                return $collection;
            }
        }

        return $collection;
    }
}
