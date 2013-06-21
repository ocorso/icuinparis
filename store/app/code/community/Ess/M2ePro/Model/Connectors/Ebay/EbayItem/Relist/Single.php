<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Relist_Single extends Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','relist');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_EbayListingsLogs::ACTION_RELIST_PRODUCT_ON_EBAY;
    }
    
    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->ebayListing->isRelistable()) {
            
            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('The item either is listed, not listed yet or not available');
                parent::MESSAGE_TEXT_KEY => 'The item either is listed, not listed yet or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addProductsLogsMessage($this->ebayListing,$message,
                                          Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM);

            return false;
        }

        return true;
    }
    
    protected function getRequestData()
    {
        $requestData = array();
        $requestData['item_id'] = $this->ebayListing->getEbayItem();
        $requestData['title'] = $this->ebayListing->getEbayTitle();
        $requestData['qty'] = $this->ebayListing->getEbayQty();
        return $requestData;
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            if ($response['already_active']) {

                $dataForUpdate = array(
                    'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED,
                    'ebay_start_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_start_date_raw']),
                    'ebay_end_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_end_date_raw'])
                );

                $this->ebayListing->addData($dataForUpdate)->save();

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item already was started on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item already was started on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addProductsLogsMessage($this->ebayListing,$message,
                                              Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM);

            } else {

                $newEbayOldItems = $this->ebayListing->getData('ebay_old_items');
                is_null($newEbayOldItems) && $newEbayOldItems = '';
                $newEbayOldItems != '' && $newEbayOldItems .= ',';
                $newEbayOldItems .= $this->ebayListing->getData('ebay_item');

                $dataForUpdate = array(
                    'ebay_item' => $response['ebay_item_id'],
                    'ebay_old_items' => $newEbayOldItems,
                    'ebay_qty_sold' => 0,
                    'ebay_bids' => 0,
                    'ebay_start_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_start_date_raw']),
                    'ebay_end_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_end_date_raw']),
                    'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED
                );

                $this->ebayListing->addData($dataForUpdate)->save();

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully relisted');
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully relisted',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addProductsLogsMessage($this->ebayListing,$message,
                                              Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM);

            }
        }

        return $response;
    }

    // ########################################
}