<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$this->getConnection()->changeColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'last_update',
    'updated_at',
    array(
        'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE,
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'  => 'Last Update',
        'nullable' => false,
    )
);

$this->endSetup();
