<?php

class Eurotext_TranslationManager_Block_Response_Ajax extends Mage_Adminhtml_Block_Abstract {

    public function returnJson(){
        return $this->toJson($this->getResponseArray());
    }

}