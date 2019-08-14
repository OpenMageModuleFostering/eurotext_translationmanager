<?php

class Eurotext_TranslationManager_Model_System_Config_Source_Salutation
{
    /**
     * @return mixed∆[]
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('eurotext_translationmanager');

        return [
            ['value' => 'MR', 'label' => $helper->__('Mr.')],
            ['value' => 'MRS', 'label' => $helper->__('Mrs.')],
        ];
    }
}
