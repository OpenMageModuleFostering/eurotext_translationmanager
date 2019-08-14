<?php

interface Eurotext_TranslationManager_Model_Export_Project_Exporter
{
    public function process(Eurotext_TranslationManager_Model_Project $project, $exportBaseDir);

    public function getExportSubDirectory();
}
