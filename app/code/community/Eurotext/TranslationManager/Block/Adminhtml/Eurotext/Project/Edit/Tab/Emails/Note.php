<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Emails_Note
    extends Mage_Adminhtml_Block_Template
{
    /**
     * @return string
     */
    public function getLanguageFromProject()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getStoreviewDstLocale();
    }
}
