<?php

/**
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Listings extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        
        // Initialization block
        //------------------------------
        $this->setId('listingsListings');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_listings';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Listings');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listings/index').'\')',
                'class'     => 'back'
            ));
        }
        
        $this->_addButton('goto_ebay_listings', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebayListings/index',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index'))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_templates', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link drop_down goto_templates_drop_down'
        ));

        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/listings',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index'))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_listings_items', array(
            'label'     => Mage::helper('M2ePro')->__('Search Items'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_listings/items',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index'))).'\')',
            'class'     => 'button_link search'
        ));
        
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/add',array('step'=>'1','clear'=>'yes')).'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_listings_help');
        $filtersBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_listings_filters');

        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getGotoTemplatesDropDownHtml());

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        $$('.goto_templates_drop_down')[0].innerHTML += '{$tempDropDownHtml}';
        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.goto_templates_drop_down')[0]);
    });

</script>
JAVASCRIPT;

        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid">';
        return $javascriptsMain.str_replace($startHtmlDivGrid,$helpBlock->toHtml().$filtersBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());
    }

    public function getGotoTemplatesDropDownHtml()
    {
        $sellingFormatTemplate = Mage::helper('M2ePro')->__('Selling Format Template');
        $descriptionTemplate = Mage::helper('M2ePro')->__('Description Template');
        $generalTemplate = Mage::helper('M2ePro')->__('General Template');
        $synchronizationTemplate = Mage::helper('M2ePro')->__('Synchronization Template');

        return <<<HTML
<ul style="display: none;">
    <li href="{$this->getUrl('*/adminhtml_sellingFormatTemplates/index')}">{$sellingFormatTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_descriptionTemplates/index')}">{$descriptionTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_listingTemplates/index')}">{$generalTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_synchronizationTemplates/index')}">{$synchronizationTemplate}</li>
</ul>
HTML;
    }
}