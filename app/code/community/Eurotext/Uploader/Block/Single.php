<?php

class Eurotext_Uploader_Block_Single extends Eurotext_Uploader_Block_Abstract
{
    /**
     * Prepare layout, change button and set front-end element ids mapping
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getChild('browse_button')->setLabel(Mage::helper('eurotext_uploader')->__('…'));

        return $this;
    }

    /**
     * Constructor for single uploader block
     */
    public function __construct()
    {
        parent::__construct();

        $this->getUploaderConfig()->setSingleFile(true);
        $this->getButtonConfig()->setSingleFile(true);
    }
}
