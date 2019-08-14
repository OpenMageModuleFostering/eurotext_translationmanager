<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();
$tables = [
    'eurotext_translationmanager/project_csv'            => 'project_csv_id',
    'eurotext_translationmanager/project_emailtemplates' => 'project_emailtemplates_id',
];

foreach ($tables as $table => $newPrimary) {
    $tableName = $this->getTable($table);
    $this->getConnection()->dropIndex($tableName, 'PRIMARY');

    $this->getConnection()->addColumn(
        $tableName,
        $newPrimary,
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
