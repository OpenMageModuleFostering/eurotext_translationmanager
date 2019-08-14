<?php

class Eurotext_TranslationManager_Model_Updater_MediaGalleryValue
{
    public function update(Mage_Catalog_Model_Product $product, $imgValueId, $imgLabel, $imgPosition, $imgDisabled)
    {
        $attributes               = $product->getTypeInstance(true)->getSetAttributes($product);
        $images                   = $product->getMediaGalleryImages();
        $mediaGalleryBackendModel = $attributes['media_gallery']->getBackend();
        $image                    = $images->getItemById($imgValueId);
        if (!$image) {
            return;
        }
        /** @var Mage_Catalog_Model_Product_Attribute_Backend_Media $mediaGalleryBackendModel */
        $mediaGalleryBackendModel->updateImage(
            $product,
            $image->getFile(),
            ['label' => $imgLabel, 'position' => $imgPosition, 'disabled' => $imgDisabled]
        );

    }
}
