<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();
$connection->changeColumn(
    $this->getTable('eurotext_translationmanager/project_emailtemplates'),
    'project_emailtemplates_id',
    'project_emailtemplate_id',
    [
        'type'           => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'         => 10,
        'primary'        => true,
        'unsigned'       => true,
        'nullable'       => false,
        'auto_increment' => true,
        'comment'        => 'Primary key',
    ]
);

$this->endSetup();
