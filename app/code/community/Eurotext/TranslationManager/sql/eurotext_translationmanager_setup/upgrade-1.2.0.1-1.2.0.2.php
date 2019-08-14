<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $this */

$this->startSetup();

$tables = [
    'eurotext_translationmanager/project_cmsblocks' => ['project_cmsblock_id', 'block_id'],
    'eurotext_translationmanager/project_cmspages'  => ['project_cmspage_id', 'page_id'],
    'eurotext_translationmanager/project_products'  => ['project_product_id', 'product_id'],
];

foreach ($tables as $table => $columnNames) {
    $tableName = $this->getTable($table);
    $this->getConnection()->dropIndex($tableName, 'PRIMARY');
    $this->getConnection()->addIndex(
        $tableName,
        $this->getConnection()->getIndexName(
            $tableName,
            ['project_id', $columnNames[1]],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        ['project_id', $columnNames[1]],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );

    $this->getConnection()->addColumn(
        $tableName,
        $columnNames[0],
        [
            'primary'  => true,
            'comment'  => 'Primary key',
            'unsigned' => true,
            'identity' => true,
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        ]
    );
}

$this->endSetup();
