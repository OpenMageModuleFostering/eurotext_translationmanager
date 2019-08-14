<?php

class Eurotext_TranslationManager_Model_Import_Project
{
    /**
     * @var mixed[]
     */
    private $skippedEntities = [];

    public function import(
        Eurotext_TranslationManager_Model_Project $project
    ) {
        $tmpdir = $this->getTempDir();

        /** @var Eurotext_TranslationManager_Model_Resource_Project_Import_Collection $importFiles */
        $importFiles = Mage::getResourceModel('eurotext_translationmanager/project_import_collection');
        $importFiles->addFieldToFilter('project_id', $project->getId())->setPageSize(1);

        $fileCollection = Mage::getResourceModel('eurotext_translationmanager/project_import_collection')
            ->addFieldToFilter('project_id', $project->getId())
            ->addFieldToFilter('is_imported', 0)
            ->setPageSize(1);

        /** @var Eurotext_TranslationManager_Model_Project_Import $importFile */
        $importFile = $fileCollection->getFirstItem();

        if ($importFile->isObjectNew()) {
            $project->setProjectStatus(Eurotext_TranslationManager_Model_Project::STATUS_DONE)->save();

            return;
        }

        $filename = $importFile->getFilename();
        $fullFilename = $tmpdir . DIRECTORY_SEPARATOR . $filename;

        if (stripos($filename, 'cms-sites' . DS . 'cms-') === 0) {
            $this->importstepActionImportCMS($fullFilename, $project);
        } elseif (stripos($filename, 'cms-sites' . DS . 'cmsblock-') === 0) {
            $this->importstepActionImportBlocks($fullFilename, $project);
        } elseif (stripos($filename, 'articles' . DS) === 0) {
            $this->importstepActionImportArticle($fullFilename, $project);
        } elseif (stripos($filename, 'categories' . DS) === 0) {
            $this->importstepActionImportCategory($fullFilename, $project);
        } elseif (stripos($filename, 'framework' . DS) === 0) {
            $this->importstepActionImportTranslation($fullFilename);
        } elseif (stripos($filename, 'attributes' . DS) === 0) {
            $this->importstepActionImportAttributes($fullFilename, $project);
        } elseif (stripos($filename, 'emailtemplates' . DS) === 0) {
            $this->importstepActionImportTemplates($fullFilename, $project);
        }

        $importFile->setIsImported(1);
        $importFile->save();
    }

    /**
     * @return string
     */
    private function getTempDir()
    {
        $dir = Mage::getBaseDir('tmp');
        $dir .= DS . 'eurotext';
        if (!@mkdir($dir) && !is_dir($dir)) {
            Mage::helper('eurotext_translationmanager')->log(
                'Temporary directory could not be created in ' . Mage::getBaseDir('var'),
                Zend_Log::CRIT
            );
            throw new Magento_Exception('Eurotext temporary directory could not be created.');
        }
        $htaccessFilename = $dir . DS . '.htaccess';
        if (!is_file($htaccessFilename)) {
            file_put_contents($htaccessFilename, "# Eurotext Temp folder\r\nOrder Deny,Allow\r\nDeny From All");
        }

        return $dir;
    }

    /**
     * @param string                                    $fullFilename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportCMS($fullFilename, Eurotext_TranslationManager_Model_Project $project)
    {
        $importer = Mage::getModel('eurotext_translationmanager/import_project_cmsPages');
        $importer->import($fullFilename, $project);
        $this->addSkippedEntities('cms pages', $importer->getSkippedEntities());
    }

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportBlocks($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $importer = Mage::getModel('eurotext_translationmanager/import_project_cmsBlocks');
        $importer->import($filename, $project);
        $this->addSkippedEntities('cms blocks', $importer->getSkippedEntities());
    }

    /**
     * @param string                                    $fullFilename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportArticle($fullFilename, Eurotext_TranslationManager_Model_Project $project)
    {

        $factory = Mage::getModel('eurotext_translationmanager/factory');
        $importer = $factory->getImportProduct();
        $importer->import($fullFilename, $project);
        $this->addSkippedEntities('products', $importer->getSkippedEntities());
    }

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportCategory($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $importer = Mage::getModel('eurotext_translationmanager/import_project_categories');
        $importer->import($filename, $project);
        $this->addSkippedEntities('categories', $importer->getSkippedEntities());
    }

    /**
     * @param string $fullFilename
     */
    private function importstepActionImportTranslation($fullFilename)
    {
        Mage::getModel('eurotext_translationmanager/import_project_localeCsvFiles')->import($fullFilename);
    }

    /**
     * @param string                                    $fullFilename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportAttributes(
        $fullFilename,
        Eurotext_TranslationManager_Model_Project $project
    ) {
        $importer = Mage::getModel('eurotext_translationmanager/import_project_attributes');
        $importer->import($fullFilename, $project);
        $this->addSkippedEntities('attributes', $importer->getSkippedEntities());
    }

    /**
     * @param string                                    $fullFilename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importstepActionImportTemplates($fullFilename, Eurotext_TranslationManager_Model_Project $project)
    {
        $importer = Mage::getModel('eurotext_translationmanager/import_project_emailTemplates');
        $importer->import($fullFilename, $project);
        $this->addSkippedEntities('email templates', $importer->getSkippedEntities());
    }

    /**
     * @return mixed[]
     */
    public function getSkippedEntities()
    {
        return $this->skippedEntities;
    }

    /**
     * @param string         $type
     * @param int[]|string[] $entities
     */
    private function addSkippedEntities($type, $entities)
    {
        if (isset($this->skippedEntities[$type])) {
            $this->skippedEntities[$type] = array_merge($this->skippedEntities[$type], $entities);
        } else {
            $this->skippedEntities[$type] = $entities;
        }
    }
}
