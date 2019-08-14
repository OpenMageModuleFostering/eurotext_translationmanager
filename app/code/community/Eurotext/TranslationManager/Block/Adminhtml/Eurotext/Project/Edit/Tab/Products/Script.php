<?php

/**
 * @method int[] getSelectedProducts()
 * @method setSelectedProducts(int[] $products)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_Script
    extends Mage_Adminhtml_Block_Template
{
    /**
     * @var int[]
     */
    private $productWithCategories;

    /**
     * @var int[]
     */
    private $categoryWithProducts;

    public function getCategoriesForProducts()
    {
        $this->createRelations();

        return $this->productWithCategories;
    }

    public function getProductForCategoriesFromProducts()
    {
        $this->createRelations();

        return $this->categoryWithProducts;
    }

    private function createRelations()
    {
        if ($this->productWithCategories !== null) {
            return;
        }
        $products = Mage::getModel('catalog/product_api_v2')->items(
            ['product_id' => ['in' => $this->getSelectedProducts()]]
        );

        $this->productWithCategories = [];
        $this->categoryWithProducts = [];

        foreach ($products as $p) {
            $this->productWithCategories[$p['product_id']] = $p['category_ids'];
            foreach ($p['category_ids'] as $c) {
                $this->categoryWithProducts[$c][] = $p['product_id'];
            }
        }
    }
}
