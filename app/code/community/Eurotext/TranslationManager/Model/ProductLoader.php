<?php

class Eurotext_TranslationManager_Model_ProductLoader
{
    private $requiredAttributes = [
        'entity_id', 'attribute_set_id', 'store_id', 'media_gallery'
    ];

    /**
     * @param int $productId
     * @param int $storeId
     *
     * @return Mage_Catalog_Model_Product
     */
    public function load($productId, $storeId)
    {
        if (!is_int($productId) || $productId <= 0) {
            if (is_string($productId) || is_int($productId)) {
                Mage::log("Wrong Product (ID: $productId)");
                Mage::throwException(
                    Mage::helper('eurotext_translationmanager')->__("Wrong Product ID '%s'", $productId)
                );
            }
            Mage::log('Wrong Product (ID: <no int>)');
            Mage::throwException(
                Mage::helper('eurotext_translationmanager')->__("Wrong Product ID '<no int>'", $productId)
            );
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

        if ($product->isObjectNew()) {
            throw new Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity(
                sprintf('Product with id "%s" doesn\'t exist (anymore).', $productId),
                0,
                null,
                $productId
            );
        }

        $requiredAttributes = $this->requiredAttributes;
        $data = $product->getData();
        array_walk(
            $data,
            function (&$value, $key) use ($requiredAttributes) {
                if (in_array($key, $requiredAttributes, true)) {
                    return;
                }
                $value = false;
            }
        );

        $product->setData($data);

        return $product;
    }
}
