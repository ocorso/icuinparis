<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Listings_Filters extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsListingsFilters');
        //------------------------------

        $this->setTemplate('M2ePro/listings/filters.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $tempData = Mage::getModel('M2ePro/SellingFormatTemplates')->getCollection()->setOrder('title', 'ASC')->toArray();
        $sellingFormatTemplates = array();
        foreach ($tempData['items'] as $item) {
            $sellingFormatTemplates[$item['id']] = Mage::helper('M2ePro')->escapeHtml($item['title']);
        }
        $this->sellingFormatTemplates = $sellingFormatTemplates;
        $this->selectedSellingFormatTemplate = (int)$this->getRequest()->getParam('filter_selling_format_template');
        $this->sellingFormatTemplateUrl = $this->makeCutUrlForTemplate('filter_selling_format_template');
        //-------------------------------

        //-------------------------------
        $tempData = Mage::getModel('M2ePro/DescriptionsTemplates')->getCollection()->setOrder('title', 'ASC')->toArray();
        $descriptionsTemplates = array();
        foreach ($tempData['items'] as $item) {
            $descriptionsTemplates[$item['id']] = Mage::helper('M2ePro')->escapeHtml($item['title']);
        }
        $this->descriptionsTemplates = $descriptionsTemplates;
        $this->selectedDescriptionTemplate = (int)$this->getRequest()->getParam('filter_description_template');
        $this->descriptionTemplateUrl = $this->makeCutUrlForTemplate('filter_description_template');
        //-------------------------------

        //-------------------------------
        $tempData = Mage::getModel('M2ePro/ListingsTemplates')->getCollection()->setOrder('title', 'ASC')->toArray();
        $listingsTemplates = array();
        foreach ($tempData['items'] as $item) {
            $listingsTemplates[$item['id']] = Mage::helper('M2ePro')->escapeHtml($item['title']);
        }
        $this->listingsTemplates = $listingsTemplates;
        $this->selectedListingTemplate = (int)$this->getRequest()->getParam('filter_listing_template');
        $this->listingTemplateUrl = $this->makeCutUrlForTemplate('filter_listing_template');
        //-------------------------------

        //-------------------------------
        $tempData = Mage::getModel('M2ePro/SynchronizationsTemplates')->getCollection()->setOrder('title', 'ASC')->toArray();
        $synchronizationsTemplates = array();
        foreach ($tempData['items'] as $item) {
            $synchronizationsTemplates[$item['id']] = Mage::helper('M2ePro')->escapeHtml($item['title']);
        }
        $this->synchronizationsTemplates = $synchronizationsTemplates;
        $this->selectedSynchronizationTemplate = (int)$this->getRequest()->getParam('filter_synchronization_template');
        $this->synchronizationTemplateUrl = $this->makeCutUrlForTemplate('filter_synchronization_template');
        //-------------------------------
        
        return parent::_beforeToHtml();
    }

    protected function makeCutUrlForTemplate($templateUrlParamName)
    {
        $paramsFilters = array(
            'filter_selling_format_template',
            'filter_description_template',
            'filter_listing_template',
            'filter_synchronization_template'
        );
        
        $params = array();
        foreach ($paramsFilters as $value) {
            if ($value != $templateUrlParamName) {
                $params[$value] = $this->getRequest()->getParam($value);
            }
        }

        return $this->getUrl('*/*/*',$params);
    }
}