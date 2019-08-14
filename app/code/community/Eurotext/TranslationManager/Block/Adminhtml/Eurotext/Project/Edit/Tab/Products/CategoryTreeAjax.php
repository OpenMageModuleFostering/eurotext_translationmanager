<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_CategoryTreeAjax extends
    Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
{
    public function __construct()
    {
        parent::__construct();
        $this->_withProductCount = true;
    }


    protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);
        if (in_array($node->getId(), $this->getSelectedCategories())) {
            $item['checked'] = true;
        }
        $item['is_anchor'] = (int)$node->getIsAnchor();
        $item['url_key'] = $node->getData('url_key');
        $item['cls'] .= $item['cls'] . ' category-' . $node->getId();

        return $item;
    }
}
