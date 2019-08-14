<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_CategoryTree
    extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
        $item['text'] = $this->escapeHtml($node->getName());

        if ($this->_withProductCount) {
            $item['text'] .= ' (' . $node->getProductCount() . ')';
        }
        $item['id'] = $node->getId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'category-' . $node->getId() . ' folder ' . ($node->getIsActive(
            ) ? 'active-category' : 'no-active-category');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;

        if ($node->hasChildren()) {
            $item['children'] = [];
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }

        if (empty($item['children']) && (int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }

        if (!empty($item['children'])) {
            $item['expanded'] = true;
        }

        return $item;
    }
}
