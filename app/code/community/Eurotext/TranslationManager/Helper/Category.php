<?php

class Eurotext_TranslationManager_Helper_Category
{
    /**
     * @param int[] $categoryIds
     * @return int[]
     */
    public function getProductIdsByCategoryIds($categoryIds)
    {
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->joinField(
                'category_id',
                'catalog/category_product',
                'category_id',
                'product_id = entity_id',
                null,
                'left'
            )
            ->addAttributeToFilter('category_id', ['in' => $categoryIds]);

        return $productCollection->getAllIds();
    }
}
