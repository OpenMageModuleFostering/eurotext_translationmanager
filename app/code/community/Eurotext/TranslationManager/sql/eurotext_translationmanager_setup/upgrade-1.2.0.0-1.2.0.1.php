<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $this */

$this->startSetup();

$tablesToPutFkOn = [
    'eurotext_translationmanager/csv',
    'eurotext_translationmanager/csv_data',
    'eurotext_translationmanager/emailtemplates',
    'eurotext_translationmanager/import',
    'eurotext_translationmanager/project_categories',
    'eurotext_translationmanager/project_cmsblocks',
    'eurotext_translationmanager/project_cmspages',
    'eurotext_translationmanager/project_products',
];

$projectTableName = $this->getTable('eurotext_translationmanager/project');
$this->getConnection()->changeColumn(
    $projectTableName,
    'id',
    'id',
    ['type' => Varien_Db_Ddl_Table::TYPE_BIGINT, 'auto_increment' => true, 'unsigned' => false]
);
foreach ($tablesToPutFkOn as $table) {
    $tableName = $this->getTable($table);
    $this->getConnection()->addForeignKey(
        $this->getConnection()->getForeignKeyName(
            $tableName,
            'project_id',
            $projectTableName,
            'id'
        ),
        $tableName,
        'project_id',
        $projectTableName,
        'id'
    );
}

$this->endSetup();
