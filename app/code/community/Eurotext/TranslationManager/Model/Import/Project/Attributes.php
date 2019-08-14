<?php

class Eurotext_TranslationManager_Model_Import_Project_Attributes
    implements Eurotext_TranslationManager_Model_Import_Project_Importer
{
    use Eurotext_TranslationManager_Model_Import_Project_CollectSkipped;

    /**
     * @param string                                    $filename
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    public function import($filename, Eurotext_TranslationManager_Model_Project $project)
    {
        $doc = new DOMDocument();
        $doc->load($filename);

        foreach ($doc->getElementsByTagName("attribute") as $attribute) {
            $this->importAttribute($project, $attribute);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param DOMElement                                $attribute
     */
    private function importAttribute(Eurotext_TranslationManager_Model_Project $project, DOMElement $attribute)
    {
        /** @var Eurotext_TranslationManager_Model_Eav_Attribute_Label $eavLabel */
        $eavLabel = Mage::getResourceModel('eurotext_translationmanager/eav_attribute_label_collection')
            ->addFieldToFilter('attribute_id', $attribute->getAttribute("id"))
            ->addFieldToFilter('store_id', $project->getStoreviewDst())
            ->getFirstItem();

        $eavLabel->addData(
            [
                'attribute_id' => intval($attribute->getAttribute("id")),
                'store_id'     => $project->getStoreviewDst(),
                'value'        => $attribute->getElementsByTagName("AttributeName")[0]->textContent,
            ]
        );
        $eavLabel->save();

        $this->importOptions($project, $attribute);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param DOMElement                                $attribute
     */
    private function importOptions(Eurotext_TranslationManager_Model_Project $project, DOMElement $attribute)
    {
        foreach ($attribute->getElementsByTagName("option") as $option) {
            $this->importOption($project, $option);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param DOMElement                                $option
     */
    private function importOption(Eurotext_TranslationManager_Model_Project $project, DOMElement $option)
    {
        $value = Mage::getResourceModel('eurotext_translationmanager/eav_attribute_option_value_collection')
            ->addFieldToFilter('option_id', $option->getAttribute("id"))
            ->addFieldToFilter('store_id', $project->getStoreviewDst())
            ->getFirstItem();

        $value->addData(
            [
                'option_id' => intval($option->getAttribute("id")),
                'store_id'  => $project->getStoreviewDst(),
                'value'     => $option->getElementsByTagName("OptionName")[0]->textContent,
            ]
        );

        $value->save();
    }
}
