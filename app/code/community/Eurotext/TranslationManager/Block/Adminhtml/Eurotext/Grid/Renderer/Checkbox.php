<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_Renderer_Checkbox
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
    public function getDisabled()
    {
        /** @var $project Eurotext_TranslationManager_Model_Project */
        $project = Mage::registry('project');

        if (!$project->isEditable()) {
            return 'disabled="disabled"';
        }

        return '';
    }
}
