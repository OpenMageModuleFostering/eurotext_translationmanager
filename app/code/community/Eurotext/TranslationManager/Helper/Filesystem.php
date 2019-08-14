<?php

class Eurotext_TranslationManager_Helper_Filesystem
{
    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $subdir
     * @return string
     */
    public function getXmlSubdirectoryAndMakeSureItExists(Eurotext_TranslationManager_Model_Project $project, $subdir)
    {
        $xmlDir = $this->getExportXMLPath($project);
        $xmlDir .= DS . $subdir;
        if (!@mkdir($xmlDir, 0777, true) && !is_dir($xmlDir)) {
            throw new Exception(sprintf('Directory %s could not be created.', $xmlDir));
        }

        return $xmlDir;
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return string
     */
    public function getExportXMLPath(Eurotext_TranslationManager_Model_Project $project)
    {
        $dir = Mage::getBaseDir('var') . '/eurotext';
        $this->tryCreateEurotextVarDirectory($dir);
        $this->createHtaccessFile($dir);
        $dir = $this->createProjectExportDirectory($project, $dir);

        return $dir;
    }

    /**
     * @param string $dir
     */
    private function createHtaccessFile($dir)
    {
        $htaccessFilename = $dir . DS . '.htaccess';
        if (!is_file($htaccessFilename)) {
            file_put_contents($htaccessFilename, "# Eurotext Export folder\nOrder Deny,Allow\nDeny From All");
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $dir
     * @return string
     */
    private function createProjectExportDirectory(Eurotext_TranslationManager_Model_Project $project, $dir)
    {
        $dir .= DS . "projects/{$project->getId()}/export";
        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new Exception(sprintf('Directory %s could not be created.', $dir));
        }

        return $dir;
    }

    /**
     * @param string $dir
     */
    private function tryCreateEurotextVarDirectory($dir)
    {
        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            Mage::helper('eurotext_translationmanager')
                ->log('Working directory could not be created in ' . Mage::getBaseDir('var'), Zend_Log::CRIT);
            throw new Magento_Exception('Eurotext working directory could not be created.');
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFilenameSafeString($filename)
    {
        $filename = strtolower(trim($filename));
        $filename = preg_replace('#[^a-z0-9-_.]#', '-', $filename);

        do {
            $filename = str_replace(['..', '--'], ['.', '-'], $filename, $replaced);
        } while ($replaced);

        return $filename;
    }

    /**
     * @param string $dir
     */
    public function deleteDirectoryRecursively($dir)
    {
        mageDelTree($dir);
    }
}
