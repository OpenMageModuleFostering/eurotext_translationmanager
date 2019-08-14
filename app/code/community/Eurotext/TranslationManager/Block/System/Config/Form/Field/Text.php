<?php

class Eurotext_TranslationManager_Block_System_Config_Form_Field_Text
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {


        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5">%s</td></tr>',
                       $element->getHtmlId(),
                       $this->__((string)$element->getFieldConfig()->text));
    }
}
