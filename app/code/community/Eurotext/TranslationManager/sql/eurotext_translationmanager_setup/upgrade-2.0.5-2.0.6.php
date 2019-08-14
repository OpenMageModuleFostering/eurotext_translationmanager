<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();
$tables = [
    'eurotext_translationmanager/project_products',
    'eurotext_translationmanager/project_categories',
    'eurotext_translationmanager/project_cmsblocks',
    'eurotext_translationmanager/project_cmspages',
    'eurotext_translationmanager/project_csv',
    'eurotext_translationmanager/project_emailtemplates',
];

foreach ($tables as $table) {
    $table = $this->getTable($table);
    $this->getConnection()->dropColumn($table, 'time_added');
    $this->getConnection()->addColumn(
        $table,
        'created_at',
        [
            'type'    => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
            'comment' => 'Creation Time',
        ]
    );
}
$this->endSetup();
