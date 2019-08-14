<?php

$installer = $this;
$installer->startSetup();

try {
    $sql = "ALTER TABLE  `" . $installer->getTable("eurotext_project") . "` ADD  `filter_product_type` VARCHAR( 150 ) NOT NULL DEFAULT  '' AFTER  `filter_stock`;";
    $installer->run($sql);
} catch (Exception $ex) {
    // Column already exists
}

$installer->endSetup();

