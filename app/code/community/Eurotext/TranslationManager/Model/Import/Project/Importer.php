<?php

interface Eurotext_TranslationManager_Model_Import_Project_Importer
{
    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return void
     */
    public function import($filename, Eurotext_TranslationManager_Model_Project $project);

    /**
     * @return string[]|int[]
     */
    public function getSkippedEntities();
}
