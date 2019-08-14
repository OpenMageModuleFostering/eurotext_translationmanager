<?php

class Eurotext_TranslationManager_Helper_String
{
    /**
     * @param string $content
     * @return string
     */
    public function replaceMagentoBlockDirectives($content)
    {
        return preg_replace('#\{{2}((.*?) (.*?=)"(.*?)"\s*)\}{2}#', '{{$2 $3\'$4\'}}', $content);
    }
}
