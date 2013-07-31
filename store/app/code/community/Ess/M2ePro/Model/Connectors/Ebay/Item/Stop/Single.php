<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_Stop_Single extends Ess_M2ePro_Model_Connectors_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
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
        if (!$this->listingProduct->isStoppable()) {

            if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {
                
                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            }

            if (isset($this->params['remove']) && (bool)$this->params['remove']) {
                $this->listingProduct->deleteInstance();
            }
            
            return false;
        }
        
        return true;
    }

    protected function getRequestData()
    {
        $requestData = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                ->getStopRequestData($this->listingProduct,$this->params);
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

            $tempParams = array(
                'ebay_end_date_raw' => $response['ebay_end_date_raw']
            );

            if ($response['already_stop']) {
                $tempParams['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_EBAY;
            }
            
            Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                        ->updateAfterStopAction($this->listingProduct,
                                                array_merge($this->params,$tempParams));

            if ($response['already_stop']) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item already was stopped on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item already was stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            } else {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            }
        }

        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            
            $this->listingProduct->addData(array('status'=>Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED))->save();
            $this->listingProduct->deleteInstance();
        }

        return $response;
    }

    // ########################################
}