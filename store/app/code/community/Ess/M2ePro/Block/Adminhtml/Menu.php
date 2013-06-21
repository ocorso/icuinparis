<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu
{
    public function getMenuArray()
    {
        $menuArray = parent::getMenuArray();

        // Add wizard menu item
        //---------------------------------
        if (!Mage::getModel('M2ePro/Wizard')->isFinished()) {
            $menuArray['ebay']['children'] = array();
            $menuArray['ebay']['children']['wizard'] = array(
                'label' => Mage::helper('M2ePro')->__('Configuration Wizard'),
                'sort_order' => 1,
                'url' => $this->getUrl('M2ePro/adminhtml_wizard/index'),
                'active' => false,
                'level' => 1,
                'last' => true
            );
            return $menuArray;
        }
        //---------------------------------
        
        // Set documentation redirect url
        //---------------------------------
        $menuArray['ebay']['children']['help']['children']['docs']['click'] =
                "window.open(this.href, 'M2ePro Documentation ' + this.href); return false;";
        $menuArray['ebay']['children']['help']['children']['docs']['url'] =
                Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/documentation/', 'baseurl');
        //---------------------------------

        return $menuArray;
    }
}