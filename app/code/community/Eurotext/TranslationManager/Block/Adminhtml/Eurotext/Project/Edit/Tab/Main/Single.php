<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Main_Single
    extends Eurotext_Uploader_Block_Single
{
    const DEFAULT_UPLOAD_BUTTON_ID_SUFFIX = 'upload';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild(
            'upload_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->addData(
                    [
                        'id'    => $this->getElementId(self::DEFAULT_UPLOAD_BUTTON_ID_SUFFIX),
                        'label' => Mage::helper('eurotext_uploader')->__('Upload file'),
                        'type'  => 'button',
                    ]
                )
        );

        $this->_addElementIdsMapping(
            [
                'upload' => $this->_prepareElementsIds([self::DEFAULT_UPLOAD_BUTTON_ID_SUFFIX])
            ]
        );

        return $this;
    }

    /**
     * Get upload button html
     *
     * @return string
     */
    public function getUploadButtonHtml()
    {
        return $this->getChildHtml('upload_button');
    }
}
