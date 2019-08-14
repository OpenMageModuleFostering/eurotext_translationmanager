<?php

/**
 * @method int getId()
 * @method bool getDeleted()
 * @method int getCreateId()
 * @method string getProjectName()
 * @method int getUpatedAt()
 * @method int getStoreviewSrc()
 * @method int getStoreviewDst()
 * @method int getProjectStatus()
 * @method int getLangfilesmode()
 * @method int getExportSeo()
 * @method int getProductmode()
 * @method int getCategorymode()
 * @method int getCmsmode()
 * @method string getZipFilename()
 * @method int getExportAttributes()
 * @method int getTemplatemode()
 * @method int getExportUrlkeys()
 * @method int getFilterStatus()
 * @method int getFilterStock()
 * @method int getFilterProductTyp()
 * @method int getCreatedAt()
 * @method setId(int $id)
 * @method setDeleted(bool $deleted)
 * @method setCreateId(int $created)
 * @method setUpatedAt(int $timestamp)
 * @method setLangfilesmode(int $mode)
 * @method setExportSeo(int $seo)
 * @method setProductmode(int $mode)
 * @method setCategorymode(int $mode)
 * @method setCmsmode(int $mode)
 * @method setZipFilename(string $name)
 * @method setExportAttributes(int $attributes)
 * @method setTemplatemode(int $mode)
 * @method setExportUrlkeys(int $keys)
 * @method setFilterStatus(int $status)
 * @method setFilterStock(int $stock)
 * @method setFilterProductTyp(int $type)
 * @method setCreatedAt(int $created)
 * @method setStoreviewSrcLocale(string $locale)
 * @method setStoreviewDstLocale(string $locale)
 * @method string getStoreviewSrcLocale()
 * @method string getStoreviewDstLocale()
 * @method string getCustomerComment()
 * @method setCustomerComment(string $comment)
 *
 * @method setCategories(int[] $categories)
 * @method setProducts(int[] $products)
 * @method setBlocks(int[] $blocks)
 * @method setPages(int[] $pages)
 * @method setTranslationFiles(string[] $files)
 * @method setTransactionEmailFiles(string[] $mails)
 * @method setTransactionEmailDatabase(string[] $mails)
 *
 * @method int[] getCategories() - init with addAllRelationalData
 * @method int[] getProducts() - init with addAllRelationalData
 * @method int[] getBlocks() - init with addAllRelationalData
 * @method int[] getPages() - init with addAllRelationalData
 * @method string[] getTranslationFiles() - init with addAllRelationalData
 * @method string[] getTransactionEmailFiles() - init with addAllRelationalData
 * @method string[] getTransactionEmailDatabase() - init with addAllRelationalData
 */
class Eurotext_TranslationManager_Model_Project extends Mage_Core_Model_Abstract
{
    const STATUS_NEW = 0;
    const STATUS_EXPORTED_TO_EUROTEXT = 1;
    const STATUS_IMPORT_TRANSLATIONS_INTO_QUEUE = 2;
    const STATUS_DONE = 3;

    const DEFAULT_SRC_LOCALE = 'en_US';
    const DEFAULT_DST_LOCALE = 'en_US';

    const UNDEFINED_STORE = -1;

    const INFORMATION_PATTERN = 'Magento-Project-Id: %s, Module-Version: %s';

    /**
     * @var string[]
     */
    private $precheckedEmailTemplateFiles = [
        '/email/html/footer.html',
        '/email/html/header.html'
    ];

