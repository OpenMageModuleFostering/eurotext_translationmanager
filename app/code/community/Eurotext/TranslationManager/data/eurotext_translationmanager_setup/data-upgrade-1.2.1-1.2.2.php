<?php

/** @var Eurotext_TranslationManager_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$hnumber = Mage::getStoreConfig('eurotext/config/register_hnumber', 0);
if ($hnumber) {
    $street = trim(Mage::getStoreConfig('eurotext/config/register_street', 0));
    $installer->setConfigData('eurotext/config/register_street', trim($street . ' ' . $hnumber));
    $installer->deleteConfigData('eurotext/config/register_hnumber');
}

$locale = Mage::app()->getLocale()->getLocaleCode();
Mage::app()->getLocale()->setLocaleCode('de_DE');
$countries                    = Mage::getResourceModel('directory/country_collection')
                                    ->loadData()
                                    ->toOptionArray();
$current_registration_country = Mage::getStoreConfig('eurotext/config/register_country', 0);
foreach ($countries as $country) {
    if ($country['label'] == $current_registration_country) {
        $installer->setConfigData('eurotext/config/register_country', $country['value']);
    }
}
Mage::app()->getLocale()->setLocaleCode($locale);

$installer->endSetup();
