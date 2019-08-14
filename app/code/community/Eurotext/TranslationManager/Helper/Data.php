<?php

class Eurotext_TranslationManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    private $helpUrl = "http://www.eurotext.de";

    /**
     * @var string
     */
    private $liveRegistrationEmail = "magento@eurotext.de";

    /**
     * @var string
     */
    private $liveRegistrationEmailName = "Eurotext Magento (Live)";

    /**
     * @var string
     */
    private $debugRegistrationEmail = "debug@eurotext.de";

    /**
     * @var string
     */
    private $debugRegistrationEmailName = "Eurotext Magento (Debug)";

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    public function __construct()
    {
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
    }

    /**
     * Prüft, ob der URL-Key von Produkt/Kategorie auf "global" statt "storeview" steht
     * Liefert true, zurück wenn mindestens ein Scope NICHT(!) auf "storeview" steht
     *
     * @return bool
     */
    public function urlKeyScopeIsGlobal()
    {
        $categoryAttributes = Mage::getResourceModel('catalog/category_attribute_collection')
            ->addFieldToFilter('attribute_code', ['in' => ['url_key', 'url_path']]);

        foreach ($categoryAttributes as $attribute) {
            if ($attribute->getIsGlobal() != Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL) {
                return false;
            }
        }

        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', ['in' => ['url_key', 'url_path']]);

        foreach ($productAttributes as $attribute) {
            if ($attribute->getIsGlobal() != Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getLocaleInfoByMagentoLocale($locale)
    {
        $languages = Mage::getModel('eurotext_translationmanager/eurotextLanguages');

        return $languages->getInfoByMageLocale($locale);
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getLocaleInfoByEurotextLocale($locale)
    {
        $languages = Mage::getModel('eurotext_translationmanager/eurotextLanguages');

        return $languages->getInfoByEurotext($locale);
    }

    public function getRegistrationRecipient()
    {
        $recipient = [];
        if ($this->configHelper->isDebugMode()) {
            $recipient["email"] = $this->debugRegistrationEmail;
            $recipient["name"] = $this->debugRegistrationEmailName;

            return $recipient;
        }

        $recipient["email"] = $this->liveRegistrationEmail;
        $recipient["name"] = $this->liveRegistrationEmailName;

        return $recipient;
    }

    public function getHelpUrl()
    {
        return $this->helpUrl;
    }

    /**
     * @param string $message
     * @param int    $level
     */
    public function log($message, $level = Zend_Log::DEBUG)
    {
        if ($this->configHelper->isDebugMode()) {
            Mage::log($message, $level, 'eurotext.log', true);
        } elseif ($level < Zend_Log::ERR) {
            Mage::log($message, $level, 'eurotext_fatal.log', true);
        }
    }
}
