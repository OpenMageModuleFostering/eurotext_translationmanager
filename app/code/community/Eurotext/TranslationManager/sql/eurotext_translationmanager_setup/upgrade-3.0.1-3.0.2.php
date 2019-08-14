<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();
$connection->dropColumn($this->getTable('eurotext_translationmanager/project_import'), 'num');

$this->endSetup();
