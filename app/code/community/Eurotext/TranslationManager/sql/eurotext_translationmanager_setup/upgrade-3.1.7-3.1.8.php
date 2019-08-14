<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();

$emailTemplateDatabase = $connection->newTable(
    $this->getTable('eurotext_translationmanager/project_emailtemplate_database')
)
    ->addColumn(
        'emailtemplate_database_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn(
        'emailtemplate_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Emailtemplate ID'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    )->addForeignKey(
        $this->getFkName(
            $this->getTable('eurotext_translationmanager/project_emailtemplate_database'),
            'project_id',
            $this->getTable('eurotext_translationmanager/project'),
            'id'
        ),
        'project_id',
        $this->getTable('eurotext_translationmanager/project'),
        'id'
    )->addForeignKey(
        $this->getFkName(
            $this->getTable('eurotext_translationmanager/project_emailtemplate_database'),
            'emailtemplate_id',
            $this->getTable('core/email_template'),
            'template_id'
        ),
        'emailtemplate_id',
        $this->getTable('core/email_template'),
        'template_id'
    );

$connection->createTable($emailTemplateDatabase);

$this->endSetup();
