<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$con = $this->getConnection();

$con->dropTable($this->getTable('eurotext_translationmanager/languages'));
$con->renameTable(
    $this->getTable('eurotext_translationmanager/import'),
    $this->getTable('eurotext_translationmanager/project_import')
);
$this->endSetup();
