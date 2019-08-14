<?php

class Eurotext_TranslationManager_Model_Import_Project_CmsBlocks
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @var string[]
     */
    private $cmsBlockMapping = [
        'Title'   => 'title',
        'Content' => 'content',
    ];

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function import($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $doc = new DOMDocument();
        $doc->load($filename);
        $cmsSites = $doc->getElementsByTagName('cms-site');
        foreach ($cmsSites as $cmsSite) {
            try {
                $this->importBlock($cmsSite, $project);
            } catch (Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity $e) {
                $this->addSkipped($e->getSkippedEntity());
            }
        }
    }

    /**
     * @param Mage_Cms_Model_Block $blockSrc
     * @param int                  $storeviewDst
     * @param Mage_Cms_Model_Block $block
     */
    private function copyDataFromSourceBlock(Mage_Cms_Model_Block $blockSrc, $storeviewDst, Mage_Cms_Model_Block $block)
    {
        $block->addData(
            [
                'title'            => $blockSrc->getTitle(),
                'root_template'    => $blockSrc->getRootTemplate(),
                'meta_keywords'    => $blockSrc->getMetaKeywords(),
                'meta_description' => $blockSrc->getMetaDescription(),
                'identifier'       => $blockSrc->getIdentifier(),
                'content_heading'  => $blockSrc->getContentHeading(),
                'stores'           => [$storeviewDst],
                'content'          => $blockSrc->getContent(),
            ]
        );
    }


    /**
     * @param Mage_Cms_Model_Block $blockSrc
     * @param int                  $storeviewDst
     */
    private function removeStoreViewFromSourceBlock(Mage_Cms_Model_Block $blockSrc, $storeviewDst)
    {
        /** @var $srcStoreviewIds int[] */
        $srcStoreviewIds = $blockSrc->getStoreId();
        if (!is_array($srcStoreviewIds)) {
            return;
        }

        if (in_array(Mage_Core_Model_App::ADMIN_STORE_ID, $srcStoreviewIds)) {
            $srcStoreviewIds = array_keys(Mage::app()->getStores());
        }

        $srcStoreviewIds = array_diff($srcStoreviewIds, [$storeviewDst]);

        $blockSrc->setStores($srcStoreviewIds);
        $blockSrc->save();
    }

    /**
     * @param DOMElement                                $cmsSite
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function importBlock(DOMElement $cmsSite, Eurotext_TranslationManager_Model_Project $project)
    {
        $fieldNodes = $cmsSite->childNodes;

        $id = 0;
        $fields = [];
        $StoreviewDst = -1;

        foreach ($fieldNodes as $fieldNode) {
            $nodeName = trim($fieldNode->nodeName);
            $nodeContent = trim($fieldNode->textContent);

            if ($nodeName != '') {
                if ($nodeName == 'Id') {
                    $id = (int)$nodeContent;
                } elseif ($nodeName == 'StoreviewDst') {
                    $StoreviewDst = (int)$nodeContent;
                } else {
                    $fields[$nodeName] = $nodeContent;
                }
            }
        }

        if ($id > 0) {
            /** @var $blockSrc Mage_Cms_Model_Block */
            $blockSrc = Mage::getModel('cms/block')->load($id);

            if ($blockSrc->isObjectNew()) {
                throw new Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity(
                    sprintf('Block with id "%s" not found.', $id),
                    0,
                    null,
                    $id
                );
            }

            $this->removeStoreViewFromSourceBlock($blockSrc, $StoreviewDst);

            $identifier = $blockSrc->getIdentifier();

            /** @var Mage_Cms_Model_Block $block */
            $block = Mage::getResourceModel('cms/block_collection')
                ->addStoreFilter($project->getStoreviewDst(), false)
                ->addFieldToFilter('identifier', $identifier)
                ->addOrder('block_id', 'ASC')
                ->getFirstItem();

            $this->copyDataFromSourceBlock($blockSrc, $StoreviewDst, $block);

            foreach ($fields as $key => $value) {
                if (isset($this->cmsBlockMapping[$key])) {
                    $block->setDataUsingMethod($this->cmsBlockMapping[$key], $value);
                }
            }

            $block->save();
        }
    }
}
