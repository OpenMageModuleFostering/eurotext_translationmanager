<?php

class Eurotext_TranslationManager_Helper_Config
{
    /**
     * @var int
     */
    private $exportProductsMinPerFile = 6;

    /**
     * @var int
     */
    private $exportCategoriesMinPerFile = 6;

    const EUROTEXT_CONFIG_REGISTER_EMAILSENT = 'eurotext/config/register_emailsent';
    const EUROTEXT_CONFIG_REGISTER_EMAILSENT_DATE = 'eurotext/config/register_emailsent_date';

    /**
     * @return string
     */
    public function getEmailSentDate()
    {
        return Mage::getStoreConfig(self::EUROTEXT_CONFIG_REGISTER_EMAILSENT_DATE);
    }

    /**
     * @return bool
     */
    public function isEmailSent()
    {
        return Mage::getStoreConfigFlag(self::EUROTEXT_CONFIG_REGISTER_EMAILSENT);
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return Mage::getStoreConfig('eurotext/config/register_salutation');
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return Mage::getStoreConfig('eurotext/config/register_company');
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return Mage::getStoreConfig('eurotext/config/register_firstname');
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return Mage::getStoreConfig('eurotext/config/register_lastname');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return Mage::getStoreConfig('eurotext/config/register_street');
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return Mage::getStoreConfig('eurotext/config/register_zip');
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return Mage::getStoreConfig('eurotext/config/register_city');
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return Mage::getStoreConfig('eurotext/config/register_country');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return Mage::getStoreConfig('eurotext/config/register_email');
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return Mage::getStoreConfig('eurotext/config/register_telephone');
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return (int)Mage::getStoreConfig('eurotext/user_settings/customerid');
    }

    /**
     * @return string
     */
    public function getFtpUsername()
    {
        return Mage::getStoreConfig('eurotext/user_settings/ftp_username');
    }

    /**
     * @return string
     */
    public function getFtpPassword()
    {
        return Mage::getStoreConfig('eurotext/user_settings/ftp_password');
    }

    /**
     * @return int
     */
    public function getCategoriesPerFile()
    {
        $catPerFile = (int)Mage::getStoreConfig('eurotext/export_settings/categories_per_file');

        return $catPerFile > 0 ? max($catPerFile, $this->exportCategoriesMinPerFile) : 20;
    }

    /**
     * @return int
     */
    public function getCmsPagePerFile()
    {
        $cmsEntitiesPerFile = (int)Mage::getStoreConfig('eurotext/export_settings/cmspages_per_file');

        return $cmsEntitiesPerFile > 0 ? $cmsEntitiesPerFile : 20;
    }

    /**
     * @return int
     */
    public function getProductsPerFile()
    {
        $productsPerFile = (int)Mage::getStoreConfig('eurotext/export_settings/products_per_file');

        return $productsPerFile > 0 ? max($productsPerFile, $this->exportProductsMinPerFile) : 20;
    }

    /**
     * @return string
     */
    public function getShopname()
    {
        return Mage::getStoreConfig('eurotext/config/register_shopname');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Mage::getStoreConfig('eurotext/config/register_url');
    }

    public function setEmailSent()
    {
        Mage::getConfig()->saveConfig(self::EUROTEXT_CONFIG_REGISTER_EMAILSENT, 1);
    }

    /**
     * @param string $date
     */
    public function setEmailSentDate($date)
    {
        Mage::getConfig()->saveConfig(self::EUROTEXT_CONFIG_REGISTER_EMAILSENT_DATE, $date);
    }

    /**
     * @return string[]
     */
    public function getRegistrationSettings()
    {
        return [
            'register_shopname'   => $this->getShopname(),
            'register_url'        => $this->getUrl(),
            'register_email'      => $this->getEmail(),
            'register_salutation' => $this->getSalutation(),
            'register_firstname'  => $this->getFirstName(),
            'register_lastname'   => $this->getLastName(),
            'register_company'    => $this->getCompany(),
            'register_street'     => $this->getStreet(),
            'register_zip'        => $this->getZip(),
            'register_city'       => $this->getCity(),
            'register_country'    => $this->getCountry(),
            'register_telephone'  => $this->getTelephone(),
        ];
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return Mage::getStoreConfigFlag('dev/log/ettm_debug');
    }

    /**
     * @return bool
     */
    public function isFtpUploadDisabled()
    {
        /*
         * debug on, ftp on = upload on
         * debug on, ftp off =  upload off
         * debug off, ftp on = upload on
         * debug off, ftp off = upload on
         */
        return $this->isDebugMode() && !Mage::getStoreConfigFlag('dev/log/ettm_ftp');
    }

    /**
     * @return string
     */
    public function getModuleVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Eurotext_TranslationManager->version;
    }

    /**
     * @return string[]
     */
    public function getCustomProductAttributesForExport()
    {
        return array_keys(Mage::getStoreConfig('eurotext/translation_manager/custom_product_attributes') ?: []);
    }

    /**
     * @return string[]
     */
    public function getCustomCategoryAttributesForExport()
    {
        return array_keys(Mage::getStoreConfig('eurotext/translation_manager/custom_category_attributes') ?: []);
    }
}
