<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $this */

$this->startSetup();

$foreignKeys = array(
    array(
        'eurotext_translationmanager/project_cmsblocks',
        'block_id',
        'cms/block',
        'block_id',
    ),
    array(
        'eurotext_translationmanager/project_cmspages',
        'page_id',
        'cms/page',
        'page_id',
    ),
    array(
        'eurotext_translationmanager/project_products',
        'product_id',
        'catalog/product',
        'entity_id',
    ),
    array(
        'eurotext_translationmanager/project_categories',
        'category_id',
        'catalog/category',
        'entity_id',
    ),
);

foreach ($foreignKeys as $keyData) {
    $this->getConnection()->changeColumn(
        $this->getTable($keyData[0]),
        $keyData[1],
        $keyData[1],
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Entity ID',
        )
    );


    $this->getConnection()->addForeignKey(
        $this->getFkName(
            $this->getTable($keyData[0]),
            $keyData[1],
            $this->getTable($keyData[2]),
            $keyData[3]
        ),
        $this->getTable($keyData[0]),
        $keyData[1],
        $this->getTable($keyData[2]),
        $keyData[3]
    );
}

$this->endSetup();
