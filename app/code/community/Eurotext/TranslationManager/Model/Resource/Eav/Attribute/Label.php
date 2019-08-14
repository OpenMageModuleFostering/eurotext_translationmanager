<?php

class Eurotext_TranslationManager_Model_Resource_Eav_Attribute_Label extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eav/attribute_label', 'attribute_label_id');
    }
}
