<?php

class Eurotext_TranslationManager_Model_Export_Project_Category
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var string[]
     */
    private $attributesToExportAlwaysWithMapping = [
        'name'              => 'Title',
        'description'       => 'Longdesc',
        'short_description' => 'Shortdesc',

    ];

    /**
     * @var string[]
     */
    private $seoAttributesWithMapping = [
        'meta_title'       => 'SeoTitle',
        'meta_description' => 'SeoDescription',
        'meta_keywords'    => 'SeoKeywords'
    ];

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    public function __construct()
    {
        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    public function process(Eurotext_TranslationManager_Model_Project $project, $page)
    {
        /** @var Mage_Core_Model_App_Emulation $emulator */
        $emulator = Mage::getModel('core/app_emulation');
        $emulation = $emulator->startEnvironmentEmulation($project->getStoreviewSrc());

        $project->addAllRelationalData();

        $maxItems = $this->configHelper->getCategoriesPerFile();

        $categorySrcCollection = $this->getCategoryCollectionFor($project->getStoreviewSrc(), $page, $maxItems);
        $categoryDstCollection = $this->getCategoryCollectionFor($project->getStoreviewDst(), $page, $maxItems);

        $categorySrcCollection->addIdFilter($project->getCategories());
        $categoryDstCollection->addIdFilter($project->getCategories());


        if ($page > $categorySrcCollection->getLastPageNumber() || !count($project->getCategories())) {
            $emulator->stopEnvironmentEmulation($emulation);

            return [
                'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_COLLECT_CMSPAGES,
                'offset'     => 1,
                'status_msg' => $this->helper->__('Exported categories.'),
            ];
        }

        $cats = $this->doc->createElement('categories');
        $this->doc->appendChild($cats);

        $statusMessage = sprintf(
            Mage::helper('eurotext_translationmanager')->__('Batch %s / %s Categories:'),
            $page,
            $categorySrcCollection->getLastPageNumber()
        );

        foreach ($categorySrcCollection as $catSrc) {
            /** @var Mage_Catalog_Model_Category $catSrc */
            /** @var Mage_Catalog_Model_Category $catDst */
            $catDst = $categoryDstCollection->getItemById($catSrc->getId());

            $statusMessage .= "\n<br />- " . $catSrc->getName();

            $catNode = $this->doc->createElement('category');

            $this->createDefaultAttributeNodes($catNode, $catDst, $catSrc);
            $this->createCustomCategoryAttributeNodes($catNode, $catDst, $catSrc);

            if ($project->isExportingUrlKeys()) {
                $this->createUrlKeyNode($catSrc, $catDst, $catNode);
            }

            if ($project->isExportingMetaAttributes()) {
                $this->createSeoNodes($catSrc, $catDst, $catNode);
            }

            if ($catNode->hasChildNodes()) {
                $this->createIdNode($catNode, $catSrc);
                $this->createUrlNode($catNode, $catSrc);
                $cats->appendChild($catNode);
            }
        }

        if ($cats->hasChildNodes()) {

            $subdir = 'categories';
            $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')->getXmlSubdirectoryAndMakeSureItExists(
                $project,
                $subdir
            );

            $xmlFilename = $xmlDir . DS . 'cat' . $page . '.xml';
            $this->doc->save($xmlFilename);
        }
        $emulator->stopEnvironmentEmulation($emulation);

        return [
            'status_msg' => $statusMessage,
            'offset'     => $page + 1,
            'step'       => Eurotext_TranslationManager_Model_Export_Project::STEP_COLLECT_CATEGORIES,
        ];
    }

    /**
     * @param int $storeId
     * @param int $page
     * @param int $maxItems
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    private function getCategoryCollectionFor($storeId, $page, $maxItems)
    {
        $categoryCollection = Mage::getResourceModel('catalog/category_collection')
            ->setStore($storeId)
            ->setStoreId($storeId)
            ->addAttributeToSelect(
                [
                    'url_path', 'url_key', 'request_path', 'description', 'short_description', 'meta_title',
                    'meta_description', 'meta_keywords', 'name'
                ]
            )
            ->addAttributeToSelect($this->configHelper->getCustomCategoryAttributesForExport());

        $categoryCollection->setPage($page, $maxItems);

        return $categoryCollection;
    }

    /**
     * @param DOMElement                  $catNode
     * @param Mage_Catalog_Model_Category $catSrc
     */
    private function createIdNode($catNode, $catSrc)
    {
        $nodeId = $this->doc->createElement('Id');
        $nodeId->appendChild($this->doc->createTextNode($catSrc->getId()));

        $firstChildNode = $catNode->childNodes->item(0);
        $catNode->insertBefore($nodeId, $firstChildNode);
    }

    /**
     * @param Mage_Catalog_Model_Category $catSrc
     * @param DOMElement                  $catNode
     */
    private function createUrlNode($catNode, $catSrc)
    {
        $nodeUrl = $this->doc->createElement('Url');
        $nodeUrl->appendChild($this->doc->createTextNode($catSrc->getUrl()));

        $secondChildNode = $catNode->childNodes->item(1);
        $catNode->insertBefore($nodeUrl, $secondChildNode);
    }

    /**
     * @param DOMElement                  $catNode
     * @param Mage_Catalog_Model_Category $catDst
     * @param Mage_Catalog_Model_Category $catSrc
     */
    private function createDefaultAttributeNodes(
        DOMElement $catNode,
        Mage_Catalog_Model_Category $catDst,
        Mage_Catalog_Model_Category $catSrc
    ) {
        foreach ($this->attributesToExportAlwaysWithMapping as $attr => $nodeName) {
            $valueSrc = $catSrc->getDataUsingMethod($attr);
            $valueDst = $catDst->getDataUsingMethod($attr);

            if ($valueSrc != '' && ($valueSrc == $valueDst || $valueDst == '')) {
                $item = $this->doc->createElement($nodeName);
                Mage::helper('eurotext_translationmanager/xml')->appendTextChild($this->doc, $item, $valueSrc);
                $catNode->appendChild($item);
            }
        }
    }

    /**
     * @param DOMElement                  $catNode
     * @param Mage_Catalog_Model_Category $catDst
     * @param Mage_Catalog_Model_Category $catSrc
     */
    private function createCustomCategoryAttributeNodes(
        DOMElement $catNode,
        Mage_Catalog_Model_Category $catDst,
        Mage_Catalog_Model_Category $catSrc
    ) {
        if (!$this->configHelper->getCustomCategoryAttributesForExport()) {
            return;
        }
        $nodeCustomCategoryAttributes = $this->doc->createElement('custom_attributes');

        foreach ($this->configHelper->getCustomCategoryAttributesForExport() as $customCategoryAttribute) {
            $valueSrc = $catSrc->getDataUsingMethod($customCategoryAttribute);
            $valueDst = $catDst->getDataUsingMethod($customCategoryAttribute);

            if ($valueSrc != '' && ($valueSrc == $valueDst || $valueDst == '')) {
                Mage::helper('eurotext_translationmanager/xml')->appendTextNode(
                    $this->doc,
                    (string)$customCategoryAttribute,
                    $valueSrc,
                    $nodeCustomCategoryAttributes
                );
            }
        }

        if ($nodeCustomCategoryAttributes->hasChildNodes()) {
            $catNode->appendChild($nodeCustomCategoryAttributes);
        }
    }

    /**
     * @param Mage_Catalog_Model_Category $catSrc
     * @param Mage_Catalog_Model_Category $catDst
     * @param DOMElement                  $catNode
     */
    private function createUrlKeyNode(
        Mage_Catalog_Model_Category $catSrc,
        Mage_Catalog_Model_Category $catDst,
        DOMElement $catNode
    ) {
        $srcUrlKey = $catSrc->getUrlKey();
        $dstUrlKey = $catDst->getUrlKey();
        if ($srcUrlKey != '' && ($srcUrlKey == $dstUrlKey || $dstUrlKey == '')) {
            $item = $this->doc->createElement('UrlKey');
            Mage::helper('eurotext_translationmanager/xml')->appendTextChild(
                $this->doc,
                $item,
                $srcUrlKey
            );
            $catNode->appendChild($item);
        }
    }

    /**
     * @param Mage_Catalog_Model_Category $catSrc
     * @param Mage_Catalog_Model_Category $catDst
     * @param DOMElement                  $catNode
     */
    private function createSeoNodes(
        Mage_Catalog_Model_Category $catSrc,
        Mage_Catalog_Model_Category $catDst,
        DOMElement $catNode
    ) {
        foreach ($this->seoAttributesWithMapping as $attr => $nodeName) {
            $valueSrc = $catSrc->getDataUsingMethod($attr);
            $valueDst = $catDst->getDataUsingMethod($attr);

            if ($valueSrc != '' && ($valueSrc == $valueDst || $valueDst == '')) {
                $item = $this->doc->createElement($nodeName);
                Mage::helper('eurotext_translationmanager/xml')->appendTextChild($this->doc, $item, $valueSrc);
                $catNode->appendChild($item);
            }
        }
    }
}
