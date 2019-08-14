<?php

/**
 * @method string getFilename()
 * @method int getProjectId()
 * @method string getCreatedAt()
 * @method int getProjectEmailtemplatesId()
 * @method $this setFilename(string $filename)
 * @method $this setProjectId(int $projectId)
 * @method $this setCreatedAt(string $createdAtDate)
 * @method $this setProjectEmailtemplatesId(int $projectEmailTemplateId)
 */
class Eurotext_TranslationManager_Model_Project_EmailtemplateFile extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project_emailtemplateFile');
    }
}
