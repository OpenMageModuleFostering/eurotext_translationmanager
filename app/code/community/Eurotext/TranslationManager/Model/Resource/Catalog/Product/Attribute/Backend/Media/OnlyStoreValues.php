<?php

class Eurotext_TranslationManager_Model_Resource_Catalog_Product_Attribute_Backend_Media_OnlyStoreValues
    extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media
{
    protected function _getLoadGallerySelect(array $productIds, $storeId, $attributeId)
    {
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
                ['main' => $this->getMainTable()],
                ['value_id', 'value AS file', 'product_id' => 'entity_id']
            )
            ->joinLeft(
                ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
                ['label', 'position', 'disabled']
            )
            ->joinLeft( // Joining default values
                ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                [
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                ]
            )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id in (?)', $productIds)
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }

}
