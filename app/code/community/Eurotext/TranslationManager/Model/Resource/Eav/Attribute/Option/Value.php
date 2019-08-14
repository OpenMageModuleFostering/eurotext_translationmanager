<?php

class Eurotext_TranslationManager_Model_Resource_Eav_Attribute_Option_Value extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eav/attribute_option_value', 'value_id');
    }
}
