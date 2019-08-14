<?php

/**
 * @method setTitle(string $title)
 */
class Eurotext_TranslationManager_Block_Status extends Mage_Adminhtml_Block_Widget_Form
{
    private $testVarSubdirectories = [];

    /**
     * @var  Varien_Data_Form
     */
    private $form;

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    protected function _construct()
    {
        $this->testVarSubdirectories = [
            'Import' => Mage::getBaseDir('tmp') . '/eurotext',
            // \Eurotext_TranslationManager_Adminhtml_Eurotext_Project_ImportController::getTempDirectory
            'Export' => Mage::getBaseDir('var') . '/eurotext/projects'
            // \Eurotext_TranslationManager_Helper_Filesystem::createProjectExportDirectory
        ];
        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
        parent::_construct();
        $this->setId('project_id');
        $this->setTitle(Mage::helper('eurotext_translationmanager')->__('Project Information'));
    }

    protected function _prepareForm()
    {
        $this->form = new Varien_Data_Form(['id' => 'status_form']);

        $this->addStatus();
        $this->addLanguages();
        $this->addRequirements();
        $this->addDirectoryRights();
        $this->setForm($this->form);
    }

    /**
     * @return string
     */
    private function getUpgradeScopeUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/eurotext_status/upgradescope');
    }

    /**
     * @return bool
     */
    private function isCustomerIdSet()
    {
        return (bool)$this->configHelper->getCustomerId();
    }

    /**
     * @return string[][]
     */
    private function getLanguagesInStores()
    {
        $languages = [];
        foreach (Mage::app()->getStores() as $store) {
            /** @var Mage_Core_Model_Store $store */
            $locale = Mage::getStoreConfig('general/locale/code', $store->getId());
            $localeInfo = $this->helper->getLocaleInfoByMagentoLocale($locale);
            $languages[] =
                [
                    'store_id'      => $store->getId(),
                    'name'          => $store->getName(),
                    'code'          => $store->getCode(),
                    'locale'        => $locale,
                    'language_name' => $localeInfo['lang_name'],
                    'et_locale'     => $localeInfo['locale_eurotext'],
                ];
        }

        return $languages;
    }

    private function addRequirements()
    {
        $fieldset = $this->form->addFieldset('requirements', ['legend' => $this->__('System requirements')]);

        $requirements = [
            'module_version'     => [
                'name'           => $this->__('Plugin version'),
                'value_now'      => $this->configHelper->getModuleVersion(),
                'value_required' => '&mdash;',
            ],
            'php_version'        => [
                'name'           => $this->__('PHP version'),
                'value_now'      => PHP_VERSION,
                'value_required' => '> 5.5.0',
            ],
            'ftp_extension'      => [
                'name'           => $this->__('FTP-Extension'),
                'value_now'      => extension_loaded('ftp') ? $this->__('Installed') : $this->__('Missing'),
                'value_required' => $this->__('Installed'),
            ],
            'zip_extension'      => [
                'name'           => $this->__('ZIP-Extension'),
                'value_now'      => extension_loaded('zip') ? $this->__('Installed') : $this->__('Missing'),
                'value_required' => $this->__('Installed'),
            ],
            'max_execution_time' => [
                'name'           => $this->__("Value for 'max_execution_time'"),
                'value_now'      => ini_get('max_execution_time'),
                'value_required' => $this->__('Higher is better (Zero is infinite)'),

            ]
        ];

        foreach ($requirements as $name => $req) {
            $fieldset->addField(
                $name,
                'note',
                [
                    'label' => $req['name'],
                    'text'  => sprintf('%s (%s)', $req['value_now'], $req['value_required']),
                ]
            );
        }
    }

    private function addLanguages()
    {
        $fieldset = $this->form->addFieldset('language_settings', ['legend' => $this->__('Languages in Stores')]);

        foreach ($this->getLanguagesInStores() as $store) {
            $fieldset->addField(
                'languages_' . $store['store_id'],
                'note',
                [
                    'label' => $store['name'] . ' (' . $store['locale'] . ')',
                    'text'  => $store['language_name'] . ': ' . $store['et_locale'],
                ]
            );
        }
    }

    private function addStatus()
    {
        $fieldset = $this->form->addFieldset('status', ['legend' => $this->__('Status')]);

        if (!$this->configHelper->isEmailSent()) {
            $fieldset->addField(
                'register_email',
                'note',
                [
                    'label' => $this->__('Registration'),
                    'text'  => $this->__(
                        'You need to register first. Please go to <a href="%s">Registration</a> first and submit your information.',
                        Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/', ['section' => 'eurotext'])
                    ),
                ]
            );
        }

        if (!$this->isCustomerIdSet()) {
            $fieldset->addField(
                'customer_id_set',
                'note',
                [
                    'label' => $this->__('Login Credentials'),
                    'text'  => $this->__(
                        'You need to provide your login credentials first. Please go to <a href="%s">Settings</a> first and submit your information.',
                        Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/', ['section' => 'eurotext'])
                    ),
                ]
            );
        }

        try {
            Mage::getModel('eurotext_translationmanager/export_project_ftpUpload')->validateFtpConnection();
            $ftpMessage = $this->__('FTP Upload successfully tested.');
        } catch (Eurotext_TranslationManager_Exception_FtpException $e) {
            $ftpMessage = $this->__($e->getMessage());
        }

        $fieldset->addField(
            'ftp_problem',
            'note',
            [
                'label' => $this->__('FTP Status'),
                'text'  => $ftpMessage,
            ]
        );

        if ($this->helper->urlKeyScopeIsGlobal()) {
            $fieldset->addField(
                'url_key_scope',
                'note',
                [
                    'label' => $this->__('Catalog URL key scope'),
                    'text'  => $this->__(
                        'Currently your Magento installation is not capable to save translated URL keys for products and categories.'
                    ),
                ]
            );
            $fieldset->addField(
                'url_key_scope_link',
                'link',
                [
                    'label' => $this->__('Change URL key scope'),
                    'value' => $this->__(
                        'Click here to change the scope of URL keys to enable translation of URL keys for products and categories. (Attention: This cannot be undone!)'
                    ),
                    'href'  => $this->getUpgradeScopeUrl(),
                ]
            );
        } else {
            $ftpMessage = $this->__(
                'Your Magento installation allows the translation of URLs of categories and products.'
            );
            $fieldset->addField(
                'url_key_scope',
                'note',
                [
                    'label' => $this->__('Catalog URL key scope'),
                    'text'  => $ftpMessage,
                ]
            );
        }
    }

    private function addDirectoryRights()
    {
        $fieldset = $this->form->addFieldset('directories', ['legend' => $this->__('Directory permissions')]);

        foreach ($this->testVarSubdirectories as $type => $dir) {
            if ($this->checkDirectory($dir)) {
                $text = $this->__('Directory "%s" is writeable.', $dir);
            } else {
                $text = $this->__('Directory "%s" is not writeable.', $dir);
            }

            $fieldset->addField(
                $type,
                'note',
                [
                    'label' => $this->__(str_replace('_', ' ', $type)),
                    'text'  => $text,
                ]
            );
        }
    }

    /**
     * @param string $dir
     * @return bool
     */
    private function checkDirectory($dir)
    {
        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new Exception(sprintf('Directory %s could not be created.', $dir));
        }

        return is_dir_writeable($dir);
    }
}
