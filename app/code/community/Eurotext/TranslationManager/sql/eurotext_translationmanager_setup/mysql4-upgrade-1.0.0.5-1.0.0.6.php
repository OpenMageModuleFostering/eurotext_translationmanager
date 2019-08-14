<?php

$installer = $this;
$installer->startSetup();

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_project_cmsblocks") . "` (";
    $sql .= "  `block_id` bigint(20) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `time_added` bigint(20) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`block_id`,`project_id`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

    $installer->run($sql);
} catch (Exception $e) {

}

try {
    $installer->run("ALTER TABLE `" . $installer->getTable("eurotext_project") . "` ADD `filter_status` INT NOT NULL DEFAULT '1' AFTER `export_urlkeys`");
} catch (Exception $e) {
    // Column already exists
}

try {
    $installer->run("ALTER TABLE `" . $installer->getTable("eurotext_project") . "` ADD `filter_stock` INT NOT NULL DEFAULT '1' AFTER  `filter_status`");
} catch (Exception $e) {
    // Column already exists
}

$installer->endSetup();

