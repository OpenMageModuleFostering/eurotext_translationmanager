<?php

/**
 * @method setStatusCode(string $code)
 * @method string getStatusCode()
 * @method setStatusMsg(string $msg)
 * @method string getStatusMsg()
 * @method setOffset(int $offset)
 * @method int getOffset()
 * @method setStep(int $step)
 * @method int getStep()
 * @method setFinished(bool $finished)
 * @method bool getFinished()
 * @method setText(string $text)
 * @method mixed[] getResponseArray()
 */
class Eurotext_TranslationManager_Block_Response_Ajax extends Mage_Adminhtml_Block_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setFinished(false);
        $this->setOffset(0);
        $this->setStep(0);
    }


    public function returnJson()
    {
        return $this->toJson($this->getResponseArray());
    }
}
