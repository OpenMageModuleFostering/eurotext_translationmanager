<?php

class Eurotext_TranslationManager_Model_Resource_Project_Import extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project_import', 'import_id');
    }

    /**
     * @param int $id
     */
    public function deleteByProjectId($id)
    {
        $this->_getConnection('core_write')->delete(
            $this->getMainTable(),
            $this->getReadConnection()->quoteInto('project_id = ?', $id)
        );
    }
}
