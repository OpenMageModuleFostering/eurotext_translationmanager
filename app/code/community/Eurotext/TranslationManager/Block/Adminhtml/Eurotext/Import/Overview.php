<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Import_Overview extends Mage_Adminhtml_Block_Template
{
    /**
     * @return int
     */
    public function getProjectId()
    {
        return Mage::registry('project')->getId();
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getUrl('*/eurotext_project/edit', ['project_id' => $this->getProjectId()]);
    }

    /**
     * @return string
     */
    public function getAddFilesToImportQueueUrl()
    {
        return $this->getUrl('*/*/addFilesToImportQueue');
    }

    /**
     * @return string
     */
    public function getProcessFilesFromQueueUrl()
    {
        return $this->getUrl('*/*/processFilesFromQueue');
    }
}
