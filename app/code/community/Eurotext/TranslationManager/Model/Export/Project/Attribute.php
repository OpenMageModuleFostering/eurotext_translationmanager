<?php

use Eurotext_TranslationManager_Model_Export_Project as ProjectExporter;

class Eurotext_TranslationManager_Model_Export_Project_Attribute
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMElement
     */
    private $nodeAttributes;

    public function __construct()
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        $this->nodeAttributes = $this->doc->createElement("attributes");
        $this->doc->appendChild($this->nodeAttributes);
    }


    public function process(Eurotext_TranslationManager_Model_Project $project)
    {
        if (!$project->isExportingAttributes()) {
            return [
                'status_msg' => Mage::helper('eurotext_translationmanager')->__('Attributes not exported.'),
                'step'       => ProjectExporter::STEP_GENERATE_CONTROL_FILE,
                'offset'     => 1,
            ];
        }

        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->addStoreLabel($project->getStoreviewSrc())
            ->addFieldToFilter('is_user_defined', 1);

        foreach ($attributes as $a) {
            $comment = $a->getFrontendLabel() == $a->getStoreLabel() ? "(default label)" : "(src-storeview label)";

            $nodeAttribute = $this->doc->createElement("attribute");
            $this->nodeAttributes->appendChild($nodeAttribute);

            $optionId = $this->doc->createAttribute("id");
            $optionId->value = $a->getId();
            $nodeAttribute->appendChild($optionId);

            $comment .= " attribute_code='" . $a['attribute_code'] . "', entity_type_id: " . $a['entity_type_id'];
            $nodeAttributeComment = $this->doc->createComment($comment);
            $nodeAttribute->appendChild($nodeAttributeComment);

            $nodeName = $this->doc->createElement("AttributeName");
            Mage::helper('eurotext_translationmanager/xml')
                ->appendTextChild($this->doc, $nodeName, $a->getStoreLabel());

            $attrTranslate = $this->doc->createAttribute("translate");
            $attrTranslate->value = "1";
            $nodeName->appendChild($attrTranslate);

            $nodeAttribute->appendChild($nodeName);

            /** @var Eurotext_TranslationManager_Model_Resource_Eav_Attribute_Option_Value_Collection $optionCollection */
            $optionCollection = Mage::getResourceModel(
                'eurotext_translationmanager/eav_attribute_option_value_collection'
            );
            $optionCollection->filterByAttribute($a->getId());
            $optionCollection->joinStoreLabel($project->getStoreviewSrc());
            $optionCollection->addOrder('sort_order ASC, main_table.option_id', 'ASC');

            if ($optionCollection->count()) {
                $nodeOptions = $this->doc->createElement("options");
                $nodeAttribute->appendChild($nodeOptions);
                foreach ($optionCollection as $optionValue) {
                    $defaultValue = $optionValue->getValue();
                    $storeValue = $optionValue->getStoreValue();

                    $nodeOption = $this->doc->createElement("option");
                    $nodeOptions->appendChild($nodeOption);

                    $optionId = $this->doc->createAttribute("id");
                    $optionId->value = $optionValue->getOptionId();
                    $nodeOption->appendChild($optionId);

                    $optionComment = empty($storeValue) || $defaultValue == $storeValue ? "(has default label)" : "(has src-storeview label)";

                    $nodeAttributeOptionComment = $this->doc->createComment($optionComment);
                    $nodeOption->appendChild($nodeAttributeOptionComment);

                    $nodeName = $this->doc->createElement("OptionName");
                    Mage::helper('eurotext_translationmanager/xml')
                        ->appendTextChild($this->doc, $nodeName, $storeValue ?: $defaultValue);

                    $attrTranslate = $this->doc->createAttribute("translate");
                    $attrTranslate->value = "1";
                    $nodeName->appendChild($attrTranslate);

                    $nodeOption->appendChild($nodeName);
                }
            }
        }

        if ($this->nodeAttributes->hasChildNodes()) {
            $subdir = 'attributes';
            $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')->getXmlSubdirectoryAndMakeSureItExists(
                $project,
                $subdir
            );

            $xml_filename = $xmlDir . DS . "attributes.xml";

            $this->doc->save($xml_filename);
        }

        return [
            'status_msg' => Mage::helper('eurotext_translationmanager')->__("Exported attributes."),
            'step'       => ProjectExporter::STEP_GENERATE_CONTROL_FILE,
            'offset'     => 1,
        ];
    }
}
