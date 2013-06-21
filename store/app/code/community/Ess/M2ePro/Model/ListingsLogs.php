<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Model_ListingsLogs extends Ess_M2ePro_Model_LogsBase
{
    const ACTION_UNKNOWN = 1;
    const _ACTION_UNKNOWN = 'System';

    const ACTION_ADD_LISTING = 2;
    const _ACTION_ADD_LISTING = 'Add new listing';
    const ACTION_DELETE_LISTING = 3;
    const _ACTION_DELETE_LISTING = 'Delete existing listing';

    const ACTION_ADD_PRODUCT_TO_LISTING = 4;
    const _ACTION_ADD_PRODUCT_TO_LISTING = 'Add product to listing';
    const ACTION_DELETE_PRODUCT_FROM_LISTING = 5;
    const _ACTION_DELETE_PRODUCT_FROM_LISTING = 'Delete item from listing';

    const ACTION_ADD_PRODUCT_TO_MAGENTO = 6;
    const _ACTION_ADD_PRODUCT_TO_MAGENTO = 'Add new product to magento store';
    const ACTION_DELETE_PRODUCT_FROM_MAGENTO = 7;
    const _ACTION_DELETE_PRODUCT_FROM_MAGENTO = 'Delete existing product from magento store';

    const ACTION_CHANGE_PRODUCT_PRICE = 8;
    const _ACTION_CHANGE_PRODUCT_PRICE = 'Change of product price in magento store';
    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 9;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE = 'Change of product special price in magento store';
    const ACTION_CHANGE_PRODUCT_QTY = 10;
    const _ACTION_CHANGE_PRODUCT_QTY = 'Change of product qty in magento store';
    const ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 11;
    const _ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY = 'Change of product stock availability in magento store';
    const ACTION_CHANGE_PRODUCT_STATUS = 12;
    const _ACTION_CHANGE_PRODUCT_STATUS = 'Change of product status in magento store';
    
    const ACTION_LIST_PRODUCT_ON_EBAY = 13;
    const _ACTION_LIST_PRODUCT_ON_EBAY = 'List item on eBay';
    const ACTION_RELIST_PRODUCT_ON_EBAY = 14;
    const _ACTION_RELIST_PRODUCT_ON_EBAY = 'Relist item on eBay';
    const ACTION_RESIVE_PRODUCT_ON_EBAY = 15;
    const _ACTION_RESIVE_PRODUCT_ON_EBAY = 'Revise item on eBay';
    const ACTION_STOP_PRODUCT_ON_EBAY = 16;
    const _ACTION_STOP_PRODUCT_ON_EBAY = 'Stop item on eBay';
    const ACTION_STOP_AND_REMOVE_PRODUCT = 17;
    const _ACTION_STOP_AND_REMOVE_PRODUCT = 'Stop and remove item';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 19;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE = 'Change of product special price from date in magento store';

    const ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 20;
    const _ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE = 'Change of product special price to date in magento store';

    const ACTION_CHANGE_CUSTOM_ATTRIBUTE = 18;
    const _ACTION_CHANGE_CUSTOM_ATTRIBUTE = 'Change of product custom attribute in magento store';
    
    //####################################
    
	public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsLogs');
    }

    //####################################

    public function addListingMessage($listingId , $initiator = parent::INITIATOR_UNKNOWN , $actionId = NULL , $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(  $listingId ,
                                              $this->makeCreator() ,
                                              $initiator ,
                                              NULL ,
                                              $actionId ,
                                              $action ,
                                              $description ,
                                              $type ,
                                              $priority );

        $this->createMessage($dataForAdd);
    }

    public function addProductMessage($listingId , $productId , $initiator = parent::INITIATOR_UNKNOWN , $actionId = NULL , $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(  $listingId ,
                                              $this->makeCreator() ,
                                              $initiator ,
                                              $productId ,
                                              $actionId ,
                                              $action ,
                                              $description ,
                                              $type ,
                                              $priority );

        $this->createMessage($dataForAdd);
    }

    public function clearMessages($listingId = NULL)
    {
        $columnName = !is_null($listingId) ? 'listing_id' : NULL;
        parent::clearMessagesByTable('M2ePro/ListingsLogs',$columnName,$listingId);
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

    public function updateProductTitle($productId , $title)
    {
         if ($title == '') {
             return false;
         }

        $this->getResource()
             ->getReadConnection()
             ->update($this->getResource()->getMainTable(),
                      array('product_title'=>$title),array('product_id = ?'=>(int)$productId));

        return true;
    }

    public function updateListingTitle($listingId , $title)
    {
        if ($title == '') {
             return false;
        }

        $this->getResource()
             ->getReadConnection()
             ->update($this->getResource()->getMainTable(),
                      array('listing_title'=>$title),array('listing_id = ?'=>(int)$listingId));

        return true;
    }

    //####################################

    private function makeDataForAdd($listingId , $creator , $initiator = parent::INITIATOR_UNKNOWN , $productId = NULL , $actionId = NULL, $action = NULL , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = array();

        $dataForAdd['listing_id'] = (int)$listingId;
        $dataForAdd['creator'] = $creator;
        $dataForAdd['initiator'] = $initiator;

        if (!is_null($productId)) {
            $dataForAdd['product_id'] = (int)$productId;
        } else {
            $dataForAdd['product_id'] = NULL;
        }
        
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
        $listing = Mage::getModel('M2ePro/Listings')
                            ->load($dataForAdd['listing_id']);

        $dataForAdd['listing_title'] = $listing->getData('title');

        if (isset($dataForAdd['product_id'])) {
            $dataForAdd['product_title'] = Mage::getModel('M2ePro/MagentoProduct')->getNameByProductId($dataForAdd['product_id']);
        } else {
            unset($dataForAdd['product_title']);
        }

        Mage::getModel('M2ePro/ListingsLogs')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    //####################################
}