    /**
     * @var bool
     */
    private $additionalRelationsLoaded = false;

    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project');
        $this->setTransactionEmailFiles($this->precheckedEmailTemplateFiles);
    }

    public function reset()
    {
        switch ($this->getProjectStatus()) {
            case self::STATUS_NEW:
                return;
            case self::STATUS_EXPORTED_TO_EUROTEXT:
                $this->setProjectStatus(self::STATUS_NEW);

                return;
            case self::STATUS_IMPORT_TRANSLATIONS_INTO_QUEUE:
            case self::STATUS_DONE:
                $this->setProjectStatus(self::STATUS_EXPORTED_TO_EUROTEXT);

                return;
            default:
                Mage::throwException('Project is in an undefined status.');
        }
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->getProjectStatus() == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    public function isExportable()
    {
        return $this->isEditable();
    }

    /**
     * @return bool
     */
    public function isImportFileLoaded()
    {
        return $this->getProjectStatus() == self::STATUS_IMPORT_TRANSLATIONS_INTO_QUEUE;
    }

    /**
     * @return bool
     */
    public function isTranslationImportable()
    {
        return $this->getProjectStatus() == self::STATUS_EXPORTED_TO_EUROTEXT;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setProjectName($name)
    {
        if (!is_string($name)) {
            Mage::throwException('Project name must be string.');
        }
        if ($name === '') {
            Mage::throwException('Project name can not be empty');
        }
        $this->setData('project_name', htmlspecialchars(trim(strip_tags($name))));

        return $this;
    }

    /**
     * @param int $source
     * @return $this
     */
    public function setStoreviewSrc($source)
    {
        $source = (int)$source;
        if ($source === 0) {
            Mage::throwException('Please define store view source');
        }

        if ($source === $this->getStoreviewDst()) {
            Mage::throwException('Please select different Storeviews for Source and Destination!');
        }
        $this->setData('storeview_src', $source);

        return $this;
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStoreSrc()
    {
        return Mage::app()->getStore($this->getStoreviewSrc());
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStoreDst()
    {
        return Mage::app()->getStore($this->getStoreviewDst());
    }

    /**
     * @param int $destination
     * @return $this
     */
    public function setStoreviewDst($destination)
    {
        $destination = (int)$destination;
        if ($destination === 0) {
            Mage::throwException('Please define store view destination');
        }

        if ($destination === $this->getStoreviewSrc()) {
            Mage::throwException('Please select different store views for source and destination!');
        }
        $this->setData('storeview_dst', $destination);

        return $this;
    }

    public function addAllRelationalData()
    {
        if (!$this->additionalRelationsLoaded) {
            $this->additionalRelationsLoaded = true;
            $this->getResource()->addAllRelationalData($this);
        }
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->getProjectStatus() == self::STATUS_DONE;
    }

    /**
     * @return Eurotext_TranslationManager_Model_Project
     */
    protected function _beforeSave()
    {
        if ($this->getStoreviewDst() == $this->getStoreviewSrc()) {
            Mage::throwException(
                Mage::helper('eurotext_translationmanager')->__('Please choose two different store views.')
            );
        }

        return $this;
    }

    /**
     * @return Eurotext_TranslationManager_Model_Project
     */
    protected function _afterLoad()
    {
        $this->setStoreviewSrcLocale(self::DEFAULT_SRC_LOCALE);
        $this->setStoreviewDstLocale(self::DEFAULT_SRC_LOCALE);
        if ($this->getStoreviewSrc() != self::UNDEFINED_STORE) {
            $this->setStoreviewSrcLocale(Mage::getStoreConfig('general/locale/code', $this->getStoreviewSrc()));
        }

        if ($this->getStoreviewDst() != self::UNDEFINED_STORE) {
            $this->setStoreviewDstLocale(Mage::getStoreConfig('general/locale/code', $this->getStoreviewDst()));
        }

        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = parent::_afterLoad();

        return $project;
    }

    /**
     * @return bool
     */
    public function isExportingAllCategories()
    {
        return (bool)$this->getCategorymode();
    }

    /**
     * @return bool
     */
    public function isExportingAllProducts()
    {
        return (bool)$this->getProductmode();
    }

    /**
     * @return bool
     */
    public function isExportingAllEmailTemplates()
    {
        return (bool)$this->getTemplatemode();
    }

    /**
     * @return bool
     */
    public function isExportingAllCmsContent()
    {
        return (bool)$this->getCmsmode();
    }

    /**
     * @return bool
     */
    public function isExportingAllLanguageFiles()
    {
        return (bool)$this->getLangfilesmode();
    }

    /**
     * @return bool
     */
    public function isExportingAttributes()
    {
        return (bool)$this->getExportAttributes();
    }

    /**
     * @return bool
     */
    public function isExportingMetaAttributes()
    {
        return (bool)$this->getExportSeo();
    }

    /**
     * @return bool
     */
    public function isExportingUrlKeys()
    {
        return (bool)$this->getExportUrlkeys();
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setProjectStatus($status)
    {
        $status = max($status, self::STATUS_NEW);
        $this->setData('project_status', $status);

        return $this;
    }
}
