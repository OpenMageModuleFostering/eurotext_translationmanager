<?php

/**
 * @method Eurotext_TranslationManager_Model_Import setProjectId(int $projectId)
 * @method Eurotext_TranslationManager_Model_Import setFilename(string $filename)
 * @method Eurotext_TranslationManager_Model_Import setStoreviewDst(int $storeviewDst)
 * @method Eurotext_TranslationManager_Model_Import setNum(int $num)
 * @method Eurotext_TranslationManager_Model_Import setIsImported(int $isImported)
 * @method int getProjectId()
 * @method string getFilename()
 * @method int getStoreviewDst()
 * @method int getNum()
 * @method int getIsImported()
 * @method Eurotext_TranslationManager_Model_Resource_Project_Import getResource()
 */
class Eurotext_TranslationManager_Model_Project_Import extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project_import');
    }

    /**
     * @param int $id
     */
    public function deleteByProjectId($id)
    {
        $this->getResource()->deleteByProjectId($id);
    }
}
