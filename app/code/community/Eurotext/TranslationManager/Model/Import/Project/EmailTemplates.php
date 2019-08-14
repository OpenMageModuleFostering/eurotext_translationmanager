<?php

class Eurotext_TranslationManager_Model_Import_Project_EmailTemplates
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @var string[]
     */
    private $mapping = [
        'template_text'    => 'Text',
        'template_styles'  => 'Styles',
        'template_subject' => 'Subject',
    ];

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $file
     */
    public function import($file, Eurotext_TranslationManager_Model_Project $project)
    {
        $sxml = simplexml_load_file($file);
        foreach ($sxml->xpath('//email') as $templateNode) {
            $this->importTemplate($project, $templateNode);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param SimpleXMLElement                          $templateNode
     */
    private function importTemplate(Eurotext_TranslationManager_Model_Project $project, SimpleXMLElement $templateNode)
    {
        if ('false' === (string)$templateNode->Database) {
            $this->importEmailFileTemplate($project, $templateNode);
        } else {
            $this->importEmailDatabaseTemplate($project, $templateNode);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param SimpleXMLElement                          $templateNode
     */
    private function importEmailDatabaseTemplate(
        Eurotext_TranslationManager_Model_Project $project,
        SimpleXMLElement $templateNode
    ) {
        try {
            $template = $this->createTemplate($templateNode, $project);
            $this->updateEmailConfig($templateNode, $template);
            $this->clearCache();
        } catch (Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity $e) {
            $this->addSkipped($e->getSkippedEntity());
        }
    }

    /**
     * @param SimpleXMLElement                          $templateNode
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return Mage_Core_Model_Email_Template
     */
    private function createTemplate($templateNode, Eurotext_TranslationManager_Model_Project $project)
    {
        /** @var Mage_Core_Model_Email_Template $template */
        $templateId = (int)$templateNode->Id;
        $template = Mage::getModel('core/email_template')->load($templateId);
        if ($template->isObjectNew()) {
            throw new Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity(
                sprintf('Template with id "%s" can\'t be cloned, it is missing.', $templateId), 0, null, $templateId
            );
        }
        $template->setId(null);
        foreach ($this->mapping as $magento => $eurotext) {
            $template->setDataUsingMethod($magento, (string)$templateNode->{$eurotext});
        }
        $template->setTemplateCode(sprintf('[%s] %s', $project->getStoreviewDstLocale(), $template->getTemplateCode()));
        $template->save();

        return $template;
    }

    /**
     * @param SimpleXMLElement               $templateNode
     * @param Mage_Core_Model_Email_Template $template
     */
    private function updateEmailConfig($templateNode, $template)
    {
        /** @var $configPaths SimpleXMLElement */
        $configPaths = $templateNode->Path;
        for ($i = 0; $i < $configPaths->count(); $i++) {
            $path = $configPaths[$i];
            $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES;
            Mage::getConfig()->saveConfig((string)$path, $template->getId(), $scope, (int)$templateNode->StoreviewDst);
        }
    }

    private function clearCache()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    private function importEmailFileTemplate(
        Eurotext_TranslationManager_Model_Project $project,
        SimpleXMLElement $templateNode
    ) {
        $path = (string)$templateNode->Path;
        $text = (string)$templateNode->Text;
        $subject = (string)$templateNode->Subject;
        $styles = (string)$templateNode->Styles;

        $html = <<<HTML
<!--@subject $subject @-->
<!--@styles $styles@-->
$text

HTML;
        $file = Mage::getBaseDir('app') . '/locale/' . $project->getStoreviewDstLocale() . '/template' . $path;
        $dir = dirname($file);
        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new Exception(sprintf('Directory %s could not be created.', $dir));
        }
        file_put_contents($file, $html);
    }
}
