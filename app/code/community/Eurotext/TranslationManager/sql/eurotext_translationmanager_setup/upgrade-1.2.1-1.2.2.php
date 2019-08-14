<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$this->getConnection()->addColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'created_at',
    [
        'default'  => '0000-00-00 00:00:00',
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'comment'  => 'Created At',
        'nullable' => false,
    ]
);

$this->endSetup();
