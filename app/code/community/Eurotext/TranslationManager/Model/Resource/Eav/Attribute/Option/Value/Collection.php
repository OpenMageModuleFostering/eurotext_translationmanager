<?php

class Eurotext_TranslationManager_Model_Resource_Eav_Attribute_Option_Value_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/eav_attribute_option_value');
    }

    /**
     * @param int $id
     */
    public function filterByAttribute($id)
    {
        $this->join(['option_table' => 'eav/attribute_option'], 'option_table.option_id=main_table.option_id', '');
        $this->addFieldToFilter('option_table.attribute_id', $id);
    }

    public function joinStoreLabel($storeId)
    {
        $this->addFieldToFilter('main_table.store_id', Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->getSelect()->joinLeft(
            ['store_label' => $this->getMainTable()],
            "main_table.option_id = store_label.option_id AND store_label.store_id = $storeId",
            ['store_value' => 'store_label.value']
        );
    }
}
