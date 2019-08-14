<?php

class Eurotext_TranslationManager_Model_Resource_Catalog_Product_Option_Title
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/product_option_title', 'option_title_id');
    }

    /**
     * @param int    $optionId
     * @param string $title
     * @param int    $storeId
     */
    public function updateTitleForStore($optionId, $title, $storeId)
    {
        $this->_getConnection('catalog_write')->query(
            "INSERT INTO
                  {$this->getMainTable()}
                  (option_id, store_id, title) VALUES (:optionId, :storeId, :title)
                    ON DUPLICATE KEY UPDATE title=:title;",
            [':optionId' => $optionId, ':storeId' => $storeId, ':title' => $title]
        );
    }
}
