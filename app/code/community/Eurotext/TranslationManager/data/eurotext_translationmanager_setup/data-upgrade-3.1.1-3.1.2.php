<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$mapping = [
    'eurotext/config/eurotext_customerid'    => 'eurotext/user_settings/customerid',
    'eurotext/config/eurotext_username'      => 'eurotext/user_settings/ftp_username',
    'eurotext/config/eurotext_password'      => 'eurotext/user_settings/ftp_password',
    'eurotext/config/et_categories_per_file' => 'eurotext/export_settings/categories_per_file',
    'eurotext/config/et_cmspages_per_file'   => 'eurotext/export_settings/cmspages_per_file',
    'eurotext/config/et_products_per_file'   => 'eurotext/export_settings/products_per_file',
    'eurotext/config/register_sal'           => 'eurotext/config/register_salutation',
    'eurotext/config/register_fname'         => 'eurotext/config/register_firstname',
    'eurotext/config/register_lname'         => 'eurotext/config/register_lastname',
    'eurotext/config/register_telefon'       => 'eurotext/config/register_telephone',
];

foreach ($mapping as $oldPath => $newPath) {
    if (!Mage::getStoreConfig($oldPath)) {
        continue;
    }
    $installer->setConfigData($newPath, Mage::getStoreConfig($oldPath));
    $installer->deleteConfigData($oldPath);
}

$installer->endSetup();
