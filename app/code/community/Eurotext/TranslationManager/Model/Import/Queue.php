<?php

class Eurotext_TranslationManager_Model_Import_Queue
{
    /**
     * @var int
     */
    private $filesAddedToTheQueue = 0;

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function cleanUpQueueFor($project)
    {
        Mage::getModel('eurotext_translationmanager/project_import')->deleteByProjectId($project->getId());
    }

    /**
     * @param string $controlFile
     */
    public function addControlXmlToQueue(Eurotext_TranslationManager_Model_Project $project, $controlFile)
    {

        $xml = simplexml_load_file($controlFile);
        foreach ($xml->xpath('//uploadedFile') as $fileNode) {
            /** @var $import Eurotext_TranslationManager_Model_Project_Import */
            $import = Mage::getModel('eurotext_translationmanager/project_import');
            $import->setProjectId($project->getId());
            $import->setFilename((string)$fileNode['fileName']);
            $import->setStoreviewDst($project->getStoreviewDst());
            $import->setIsImported(0);
            $import->save();
            $this->filesAddedToTheQueue++;
        }
    }

    /**
     * @return int
     */
    public function getFilesAddedToTheQueue()
    {
        return $this->filesAddedToTheQueue;
    }
}
