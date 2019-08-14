<?php

abstract class Eurotext_Uploader_Block_Abstract extends Mage_Adminhtml_Block_Widget
{
    /**
     * Template used for uploader
     *
     * @var string
     */
    protected $_template = 'eurotext/uploader/media/uploader.phtml';

    /**
     * @var Eurotext_Uploader_Model_Config_Misc
     */
    protected $_misc;

    /**
     * @var Eurotext_Uploader_Model_Config_Uploader
     */
    protected $_uploaderConfig;

    /**
     * @var Eurotext_Uploader_Model_Config_Browsebutton
     */
    protected $_browseButtonConfig;

    /**
     * @var Eurotext_Uploader_Model_Config_Misc
     */
    protected $_miscConfig;

    /**
     * @var array
     */
    protected $_idsMapping = [];

    /**
     * Default browse button ID suffix
     */
    const DEFAULT_BROWSE_BUTTON_ID_SUFFIX = 'browse';

    /**
     * Constructor for uploader block
     *
     * @see         https://github.com/flowjs/flow.js/tree/v2.9.0#configuration
     * @description Set unique id for block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId($this->getId() . '_Uploader');
    }

    /**
     * Helper for file manipulation
     *
     * @return Eurotext_Uploader_Helper_File
     */
    protected function _getHelper()
    {
        return Mage::helper('eurotext_uploader/file');
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->helper('core')->jsonEncode(
            [
                'uploaderConfig' => $this->getUploaderConfig()->getData(),
                'elementIds'     => $this->_getElementIdsMapping(),
                'browseConfig'   => $this->getButtonConfig()->getData(),
                'miscConfig'     => $this->getMiscConfig()->getData(),
            ]
        );
    }

    /**
     * Get mapping of ids for front-end use
     *
     * @return array
     */
    protected function _getElementIdsMapping()
    {
        return $this->_idsMapping;
    }

    /**
     * Add mapping ids for front-end use
     *
     * @param array $additionalButtons
     * @return $this
     */
    protected function _addElementIdsMapping(array $additionalButtons = [])
    {
        $this->_idsMapping = array_merge($this->_idsMapping, $additionalButtons);

        return $this;
    }

    /**
     * Prepare layout, create buttons, set front-end elements ids
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'browse_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    [
                        // Workaround for IE9
                        'before_html' => sprintf(
                            '<div style="display:inline-block;" id="%s">',
                            $this->getElementId(self::DEFAULT_BROWSE_BUTTON_ID_SUFFIX)
                        ),
                        'after_html'  => '</div>',
                        'id'          => $this->getElementId(self::DEFAULT_BROWSE_BUTTON_ID_SUFFIX . '_button'),
                        'label'       => Mage::helper('eurotext_uploader')->__('Browse Files…'),
                        'type'        => 'button',
                    ]
                )
        );

        $this->setChild(
            'delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    [
                        'id'    => '{{id}}',
                        'class' => 'delete',
                        'type'  => 'button',
                        'label' => Mage::helper('eurotext_uploader')->__('Remove')
                    ]
                )
        );

        $this->_addElementIdsMapping(
            [
                'container'    => $this->getHtmlId(),
                'templateFile' => $this->getElementId('template'),
                'browse'       => $this->_prepareElementsIds([self::DEFAULT_BROWSE_BUTTON_ID_SUFFIX])
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get browse button html
     *
     * @return string
     */
    public function getBrowseButtonHtml()
    {
        return $this->getChildHtml('browse_button');
    }

    /**
     * Get delete button html
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Get uploader misc settings
     *
     * @return Eurotext_Uploader_Model_Config_Misc
     */
    public function getMiscConfig()
    {
        if (null === $this->_miscConfig) {
            $this->_miscConfig = Mage::getModel('eurotext_uploader/config_misc');
        }

        return $this->_miscConfig;
    }

    /**
     * Get uploader general settings
     *
     * @return Eurotext_Uploader_Model_Config_Uploader
     */
    public function getUploaderConfig()
    {
        if (null === $this->_uploaderConfig) {
            $this->_uploaderConfig = Mage::getModel('eurotext_uploader/config_uploader');
        }

        return $this->_uploaderConfig;
    }

    /**
     * Get browse button settings
     *
     * @return Eurotext_Uploader_Model_Config_Browsebutton
     */
    public function getButtonConfig()
    {
        if (null === $this->_browseButtonConfig) {
            $this->_browseButtonConfig = Mage::getModel('eurotext_uploader/config_browsebutton');
        }

        return $this->_browseButtonConfig;
    }

    /**
     * Get button unique id
     *
     * @param string $suffix
     * @return string
     */
    public function getElementId($suffix)
    {
        return $this->getHtmlId() . '-' . $suffix;
    }

    /**
     * Prepare actual elements ids from suffixes
     *
     * @param array $targets $type => array($idsSuffixes)
     * @return array $type => array($htmlIds)
     */
    protected function _prepareElementsIds($targets)
    {
        return array_map([$this, 'getElementId'], array_unique(array_values($targets)));
    }
}
