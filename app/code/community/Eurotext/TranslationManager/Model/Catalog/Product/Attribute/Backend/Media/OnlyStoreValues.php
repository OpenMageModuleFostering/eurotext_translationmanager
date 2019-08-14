<?php

class Eurotext_TranslationManager_Model_Catalog_Product_Attribute_Backend_Media_OnlyStoreValues
    extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{
    protected function _getResource()
    {
        return Mage::getResourceSingleton(
            'eurotext_translationmanager/catalog_product_attribute_backend_media_onlyStoreValues'
        );
    }
}
