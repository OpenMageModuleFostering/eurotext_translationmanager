<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();
$connection->dropColumn($this->getTable('eurotext_translationmanager/project'), 'productmode');
$connection->dropColumn($this->getTable('eurotext_translationmanager/project'), 'categorymode');
$connection->dropColumn($this->getTable('eurotext_translationmanager/project'), 'cmsmode');
$connection->dropColumn($this->getTable('eurotext_translationmanager/project'), 'langfilesmode');
$connection->dropColumn($this->getTable('eurotext_translationmanager/project'), 'templatemode');

$this->endSetup();
