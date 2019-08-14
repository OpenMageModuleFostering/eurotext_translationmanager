<?php

use Eurotext_TranslationManager_Model_Export_Project as ProjectExporter;

class Eurotext_TranslationManager_Model_Export_Project_CmsBlock
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

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return string[]
     */
    public function process(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        $project->addAllRelationalData();
        $helper = Mage::helper('eurotext_translationmanager');

        $manualSelected = false;

        /** @var Mage_Cms_Model_Resource_Block_Collection $blockSrcCollection */
        $blockSrcCollection = $this->getCmsBlockCollectionFor($project->getStoreviewSrc());
        /** @var Mage_Cms_Model_Resource_Block_Collection $blockDstCollection */
        $blockDstCollection = $this->getCmsBlockCollectionFor($project->getStoreviewDst());

        if (!$project->isExportingAllCmsContent()) {
            $blockSrcCollection->addFieldToFilter('main_table.block_id', ['in' => $project->getBlocks()]);
            $blockDstCollection->addFieldToFilter('main_table.block_id', ['in' => $project->getBlocks()]);
            $manualSelected = true;
        }

        if ((!$project->isExportingAllCmsContent() && !count($project->getBlocks()))
            || $offset > $blockSrcCollection->getLastPageNumber()
        ) {
            return [
                'status_msg' => $helper->__("Exported CMS Blocks."),
                'step'       => ProjectExporter::STEP_COLLECT_TEMPLATES_FILES,
                'offset'     => 0,
            ];
        }

        $statusMessage = sprintf(
            Mage::helper('eurotext_translationmanager')->__("Batch %s / %s CMS Blocks:"),
            $offset + 1,
            $blockSrcCollection->getLastPageNumber()
        );

        foreach ($blockSrcCollection as $blockSrc) {
            /** @var $blockSrc Mage_Cms_Model_Block */
            $statusMessage .= "\n<br />- " . $blockSrc->getIdentifier();
            /** @var $blockDst Mage_Cms_Model_Block */
            $blockDst = clone $blockSrc;

            $identifier = $blockSrc->getIdentifier();
            /** @var Mage_Cms_Model_Block $matchingPage */
            $matchingPage = Mage::getResourceModel('cms/block_collection')
                ->addStoreFilter($project->getStoreDst())
                ->addFieldToFilter('identifier', $identifier)
                ->getFirstItem();

            if (!$matchingPage->isObjectNew()) {
                $blockDst = $matchingPage;
            }

            $this->addBasicInformation($project, $blockSrc, $blockDst);

            foreach ($this->attributes as $attr => $nodeName) {
                $this->addAttributeToXml($attr, $nodeName, $blockSrc, $blockDst, $manualSelected);
            }

            if ($project->isExportingMetaAttributes()) {
                foreach ($this->metAttributes as $attr => $nodeName) {
                    $this->addAttributeToXml($attr, $nodeName, $blockSrc, $blockDst, $manualSelected);
                }
            }

            $this->writeXmlToFile($project, $blockSrc);
            $this->createNewDOMDocument();
        }

        return [
            "status_msg" => $statusMessage,
            "offset"     => ($offset + 1),
            "step"       => ProjectExporter::STEP_COLLECT_CMSBLOCKS,
        ];
    }

    /**
     * @param string $nodeName
     * @param string $value
     * @return DOMElement
     */
    private function addNodeToBlockNode($nodeName, $value)
    {
        $item = $this->doc->createElement($nodeName);
        $item->appendChild($this->doc->createTextNode($value));
        $this->cmsSiteNode->appendChild($item);
    }

    /**
     * @param string               $attr
     * @param string               $nodeName
     * @param Mage_Cms_Model_Block $blockSrc
     * @param Mage_Cms_Model_Block $blockDst
     * @param bool                 $manualSelected
     */
    private function addAttributeToXml($attr, $nodeName, $blockSrc, $blockDst, $manualSelected)
    {
        $srcValue = $blockSrc->getDataUsingMethod($attr);
        $dstValue = $blockDst->getDataUsingMethod($attr);
        if ($srcValue != "" && (($srcValue == $dstValue) || ($dstValue == "") || ($manualSelected))) {
            $item = $this->doc->createElement($nodeName);
            Mage::helper('eurotext_translationmanager/xml')
                ->appendTextChild($this->doc, $item, $srcValue);
            $this->cmsSiteNode->appendChild($item);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Cms_Model_Block                      $block
     */
    private function writeXmlToFile(Eurotext_TranslationManager_Model_Project $project, $block)
    {
        if ($this->cmsSiteNode->hasChildNodes()) {
            $subdir = 'cms-sites';
            $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
                ->getXmlSubdirectoryAndMakeSureItExists($project, $subdir);

            $identifier = Mage::helper('eurotext_translationmanager/filesystem')
                ->getFilenameSafeString($block->getIdentifier());
            $xmlFilename = "$xmlDir/cmsblock-{$identifier}-{$block->getId()}.xml";

            $this->doc->save($xmlFilename);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param Mage_Cms_Model_Block                      $blockSrc
     * @param Mage_Cms_Model_Block                      $blockDst
     */
    private function addBasicInformation(Eurotext_TranslationManager_Model_Project $project, $blockSrc, $blockDst)
    {
        $this->addNodeToBlockNode("Id", $blockSrc->getId());
        $this->addNodeToBlockNode("StoreviewSrc", $project->getStoreviewSrc());
        $this->addNodeToBlockNode("StoreviewDst", $project->getStoreviewDst());
        $this->addNodeToBlockNode("PageIdDst", $blockDst->getId() != $blockSrc->getId() ? $blockDst->getId() : -1);
    }

    /**
     * @param int $storeId
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    private function getCmsBlockCollectionFor($storeId)
    {
        return Mage::getResourceModel('cms/block_collection')
            ->addStoreFilter($storeId)
            ->addOrder('block_id');
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
