<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $this */

$this->startSetup();

$tableName = $this->getTable('eurotext_translationmanager/import');

$this->getConnection()->dropIndex($tableName, 'pk');
$this->getConnection()->addIndex(
    $tableName,
    $this->getConnection()->getIndexName(
        $tableName,
        array('project_id', 'filename'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('project_id', 'filename'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$this->getConnection()->addColumn(
    $tableName,
    'import_id',
    array(
        'primary'  => true,
        'comment'  => 'Primary key',
        'unsigned' => true,
        'identity' => true,
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    )
);

$tableName = $this->getTable('eurotext_translationmanager/project_categories');
$this->getConnection()->dropIndex($tableName, 'PRIMARY');
$this->getConnection()->addIndex(
    $tableName,
    $this->getConnection()->getIndexName(
        $tableName,
        array('project_id', 'category_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('project_id', 'category_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$this->getConnection()->addColumn(
    $tableName,
    'project_category_id',
    array(
        'primary'  => true,
        'comment'  => 'Primary key',
        'unsigned' => true,
        'identity' => true,
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    )
);

$this->endSetup();
