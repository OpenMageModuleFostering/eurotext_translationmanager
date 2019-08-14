<?php

class Eurotext_TranslationManager_Model_Resource_Project_Csv extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project_csv', 'line_hash');
    }
}
