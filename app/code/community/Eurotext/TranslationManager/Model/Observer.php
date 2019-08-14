<?php

class Eurotext_TranslationManager_Model_Observer
{
    /**
     * @var bool
     */
    private $alreadyRegistered;

    /**
     * @var string
     */
    private $subject = 'Magento translationMANAGER: Registrierung';

    /**
     * @var string[]
     */
    private $registrationLabel = [
        'register_shopname'   => 'Shopname',
        'register_url'        => 'Shop URL',
        'register_email'      => 'eEmail-Adresse',
        'register_salutation' => 'Anrede',
        'register_firstname'  => 'Vorname',
        'register_lastname'   => 'Nachname',
        'register_company'    => 'Firma',
        'register_street'     => 'Strasse',
        'register_zip'        => 'PLZ',
        'register_city'       => 'Stadt',
        'register_country'    => 'Land',
        'register_telephone'  => 'Tel-Nr.',
    ];

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    /**
     * @var string
     */
    private $mailBody;

    /**
     * @var bool
     */
    private $debugNoticeShown = false;

    public function __construct()
    {
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
        $this->helper       = Mage::helper('eurotext_translationmanager');
    }

    public function outputDebugNotice(Varien_Event_Observer $observer)
    {
        if ($this->debugNoticeShown) {
            return;
        }

        if (strpos($observer->getControllerAction()->getFullActionName(), 'eurotext') !== false) {
            $messages = [];
            if ($this->configHelper->isDebugMode()) {
                $messages[] = Mage::getSingleton('core/message')->notice(
                    $this->helper->__('Eurotext AG translationMANAGER: Debug mode enabled!')
                );
            }


            if ($this->configHelper->isFtpUploadDisabled()) {
                $messages[] = Mage::getSingleton('core/message')->notice(
                    $this->helper->__('Eurotext AG translationMANAGER: FTP Upload disabled!')
                );
            }
            Mage::getSingleton('adminhtml/session')->addUniqueMessages($messages);
            $this->debugNoticeShown = true;
        }

    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function modelConfigDataSaveBefore(Varien_Event_Observer $observer)
    {
        $this->alreadyRegistered = $this->configHelper->isEmailSent();

        $section = $observer->getObject()->getSection();
        if ($section != 'eurotext') {
            return;
        }

        $this->sendRegisterMailToEurotext($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    private function sendRegisterMailToEurotext(Varien_Event_Observer $observer)
    {
        $groups    = $observer->getObject()->getGroups();
        $newValues = $changes = [];

        foreach ($groups['config']['fields'] as $key => $data) {
            $newValues[$key] = $data['value'];
        }
        $prevValues = $this->configHelper->getRegistrationSettings();

        if ($this->alreadyRegistered) {
            $changes = array_diff_assoc($prevValues, $newValues);
        }

        /** Wir steigen aus, wenn keine Email geschickt werden muss. */
        if ($this->alreadyRegistered === true && empty($changes)) {
            return;
        }

        $this->composeEmail($changes, $newValues, $prevValues);

        $this->sendEmail();
        $this->mailBody = '';

        if (!$this->alreadyRegistered) {
            $this->updateConfig();
        }

        Mage::getSingleton('adminhtml/session')
            ->addSuccess($this->helper->__('Your registration data has been saved and submitted.'));
    }

    /**
     * @param string[] $changes
     * @param string[] $newValues
     * @param string[] $prevValues
     */
    private function composeEmail($changes, $newValues, $prevValues)
    {
        $this->addLineHtml('Hallo,');
        $this->addLineHtml('Ein Kunde hat sich via Magento-Modul neu registriert');
        if ($this->alreadyRegistered) {
            $this->subject = 'Update - Ein Magento translationMANAGER-Kunde hat seine Registrierungsdaten geändert';

            if (!empty($changes)) {
                $this->addChangesToEmailBody($changes, $newValues, $prevValues);
            }
        } else {
            $this->addHorizontalRuler();
            $this->addNewValuesToEmailBody($newValues);

        }
        $this->addHorizontalRuler();
    }

    /**
     * @param string $line
     */
    private function addLineHtml($line)
    {
        $line = htmlentities($line);

        return $this->addLine("<div>$line</div>");
    }

    /**
     * @param string $line
     */
    private function addLine($line)
    {
        $this->mailBody = $this->mailBody . $line . "\r\n";
    }

    /**
     * @param string[] $changes
     * @param string[] $newValues
     * @param string[] $prevValues
     */
    private function addChangesToEmailBody($changes, $newValues, $prevValues)
    {
        $this->addLineHtml('Ein Kunde hat im Magento-Modul seine Kontaktdaten geändert:');

        $customerId = $this->configHelper->getCustomerId();

        if ($customerId) {
            $this->addLineHtml('Kundennummer: ' . $customerId);
        } else {
            $this->addLineHtml('Bisher hat der Kunde keine gültige Kundennummer eingetragen.');
        }

        $this->addHorizontalRuler();

        foreach ($changes as $key => $value) {
            $this->addLineHtml(
                sprintf(
                    '%s: %s --- war bisher: %s',
                    $this->registrationLabel[$key],
                    $newValues[$key],
                    $prevValues[$key]
                )
            );
        }
    }

    private function addHorizontalRuler()
    {
        $this->addLine("<hr size='1' color='black'>");
    }

    /**
     * @param string[] $newValues
     */
    private function addNewValuesToEmailBody($newValues)
    {
        foreach ($newValues as $key => $value) {
            $this->addLineHtml(
                sprintf('%s: %s', $this->registrationLabel[$key], $value)
            );
        }
    }

    private function sendEmail()
    {
        $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
        $senderName  = Mage::getStoreConfig('trans_email/ident_general/name');
        $recipient   = $this->helper->getRegistrationRecipient();

        /** @var $mail Mage_Core_Model_Email */
        $mail = Mage::getModel('core/email');
        $mail->setFromName($senderName);
        $mail->setFromEmail($senderEmail);
        $mail->setToName($recipient['name']);
        $mail->setToEmail($recipient['email']);
        $mail->setType('html');
        $mail->setBody(utf8_decode($this->mailBody));
        $mail->setSubject(utf8_decode($this->subject));

        $mail->send();
    }

    private function updateConfig()
    {
        $this->configHelper->setEmailSent();
        $this->configHelper->setEmailSentDate(date('d.m.Y H:i:s (T)'));
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function adminSystemConfigSectionSaveAfter(Varien_Event_Observer $observer)
    {
        $section = $observer->getSection();
        if ($section != 'eurotext') {
            return;
        }

        try {
            Mage::getModel('eurotext_translationmanager/export_project_ftpUpload')
                ->validateFtpConnection();
        } catch (Eurotext_TranslationManager_Exception_FtpException $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('eurotext_translationmanager')->__('FTP test failed! Please check your connection credentials.')
            );
        }
    }
}
