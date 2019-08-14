<?php

class Eurotext_TranslationManager_Model_Renderer_Filesystem_RelativeToLocaleTemplateDirectory implements Eurotext_TranslationManager_Model_Renderer_Filesystem
{
    public function render($filename)
    {
        $appDir = Mage::getBaseDir('locale');
        $path = str_replace($appDir, '', $filename);

        // remove first characters which are like 'en_US/template'
        return substr($path, strlen('/en_US/template'));
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'relativeToLocaleTemplate';
    }
}
