<?php

class Eurotext_TranslationManager_Model_Resource_Emailtemplate_Filesystem_Collection
    extends Eurotext_TranslationManager_Model_Resource_Filesystem_Collection
{
    /**
     * @var string
     */
    private $language;

    protected $_allowedFilesMask = '/^[\S0-9\.\-\_]+\.[a-z0-9]+$/i';

    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->getLanguage()) {
            Mage::throwException('Please set language before loading!');
        }
        $this->addTargetDir(Mage::getBaseDir('locale') . '/' . $this->getLanguage() . '/template');

        return parent::loadData($printQuery, $logQuery);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }
}
