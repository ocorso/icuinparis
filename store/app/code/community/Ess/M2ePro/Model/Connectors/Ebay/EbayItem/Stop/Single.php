<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Stop_Single extends Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_EbayListingsLogs::ACTION_STOP_PRODUCT_ON_EBAY;
    }
    
    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->ebayListing->isStoppable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );
            $this->addProductsLogsMessage($this->ebayListing,$message,Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_MEDIUM);
            return false;
        }
        
        return true;
    }

    protected function getRequestData()
    {
        $requestData = array();
        $requestData['item_id'] = $this->ebayListing->getEbayItem();
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

            $dataForUpdate = array(
                'status' => Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED,
                'ebay_end_date' => Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($response['ebay_end_date_raw'])
            );

            $this->ebayListing->addData($dataForUpdate)->save();

            if ($response['already_stop']) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item already was stopped on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item already was stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addProductsLogsMessage($this->ebayListing, $message,
                                              Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            } else {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addProductsLogsMessage($this->ebayListing, $message,
                                              Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            }
        }

        return $response;
    }

    // ########################################
}