<?php

class Eurotext_TranslationManager_Block_System_Config_Form_Field_Registration_Status
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var Eurotext_TranslationManager_Helper_Config $helper */
        $helper = Mage::helper('eurotext_translationmanager/config');
        if ($helper->isEmailSent()) {
            $html = sprintf(
                '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5">%s</td></tr>',
                $element->getHtmlId(),
                $this->__('Your registration has been last saved at: %s', $helper->getEmailSentDate())
            );
        } else {
            $html = sprintf(
                '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4>%s</h4><br>%s</td></tr>',
                $element->getHtmlId(),
                $this->__('Notice'),
                $this->__(
                    'Before you can use our translation portal you must register with our service.'
                ) . '<br><br>' .
                $this->__(
                    'This information is your personal access data for the Eurotext AG translation portal.<br/>Registration is free of charge. You will receive your personal access information within 24 hours (weekdays).'
                ) . '<br><br>' .
                $this->__('Please enter all requested information!') . '<br><br>' .
                '<b>' . $this->__('Important') . ':</b><br>' .
                $this->__(
                    'Any information you provide is voluntary. Your data will be used for the purpose of logging in to the translation portal and for project processing in your dealings with Eurotext AG only. Your information will not be forwarded to third parties. You can request a deletion of your information at any time by sending an email to <a href="mailto:%1$s">%1$s</a>.',
                    'datenschutz@eurotext.de'
                )
            );
        }

        return $html;
    }
}
