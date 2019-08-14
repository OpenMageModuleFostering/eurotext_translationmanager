<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();

$connection->addColumn(
    $this->getTable('eurotext_translationmanager/project'),
    'customer_comment',
    [
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'comment'  => 'Customer comment',
    ]
);

$this->endSetup();
