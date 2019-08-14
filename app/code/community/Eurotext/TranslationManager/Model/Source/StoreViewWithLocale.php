<?php

class Eurotext_TranslationManager_Model_Source_StoreViewWithLocale
{
    private $options = [];

    public function toOptionArray()
    {
        if ($this->options) {
            return $this->options;
        }
        $stores = Mage::getResourceModel('core/store_collection')
            ->load();

        $options = [];

        foreach ($stores as $store) {
            $language = Mage::getStoreConfig('general/locale/code', $store);
            $options[] =
                [
                    'label' => sprintf('%s (%s)', $store->getName(), $language),
                    'value' => $store->getId()
                ];
        }
        $this->options = $options;

        return $this->options;
    }
}
