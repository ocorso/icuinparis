<?php
/**
 * BFM_All_Helper_Script manages JavaScript and CSS stylesheets for .phtml templates.
 * Renders the registered files and code to the registered places.
 * BFM_All_Helper_Script thus gets a chance to insert script tags
 * at <code>head</code> and <code>body</code> sections in the HTML output.
 */
class BFM_All_Helper_Script extends Mage_Core_Helper_Abstract
{
    /**
     * The placeholders inserted into body content to be replaced with generated HTML.
     */
    const HEAD_PLACEHODLER = '<##bfm##head##>';

    const BODY_BEGIN_PLACEHODLER = '<##bfm##begin##>';

    const BODY_END_PLACEHODLER = '<##bfm##end##>';

    /**
     * The script is rendered in the head section right before the title element.
     */
    const POS_HEAD = 0;
    /**
     * The script is rendered at the beginning of the body section.
     */
    const POS_BEGIN = 1;
    /**
     * The script is rendered at the end of the body section.
     */
    const POS_END = 2;
    /**
     * @var array the mapping between script file names and the corresponding script URLs.
     * The array keys are script file names (without directory part) and the array values are the corresponding URLs.
     * If an array value is false, the corresponding script file will not be rendered.
     * If an array key is '*.js' or '*.css', the corresponding URL will replace all
     * all JavaScript files or CSS files, respectively.
     *
     * This property is mainly used to optimize the generated HTML pages
     * by merging different scripts files into fewer and optimized script files.
     */
    public $scriptMap = array();
    /**
     * @var array the registered CSS files (CSS URL=>media type).
     */
    protected $_cssFiles = array();
    /**
     * @var array the registered JavaScript files (position, key => URL)
     */
    protected $_scriptFiles = array();
    /**
     * @var array the registered JavaScript code blocks (position, key => code)
     */
    protected $_scripts = array();

    protected $_hasScripts = false;

    /**
     * Registers a CSS file
     *
     * @param string $file URL of the CSS file
     * @param string $media media that the CSS file should be applied to. If empty, it means all media types.
     * @return BFM_All_Helper_Script the BFM_All_Helper_Script object itself (to support method chaining).
     */
    public function addCssFile($file, $media = '')
    {
        $fileUrl = $this->_getSkinFilelUrl($file);
        $this->_cssFiles[$fileUrl] = $media;

        return $this;

    }

    /**
     * Registers a javascript file.
     *
     * @param string $file URL of the javascript file
     * @param integer $position the position of the JavaScript code. Valid values include the following:
     * <ul>
     * <li>BFM_All_Helper_Script::POS_HEAD : the script is inserted in the head section right before the title element.</li>
     * <li>BFM_All_Helper_Script::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
     * <li>BFM_All_Helper_Script::POS_END : the script is inserted at the end of the body section.</li>
     * </ul>
     * BFM_All_Helper_Script::POS_END is a default script file position.
     *
     * @return BFM_All_Helper_Script the BFM_All_Helper_Script object itself (to support method chaining).
     */
    public function addScriptFile($file, $position = self::POS_END, $isExternal = false)
    {
        $this->_hasScripts = true;
        if ($isExternal === false) {
            $fileUrl = $this->_getSkinFilelUrl($file);
        }
        else
        {
            $fileUrl = $file;
        }

        $this->_scriptFiles[$position][$fileUrl] = $fileUrl;
        return $this;
    }

    /**
     * Registers a piece of javascript code.
     *
     * @param string $id ID that uniquely identifies this piece of JavaScript code (for example: 'faq.accordion')
     * @param string $script the javascript code
     * @param integer $position the position of the JavaScript code. Valid values include the following:
     * <ul>
     * <li>BFM_All_Helper_Script::POS_HEAD : the script is inserted in the head section right before the title element.</li>
     * <li>BFM_All_Helper_Script::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
     * <li>BFM_All_Helper_Script::POS_END : the script is inserted at the end of the body section.</li>
     * </ul>
     * BFM_All_Helper_Script::POS_END is a default script position.
     * @return BFM_All_Helper_Script the BFM_All_Helper_Script object itself (to support method chaining).
     */
    public function addScript($id, $script, $position = self::POS_END)
    {
        $this->_hasScripts = true;
        $this->_scripts[$position][$id] = $script;
        return $this;
    }

    /**
     * Renders the registered scripts in .phtml files.
     *
     * This method is called in {@link Mage_Core_Controller_Varien_Action::renderLayout} when it finishes
     * rendering content and calls system Magento Model {@link Mage_Core_Model_Translate_Inline::processResponseBody} method.
     * BFM_All module is overrided that Model and all calls translated through {@link BFM_All_Model_Renderer::processResponseBody}.
     * Extended method calls {@link BFM_All_Helper_Script::render} to render script and css files that have been added through .phtml templates.
     * BFM_All_Helper_Script thus gets a chance to insert script tags at <code>head</code> and <code>body</code> sections in the HTML output.
     *
     * @param string $output the existing output that needs to be inserted with script tags
     */
    public function render(&$output)
    {
        if (!empty($this->scriptMap))
            $this->_remapScripts();

        $this->unifyScripts();

        $this->_renderHead($output);
        if ($this->_hasScripts) {
            $this->_renderBodyBegin($output);
            $this->_renderBodyEnd($output);
        }
    }

