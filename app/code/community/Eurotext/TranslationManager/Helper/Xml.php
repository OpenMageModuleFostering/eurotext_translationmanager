<?php

class Eurotext_TranslationManager_Helper_Xml
{
    /**
     * @param DOMDocument $doc
     * @param DOMNode     $xmlNode
     * @param string      $textContent
     */
    public function appendTextChild(DOMDocument $doc, DOMNode $xmlNode, $textContent)
    {
        $this->doc = $xmlNode->ownerDocument;
        $textContent = Mage::helper('eurotext_translationmanager/string')->replaceMagentoBlockDirectives($textContent);
        $xmlNode->appendChild($doc->createCDATASection($textContent));
    }

    /**
     * @param DOMDocument $doc
     * @param string      $nodeName
     * @param string      $value
     * @param DOMElement  $nodeToAppend
     */
    public function appendTextNode(DOMDocument $doc, $nodeName, $value, $nodeToAppend)
    {
        $newNode = $doc->createElement($nodeName);
        $this->appendTextChild($doc, $newNode, $value);
        $nodeToAppend->appendChild($newNode);
    }
}
