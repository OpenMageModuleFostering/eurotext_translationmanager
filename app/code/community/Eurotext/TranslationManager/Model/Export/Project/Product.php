<?php

use Eurotext_TranslationManager_Model_Export_Project as ProjectExporter;

class Eurotext_TranslationManager_Model_Export_Project_Product
{
    /**
     * @var string[]
     */
    private $seoAttributes = [
        'meta_title'       => 'SeoTitle',
        'meta_description' => 'SeoDescription',
        'meta_keyword'     => 'SeoKeywords',
    ];

    /**
     * @var string[]
     */
    private $attributesToExportAlways = [
        'name'              => 'Title',
        'description'       => 'Longdesc',
        'short_description' => 'Shortdesc',
    ];

    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMElement
     */
    private $articlesNode;
    /**
     * @var DOMElement
     */
    private $nodeArticle;

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

        $this->articlesNode = $this->doc->createElement('articles');
        $this->doc->appendChild($this->articlesNode);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $page
     * @return string[]
     */
    public function process(Eurotext_TranslationManager_Model_Project $project, $page)
    {
        /** @var Mage_Core_Model_App_Emulation $emulator */
        $emulator = Mage::getModel('core/app_emulation');
        $emulation = $emulator->startEnvironmentEmulation($project->getStoreviewSrc());

        $project->addAllRelationalData();

        $maxItems = $this->configHelper->getProductsPerFile();


        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollectionSrc */
        $productCollectionSrc = $this->getProductCollectionFor($project->getStoreviewSrc(), $page, $maxItems);
        $productCollectionDst = $this->getProductCollectionFor($project->getStoreviewDst(), $page, $maxItems);

        $productCollectionSrc->addIdFilter($project->getProducts());
        $productCollectionDst->addIdFilter($project->getProducts());

        if ($page > $productCollectionSrc->getLastPageNumber() || !count($project->getProducts())) {
            $emulator->stopEnvironmentEmulation($emulation);

            return [
                'status_msg' => $this->helper->__('Exported products.'),
                'step'       => ProjectExporter::STEP_COLLECT_CATEGORIES,
                'offset'     => 1,
            ];
        }

        $productCollectionSrc->addOptionsToResult();
        $productCollectionDst->addOptionsToResult();

        $galleryBackendModel = $productCollectionSrc->getResource()->getAttribute('media_gallery')->getBackend();
        /** @var Eurotext_TranslationManager_Model_Catalog_Product_Attribute_Backend_Media_OnlyStoreValues $galleryBackendModelForOnlyStoreValues */
        $galleryBackendModelForOnlyStoreValues = Mage::getModel(
            'eurotext_translationmanager/catalog_product_attribute_backend_media_onlyStoreValues'
        );

        $statusMessage = Mage::helper('eurotext_translationmanager')->__(
            'Batch %s / %s Products:',
            $page,
            $productCollectionSrc->getLastPageNumber()
        );

        foreach ($productCollectionSrc as $productSrc) {
            $this->nodeArticle = $this->doc->createElement('article');
            $statusMessage .= "\n<br />- " . $productSrc->getSku();
            /** @var Mage_Catalog_Model_Product $productDst */
            $productDst = $productCollectionDst->getItemById($productSrc->getId());

            $galleryBackendModel->afterLoad($productSrc);
            $galleryBackendModelForOnlyStoreValues
                ->setAttribute($productCollectionSrc->getResource()->getAttribute('media_gallery'))
                ->afterLoad($productDst);

            $this->exportDefaultAttributes($productSrc, $productDst);
            $this->exportCustomProductAttributes($productSrc, $productDst);
            $this->exportImageLabels($productSrc, $productDst);
            $this->exportOptions($productSrc);
            $this->exportUrlKeys($project, $productSrc, $productDst);
            $this->exportMetaAttributes($project, $productSrc, $productDst);

            if ($this->nodeArticle->hasChildNodes()) {
                $firstChildNode = $this->nodeArticle->childNodes->item(0);

                $nodeArticleId = $this->doc->createElement('Id');
                $nodeArticleId->appendChild($this->doc->createTextNode($productSrc->getId()));
                $this->nodeArticle->insertBefore($nodeArticleId, $firstChildNode);

                $nodeProductUrl = $this->doc->createElement('Url');
                $nodeProductUrl->appendChild($this->doc->createTextNode($productSrc->getProductUrl()));
                $this->nodeArticle->insertBefore($nodeProductUrl, $firstChildNode);

                $this->articlesNode->appendChild($this->nodeArticle);
            }
        }

        if ($this->articlesNode->hasChildNodes()) {
            $subdir = 'articles';
            $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
                ->getXmlSubdirectoryAndMakeSureItExists($project, $subdir);

            $this->doc->save($xmlDir . DS . 'a' . ((int)$page) . '.xml');
        }
        $emulator->stopEnvironmentEmulation($emulation);

        return [
            'offset'     => $page + 1,
            'step'       => ProjectExporter::STEP_COLLECT_PRODUCTS,
            'status_msg' => $statusMessage,
        ];
    }

