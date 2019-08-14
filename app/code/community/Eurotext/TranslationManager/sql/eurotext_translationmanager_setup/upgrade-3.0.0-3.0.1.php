<?php

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$connection = $this->getConnection();
$connection
    ->addColumn(
        $this->getTable('eurotext_translationmanager/project_import'),
        'storeview_dst',
        [
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'   => null,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'storeview destination'
        ]
    );

$connection->addForeignKey(
    $this->getFkName(
        $this->getTable('eurotext_translationmanager/project_import'),
        'storeview_dst',
        $this->getTable('core/store'),
        'store_id'
    ),
    $this->getTable('eurotext_translationmanager/project_import'),
    'storeview_dst',
    $this->getTable('core/store'),
    'store_id'
);

$this->endSetup();
