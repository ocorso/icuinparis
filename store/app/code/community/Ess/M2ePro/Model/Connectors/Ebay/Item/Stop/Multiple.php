<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_Stop_Multiple extends Ess_M2ePro_Model_Connectors_Ebay_Item_MultipleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','ends');
    }

    protected function getListingsLogsCurrentAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_ListingsLogs::ACTION_STOP_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_ListingsLogs::ACTION_STOP_PRODUCT_ON_EBAY;
    }
    
    // ########################################

    protected function validateNeedRequestSend()
    {
        $countStoppedItems = 0;
        
        foreach ($this->listingsProducts as $listingProduct) {
            
            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            if (!$listingProduct->isStoppable()) {

                if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {
                    
                    $message = array(
                        // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                        parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                        parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                    );

                    $this->addListingsProductsLogsMessage($listingProduct, $message,
                                                          Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
                }

                $countStoppedItems++;
            }
        }

        if (count($this->listingsProducts) <= $countStoppedItems) {

            if (isset($this->params['remove']) && (bool)$this->params['remove']) {
                
                foreach ($this->listingsProducts as $listingProduct) {
                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
                    $listingProduct->deleteInstance();
                }
            }
            return false;
        }

        return true;
    }
    
    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            if ($listingProduct->isStoppable()) {
                
                $tempData = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                    ->getStopRequestData($listingProduct,$this->params);
                
                $requestData['items'][$listingProduct->getId()] = $tempData['item_id'];
            }
        }

        return $requestData;
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if (isset($response['result'])) {

            foreach ($response['result'] as $tempIdProduct=>$tempResultProduct) {

                $listingProductInArray = NULL;
                foreach ($this->listingsProducts as $listingProduct) {
                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
                    if ($tempIdProduct == $listingProduct->getId()) {
                        $listingProductInArray = $listingProduct;
                        break;
                    }
                }

                if (is_null($listingProductInArray)) {
                    continue;
                }

                $resultSuccess = true;
                if (isset($tempResultProduct['messages'])){
                    foreach ($tempResultProduct['messages'] as $message) {
                        if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                            $resultSuccess = false;
                            break;
                        }
                    }
                }

                if ($resultSuccess) {

                    $tempParams = array(
                        'ebay_end_date_raw' => $tempResultProduct['ebay_end_date_raw']
                    );

                    if ($tempResultProduct['already_stop']) {
                        $tempParams['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_EBAY;
                    }

                    Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                            ->updateAfterStopAction($listingProductInArray,
                                                    array_merge($this->params,$tempParams));

                    if ($tempResultProduct['already_stop']) {

                        $message = array(
                            // Parser hack -> Mage::helper('M2ePro')->__('Item already was stopped on eBay');
                            parent::MESSAGE_TEXT_KEY => 'Item already was stopped on eBay',
                            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                        );

                        $this->addListingsProductsLogsMessage($listingProductInArray, $message,
                                                              Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
                    } else {

                        $message = array(
                            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                            parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                        );

                        $this->addListingsProductsLogsMessage($listingProductInArray, $message,
                                                              Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
                    }
                }
            }
        }

        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            
            foreach ($this->listingsProducts as $listingProduct) {
                /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
                $listingProduct->addData(array('status'=>Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED))->save();
                $listingProduct->deleteInstance();
            }
        }

        return $response;
    }

    // ########################################
}