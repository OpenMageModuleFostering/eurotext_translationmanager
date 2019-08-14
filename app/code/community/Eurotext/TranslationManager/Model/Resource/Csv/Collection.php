<?php

class Eurotext_TranslationManager_Model_Resource_Csv_Collection
    extends Eurotext_TranslationManager_Model_Resource_Filesystem_Collection
{
    /**
     * @var string
     */
    private $language;

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return $this
     */
    public function filterByProject(Eurotext_TranslationManager_Model_Project $project)
    {
        $project->addAllRelationalData();

        $localeFilesWithBasePath = array_map(
            function ($fileWithoutBasePath) {
                return Mage::getBaseDir('app') . $fileWithoutBasePath;
            },
            $project->getTranslationFiles()
        );

        $this->addFieldToFilter(
            'filename',
            [
                'in' => $localeFilesWithBasePath
            ]
        );

        $this->setTargetPathByLanguageAndStore($project->getStoreviewSrcLocale(), $project->getStoreviewSrc());

        return $this;
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->getLanguage()) {
            Mage::throwException('Please set language before loading!');
        }

        $this->setFilesFilter('#^[a-zA-Z0-9_]+\.csv$#');
        $this->setDisallowedFilesFilter(false);

        return parent::loadData($printQuery, $logQuery);
    }

    /**
     * @return string
     */
    private function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @param string $store
     * @return $this
     */
    public function setTargetPathByLanguageAndStore($language, $store)
    {
        $this->language = $language;

        $localePath = dirname(
            Mage::getModel('core/design_package')
                ->setStore($store)
                ->getLocaleFileName('translate.csv')
        );

        $this->addTargetDir(Mage::getBaseDir('locale') . DS . $language);
        if (is_dir($localePath)) {
            $this->addTargetDir($localePath);
        }

        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        /** @var $thisCollection Eurotext_TranslationManager_Model_Resource_Csv_Collection */
        $thisCollection = parent::setPageSize($size + 1);

        return $thisCollection;
    }
}
