<?php

/**
 * @method setTitle(string $title)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('project_id');
        $this->setTitle(Mage::helper('eurotext_translationmanager')->__('Project Information'));
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('eurotext_translationmanager');
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $form = new Varien_Data_Form(
            [
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            ]
        );

        $form->setHtmtIdPrefix('project_');

        if ($project->isTranslationImportable()) {
            $fieldset = $form->addFieldset('translation', ['legend' => $helper->__('Import translations')]);
            $fieldset->addType(
                'uploader',
                Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Main_Uploader::class
            );
            $fieldset->addField(
                'translation_file',
                'uploader',
                [
                    'name'    => 'translation_file',
                    'comment' => $helper->__('Import the ZIP file containing the finished translations here.'),
                    'label'   => $helper->__('Translation'),
                    'class'   => 'required-entry',
                ]
            );
        }

        $fieldset = $form->addFieldset('project', ['legend' => $helper->__('Project Information')]);

        $fieldset->addField(
            'project_name',
            'text',
            [
                'name'     => 'project_name',
                'label'    => $helper->__('Name'),
                'class'    => 'required-entry',
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        if (!$project->isObjectNew()) {
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name'  => 'id',
                    'class' => 'required-entry',
                ]
            );
        }

        $fieldset->addField(
            'storeview_src',
            'select',
            [
                'name'     => 'storeview_src',
                'label'    => $helper->__('Source StoreView'),
                'values'   => Mage::getModel('eurotext_translationmanager/source_storeViewWithLocale')->toOptionArray(),
                'class'    => 'required-entry',
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        $fieldset->addField(
            'storeview_dst',
            'select',
            [
                'name'     => 'storeview_dst',
                'label'    => $helper->__('Target StoreView'),
                'values'   => Mage::getModel('eurotext_translationmanager/source_storeViewWithLocale')->toOptionArray(),
                'class'    => 'validate-storeview-different required-entry',
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        $fieldset->addField(
            'customer_comment',
            'textarea',
            [
                'name'     => 'customer_comment',
                'label'    => $helper->__('Comment'),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        $fieldset = $form->addFieldset(
            'exported_data',
            [
                'legend' => $helper->__('Exported data'),
            ]
        );

        $fieldset->addField(
            'comment',
            'note',
            [
                'text' => '<b>' . $helper->__('Only data is exported, which is not translated yet.') . '</b>',
            ]
        );

        $fieldset->addField('productmode_hidden', 'hidden', ['name' => 'productmode', 'value' => 0]);
        $fieldset->addField(
            'productmode',
            'checkbox',
            [
                'name'     => 'productmode',
                'label'    => $helper->__('Products'),
                'value'    => 1,
                'checked'  => $project->getProductmode(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('categorymode_hidden', 'hidden', ['name' => 'categorymode', 'value' => 0]);
        $fieldset->addField(
            'categorymode',
            'checkbox',
            [
                'name'     => 'categorymode',
                'label'    => $helper->__('Categories'),
                'value'    => 1,
                'checked'  => $project->getCategorymode(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('cmsmode_hidden', 'hidden', ['name' => 'cmsmode', 'value' => 0]);
        $fieldset->addField(
            'cmsmode',
            'checkbox',
            [
                'name'     => 'cmsmode',
                'label'    => $helper->__('CMS Pages and Blocks'),
                'value'    => 1,
                'checked'  => $project->getCmsmode(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('templatemode_hidden', 'hidden', ['name' => 'templatemode', 'value' => 0]);
        $fieldset->addField(
            'templatemode',
            'checkbox',
            [
                'name'     => 'templatemode',
                'label'    => $helper->__('Email Templates'),
                'value'    => 1,
                'checked'  => $project->getTemplatemode(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('langfilesmode_hidden', 'hidden', ['name' => 'langfilesmode', 'value' => 0]);
        $fieldset->addField(
            'langfilesmode',
            'checkbox',
            [
                'name'     => 'langfilesmode',
                'label'    => $helper->__('Translation files'),
                'value'    => 1,
                'checked'  => $project->getLangfilesmode(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('export_attributes_hidden', 'hidden', ['name' => 'export_attributes', 'value' => 0]);
        $fieldset->addField(
            'export_attributes',
            'checkbox',
            [
                'name'     => 'export_attributes',
                'label'    => $helper->__('Attributes and attribute options'),
                'value'    => 1,
                'checked'  => $project->getExportAttributes(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('export_urlkeys_hidden', 'hidden', ['name' => 'export_urlkeys', 'value' => 0]);
        $fieldset->addField(
            'export_urlkeys',
            'checkbox',
            [
                'name'     => 'export_urlkeys',
                'label'    => $helper->__('URL keys'),
                'value'    => 1,
                'checked'  => $project->getExportUrlkeys(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField('export_seo_hidden', 'hidden', ['name' => 'export_seo', 'value' => 0]);
        $fieldset->addField(
            'export_seo',
            'checkbox',
            [
                'name'     => 'export_seo',
                'label'    => $helper->__('SEO content'),
                'value'    => 1,
                'checked'  => $project->getExportSeo(),
                'disabled' => !$project->isEditable() ? 'disabled="disabled"' : '',
                'onclick'  => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $this->setForm($form);
        $form->setValues($project->getData());

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Project Details');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }
}
