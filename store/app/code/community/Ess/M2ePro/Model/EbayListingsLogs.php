<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Model_EbayListingsLogs extends Ess_M2ePro_Model_LogsBase
{
    const ACTION_UNKNOWN = 1;
    const _ACTION_UNKNOWN = 'System';
    
    const ACTION_RELIST_PRODUCT_ON_EBAY = 2;
    const _ACTION_RELIST_PRODUCT_ON_EBAY = 'Relist product on eBay';
    const ACTION_STOP_PRODUCT_ON_EBAY = 3;
    const _ACTION_STOP_PRODUCT_ON_EBAY = 'Stop product on eBay';

    //####################################
    
	public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayListingsLogs');
    }

    //####################################

    public function addGlobalMessage($initiator = parent::INITIATOR_UNKNOWN , $actionId = NULL , $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(  NULL ,
                                              $this->makeCreator() ,
                                              $initiator,
                                              $actionId ,
                                              $action ,
                                              $description ,
                                              $type ,
                                              $priority );

        $this->createMessage($dataForAdd);
    }

    public function addProductMessage($ebayListingId , $initiator = parent::INITIATOR_UNKNOWN , $actionId = NULL , $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(  $ebayListingId ,
                                              $this->makeCreator() ,
                                              $initiator,
                                              $actionId ,
                                              $action ,
                                              $description ,
                                              $type ,
                                              $priority );

        $this->createMessage($dataForAdd);
    }

    public function clearMessages($ebayListingId = NULL)
    {
        $columnName = !is_null($ebayListingId) ? 'ebay_listing_id' : NULL;
        parent::clearMessagesByTable('M2ePro/EbayListingsLogs',$columnName,$ebayListingId);
    }

    public function getActionTitle($type)
    {
        return $this->getActionTitleByClass(__CLASS__,$type);
    }

    public function getActionsTitles()
    {
        return $this->getActionsTitlesByClass(__CLASS__,'ACTION_');
    }

    //####################################

    private function makeDataForAdd($ebayListingId , $creator , $initiator = parent::INITIATOR_UNKNOWN , $actionId = NULL , $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = array();

        if (!is_null($ebayListingId)) {
            $dataForAdd['ebay_listing_id'] = (int)$ebayListingId;
        } else {
            $dataForAdd['ebay_listing_id'] = NULL;
        }

        $dataForAdd['creator'] = $creator;
        $dataForAdd['initiator'] = $initiator;

        if (!is_null($actionId)) {
            $dataForAdd['action_id'] = (int)$actionId;
        } else {
            $dataForAdd['action_id'] = NULL;
        }
        
        if (!is_null($action)) {
            $dataForAdd['action'] = (int)$action;
        } else {
            $dataForAdd['action'] = self::ACTION_UNKNOWN;
        }

        if (!is_null($description)) {
            $dataForAdd['description'] = $description;
        } else {
            $dataForAdd['description'] = NULL;
        }
        
        if (!is_null($type)) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if (!is_null($priority)) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        return $dataForAdd;
    }

    private function createMessage($dataForAdd)
    {
        if (!is_null($dataForAdd['ebay_listing_id'])) {
            $ebayListing = Mage::getModel('M2ePro/EbayListings')->load($dataForAdd['ebay_listing_id']);
            $dataForAdd['title'] = $ebayListing->getData('ebay_title');
        }
        
        Mage::getModel('M2ePro/EbayListingsLogs')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    //####################################
}