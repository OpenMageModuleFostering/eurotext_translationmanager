<?php

class Eurotext_TranslationManager_Model_Import_Validator
{
    /**
     * @param int    $projectId
     * @param string $controlFile
     */
    public function validate($projectId, $controlFile)
    {
        $this->validateControlFileExists($controlFile);

        $projectIdFromXml = $this->getProjectIdFromXml($controlFile);
        $this->compareProjectIdWithXml($projectId, $projectIdFromXml);
    }

    /**
     * @param string $controlFile
     */
    private function validateControlFileExists($controlFile)
    {
        if (!file_exists($controlFile)) {
            Mage::throwException('ZIP did not countain a control.xml file');
        }
    }

    /**
     * @param int $projectIdFromXml
     */
    private function validateProjectIdFromXml($projectIdFromXml)
    {
        if (!ctype_digit($projectIdFromXml)) {
            Mage::throwException(
                Mage::helper('eurotext_translationmanager')->__(
                    'Could not read project-id from Description-Field in control.xml'
                )
            );
        }
    }

    /**
     * @param string $projectId
     * @param string $projectIdFromXml
     */
    private function compareProjectIdWithXml($projectId, $projectIdFromXml)
    {
        if ($projectId != $projectIdFromXml) {
            Mage::throwException(
                Mage::helper('eurotext_translationmanager')->__(
                    'Project id from XML "%s" and from importing project "%s" do not match, please import zip file to correct project.',
                    $projectIdFromXml,
                    $projectId
                )
            );
        }
    }

    /**
     * @param string $controlFile
     * @return int
     */
    private function getProjectIdFromXml($controlFile)
    {
        $doc = new DOMDocument();
        $doc->load($controlFile);
        $nodes = $doc->getElementsByTagName('Description');
        $projectIdFromXml = null;
        foreach ($nodes as $node) {
            $description = $node->textContent;
            $pattern = Eurotext_TranslationManager_Model_Project::INFORMATION_PATTERN;
            $regexPattern = str_replace('%s', '(.*)', $pattern);
            preg_match('#' . $regexPattern . '#', $description, $matches);
            $projectIdFromXml = isset($matches[1]) ? $matches[1] : null;
        }

        $this->validateProjectIdFromXml($projectIdFromXml);

        return $projectIdFromXml;
    }
}
