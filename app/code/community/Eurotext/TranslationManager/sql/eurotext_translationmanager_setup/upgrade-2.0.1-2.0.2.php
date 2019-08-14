<?php
/** @var $this Eurotext_TranslationManager_Model_Resource_Setup */

$this->startSetup();

$this->getConnection()->changeColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'id',
    'id',
    array(
        'type'           => Varien_Db_Ddl_Table::TYPE_BIGINT,
        'comment'        => 'Project ID',
        'primary'        => true,
        'auto_increment' => true,
        'nullable'       => false,
        'unsigned'       => false,
    )
);

$this->endSetup();
