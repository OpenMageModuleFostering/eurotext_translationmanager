<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Main_Uploader
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Main_Single $uploader */
        $uploader = Mage::app()
            ->getLayout()
            ->createBlock('eurotext_translationmanager/adminhtml_eurotext_project_edit_tab_main_single');

        /** @var $config Eurotext_Uploader_Model_Config_Uploader */
        $config = $uploader->getUploaderConfig();
        $config->setTarget(Mage_Adminhtml_Helper_Data::getUrl('*/eurotext_project_import/import'));
        $config->setFileParameterName('translation_file');
        $config->setQuery(
            [
                'form_key'   => Mage::getSingleton('core/session')->getFormKey(),
                'project_id' => Mage::registry('project')->getId(),
            ]
        );

        return $uploader->toHtml();
    }


}
