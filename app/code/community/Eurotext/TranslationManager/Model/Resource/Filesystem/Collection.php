<?php

class Eurotext_TranslationManager_Model_Resource_Filesystem_Collection extends Varien_Data_Collection_Filesystem
{
    /**
     * @var Eurotext_TranslationManager_Model_Renderer_Filesystem[]
     */
    private $renderers;

    /**
     * @param Eurotext_TranslationManager_Model_Renderer_Filesystem $renderer
     * @return $this
     */
    public function addRenderer(Eurotext_TranslationManager_Model_Renderer_Filesystem $renderer)
    {
        $this->renderers[] = $renderer;

        return $this;
    }

    /**
     * @param string $filename
     * @return string[]
     */
    protected function _generateRow($filename)
    {
        $file = [
            'filename' => $filename,
            'basename' => basename($filename),
        ];

        if (!count($this->renderers)) {
            return $file;
        }

        foreach ($this->renderers as $renderer) {
            $file[$renderer->getKey()] = $renderer->render($filename);
        }

        return $file;
    }

    public function filterCallbackLike($field, $filterValue, $row)
    {
        if (!($filterValue instanceof Zend_Db_Expr)) {
            return parent::filterCallbackLike($field, $filterValue, $row);
        }
        $filterValue = substr((string)$filterValue, 1, -1);
        $filterValueRegex = str_replace('%', '(.*?)', preg_quote($filterValue, '/'));

        return (bool)preg_match("/^{$filterValueRegex}$/i", $row[$field]);
    }
}
