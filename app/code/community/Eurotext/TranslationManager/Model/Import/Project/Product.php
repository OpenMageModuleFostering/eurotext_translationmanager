<?php

class Eurotext_TranslationManager_Model_Import_Project_Product
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @var string[]
     */
    private $eurotextToMagentoMappingForSimpleAttributes = [
        'Title'          => 'name',
        'Longdesc'       => 'description',
        'Shortdesc'      => 'short_description',
        'SeoTitle'       => 'meta_title',
        'SeoDescription' => 'meta_description',
        'SeoKeywords'    => 'meta_keyword',
    ];

    /**
     * @var string[]
     */
    private $ignoreFields = ['#text', 'Url'];

    /**
     * @var mixed[]
     */
    private $project;

    /**
     * @var DOMDocument
     */
    private $domDocument;

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    /**
     * @var Eurotext_TranslationManager_Model_ProductLoader
     */
    private $productLoader;

    /**
     * @var Eurotext_TranslationManager_Model_Factory
     */
    private $factory;

    /**
     * @param Eurotext_TranslationManager_Helper_Data   $helper
     * @param Eurotext_TranslationManager_Helper_Config $configHelper
     * @param Eurotext_TranslationManager_Model_Factory $factory
     */
    public function __construct(
        Eurotext_TranslationManager_Helper_Data $helper,
        Eurotext_TranslationManager_Helper_Config $configHelper,
        Eurotext_TranslationManager_Model_Factory $factory
    ) {
        $this->helper = $helper;
        $this->factory = $factory;
        $this->productLoader = $this->factory->getProductLoader();
        $this->configHelper = $configHelper;
    }

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function import($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $this->project = $project;
        $this->domDocument = new DOMDocument();
        $this->domDocument->load($filename);

        $this->helper->log('=== Importing Products ===');

        /** @var $article DOMElement */
        foreach ($this->domDocument->getElementsByTagName('article') as $article) {
            try {
                $product = $this->productLoader->load(
                    $this->getProductIdFromXml($article),
                    $this->getStoreId()
                );
                Mage::dispatchEvent(
                    'eurotext_product_import_translate_before',
                    ['product' => $product, 'translation' => $article]
                );
                $this->updateProductTranslation($article, $product);
                Mage::dispatchEvent(
                    'eurotext_product_import_save_before',
                    ['product' => $product, 'translation' => $article]
                );
                $product->save();
                Mage::dispatchEvent(
                    'eurotext_product_import_save_after',
                    ['product' => $product, 'translation' => $article]
                );
                $this->helper->log('== Product has been saved ==');
            } catch (Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity $e) {
                $this->addSkipped($e->getSkippedEntity());
            }
        }
    }

    /**
     * @param DOMElement $article
     *
     * @return int
     */
    private function getProductIdFromXml($article)
    {
        return (int)$article->getElementsByTagName('Id')->item(0)->textContent;
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return (int)$this->project['storeview_dst'];
    }

    /**
     * @param DOMElement                 $article
     * @param Mage_Catalog_Model_Product $product
     */
    private function updateProductTranslation(DOMElement $article, Mage_Catalog_Model_Product $product)
    {
        $fieldNodes = $article->childNodes;
        $id = 0;
        $fields = [];

        foreach ($fieldNodes as $fieldNode) {
            $nodeName = trim($fieldNode->nodeName);
            $nodeContent = trim($fieldNode->textContent);

            if ($nodeName != '') {
                if ($nodeName == 'Id') {
                    $id = (int)$nodeContent;
                } else {
                    if ('custom_attributes' == $nodeName) {
                        $fields[$nodeName] = $fieldNode;
                    } else {
                        $fields[$nodeName] = $nodeContent;
                    }
                }
            }
        }

        $this->helper->log("Saving Product (ID $id) for StoreID: {$this->getStoreId()}");

        foreach ($fields as $key => $value) {
            if (array_key_exists($key, $this->eurotextToMagentoMappingForSimpleAttributes)) {
                $product->setDataUsingMethod($this->eurotextToMagentoMappingForSimpleAttributes[$key], $value);
                continue;
            }
            if ($key == 'UrlKey') {
                $productId = $product->getId();
                $value = $this->fixEeCoreBugAndGenerateUniqueUrlKey($value, $productId);
                $product->setDataUsingMethod('url_key', $value);
                continue;
            }
            if ($key == 'Images') {
                $this->processProductImages($article, $product);
                continue;
            }
            if ($key == 'Options') {
                $this->processOptionsForProduct($article);
                continue;
            }
            if ('custom_attributes' == $key) {
                $this->processCustomProductAttributesOn($product, $value);
                continue;
            }
            if (!in_array($key, $this->ignoreFields)) {
                $this->helper->log(
                    'Unknown Field: ' . $key,
                    Zend_Log::EMERG
                );
                throw new Mage_Exception('Unknown Field: ' . $key);
            }
        }
    }

    /**
     * @param DOMElement                 $article
     * @param Mage_Catalog_Model_Product $product
     */
    private function processProductImages(DOMElement $article, Mage_Catalog_Model_Product $product)
    {
        $imageNodes = $article->getElementsByTagName('Image');

        if ($imageNodes->length) {
            $product->getMediaGalleryImages();
        }

        foreach ($imageNodes as $imageNode) {
            /** @var $imageNode DomElement */
            $imgValueId = (int)$imageNode->getAttributeNode('value_id')->value;
            $imgPosition = (int)$imageNode->getAttributeNode('position')->value;
            $imgDisabled = (int)$imageNode->getAttributeNode('disabled')->value;

            $labelNodes = $imageNode->getElementsByTagName('Label');
            $imgLabel = '';
            foreach ($labelNodes as $labelNode) {
                $imgLabel = trim($labelNode->textContent);
            }

            $this->factory->getMediaGalleryValueUpdate()
                ->update($product, $imgValueId, $imgLabel, $imgPosition, $imgDisabled);
        }
    }

    /**
     * @param DOMElement $article
     */
    private function processOptionsForProduct($article)
    {
        $optionNodes = $article->getElementsByTagName('Option');
        foreach ($optionNodes as $optionNode) {
            /** @var $optionNode DOMElement */
            $optionId = (int)$optionNode->getAttributeNode('Id')->value;
            $title = trim($this->getXMLChildNodeText($optionNode, 'Title', ''));
            if ($title) {
                Mage::getResourceModel('eurotext_translationmanager/catalog_product_option_title')
                    ->updateTitleForStore($optionId, $title, $this->getStoreId());
            }

            $OptionValueNodes = $article->getElementsByTagName('Value');
            foreach ($OptionValueNodes as $OptionValueNode) {
                $optionValueId = (int)$OptionValueNode->getAttributeNode('Id')->value;
                $optionValueTitle = trim($this->getXMLChildNodeText($OptionValueNode, 'Title', ''));

                if ($optionValueTitle) {
                    Mage::getResourceModel('eurotext_translationmanager/catalog_product_option_type_title')
                        ->updateTitleForStore($optionValueId, $optionValueTitle, $this->getStoreId());
                }
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param string     $childnodeName
     * @param string     $defaultValue
     *
     * @return string
     */
    private function getXMLChildNodeText(DOMElement $element, $childnodeName, $defaultValue = '')
    {
        $childNode = $this->getXMLChildNode($element, $childnodeName);
        if ($childNode == null) {
            return $defaultValue;
        }

        /** @var $childNode DOMElement */
        return $childNode->textContent;
    }

    /**
     * @param DOMElement $element
     * @param string     $childnodeName
     *
     * @return DOMElement
     */
    private function getXMLChildNode($element, $childnodeName)
    {
        $childNodes = $element->childNodes;
        foreach ($childNodes as $childNode) {
            if ($childNode->nodeType == XML_ELEMENT_NODE && $childNode->tagName == $childnodeName) {
                return $childNode;
            }
        }

        return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param DOMElement                 $value
     */
    private function processCustomProductAttributesOn($product, $value)
    {
        $customProductAttributes = $this->configHelper->getCustomProductAttributesForExport();

        // value doesn't contain a text value as usual
        // only for custom_product_attributes key it's the node object
        $customProductAttributesNodes = $value->childNodes;

        foreach ($customProductAttributesNodes as $customProductAttributesNode) {
            $custom_product_attribute_key = trim($customProductAttributesNode->nodeName);
            $custom_product_attribute_value = trim($customProductAttributesNode->textContent);
            if (in_array($custom_product_attribute_key, $customProductAttributes)) {
                $product->setDataUsingMethod(
                    $custom_product_attribute_key,
                    $custom_product_attribute_value
                );
            }
        }
    }

    /**
     * @param string $origUrlKey
     * @param int    $productId
     * @return string
     */
    private function fixEeCoreBugAndGenerateUniqueUrlKey($origUrlKey, $productId)
    {
        $urlKey = $origUrlKey;
        if (preg_match("#^(.*)(-\d+)$#", $origUrlKey, $matches)) {
            $urlKey = $matches[1];
        }
        $urlKeys = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('url_key', ['regexp' => "^$urlKey(-\d+)?"])
            ->addIdFilter($productId, true)
            ->getColumnValues('url_key');
        if (!in_array($origUrlKey, $urlKeys)) {
            return $origUrlKey;
        }
        natsort($urlKeys);
        $greatestUrlKey = end($urlKeys);
        $greatestNumber = str_replace($urlKey . '-', '', $greatestUrlKey);

        return $urlKey . '-' . ($greatestNumber + 1);
    }
}
