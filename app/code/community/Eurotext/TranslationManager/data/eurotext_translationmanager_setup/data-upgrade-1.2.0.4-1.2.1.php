<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $installer */
$installer = $this;

$connection = $installer->getConnection();

$xmlPrefix = 'eurotext/config/';

$eurotextConfigTable = $connection->getTableName('eurotext_config');
$select = $connection->select()->from($eurotextConfigTable);
$rows = $connection->fetchAssoc($select);
foreach ($rows as $key => $data) {
    Mage::getConfig()->saveConfig($xmlPrefix . $key, $data['config_value']);
}

$connection->dropTable($eurotextConfigTable);
