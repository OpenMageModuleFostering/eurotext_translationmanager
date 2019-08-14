<?php

class Eurotext_TranslationManager_Model_Export_Project_EmailFileTemplates
{
    /**
     * @var string
     */
    private $xmlDir;

    /**
     * @var Eurotext_TranslationManager_Model_Project
     */
    private $project;

    /**
     * @var mixed[]
     */
    private $emailPaths;

    /** @var int */
    private $exportedTemplateFiles = 0;


    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return mixed[]
     */
    public function process(Eurotext_TranslationManager_Model_Project $project)
    {
        $this->project = $project;
        $emailTemplates = $this->createTemplateCollection($project);
        $this->filterTemplatesByProject($project, $emailTemplates);

        $numberOfEmailTemplatesExported = $emailTemplates->count();

        if (!$numberOfEmailTemplatesExported) {
            return [
                'status_msg' => $this->getHelper()->__('No Email-File-Templates exported'),
                'offset'     => 1,
                'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_COLLECT_TEMPLATES_DATABASE,
            ];
        }

        $this->xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
            ->getXmlSubdirectoryAndMakeSureItExists($project, 'emailtemplates');

        $this->collectEmailTemplatesFromConfig();
        $this->writeXml($emailTemplates, $project);

        return [
            'status_msg' => $this->getHelper()->__('Exported %s Email Templates', $this->exportedTemplateFiles),
            'offset'     => 1,
            'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_COLLECT_TEMPLATES_DATABASE,
        ];
    }

    /**
     * @param Varien_Data_Collection                    $templateCollection
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function writeXml(
        Varien_Data_Collection $templateCollection,
        Eurotext_TranslationManager_Model_Project $project
    ) {
        $i = 1;
        foreach ($templateCollection as $template) {
            $xmlWriter = new XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->startElement('emails');

            /** @var Varien_Object $template */
            $relativeFilePath = $template->getData('relativeToLocaleTemplate');
            $fileInConfigXml = substr($relativeFilePath, strlen('/email/'));

            $baseLocale = Mage::getBaseDir('locale');
            $filenameSrc = $baseLocale . "/{$project->getStoreviewSrcLocale()}/template$relativeFilePath";
            $filenameDst = $baseLocale . "/{$project->getStoreviewDstLocale()}/template$relativeFilePath";

            if (!isset($this->emailPaths[$fileInConfigXml]) || !$this->isTranslationNeeded($filenameDst)) {
                continue;
            }
            $this->exportedTemplateFiles++;

            $info = $this->parseTemplate($filenameSrc);

            $xmlWriter->startElement('email');
            $xmlWriter->writeElement('Id', 0);
            $xmlWriter->writeElement('StoreviewSrc', $this->project->getStoreviewSrc());
            $xmlWriter->writeElement('StoreviewDst', $this->project->getStoreviewDst());
            $xmlWriter->writeElement('Path', $relativeFilePath);
            $xmlWriter->writeElement('Database', 'false');
            $type = $this->emailPaths[$fileInConfigXml]['type'];
            $xmlWriter->writeElement('Type', $type);
            $xmlWriter->startElement('Styles');
            $xmlWriter->writeCData($info['styles']);
            $xmlWriter->endElement(); // Styles
            $xmlWriter->startElement('Subject');
            $xmlWriter->writeCData($info['subject']);
            $xmlWriter->endElement(); // Subject
            $xmlWriter->startElement('Text');
            $xmlWriter->writeCData($info['text']);
            $xmlWriter->endElement(); // Text
            $xmlWriter->endElement(); // email

            $xmlWriter->endElement(); // emails

            file_put_contents($this->xmlDir . "/emailtemplates-file-$i.xml", $xmlWriter->flush());
            $i++;
        }
    }

    /**
     * @param string $file
     * @return string[]
     */
    private function parseTemplate($file)
    {
        $content = file_get_contents($file);

        preg_match('#<!--@subject(.*?)@-->#s', $content, $matches);
        $info['subject'] = trim(isset($matches[1]) ? $matches[1] : '');

        preg_match('#<!--@vars(.*?)@-->#s', $content, $matches);
        $info['vars'] = trim(isset($matches[1]) ? $matches[1] : '');

        preg_match('#<!--@styles(.*?)@-->#s', $content, $matches);
        $info['styles'] = trim(isset($matches[1]) ? $matches[1] : '');

        $info['text'] = Mage::helper('eurotext_translationmanager/string')
            ->replaceMagentoBlockDirectives(trim(preg_replace('#<!--@.*?@-->#s', '', $content)));

        return $info;
    }

    private function collectEmailTemplatesFromConfig()
    {
        if ($this->emailPaths !== null) {
            return;
        }

        $this->emailPaths = [];

        $allEmails = Mage::getConfig()->getNode('global/template/email');
        foreach ($allEmails->children() as $emailNode) {
            $this->emailPaths[(string)$emailNode->file] = [
                'label' => (string)$emailNode->label,
                'file'  => (string)$emailNode->file,
                'type'  => (string)$emailNode->type,
            ];
        }
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Data
     */
    private function getHelper()
    {
        return Mage::helper('eurotext_translationmanager');
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return Eurotext_TranslationManager_Model_Resource_Emailtemplate_Filesystem_Collection
     */
    private function createTemplateCollection(Eurotext_TranslationManager_Model_Project $project)
    {
        /** @var Eurotext_TranslationManager_Model_Resource_Emailtemplate_Filesystem_Collection $emailTemplates */
        $renderer = Mage::getModel('eurotext_translationmanager/renderer_filesystem_relativeToLocaleTemplateDirectory');
        $emailTemplates = Mage::getResourceModel('eurotext_translationmanager/emailtemplate_filesystem_collection')
            ->setLanguage($project->getStoreviewSrcLocale());
        $emailTemplates->addRenderer($renderer);

        return $emailTemplates;
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project                                      $project
     * @param Eurotext_TranslationManager_Model_Resource_Emailtemplate_Filesystem_Collection $emailTemplates
     */
    private function filterTemplatesByProject(Eurotext_TranslationManager_Model_Project $project, $emailTemplates)
    {
        $filenames = Mage::getResourceModel('eurotext_translationmanager/project_emailtemplateFile_collection')
            ->addFieldToFilter('project_id', $project->getId())->getColumnValues('filename');

        $emailTemplatesWithBasePath = array_map(
            function ($template) use ($project) {
                return Mage::getBaseDir('locale') . "/{$project->getStoreviewSrcLocale()}/template" . $template;
            },
            $filenames
        );

        $emailTemplates->addFieldToFilter('filename', ['in' => $emailTemplatesWithBasePath]);
    }

    /**
     * @param string $destination
     * @return bool
     */
    private function isTranslationNeeded($destination)
    {
        return !file_exists($destination);
    }
}
