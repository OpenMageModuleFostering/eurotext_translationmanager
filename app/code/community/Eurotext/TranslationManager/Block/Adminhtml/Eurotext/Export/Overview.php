<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Export_Overview extends Mage_Adminhtml_Block_Template
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
    public function getExportUrl()
    {
        return $this->getUrl('*/eurotext_project_export/export');
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getUrl('*/eurotext_project/edit', ['project_id' => $this->getProjectId()]);
    }
}
