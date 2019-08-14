<?php

class Eurotext_TranslationManager_Model_Factory
{
    private $mediaGalleryUpdater;

    /**
     * @return Eurotext_TranslationManager_Model_Updater_MediaGalleryValue
     */
    public function getMediaGalleryValueUpdate()
    {
        if (null === $this->mediaGalleryUpdater) {
            $this->mediaGalleryUpdater = Mage::getModel('eurotext_translationmanager/updater_mediaGalleryValue');
        }

        return $this->mediaGalleryUpdater;
    }

    public function getProductLoader()
    {
        return Mage::getModel('eurotext_translationmanager/productLoader');
    }

    /**
     * @return Eurotext_TranslationManager_Model_Import_Project_Product
     */
    public function getImportProduct()
    {
        return new Eurotext_TranslationManager_Model_Import_Project_Product(
            Mage::helper('eurotext_translationmanager'),
            Mage::helper('eurotext_translationmanager/config'),
            $this
        );
    }
}
