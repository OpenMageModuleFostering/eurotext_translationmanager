<?php

class Eurotext_TranslationManager_Model_Export_Project_EmailDatabaseTemplates
{
    const PREG_XML_PATH = '#/config/sections/([_a-zA-Z]*)/groups/([_a-zA-Z]*)/fields/([_a-zA-Z]*)/source_model#';
    const PAGE_SIZE = 20;

    /**
     * @var Eurotext_TranslationManager_Model_Project
     */
    private $project;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $xmlDir;

    /**
     * @var string[]
     */
    private $emailConfigPaths;

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    public function process(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        $this->project = $project;
        $this->offset = $offset;
        $this->project->addAllRelationalData();

        /** @var $templateCollection Mage_Core_Model_Resource_Email_Template_Collection */
        $templateCollection = Mage::getResourceModel('core/email_template_collection');
        $templateCollection->setPageSize(self::PAGE_SIZE);
        $templateCollection->setCurPage($this->offset);

        if (!$this->project->isExportingAllEmailTemplates()) {
            $templateCollection->addFieldToFilter(
                'template_id',
                ['in' => $this->project->getTransactionEmailDatabase()]
            );
        }

        if (!$templateCollection->getSize()) {
            return [
                'status_msg' => $this->getHelper()->__('No Email Templates from the database exported'),
                'offset'     => 1,
                'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_EXPORT_ATTRIBUTES,
            ];
        }

        if ($templateCollection->getLastPageNumber() < $this->offset) {
            return [
                'status_msg' => $this->getHelper()->__('All Email-Database-Templates exported'),
                'offset'     => 1,
                'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_EXPORT_ATTRIBUTES,
            ];
        }

        $this->xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
            ->getXmlSubdirectoryAndMakeSureItExists($project, 'emailtemplates');

        $this->writeXml($templateCollection);

        return [
            'status_msg' => $this->getHelper()->__('%s Email-Database-Templates exported', $templateCollection->getSize()),
            'offset'     => $this->offset + 1,
            'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_EXPORT_ATTRIBUTES,
        ];
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Data
     */
    private function getHelper()
    {
        return Mage::helper('eurotext_translationmanager');
    }

    /**
     * @param Mage_Core_Model_Resource_Email_Template_Collection $templateCollection
     */
    private function writeXml($templateCollection)
    {
        $i = 1;
        foreach ($templateCollection as $t) {
            $xmlWriter = new XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->startElement('emails');

            $xmlWriter->startElement('email');
            $xmlWriter->writeElement('Id', $t->getId());
            $xmlWriter->writeElement('StoreviewSrc', $this->project->getStoreviewSrc());
            $xmlWriter->writeElement('StoreviewDst', $this->project->getStoreviewDst());
            foreach ($this->getPathsForTemplate($t->getId()) as $path) {
                $xmlWriter->writeElement('Path', $path);
            }
            $xmlWriter->writeElement('Database', 'config_path');
            $type = $t->getType() == \Mage_Core_Model_Template::TYPE_HTML ? 'html' : 'plaintext';
            $xmlWriter->writeElement('Type', $type);
            $xmlWriter->startElement('Styles');
            $xmlWriter->writeCData($t->getTemplateStyles());
            $xmlWriter->endElement(); // Styles
            $xmlWriter->startElement('Subject');
            $xmlWriter->writeCData($t->getTemplateSubject());
            $xmlWriter->endElement(); // Subject
            $xmlWriter->startElement('Text');
            $xmlWriter->writeCData(
                Mage::helper('eurotext_translationmanager/string')
                    ->replaceMagentoBlockDirectives($t->getTemplateText())
            );
            $xmlWriter->endElement(); // Text
            $xmlWriter->endElement(); // email

            $xmlWriter->endElement(); // emails

            file_put_contents(
                sprintf($this->xmlDir . '/emailtemplates-db-%s.xml', (($this->offset - 1) * self::PAGE_SIZE) + $i++),
                $xmlWriter->flush()
            );
        }
    }

    private function getPathsForTemplate($id)
    {
        $this->collectEmailConfigurationPaths();

        $setPaths = [];
        foreach ($this->emailConfigPaths as $path) {
            if (Mage::getStoreConfig($path, $this->project->getStoreviewSrc()) == $id) {
                $setPaths[] = $path;
            }
        }

        return $setPaths;
    }

    private function collectEmailConfigurationPaths()
    {
        if ($this->emailConfigPaths === null) {
            $this->emailConfigPaths = [];

            $config = Mage::getConfig()->loadModulesConfiguration('system.xml')->applyExtends();
            $dom = new DOMDocument();
            $dom->loadXML($config->getXmlString());

            $xpath = new DOMXPath($dom);
            $allEmails = $xpath->evaluate('//source_model[text()=\'adminhtml/system_config_source_email_template\']');
            foreach ($allEmails as $sourceModelNode) {
                /** @var DOMNode $sourceModelNode $ */
                $emailPath = $sourceModelNode->getNodePath();
                if (preg_match(self::PREG_XML_PATH, $emailPath, $matches)) {
                    unset($matches[0]);
                    $this->emailConfigPaths[] = implode('/', $matches);
                }
            }
        }
    }
}
