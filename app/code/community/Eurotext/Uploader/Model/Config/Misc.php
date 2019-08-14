<?php

/**
 * @method Eurotext_Uploader_Model_Config_Misc setMaxSizePlural (string $sizePlural) Set plural info about max upload size
 * @method Eurotext_Uploader_Model_Config_Misc setMaxSizeInBytes (int $sizeInBytes) Set max upload size in bytes
 * @method Eurotext_Uploader_Model_Config_Misc setReplaceBrowseWithRemove (bool $replaceBrowseWithRemove)
 *      Replace browse button with remove
 *
 */
class Eurotext_Uploader_Model_Config_Misc extends Eurotext_Uploader_Model_Config_Abstract
{
    /**
     * Prepare misc params
     */
    protected function _construct()
    {
        $this
            ->setMaxSizeInBytes($this->_getHelper()->getDataMaxSizeInBytes())
            ->setMaxSizePlural($this->_getHelper()->getDataMaxSize());
    }
}
