<?php

class Eurotext_TranslationManager_Helper_String
{
    /**
     * @param string $content
     * @return string
     */
    public function replaceMagentoBlockDirectives($content)
    {
        preg_match_all('#(\{{2}.*?\}{2})#', $content, $matches);
        $replace = [];
        foreach ($matches[0] as $m) {
            if ($m === []) {
                continue;
            }
            $replace[$m] = str_replace('"', "'", $m);
        }

        return strtr($content, $replace);
    }
}
