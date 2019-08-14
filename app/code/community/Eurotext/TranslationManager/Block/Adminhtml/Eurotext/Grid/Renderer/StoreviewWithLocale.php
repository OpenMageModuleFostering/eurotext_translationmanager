<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_Renderer_StoreviewWithLocale extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $storeId = $row->getData($this->getColumn()->getIndex());
        try {
            $store = Mage::app()->getStore($storeId);

            return sprintf('%s (%s)', $store->getName(), Mage::getStoreConfig('general/locale/code', $store));
        } catch (Mage_Core_Model_Store_Exception $e) {
            // store not found (happens if $storeId = -1)
        }

        return '';
    }
}
