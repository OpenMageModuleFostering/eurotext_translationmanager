<?php

class Eurotext_TranslationManager_Model_Renderer_Filesystem_RelativeToAppDirectory implements Eurotext_TranslationManager_Model_Renderer_Filesystem
{
    public function render($filename)
    {
        $appDir = Mage::getBaseDir('app');

        return str_replace($appDir, '', $filename);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'relativeToApp';
    }
}
