<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$this->getConnection()->renameTable(
    $this->getTable('eurotext_translationmanager/emailtemplates'),
    $this->getTable('eurotext_translationmanager/project_emailtemplates')
);

$this->endSetup();
