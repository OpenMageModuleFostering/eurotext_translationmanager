<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();

$connection->renameTable(
    $this->getTable('eurotext_translationmanager/project_emailtemplates'),
    $this->getTable('eurotext_translationmanager/project_emailtemplate_files')
);

$this->endSetup();
