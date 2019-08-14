<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();
$tables = [
    'eurotext_translationmanager/project_csv',
    'eurotext_translationmanager/project_emailtemplates',
];

foreach ($tables as $table) {
    $tableName = $this->getTable($table);
    $this->getConnection()->dropColumn($tableName, 'file_hash');
    $this->getConnection()->dropColumn($tableName, 'line_hash');
    $this->getConnection()->dropColumn($tableName, 'translate_flag');
    $this->getConnection()->dropColumn($tableName, 'deleteflag');
    $this->getConnection()->dropColumn($tableName, 'locale_dst');
}
$this->endSetup();
