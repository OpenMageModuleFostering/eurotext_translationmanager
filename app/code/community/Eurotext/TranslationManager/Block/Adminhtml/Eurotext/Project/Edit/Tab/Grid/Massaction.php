<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Grid_Massaction
    extends Mage_Adminhtml_Block_Widget_Grid_Massaction
{
    /**
     * @return string
     */
    public function getJavaScript()
    {
        return " {$this->getJsObjectName()} = new varienGridMassaction('{$this->getHtmlId()}', "
            . "{$this->getGridJsObjectName()}, '{$this->getSelectedJson()}'"
            . ", '{$this->getFormFieldNameInternal()}', '{$this->getFormFieldName()}');"
            . "{$this->getJsObjectName()}.setItems({$this->getItemsJson()}); "
            . "{$this->getJsObjectName()}.setGridIds('{$this->getGridIdsJson()}');"
            . ($this->getUseAjax() ? "{$this->getJsObjectName()}.setUseAjax(true);" : '')
            . ($this->getUseSelectAll() ? "{$this->getJsObjectName()}.setUseSelectAll(true);" : '')
            . "{$this->getJsObjectName()}.errorText = '{$this->getErrorText()}';";
    }

    /**
     * Retrieve JSON string of selected checkboxes
     *
     * @return string
     */
    public function getSelectedJson()
    {
        $internalField = $this->getRequest()->getParam($this->getFormFieldNameInternal());
        $selected = array_merge(
            is_array($internalField) ? $internalField : [],
            $this->getParentBlock()->getSelected()
        );
        if ($selected) {
            return implode(',', $selected);
        }

        return '';
    }

    /**
     * Retrieve array of selected checkboxes
     *
     * @return array
     */
    public function getSelected()
    {
        $internalField = $this->getRequest()->getParam($this->getFormFieldNameInternal());
        $selected = $internalField . implode(',', $this->getParentBlock()->getSelected());

        if ($selected) {
            $selected = explode(',', $selected);

            return $selected;
        }

        return [];
    }
}
