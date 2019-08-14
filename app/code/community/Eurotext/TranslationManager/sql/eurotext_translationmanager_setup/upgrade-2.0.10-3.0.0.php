<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'created_at',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'length'  => null,
        'comment' => 'Created at'
    ]
);

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'updated_at',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'length'  => null,
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE,
        'comment' => 'Last Update'
    ]
);

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'templatemode',
    [
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length'   => 6,
        'nullable' => false,
        'default'  => 0,
        'comment'  => '0=selected email templates, 1=All missing email templates'
    ]
);

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project_csv'),
    'filename',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 255,
        'comment' => 'Filename to export'
    ]
);

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project_emailtemplates'),
    'filename',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 1500,
        'comment' => 'filename of csv'
    ]
);

$connection->modifyColumn(
    $this->getTable('eurotext_translationmanager/project_emailtemplates'),
    'project_id',
    [
        'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
        'length'   => 20,
        'nullable' => false,
        'comment'  => 'FK on project',
    ]
);

$connection->addColumn(
    $this->getTable('eurotext_translationmanager/project_import'),
    'created_at',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'length'  => null,
        'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        'comment' => 'Created at'
    ]
);

$connection->dropTable($this->getTable('eurotext_translationmanager/project_csv_data'));

$this->endSetup();
