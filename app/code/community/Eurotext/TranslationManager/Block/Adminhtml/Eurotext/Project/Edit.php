<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        /** @var $project Eurotext_TranslationManager_Model_Project */
        $project = Mage::registry('project');
        $this->_objectId = 'project_id';

        parent::__construct();

        $this->_removeButton('reset');

        $this->_controller = 'adminhtml_eurotext_project';
        $this->_blockGroup = 'eurotext_translationmanager';

        $this->_updateButton('save', 'label', Mage::helper('eurotext_translationmanager')->__('Save Project'));

        $projectId = $project->getId();

        $exportUrl = $this->getUrl('*/eurotext_project_export', ['project_id' => $projectId]);
        if (!$project->isObjectNew()) {
            $this->_addButton(
                'export',
                [
                    'label'   => $this->__('Export to Eurotext AG'),
                    'onclick' => "setLocation('$exportUrl')",
                    'class'   => 'go export',
                    'id'      => 'export_button'
                ]
            );
        }

        if (!$project->isEditable()) {
            $this->_removeButton('save');
            $this->_removeButton('export');

            $url = $this->getUrl('*/eurotext_project/reset', ['project_id' => $projectId]);
            $this->_addButton(
                'step_back',
                [
                    'label'   => $this->__('Reset to last status'),
                    'onclick' => "setLocation('$url');",
                    'class'   => 'cancel'
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('eurotext_translationmanager')->__('New project');
    }
}
