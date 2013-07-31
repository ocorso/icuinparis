<?php
class BFM_All_Model_Renderer extends Mage_Core_Model_Translate_Inline
{

    /**
     * Replace translate templates to HTML fragments
     *
     * @param array|string $body
     * @return Mage_Core_Model_Translate_Inline
     */
    public function processResponseBody(&$body)
    {
        /**
         * Calls a method that will insert registered css and js files and scripts into the html page body.
         */
        if (Mage::getDesign()->getArea() == 'frontend') {
            Mage::helper('bfmall/script')->render($body);
        }

        if (!$this->isAllowed()) {
            if (Mage::getDesign()->getArea() == 'adminhtml') {
                $this->stripInlineTranslations($body);
            }
            return $this;
        }

        if (is_array($body)) {
            foreach ($body as &$part)
            {
                $this->processResponseBody($part);
            }
        }
        else if (is_string($body)) {
            $this->_content = $body;

            $this->_tagAttributes();
            $this->_specialTags();
            $this->_otherText();
            $this->_insertInlineScriptsHtml();

            $body = $this->_content;
        }

        return $this;
    }
}