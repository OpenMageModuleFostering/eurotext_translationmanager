<?php

class Eurotext_TranslationManager_Model_Project_Source_Status extends Mage_Adminhtml_Model_System_Config_Source_Yesno
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Eurotext_TranslationManager_Model_Project::STATUS_NEW                 => Mage::helper('eurotext_translationmanager')->__('New'),
            Eurotext_TranslationManager_Model_Project::STATUS_EXPORTED_TO_EUROTEXT => Mage::helper('eurotext_translationmanager')->__('Exported'),
            Eurotext_TranslationManager_Model_Project::STATUS_IMPORT_TRANSLATIONS_INTO_QUEUE => Mage::helper('eurotext_translationmanager')->__('In progress'),
            Eurotext_TranslationManager_Model_Project::STATUS_DONE                => Mage::helper('eurotext_translationmanager')->__('Loaded'),
        ];
    }
}
