<?php

/** @var Mage_Core_Model_Resource_Setup $this */

$this->startSetup();

$con = $this->getConnection();

$import = $con->newTable($this->getTable('eurotext_translationmanager/project_import'))
    ->addColumn(
        'import_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true,],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [], 'Filename of import file')
    ->addColumn(
        'storeview_dst',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Store Id'
    )
    ->addColumn(
        'num',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        ['nullable' => false, 'default' => -1],
        'File number of import'
    )
    ->addColumn(
        'is_imported',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        [
            'nullable' => false,
            'default'  => 0,
        ]
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );
$con->createTable($import);

$project = $con->newTable($this->getTable('eurotext_translationmanager/project'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_BIGINT,
        20,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true,],
        'Project ID'
    )
    ->addColumn('project_name', Varien_Db_Ddl_Table::TYPE_TEXT, null, ['nullable' => false], 'Project name')
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE],
        'Last Update'
    )
    ->addColumn(
        'storeview_src',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        ['nullable' => false, 'default' => -1],
        'Source Storeview'
    )
    ->addColumn(
        'storeview_dst',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        ['nullable' => false, 'default' => -1],
        'Destination Storeview'
    )
    ->addColumn(
        'project_status',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        11,
        ['nullable' => false, 'default' => 0],
        'Status'
    )
    ->addColumn(
        'langfilesmode',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        'Export all languagefiles'
    )
    ->addColumn(
        'export_seo',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 1],
        'Export SEO informations'
    )
    ->addColumn(
        'productmode',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        '0=Selected Products, 1=All Products without translation'
    )
    ->addColumn(
        'categorymode',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        '0=Selected Categories, 1=All Categories without translation'
    )
    ->addColumn(
        'cmsmode',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        '0=Selected CMS-Pages, 1 = All missing CMS-Pages'
    )
    ->addColumn(
        'zip_filename',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        ['nullable' => false, 'default' => ''],
        'Filename exported zipfile'
    )
    ->addColumn(
        'export_attributes',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        'Export EAV attributes'
    )
    ->addColumn(
        'templatemode',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        '0=selected email templates, 1=All missing email templates'
    )
    ->addColumn(
        'export_urlkeys',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        6,
        ['nullable' => false, 'default' => 0],
        'Export URL keys'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        [],
        'Created at'
    );


$con->createTable($project);

$projectCategories = $con->newTable($this->getTable("eurotext_translationmanager/project_categories"))
    ->addColumn(
        'project_category_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn(
        'category_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Category ID'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );

$con->createTable($projectCategories);

$projectCmsBlocks = $con->newTable($this->getTable('eurotext_translationmanager/project_cmsblocks'))
    ->addColumn(
        'project_cmsblock_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn(
        'block_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Block ID'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );

$con->createTable($projectCmsBlocks);

$projectCmsPages = $con->newTable($this->getTable('eurotext_translationmanager/project_cmspages'))
    ->addColumn(
        'project_cmspage_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn(
        'page_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Page ID'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );

$con->createTable($projectCmsPages);

$projectEmailtemplates = $con->newTable($this->getTable('eurotext_translationmanager/project_emailtemplates'))
    ->addColumn(
        'project_emailtemplates_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_TEXT, null, [], 'filename of csv')
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );

$con->createTable($projectEmailtemplates);

$projectProducts = $con->newTable($this->getTable('eurotext_translationmanager/project_products'))
    ->addColumn(
        'project_product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn(
        'product_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Product ID'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );


$con->createTable($projectProducts);

$projectCsv = $con->newTable($this->getTable('eurotext_translationmanager/project_csv'))
    ->addColumn(
        'project_csv_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        ['primary' => true, 'unsigned' => true, 'nullable' => false, 'auto_increment' => true],
        'Primary key'
    )
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, ['nullable' => false], 'FK on project')
    ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [], 'Filename to export')
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT],
        'Created at'
    );

$con->createTable($projectCsv);

$tableName = $this->getTable('eurotext_translationmanager/project_import');
$this->getConnection()->addIndex(
    $tableName,
    $this->getConnection()->getIndexName(
        $tableName,
        ['project_id', 'filename'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    ['project_id', 'filename'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$tableName = $this->getTable('eurotext_translationmanager/project_categories');
$this->getConnection()->addIndex(
    $tableName,
    $this->getConnection()->getIndexName(
        $tableName,
        ['project_id', 'category_id'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    ['project_id', 'category_id'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$tablesToPutFkOn = [
    'eurotext_translationmanager/project_csv',
    'eurotext_translationmanager/project_emailtemplates',
    'eurotext_translationmanager/project_import',
    'eurotext_translationmanager/project_categories',
    'eurotext_translationmanager/project_cmsblocks',
    'eurotext_translationmanager/project_cmspages',
    'eurotext_translationmanager/project_products',
];

$projectTableName = $this->getTable('eurotext_translationmanager/project');
$this->getConnection()->changeColumn(
    $projectTableName,
    'id',
    'id',
    ['type' => Varien_Db_Ddl_Table::TYPE_BIGINT, 'auto_increment' => true, 'unsigned' => false]
);

foreach ($tablesToPutFkOn as $table) {
    $tableName = $this->getTable($table);
    $this->getConnection()->addForeignKey(
        $this->getConnection()->getForeignKeyName(
            $tableName,
            'project_id',
            $projectTableName,
            'id'
        ),
        $tableName,
        'project_id',
        $projectTableName,
        'id'
    );
}

$tables = [
    'eurotext_translationmanager/project_cmsblocks' => ['project_cmsblock_id', 'block_id'],
    'eurotext_translationmanager/project_cmspages'  => ['project_cmspage_id', 'page_id'],
    'eurotext_translationmanager/project_products'  => ['project_product_id', 'product_id'],
];

foreach ($tables as $table => $columnNames) {
    $tableName = $this->getTable($table);
    $this->getConnection()->addIndex(
        $tableName,
        $this->getConnection()->getIndexName(
            $tableName,
            ['project_id', $columnNames[1]],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        ['project_id', $columnNames[1]],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

$foreignKeys = [
    [
        'eurotext_translationmanager/project_cmsblocks',
        'block_id',
        'cms/block',
        'block_id',
    ],
    [
        'eurotext_translationmanager/project_cmspages',
        'page_id',
        'cms/page',
        'page_id',
    ],
    [
        'eurotext_translationmanager/project_products',
        'product_id',
        'catalog/product',
        'entity_id',
    ],
    [
        'eurotext_translationmanager/project_categories',
        'category_id',
        'catalog/category',
        'entity_id',
    ],
];

foreach ($foreignKeys as $keyData) {
    $this->getConnection()->changeColumn(
        $this->getTable($keyData[0]),
        $keyData[1],
        $keyData[1],
        [
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Entity ID',
        ]
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

$this->getConnection()->dropTable($this->getTable('eurotext_translationmanager/project_csv_data'));
