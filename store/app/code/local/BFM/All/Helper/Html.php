<?php
class BFM_All_Helper_Html extends Mage_Core_Helper_Abstract
{

    public static function encode($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'utf-8');
    }

    /**
     * Encloses the given CSS content with a CSS tag.
     *
     * @param string $text the CSS content
     * @param string $media the media that this CSS should apply to.
     * @return string the CSS properly enclosed
     */
    public function css($text, $media = '')
    {
        if ($media !== '')
            $media = ' media="' . $media . '"';
        return "<style type=\"text/css\"{$media}>\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</style>";
    }

    /**
     * Links to the specified CSS file.
     *
     * @param string $url the CSS URL
     * @param string $media the media that this CSS should apply to.
     * @return string the CSS link.
     */
    public function cssFile($url, $media = '')
    {
        if ($media !== '')
            $media = ' media="' . $media . '"';
        return '<link rel="stylesheet" type="text/css" href="' . self::encode($url) . '"' . $media . ' />';
    }

    /**
     * Encloses the given JavaScript within a script tag.
     *
     * @param string $text the JavaScript to be enclosed
     * @return string the enclosed JavaScript
     */
    public function script($text)
    {
        return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</script>";
    }

    /**
     * Includes a JavaScript file.
     *
     * @param string $url URL for the JavaScript file
     * @return string the JavaScript file tag
     */
    public function scriptFile($url)
    {
        return '<script type="text/javascript" src="' . self::encode($url) . '"></script>';
    }
}