<?php

class Eurotext_TranslationManager_Model_Xml_Import_Cms_BlockReader implements IteratorAggregate
{
    /**
     * @var SimpleXMLElement
     */
    private $domDocument;

    private $intNodes = array(
        'StoreviewSrc',
        'StoreviewDst',
        'Id',
    );

    public function __construct($xmlFile)
    {
        $xmlFile = array_pop($xmlFile);
        if (!is_file($xmlFile)) {
            Mage::throwException(sprintf('XML file with name "%s" not found.', $xmlFile));
        }
        $this->domDocument = new DOMDocument();
        $this->domDocument->loadXML(file_get_contents($xmlFile));
    }

    /**
     * @return Eurotext_TranslationManager_Model_Xml_Import_Cms_Block[]
     */
    private function getCmsBlocks()
    {

        $xmlBlocks = $this->domDocument->getElementsByTagName('cms-site');
        $blocks    = array();
        /** @var $xmlBlock DOMElement */
        foreach ($xmlBlocks as $xmlBlock) {
            $block = Mage::getModel('eurotext_translationmanager/xml_import_cms_block');
            /** @var $node DOMElement */
            foreach ($xmlBlock->childNodes as $node) {
                if ($node->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $nodeName  = $node->nodeName;
                $nodeValue = (string)$node->textContent;

                if (in_array($nodeName, $this->intNodes)) {
                    $block->$nodeName = (int)$nodeValue;
                } else {
                    $block->$nodeName = $nodeValue;
                }
            }

            $blocks[] = $block;
        }

        return $blocks;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getCmsBlocks());
    }
}