    /**
     * Inserts the scripts and css in the head section.
     *
     * @param string $output the output to be inserted with scripts.
     */
    protected function _renderHead(&$output)
    {
        $html = '';
        foreach ($this->_cssFiles as $url => $media)
            $html .= Mage::helper('bfmall/html')->cssFile($url, $media) . "\n";

        if (isset($this->_scriptFiles[self::POS_HEAD])) {
            foreach ($this->_scriptFiles[self::POS_HEAD] as $scriptFile)
                $html .= Mage::helper('bfmall/html')->scriptFile($scriptFile) . "\n";
        }

        if (isset($this->_scripts[self::POS_HEAD]))
            $html .= Mage::helper('bfmall/html')->script(implode("\n", $this->_scripts[self::POS_HEAD])) . "\n";

        if ($html !== '') {
            $count = 0;
            $output = preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is', self::HEAD_PLACEHODLER . '$1', $output, 1, $count);

            if ($count)
                $output = str_replace(self::HEAD_PLACEHODLER, $html, $output);
            else
                $output = $html . $output;
        }
    }

    /**
     * Inserts the scripts at the beginning of the body section.
     *
     * @param string $output the output to be inserted with scripts.
     */
    protected function _renderBodyBegin(&$output)
    {
        $html = '';
        if (isset($this->_scriptFiles[self::POS_BEGIN])) {
            foreach ($this->_scriptFiles[self::POS_BEGIN] as $scriptFile)
                $html .= Mage::helper('bfmall/html')->scriptFile($scriptFile) . "\n";
        }
        if (isset($this->_scripts[self::POS_BEGIN]))
            $html .= Mage::helper('bfmall/html')->script(implode("\n", $this->_scripts[self::POS_BEGIN])) . "\n";

        if ($html !== '') {
            $count = 0;
            $output = preg_replace('/(<body\b[^>]*>)/is', '$1' . self::BODY_BEGIN_PLACEHODLER, $output, 1, $count);
            if ($count)
                $output = str_replace(self::BODY_BEGIN_PLACEHODLER, $html, $output);
            else
                $output = $html . $output;
        }
    }

    /**
     * Inserts the scripts at the end of the body section.
     *
     * @param string $output the output to be inserted with scripts.
     */
    protected function _renderBodyEnd(&$output)
    {
        if (!isset($this->_scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END]))
            return;

        $fullPage = 0;
        $output = preg_replace('/(<\\/body\s*>)/is', self::BODY_END_PLACEHODLER . '$1', $output, 1, $fullPage);
        $html = '';
        if (isset($this->_scriptFiles[self::POS_END])) {
            foreach ($this->_scriptFiles[self::POS_END] as $scriptFile)
                $html .= Mage::helper('bfmall/html')->scriptFile($scriptFile) . "\n";
        }

        $scripts = isset($this->_scripts[self::POS_END]) ? $this->_scripts[self::POS_END] : array();

        if (!empty($scripts))
            $html .= Mage::helper('bfmall/html')->script(implode("\n", $scripts)) . "\n";

        if ($fullPage)
            $output = str_replace(self::BODY_END_PLACEHODLER, $html, $output);
        else
            $output = $output . $html;
    }

    /**
     * Uses {@link scriptMap} to re-map the registered scripts.
     */
    protected function _remapScripts()
    {
        $cssFiles = array();
        foreach ($this->_cssFiles as $url => $media)
        {
            $name = basename($url);
            if (isset($this->scriptMap[$name])) {
                if ($this->scriptMap[$name] !== false)
                    $cssFiles[$this->scriptMap[$name]] = $media;
            }
            else if (isset($this->scriptMap['*.css'])) {
                if ($this->scriptMap['*.css'] !== false)
                    $cssFiles[$this->scriptMap['*.css']] = $media;
            }
            else
                $cssFiles[$url] = $media;
        }
        $this->_cssFiles = $cssFiles;

        $jsFiles = array();
        foreach ($this->_scriptFiles as $position => $scripts)
        {
            $jsFiles[$position] = array();
            foreach ($scripts as $key => $script)
            {
                $name = basename($script);
                if (isset($this->scriptMap[$name])) {
                    if ($this->scriptMap[$name] !== false)
                        $jsFiles[$position][$this->scriptMap[$name]] = $this->scriptMap[$name];
                }
                else if (isset($this->scriptMap['*.js'])) {
                    if ($this->scriptMap['*.js'] !== false)
                        $jsFiles[$position][$this->scriptMap['*.js']] = $this->scriptMap['*.js'];
                }
                else
                    $jsFiles[$position][$key] = $script;
            }
        }
        $this->_scriptFiles = $jsFiles;
    }

    /**
     * Removes duplicated scripts from {@link _scriptFiles}.
     */
    protected function unifyScripts()
    {
        $map = array();
        if (isset($this->scriptFiles[self::POS_HEAD]))
            $map = $this->scriptFiles[self::POS_HEAD];

        if (isset($this->scriptFiles[self::POS_BEGIN])) {
            foreach ($this->scriptFiles[self::POS_BEGIN] as $key => $scriptFile)
            {
                if (isset($map[$scriptFile]))
                    unset($this->scriptFiles[self::POS_BEGIN][$key]);
                else
                    $map[$scriptFile] = true;
            }
        }

        if (isset($this->scriptFiles[self::POS_END])) {
            foreach ($this->scriptFiles[self::POS_END] as $key => $scriptFile)
            {
                if (isset($map[$scriptFile]))
                    unset($this->scriptFiles[self::POS_END][$key]);
            }
        }
    }

    /**
     * Returns file url
     *
     * @param string $file filename to include
     * @return string
     */
    public function _getSkinFilelUrl($file)
    {
        return Mage::getDesign()->getSkinUrl($file);
    }
}