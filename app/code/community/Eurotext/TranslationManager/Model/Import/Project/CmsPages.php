<?php

class Eurotext_TranslationManager_Model_Import_Project_CmsPages
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @param string                                    $fullFilename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function import($fullFilename, Eurotext_TranslationManager_Model_Project $project)
    {
        $doc = new DOMDocument();
        $doc->load($fullFilename);
        $cmsSites = $doc->getElementsByTagName("cms-site");
        foreach ($cmsSites as $cmsSite) {
            try {
                $this->importPage($cmsSite, $project, $fullFilename);
            } catch (Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity $e) {
                $this->addSkipped($e->getSkippedEntity());
            }
        }
    }

    /**
     * @param Mage_Cms_Model_Page $pageSrc
     * @param int                 $storeviewDst
     */
    private function removeDestinationStoreViewFromSourcePage(Mage_Cms_Model_Page $pageSrc, $storeviewDst)
    {
        /** @var $srcStoreviewIds int[] */
        $srcStoreviewIds = $pageSrc->getStoreId();
        if (in_array(Mage_Core_Model_App::ADMIN_STORE_ID, $srcStoreviewIds)) {
            $srcStoreviewIds = array_keys(Mage::app()->getStores());
        }

        // Remove destination storeview:
        $srcStoreviewIds = array_diff($srcStoreviewIds, [$storeviewDst]);

        $pageSrc->setStoreId($srcStoreviewIds);
        $pageSrc->save();
    }

    /**
     * @param DOMDocument                               $cmsSite
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param                                           $fullFilename
     * @throws Exception
     */
    private function importPage(DOMElement $cmsSite, Eurotext_TranslationManager_Model_Project $project, $fullFilename)
    {
        /** @var $cmsSite DOMNode */
        $fieldNodes = $cmsSite->childNodes;

        $id = 0;
        $fields = [];
        $storeviewDst = $project->getStoreviewDst();

        foreach ($fieldNodes as $fieldNode) {
            /** @var $fieldNode DOMNode */
            /** @var $nodeName string */
            $nodeName = trim($fieldNode->nodeName);
            $nodeContent = trim($fieldNode->textContent);

            if ($nodeName != "") {
                if ($nodeName == "Id") {
                    $id = intval($nodeContent);
                } else {
                    $fields[$nodeName] = $nodeContent;
                }
            }
        }

        if ($id <= 0) {
            throw new Exception(
                sprintf('ID of CMS page couldn\'t be read from file "%s". Value read "%s"'),
                $fullFilename,
                $id
            );
        }

        /** @var $pageSrc Mage_Cms_Model_Page */
        $pageSrc = Mage::getModel('cms/page')->load($id);
        if ($pageSrc->isObjectNew()) {
            throw new Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity(
                sprintf('CMS page with id "%s" not found.', $id),
                0,
                null,
                $id
            );
        }
        $this->removeDestinationStoreViewFromSourcePage($pageSrc, $storeviewDst);

        $identifier = $pageSrc->getIdentifier();
        // Find matching page:
        $pageDst = $this->findMatchingPage($project, $identifier);

        $contentHeading = isset($fields['ContentHeading']) ? $fields['ContentHeading'] : $pageSrc->getContentHeading();
        $pageData = [
            'title'            => isset($fields['Title']) ? $fields['Title'] : $pageSrc->getTitle(),
            'root_template'    => $pageSrc->getRootTemplate(),
            'meta_keywords'    => $pageSrc->getMetaKeywords(),
            'meta_description' => $pageSrc->getMetaDescription(),
            'identifier'       => $pageSrc->getIdentifier(),
            'content_heading'  => $contentHeading,
            'stores'           => [$storeviewDst],
            'content'          => isset($fields['Content']) ? $fields['Content'] : $pageSrc->getContent(),
        ];
        $pageDst->addData($pageData);

        $pageDst->save();
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $identifier
     * @return Mage_Cms_Model_Page
     */
    private function findMatchingPage(Eurotext_TranslationManager_Model_Project $project, $identifier)
    {
        $pageDst = Mage::getResourceModel('cms/page_collection')
            ->addStoreFilter($project->getStoreviewDst(), false)
            ->addFieldToFilter('identifier', $identifier)
            ->getFirstItem();

        return $pageDst;
    }
}
