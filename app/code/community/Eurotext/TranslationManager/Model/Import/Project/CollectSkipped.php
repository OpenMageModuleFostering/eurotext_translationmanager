<?php

trait Eurotext_TranslationManager_Model_Import_Project_CollectSkipped
{
    /**
     * @var string[]|int[]
     */
    private $skipped;

    /**
     * @param string|int $skipped
     */
    public function addSkipped($skipped)
    {
        $this->skipped[] = $skipped;
    }

    /**
     * @return int[]|string[]
     */
    public function getSkippedEntities()
    {
        return $this->skipped;
    }
}