    /**
     * @param int $storeId
     * @param int $offset
     * @param int $pageSize
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function getProductCollectionFor($storeId, $offset, $pageSize)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->setStore($storeId)
            ->setStoreId($storeId);
        $productCollection->addUrlRewrite()
            ->addAttributeToSelect(
                array_merge(
                    [
                        'name', 'description', 'short_description', 'request_path', 'url_key', 'url_path', 'meta_title',
                        'meta_keyword', 'meta_description'
                    ],
                    $this->configHelper->getCustomProductAttributesForExport()
                )
            )
            ->setPage($offset, $pageSize)
            ->addOrder('entity_id');

        return $productCollection;
    }

    /**
     * @param Mage_Catalog_Model_Product $productSrc
     * @param Mage_Catalog_Model_Product $productDst
     */
    private function exportImageLabels($productSrc, $productDst)
    {
        $imagesOrig = [];
        $imagesOrigUrl = [];
        $imagesDstDisabled = [];
        $imagesDstPosition = [];

        $imagesDstLabel = [];

        foreach ($productSrc->getMediaGallery()['images'] as $image) {
            $imgValueId = $image['value_id'];
            $imgValue = $image['file'];
            $imgLabel = $image['label'] ?: $image['label_default'];
            $imagesDstDisabled[$imgValueId] = $image['disabled'];
            $imagesDstPosition[$imgValueId] = $image['position'];

            $imagesOrig[$imgValueId] = $imgLabel;
            $imagesOrigUrl[$imgValueId] = $imgValue;
        }

        foreach ($productDst->getMediaGallery()['images'] as $image) {
            $imgValueId = $image['value_id'];
            $imagesDstDisabled[$imgValueId] = $image['disabled'];
            $imagesDstPosition[$imgValueId] = $image['position'];

            $imagesDstLabel[$imgValueId] = $image['label'];
        }

        $hasImages = false;
        $imagesNode = $this->doc->createElement('Images');

        foreach ($imagesOrig as $imgValueId => $imgLabel) {
            if (!array_key_exists($imgValueId, $imagesDstLabel)) {
                $imagesDstLabel[$imgValueId] = '';
            }

            $needsUpdate = false;
            if (trim($imagesDstLabel[$imgValueId]) === '') {
                $needsUpdate = true;
            }

            if ($needsUpdate && trim($imgLabel)) {
                $hasImages = true;
                $imageNode = $this->doc->createElement('Image');
                $imagesNode->appendChild($imageNode);

                $imageNodeId = $this->doc->createAttribute('value_id');
                $imageNodeId->value = $imgValueId;
                $imageNode->appendChild($imageNodeId);

                $imageNodePosition = $this->doc->createAttribute('position');
                $imageNodePosition->value = $imagesDstPosition[$imgValueId];
                $imageNode->appendChild($imageNodePosition);

                $imageNodeDisabled = $this->doc->createAttribute('disabled');
                $imageNodeDisabled->value = $imagesDstDisabled[$imgValueId];
                $imageNode->appendChild($imageNodeDisabled);

                // URL:
                $imgUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
                    'catalog/product' . $imagesOrigUrl[$imgValueId];

                $imageNodeUrl = $this->doc->createElement('Url');
                Mage::helper('eurotext_translationmanager/xml')
                    ->appendTextChild($this->doc, $imageNodeUrl, $imgUrl);
                $imageNode->appendChild($imageNodeUrl);

                // Label:
                $labelNode = $this->doc->createElement('Label');
                Mage::helper('eurotext_translationmanager/xml')
                    ->appendTextChild($this->doc, $labelNode, $imgLabel);
                $imageNode->appendChild($labelNode);
            }
        }

        if ($hasImages) {
            $this->nodeArticle->appendChild($imagesNode);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $productSrc
     * @param Mage_Catalog_Model_Product $productDst
     */
    private function exportCustomProductAttributes($productSrc, $productDst)
    {
        if (!$this->configHelper->getCustomProductAttributesForExport()) {
            return;
        }

        $nodeCustomProductAttributes = $this->doc->createElement('custom_attributes');
        foreach ($this->configHelper->getCustomProductAttributesForExport() as $customProductAttribute) {
            $srcValue = $productSrc->getDataUsingMethod($customProductAttribute);
            $dstValue = $productDst->getDataUsingMethod($customProductAttribute);
            if ($srcValue && ($srcValue == $dstValue || $dstValue == '')) {
                Mage::helper('eurotext_translationmanager/xml')->appendTextNode(
                    $this->doc,
                    (string)$customProductAttribute,
                    (string)$srcValue,
                    $nodeCustomProductAttributes
                );
            }
        }

        if ($nodeCustomProductAttributes->hasChildNodes()) {
            $this->nodeArticle->appendChild($nodeCustomProductAttributes);
        }

    }

    /**
     * @param Mage_Catalog_Model_Product $productSrc
     * @param Mage_Catalog_Model_Product $productDst
     */
    private function exportDefaultAttributes($productSrc, $productDst)
    {
        $xmlHelper = Mage::helper('eurotext_translationmanager/xml');
        foreach ($this->attributesToExportAlways as $attribute => $xmlNode) {
            $srcValue = $productSrc->getDataUsingMethod($attribute);
            $dstValue = $productDst->getDataUsingMethod($attribute);
            if ($srcValue && ($srcValue == $dstValue || $dstValue == '')) {
                $xmlHelper->appendTextNode($this->doc, $xmlNode, $srcValue, $this->nodeArticle);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    private function exportOptions($product)
    {
        $optionsNode = $this->doc->createElement('Options');

        $options = $product->getOptions();
        usort(
            $options,
            function (Varien_Object $a, Varien_Object $b) {
                return $b->getId() - $a->getId();
            }
        );

        if (!$options) {
            return;
        }

        foreach ($options as $_option) {
            /** @var Mage_Catalog_Model_Product_Option $_option */
            $optionNode = $this->doc->createElement('Option');

            $optionNodeIdAttribute = $this->doc->createAttribute('Id');
            $optionNodeIdAttribute->value = $_option->getId();
            $optionNode->appendChild($optionNodeIdAttribute);

            $_optionTitle = $_option->getTitle();
            if (!$_optionTitle) {
                continue;
            }
            $optionsNode->appendChild($optionNode);

            $optionsNodeTitle = $this->doc->createElement('Title');
            Mage::helper('eurotext_translationmanager/xml')
                ->appendTextChild($this->doc, $optionsNodeTitle, $_optionTitle);
            $optionNode->appendChild($optionsNodeTitle);

            // Values:
            $_values = $_option->getValues();
            if (!$_values) {
                continue;
            }
            $valuesNode = $this->doc->createElement('Values');

            foreach ($_values as $_value) {
                $_valueTitle = $_value->getTitle();
                if (!$_valueTitle) {
                    continue;
                }
                $_valueId = $_value->getId();

                $valueNode = $this->doc->createElement('Value');

                $valueNodeIdAttribute = $this->doc->createAttribute('Id');
                $valueNodeIdAttribute->value = $_valueId;
                $valueNode->appendChild($valueNodeIdAttribute);

                $valueNodeTitle = $this->doc->createElement('Title');
                Mage::helper('eurotext_translationmanager/xml')
                    ->appendTextChild($this->doc, $valueNodeTitle, $_valueTitle);
                $valueNode->appendChild($valueNodeTitle);

                $valuesNode->appendChild($valueNode);
            }
            if ($valuesNode->hasChildNodes()) {
                $optionNode->appendChild($valuesNode);
            }
        }

        if ($optionsNode->hasChildNodes()) {
            $this->nodeArticle->appendChild($optionsNode);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Catalog_Model_Product                $productSrc
     * @param Mage_Catalog_Model_Product                $productDst
     */
    private function exportUrlKeys(
        Eurotext_TranslationManager_Model_Project $project,
        Mage_Catalog_Model_Product $productSrc,
        Mage_Catalog_Model_Product $productDst
    ) {
        if (!$project->isExportingUrlKeys()) {
            return;
        }
        $valueSrc = $productSrc->getUrlKey();
        $valueDst = $productDst->getUrlKey();
        if ($valueSrc != '' && ($valueSrc == $valueDst || $valueDst == '')) {
            $item = $this->doc->createElement('UrlKey');
            Mage::helper('eurotext_translationmanager/xml')
                ->appendTextChild($this->doc, $item, $productSrc->getUrlKey());
            $this->nodeArticle->appendChild($item);

        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Catalog_Model_Product                $productSrc
     * @param Mage_Catalog_Model_Product                $productDst
     */
    private function exportMetaAttributes(
        Eurotext_TranslationManager_Model_Project $project,
        Mage_Catalog_Model_Product $productSrc,
        Mage_Catalog_Model_Product $productDst
    ) {
        if (!$project->isExportingMetaAttributes()) {
            return;
        }

        foreach ($this->seoAttributes as $attribute => $xmlNode) {
            $srcValue = $productSrc->getDataUsingMethod($attribute);
            $dstValue = $productDst->getDataUsingMethod($attribute);
            if ($srcValue && ($srcValue == $dstValue || $dstValue == '')) {
                $item = $this->doc->createElement($xmlNode);
                Mage::helper('eurotext_translationmanager/xml')
                    ->appendTextChild($this->doc, $item, $srcValue);
                $this->nodeArticle->appendChild($item);
            }
        }
    }
}
