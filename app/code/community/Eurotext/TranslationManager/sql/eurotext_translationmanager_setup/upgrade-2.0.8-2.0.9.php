<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$con = $this->getConnection();
$con->renameTable(
    $this->getTable('eurotext_translationmanager/csv_data'),
    $this->getTable('eurotext_translationmanager/project_csv_data')
);

$dropColumns = [
    'create_id', 'deleted', 'filter_status', 'filter_stock', 'filter_product_type'
];

foreach ($dropColumns as $c) {
    $con->dropColumn($this->getTable('eurotext_translationmanager/project'), $c);
}

$this->endSetup();
