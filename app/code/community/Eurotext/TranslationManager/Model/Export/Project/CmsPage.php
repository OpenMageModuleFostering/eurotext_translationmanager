<?php

use Eurotext_TranslationManager_Model_Export_Project as ProjectExporter;

class Eurotext_TranslationManager_Model_Export_Project_CmsPage
{
    /**
     * @var string[]
     */
    private $attributes = [
        'title'           => 'Title',
        'content'         => 'Content',
        'content_heading' => 'ContentHeading',
    ];

    /**
     * @var string[]
     */
    private $metAttributes = [
        'meta_keywords'    => 'SeoKeywords',
        'meta_description' => 'SeoDescription',
    ];

    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMElement
     */
    private $cmsSiteNode;

    public function __construct()
    {
        $this->createNewDOMDocument();
    }

    public function process(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        $project->addAllRelationalData();
        $helper = Mage::helper('eurotext_translationmanager');

        $manualSelected = false;

        /** @var Mage_Cms_Model_Resource_page_Collection $pageSrcCollection */
        $pageSrcCollection = $this->getCmsPageCollectionFor($project->getStoreviewSrc());
        /** @var Mage_Cms_Model_Resource_page_Collection $pageDstCollection */
        $pageDstCollection = $this->getCmsPageCollectionFor($project->getStoreviewDst());

        if (!$project->isExportingAllCmsContent()) {
            $pageSrcCollection->addFieldToFilter('main_table.page_id', ['in' => $project->getPages()]);
            $pageDstCollection->addFieldToFilter('main_table.page_id', ['in' => $project->getPages()]);
            $manualSelected = true;
        }

        if ((!$project->isExportingAllCmsContent() && !count($project->getPages()))
            || $offset > $pageSrcCollection->getLastPageNumber()
        ) {
            return [
                'status_msg' => $helper->__("Exported CMS Pages."),
                'step'       => ProjectExporter::STEP_COLLECT_CMSBLOCKS,
                'offset'     => 1,
            ];
        }

        $statusMessage = sprintf(
            Mage::helper('eurotext_translationmanager')->__("Batch %s / %s CMS Pages:"),
            $offset + 1,
            $pageSrcCollection->getLastPageNumber()
        );
        foreach ($pageSrcCollection as $pageSrc) {
            $statusMessage .= "\n<br />- " . $pageSrc->getIdentifier();
            /** @var $pageSrc Mage_Cms_Model_Page */
            /** @var $pageDst Mage_Cms_Model_Page */
            $pageDst = clone $pageSrc;

            $identifier = $pageSrc->getIdentifier();
            /** @var Mage_Cms_Model_Page $matchingPage */
            $matchingPage = Mage::getResourceModel('cms/page_collection')
                                ->addStoreFilter($project->getStoreviewDst())
                                ->addFieldToFilter('identifier', $identifier)
                                ->getFirstItem();

            if (!$matchingPage->isObjectNew()) {
                $pageDst = $matchingPage;
            }

            $this->addBasicInformation($project, $pageSrc, $pageDst);

            foreach ($this->attributes as $attr => $nodeName) {
                $this->addAttributeToXml($attr, $nodeName, $pageSrc, $pageDst, $manualSelected);
            }

            if ($project->isExportingMetaAttributes()) {
                foreach ($this->metAttributes as $attr => $nodeName) {
                    $this->addAttributeToXml($attr, $nodeName, $pageSrc, $pageDst, $manualSelected);
                }
            }

            $this->writeXmlToFile($project, $pageSrc);
            $this->createNewDOMDocument();
        }

        return [
            'step'       => ProjectExporter::STEP_COLLECT_CMSPAGES,
            'offset'     => $offset + 1,
            'status_msg' => $statusMessage,
        ];
    }

    /**
     * @param string $nodeName
     * @param string $value
     */
    private function addNodeTopageNode($nodeName, $value)
    {
        $item = $this->doc->createElement($nodeName);
        $item->appendChild($this->doc->createTextNode($value));
        $this->cmsSiteNode->appendChild($item);
    }

    /**
     * @param string              $attr
     * @param string              $nodeName
     * @param Mage_Cms_Model_Page $pageSrc
     * @param Mage_Cms_Model_Page $pageDst
     * @param bool                $manualSelected
     */
    private function addAttributeToXml($attr, $nodeName, $pageSrc, $pageDst, $manualSelected)
    {
        $srcValue = $pageSrc->getDataUsingMethod($attr);
        $dstValue = $pageDst->getDataUsingMethod($attr);
        if ($srcValue != "" && (($srcValue == $dstValue) || ($dstValue == "") || ($manualSelected))) {
            $item = $this->doc->createElement($nodeName);
            Mage::helper('eurotext_translationmanager/xml')
                ->appendTextChild($this->doc, $item, $srcValue);
            $this->cmsSiteNode->appendChild($item);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Cms_Model_Page                       $page
     */
    private function writeXmlToFile(Eurotext_TranslationManager_Model_Project $project, $page)
    {
        if ($this->cmsSiteNode->hasChildNodes()) {
            $subdir = 'cms-sites';
            $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
                          ->getXmlSubdirectoryAndMakeSureItExists($project, $subdir);

            $identifier = Mage::helper('eurotext_translationmanager/filesystem')
                              ->getFilenameSafeString($page->getIdentifier());

            $xmlFilename = "$xmlDir/cms-" . Mage::helper('eurotext_translationmanager/filesystem')
                                                ->getFilenameSafeString($identifier) . "-" . $page->getId() . ".xml";

            $this->doc->save($xmlFilename);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Cms_Model_Page                       $pageSrc
     * @param Mage_Cms_Model_Page                       $pageDst
     */
    private function addBasicInformation(Eurotext_TranslationManager_Model_Project $project, $pageSrc, $pageDst)
    {
        $this->addNodeTopageNode("Id", $pageSrc->getId());
        $this->addNodeTopageNode("StoreviewSrc", $project->getStoreviewSrc());
        $this->addNodeTopageNode("StoreviewDst", $project->getStoreviewDst());
        $this->addNodeTopageNode("PageIdDst", $pageDst->getId() != $pageSrc->getId() ? $pageDst->getId() : -1);
    }

    /**
     * @param int $storeId
     *
     * @return Mage_Cms_Model_Resource_page_Collection
     */
    private function getCmsPageCollectionFor($storeId)
    {
        return Mage::getResourceModel('cms/page_collection')
                   ->addStoreFilter($storeId)
                   ->addOrder('page_id');
    }

    private function createNewDOMDocument()
    {
        $this->doc               = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $cmsSites                = $this->doc->createElement("cms-sites");
        $this->doc->appendChild($cmsSites);
        $this->cmsSiteNode = $this->doc->createElement("cms-site");
        $cmsSites->appendChild($this->cmsSiteNode);
    }
}
