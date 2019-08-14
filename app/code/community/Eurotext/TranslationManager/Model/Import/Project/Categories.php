<?php

class Eurotext_TranslationManager_Model_Import_Project_Categories
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @var string[]
     */
    private $categoryMapping = [
        'Title'          => 'name',
        'Longdesc'       => 'description',
        'SeoTitle'       => 'meta_title',
        'SeoDescription' => 'meta_description',
        'SeoKeywords'    => 'meta_keywords',
        'UrlKey'         => 'url_key',
    ];

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    public function __construct()
    {
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
    }

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function import($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $doc = new DOMDocument();
        $doc->load($filename);
        $categories = $doc->getElementsByTagName('category');
        foreach ($categories as $categoryNode) {
            $fieldNodes = $categoryNode->childNodes;
            $id = 0;
            $fields = [];

            foreach ($fieldNodes as $fieldNode) {
                $nodeName = trim($fieldNode->nodeName);
                $nodeContent = trim($fieldNode->textContent);

                if ($nodeName != '') {
                    if ($nodeName == 'Id') {
                        $id = (int)$nodeContent;
                    }
                    if ('custom_attributes' == $nodeName) {
                        $fields[$nodeName] = $fieldNode;
                    } else {
                        $fields[$nodeName] = $nodeContent;
                    }
                }
            }

            if ($id <= 0) {
                $this->addSkipped($id);
                continue;
            }
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category')->load($id)->setStoreId($project->getStoreviewDst());

            $hasUrlKey = false;

            foreach ($fields as $key => $value) {
                if (isset($this->categoryMapping[$key])) {
                    $category->setDataUsingMethod($this->categoryMapping[$key], $value);
                }
                if ($key == 'UrlKey') {
                    $hasUrlKey = true;
                }
                if ('custom_attributes' == $key) {
                    $this->processCustomCategoryAttributesOn($category, $value);
                }
            }

            if (!$hasUrlKey) {
                // Check if urlkey is already set:
                $urlKey = Mage::getResourceModel('catalog/category')
                    ->getAttributeRawValue($id, 'url_key', $project->getStoreviewDst());

                if (!$urlKey) {
                    // setting null will force magento to generate the urlkey using the product-name
                    $category->setData('url_key', null);
                }
            }

            $category->save();
        }
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param DOMElement                  $value
     */
    private function processCustomCategoryAttributesOn($category, $value)
    {
        $customCategoryAttributes = $this->configHelper->getCustomCategoryAttributesForExport();

        // value doesn't contain a text value as usual
        // only for custom_category_attributes key it's the node object
        $customCategoryAttributesNodes = $value->childNodes;

        foreach ($customCategoryAttributesNodes as $customCategoryAttributesNode) {
            $custom_category_attribute_key = trim($customCategoryAttributesNode->nodeName);
            $custom_category_attribute_value = trim($customCategoryAttributesNode->textContent);
            if (in_array($custom_category_attribute_key, $customCategoryAttributes, false)) {
                $category->setDataUsingMethod(
                    $custom_category_attribute_key,
                    $custom_category_attribute_value);
            }
        }
    }
}
