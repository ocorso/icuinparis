<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_ProductImport extends Mage_Core_Helper_Abstract
{
    protected $_baseMediaImportTmpDir;
    protected $_baseMagentoProductImagesDir;

    public function __construct(array $params = array())
    {
        $this->_baseMediaImportTmpDir = Mage::getBaseDir('tmp') . DS . 'M2ePro' . DS;
        $this->_baseMagentoProductImagesDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'm2epro' . DS;
    }

    /**
     * Downloading image from url, check for unique name in magento M2ePro product
     * folder. If such image exist change name to filename.ext => filename(1).ext
     *
     * @param string $urlAddress where download image
     * @param string $uniqueKey hash key of import process
     *
     * @return array of downloading result.
     * Keys [success => true/false, message=> "error message on failture",
     *       filename => "on success downloaded filename"]
     */
    public function downloadImage($urlAddress, $uniqueKey)
    {
        $resultOfDownload = array(
            'success' => true,
            'message' => '',
            'filename' => ''
        );

//        @ini_set('max_execution_time', 600);

        $host = $urlAddress;

        // Prepare for download, create dir, get filename for save, etc...
        $_ffullname = substr($host, strrpos($host, "/") + 1);
        $_fname = substr($_ffullname, 0, strrpos($_ffullname, "."));
        $_fext = substr($_ffullname, strrpos($_ffullname, "."));
        $_fext = strtolower(substr($_fext, 0, 4));
        if ($_fext == ".jpe") {
            $_fext = ".jpg";
        }

        $curFileName = $_fname . $_fext;
        $counter = 0;

        // Check for have product image import dir
        $this->_checkForProductImageDirectory();
        $this->_checkForTempDirectory();

        // Check for have such name into magento e2m product folder
        while (file_exists($this->_baseMagentoProductImagesDir . $curFileName)) {
            $counter++;
            $curFileName = $_fname . "(" . $counter . ")" . $_fext;
        }

        $_ffullname = $curFileName;

        // Create dir for product download image
        if (!is_dir($this->_baseMediaImportTmpDir . $uniqueKey)) {
            mkdir($this->_baseMediaImportTmpDir . $uniqueKey);
        }

        // Path where upload file
        $path = $this->_baseMediaImportTmpDir . $uniqueKey . DS;

        // Full name of file including path
        $fileName = $path . $_ffullname;

        try {
            // Create empty file
            $fp = fopen($fileName, "w");
            fclose($fp);

            // Download prepare
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            $fp = fopen($fileName, "w+");

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_REFERER, $host);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        } catch (Exception $exception) {
            $resultOfDownload['success'] = false;
            $resultOfDownload['message'] = $exception->getMessage();
        }

        if ($resultOfDownload['success'] == false) {
            return $resultOfDownload;
        }

        try {
            $file_info = getimagesize($fileName);
            if (empty($file_info)) {
                $resultOfDownload['success'] = false;
                $resultOfDownload['message'] = "Invalid image file";
            }
        } catch (Exception $exception) {
            $resultOfDownload['success'] = false;
            $resultOfDownload['message'] = "Invalid image file";
        }

        if ($resultOfDownload['success'] != false) {
            $resultOfDownload['filename'] = $_ffullname;
        }

        // Return result of downloading image from eBay
        return $resultOfDownload;
    }

    /**
     * Copy download from eBay image to folder used by Magento product
     *
     * @param int $itemId item id on eBay
     * @param string $productImageFileName file name of imported image
     * @param string $hashKey import process hash key
     *
     * @return string/bool  true on success copy. String when error on copy
     */
    public function copyImageToProduct($productImageFileName, $hashKey)
    {
        $copyFrom = $this->_baseMediaImportTmpDir.$hashKey.DS.$productImageFileName;
        $copyTo = $this->_baseMagentoProductImagesDir.$productImageFileName;
        try {
            copy($copyFrom, $copyTo);
            // Delete file from temp location
            unlink($copyFrom);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
        return true;
    }

    public function removeTempDir($hashKey)
    {
        $this->_removeDirRec($this->_baseMediaImportTmpDir.$hashKey);
    }

    /**
     * PHP's strip_tags() function will remove tags, but it
     * doesn't remove scripts, styles, and other unwanted
     * invisible text between tags.  Also, as a prelude to
     * totalizing the text, we need to insure that when
     * block-level tags (such as <p> or <div>) are removed,
     * neighboring words aren't joined.
     *
     * @param String $text
     */
    public function stripUnvisible($text)
    {
        $text = preg_replace(
            array(
                 // Remove invisible content
                 '@<head[^>]*?>.*?</head>@siu',
                 '@<style[^>]*?>.*?</style>@siu',
                 '@<script[^>]*?.*?</script>@siu',
                 '@<object[^>]*?.*?</object>@siu',
                 '@<embed[^>]*?.*?</embed>@siu',
                 '@<applet[^>]*?.*?</applet>@siu',
                 '@<noframes[^>]*?.*?</noframes>@siu',
                 '@<noscript[^>]*?.*?</noscript>@siu',
                 '@<noembed[^>]*?.*?</noembed>@siu',

                 // Add line breaks before & after blocks
                 '@<((br)|(hr))@iu',
                 '@</?((address)|(blockquote)|(center)|(del))@iu',
                 '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                 '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                 '@</?((table)|(th)|(td)|(caption))@iu',
                 '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                 '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                 '@</?((frameset)|(frame)|(iframe))@iu',
            ),
            array(
                 ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                 "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                 "\n\$0", "\n\$0",
            ),
            $text);
        return $text;
    }

    /**
     *
     * @param String $text
     * @param String $except tags to be exepted from
     * @return String
     */
    public function stripHtmlTags($text, $except = '')
    {
        // Remove all remaining tags and comments and return.
        return str_replace('&nbsp;', ' ',strip_tags($this->stripUnvisible($text), $except));
    }

    /**
     * Perform checking for having product image import directory.
     * When dir not exist, create directory
     */
    protected function _checkForProductImageDirectory()
    {
        $baseMedialCatalog =  Mage::getBaseDir('media') . DS . 'catalog'. DS;
        $baseMedialCatalogProduct = $baseMedialCatalog.'product'. DS;

        // Base dir /media/catalog/
        if (!is_dir($baseMedialCatalog)) {
            mkdir($baseMedialCatalog);
        }

        // Base dir /media/catalog/product
        if (!is_dir($baseMedialCatalogProduct)) {
            mkdir($baseMedialCatalogProduct);
        }

        // e2m product import dir
        if (!is_dir($this->_baseMagentoProductImagesDir)) {
            mkdir($this->_baseMagentoProductImagesDir);
        }
    }

    protected function _checkForTempDirectory()
    {
        if (!is_dir($this->_baseMediaImportTmpDir)) {
            mkdir($this->_baseMediaImportTmpDir);
        }
    }

    /**
     * Function for recursive remove of a nonempty directory
     *
     */
    protected function _removeDirRec($dir)
    {
        if ($objs = glob($dir."/*")) {
            foreach($objs as $obj) {
                is_dir($obj) ? $this->_removeDirRec($obj) : unlink($obj);
            }
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}