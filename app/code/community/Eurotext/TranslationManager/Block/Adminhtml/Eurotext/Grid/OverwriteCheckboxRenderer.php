<?php

trait Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer
{
    public function getColumnRenderers()
    {
        return [
            'checkbox' => 'eurotext_translationmanager/adminhtml_eurotext_grid_renderer_checkbox',
        ];
    }
}
