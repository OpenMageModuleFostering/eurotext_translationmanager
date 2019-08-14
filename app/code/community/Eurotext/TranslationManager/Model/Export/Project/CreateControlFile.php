<?php

class Eurotext_TranslationManager_Model_Export_Project_CreateControlFile
{
    private $languageCombinationNode;
    /**
     * @var string
     */
    private $currentDirectory;

    /**
     * @var DOMDocument
     */
    private $doc;
    /**
     * @var DOMElement
     */
    private $requestNode;

    /**
     * @var DOMElement
     */
    private $generalNode;

    private $allowedDirectories = [
        'framework',
        'articles',
        'attributes',
        'categories',
        'cms-sites',
        'emailtemplates'
    ];

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Eurotext
     */
    private $etHelper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $this->requestNode = $this->doc->createElement("Request");
        $this->doc->appendChild($this->requestNode);
        $this->generalNode = $this->doc->createElement("General");
        $this->requestNode->appendChild($this->generalNode);
        $this->languageCombinationNode = $this->doc->createElement("LanguageCombinations");
        $this->requestNode->appendChild($this->languageCombinationNode);

        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->etHelper = Mage::helper('eurotext_translationmanager/eurotext');
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
    }

    public function create(Eurotext_TranslationManager_Model_Project $project)
    {
        $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')->getExportXMLPath($project);

        $this->addGeneralNode($project);
        $this->addLanguageCombinations($project, $xmlDir);
        $this->writeXml($xmlDir);
    }

    private function addGeneralNode(Eurotext_TranslationManager_Model_Project $project)
    {
        $this->addCustomerContact();
        $this->addCustomerEmail();
        $this->addCustomerId();
        $this->addProjectName($project);
        $this->addDescription($project);
        $this->addDeadline();
        $this->addTarget();
    }

    private function addCustomerContact()
    {
        $nodeCustomerContact = $this->doc->createElement("CustomerContact");
        $nodeCustomerContact->appendChild($this->doc->createTextNode($this->configHelper->getName()));
        $this->generalNode->appendChild($nodeCustomerContact);
    }

    private function addCustomerEmail()
    {
        $nodeCustomerEmail = $this->doc->createElement("CustomerMail");
        $nodeCustomerEmail->appendChild($this->doc->createTextNode($this->configHelper->getEmail()));
        $this->generalNode->appendChild($nodeCustomerEmail);
    }

    private function addCustomerId()
    {
        $customerId = $this->doc->createElement("CustomerID_of_Supplier");
        $customerId->appendChild($this->doc->createTextNode($this->configHelper->getCustomerId()));
        $this->generalNode->appendChild($customerId);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function addProjectName(Eurotext_TranslationManager_Model_Project $project)
    {
        $nodeProjectName = $this->doc->createElement("ProjectName");
        $nodeProjectName->appendChild($this->doc->createTextNode($project->getProjectName()));
        $this->generalNode->appendChild($nodeProjectName);
    }

    private function addDeadline()
    {
        $nodeDeadline = $this->doc->createElement("Deadline");
        $this->generalNode->appendChild($nodeDeadline);
    }

    private function addTarget()
    {
        $nodeTargetProject = $this->doc->createElement("TargetProject");
        $nodeTargetProject->appendChild($this->doc->createTextNode("Quote"));
        $this->generalNode->appendChild($nodeTargetProject);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function addDescription(Eurotext_TranslationManager_Model_Project $project)
    {
        $moduleVersion = $this->configHelper->getModuleVersion();

        $srcName = Mage::getModel('core/store')->load($project->getStoreviewSrc())->getName();
        $dstName = Mage::getModel('core/store')->load($project->getStoreviewDst())->getName();

        $description = '';

        if ($project->getCustomerComment()) {
            $description .= <<<COMMENT
HINWEIS DES KUNDEN:
##########
{$project->getCustomerComment()}
##########

COMMENT;

        }

        $description .= sprintf(
            Eurotext_TranslationManager_Model_Project::INFORMATION_PATTERN,
            $project->getId(),
            $moduleVersion
        );
        $description .= "\n\nSrc-Storeview: '" . $srcName . "' (" . $project->getStoreviewSrcLocale() . ")\n";
        $description .= "Dst-Storeview: '" . $dstName . "' (" . $project->getStoreviewDstLocale() . ")\n";
        $description .= "Export SEO content: " . ($project->isExportingMetaAttributes() ? "Yes" : "No") . "\n";
        $description .= "Export attributes and attribute options? ";
        $description .= ($project->isExportingAttributes() ? "Yes" : "No") . "\n";
        $description .= "Export URL keys? " . ($project->isExportingUrlKeys() ? "Yes" : "No");

        $nodeDescription = $this->doc->createElement("Description");
        $nodeDescription->appendChild($this->doc->createTextNode($description));
        $this->generalNode->appendChild($nodeDescription);
    }

    /**
     * @param string $directory
     */
    private function validateDirectory($directory)
    {
        if (!in_array($directory, $this->allowedDirectories)) {
            throw new Exception(sprintf('Directory found, but not allowed: %s', $directory));
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $xmlDir
     */
    private function addLanguageCombinations(Eurotext_TranslationManager_Model_Project $project, $xmlDir)
    {
        $allFiles = $this->getFilesToZip($xmlDir);

        $nodeLanguageCombination = null;
        foreach ($allFiles as $file) {
            $directory = strtok($file, '/');
            $this->validateDirectory($directory);
            if ($this->isDirectoryChanged($directory)) {
                if ($nodeLanguageCombination) {
                    $this->languageCombinationNode->appendChild($nodeLanguageCombination);
                }
                $nodeLanguageCombination = $this->createLanguageCombination($project);
            }

            $this->addFileTo($nodeLanguageCombination, $file, $xmlDir);
        }
    }

    /**
     * @param string $xmlDir
     */
    private function writeXml($xmlDir)
    {
        $xmlFilename = $xmlDir . DS . "control.xml";
        $this->doc->save($xmlFilename);
    }

    /**
     * @param string $directory
     * @return bool
     */
    private function isDirectoryChanged($directory)
    {
        if ($this->currentDirectory !== $directory) {
            $this->currentDirectory = $directory;

            return true;
        }

        return false;
    }

    /**
     * @param string $locale
     * @return array
     */
    private function getEurotextLocale($locale)
    {
        $eurotextLocale = $this->helper->getLocaleInfoByMagentoLocale($locale);
        if ($eurotextLocale['supported'] == false) {
            throw new Exception('Locale is not supported by Eurotext.');
        }

        return $eurotextLocale;
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return DOMElement
     */
    private function createLanguageCombination(Eurotext_TranslationManager_Model_Project $project)
    {
        $nodeLanguageCombination = $this->doc->createElement("LanguageCombination");
        $source = $this->doc->createAttribute("source");

        $etLocalesrc = $this->getEurotextLocale($project->getStoreviewSrcLocale());
        $source->value = $etLocalesrc['locale_eurotext'];
        $nodeLanguageCombination->appendChild($source);

        $etLocaleDst = $this->getEurotextLocale($project->getStoreviewDstLocale());
        $target = $this->doc->createAttribute("target");
        $target->value = $etLocaleDst['locale_eurotext'];

        $nodeLanguageCombination->appendChild($target);

        $this->languageCombinationNode->appendChild($nodeLanguageCombination);

        return $nodeLanguageCombination;
    }

    /**
     * @param string $xmlDir
     * @return array
     */
    private function getFilesToZip($xmlDir)
    {
        $files = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($xmlDir)));

        $directoryFiltered = array_filter(
            $files,
            function (SplFileInfo $entry) {
                return $entry->isFile();
            }
        );
        $allFilesWithoutXmlDir = array_map(
            function (SplFileInfo $entry) use ($xmlDir) {
                return str_replace($xmlDir . '/', '', $entry->getRealPath());
            },
            $directoryFiltered
        );

        return $allFilesWithoutXmlDir;
    }

    /**
     * @param DOMElement $nodeLanguageCombination
     * @param string     $file
     * @param string     $xmlDir
     */
    private function addFileTo($nodeLanguageCombination, $file, $xmlDir)
    {
        $absoluteFile = $xmlDir . '/' . $file;

        $nodeUploadedFile = $this->doc->createElement("uploadedFile");
        $nodeLanguageCombination->appendChild($nodeUploadedFile);

        $attrfileName = $this->doc->createAttribute("fileName");
        $attrfileName->value = $file;
        $nodeUploadedFile->appendChild($attrfileName);


        $attrsize = $this->doc->createAttribute("size");
        $attrsize->value = filesize($absoluteFile);
        $nodeUploadedFile->appendChild($attrsize);
    }
}
