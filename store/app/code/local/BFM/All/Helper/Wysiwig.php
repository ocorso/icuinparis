<?php
final class BFM_All_Helper_Wysiwig
{

    public static function getConfig($tabId = null)
    {
        $config = array();
        $config ['add_variables'] = false;
        $config ['add_widgets'] = false;
        $config ['add_directives'] = true;
        $config ['add_images'] = true;
        $config ['use_container'] = true;
        $config ['container_class'] = 'hor-scroll';
        $config += array('directives_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'), 'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'));
        if (!is_null($tabId))
            $config ['tab_id'] = $tabId;

        return $config;
    }
}
