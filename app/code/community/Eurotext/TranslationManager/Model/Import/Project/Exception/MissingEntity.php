<?php

class Eurotext_TranslationManager_Model_Import_Project_Exception_MissingEntity extends Mage_Core_Exception
{
    /**
     * @var string|int
     */
    private $skippedEntity;

    /**
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     * @param string|int     $skippedEntity
     */
    public function __construct($message, $code, $previous, $skippedEntity)
    {
        parent::__construct($message, $code, $previous);
        $this->skippedEntity = $skippedEntity;
    }

    /**
     * @return int|string
     */
    public function getSkippedEntity()
    {
        return $this->skippedEntity;
    }
}
