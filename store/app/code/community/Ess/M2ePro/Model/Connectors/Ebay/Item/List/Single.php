<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_List_Single extends Ess_M2ePro_Model_Connectors_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_ListingsLogs::ACTION_LIST_PRODUCT_ON_EBAY;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->listingProduct->isListable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            
            return false;
        }

        return true;
    }
    
    protected function getRequestData()
    {
        $productVariations = $this->listingProduct->getListingsProductsVariations(true);
        
        foreach ($productVariations as $variation) {
           /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
           $variation->deleteInstance();
        }

        $requestData = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                ->getListRequestData($this->listingProduct,$this->params);
        
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
                'ebay_item_id' => $response['ebay_item_id'],
                'ebay_start_date_raw' => $response['ebay_start_date_raw'],
                'ebay_end_date_raw' => $response['ebay_end_date_raw'],
                'is_eps_ebay_images_mode' => $response['is_eps_ebay_images_mode']
            );
            
            Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                ->updateAfterListAction($this->listingProduct,
                                                        array_merge($this->params,$tempParams));

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );

            $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
        }

        return $response;
    }

    // ########################################
}