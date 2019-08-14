<?php

interface Eurotext_TranslationManager_Model_Renderer_Filesystem
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @param string $filename
     * @return string
     */
    public function render($filename);
}
