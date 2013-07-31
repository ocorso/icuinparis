<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_List_Multiple extends Ess_M2ePro_Model_Connectors_Ebay_Item_MultipleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','multiple');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_ListingsLogs::ACTION_LIST_PRODUCT_ON_EBAY;
    }
    
    // ########################################

    protected function validateNeedRequestSend()
    {
        $countListedItems = 0;

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            if (!$listingProduct->isListable()) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($listingProduct, $message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);

                $countListedItems++;
            }
        }

        if (count($this->listingsProducts) <= $countListedItems) {
            return false;
        }

        return true;
    }
    
    protected function getRequestData()
    {
        $requestData = array();

        $requestData['products'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */
            if ($listingProduct->isListable()) {
                
                $productVariations = $listingProduct->getListingsProductsVariations(true);

                foreach ($productVariations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_ListingsProductsVariations */
                    $variation->deleteInstance();
                }

                $requestData['products'][$listingProduct->getId()] = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                                                                ->getListRequestData($listingProduct,$this->params);
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
                        'ebay_item_id' => $tempResultProduct['ebay_item_id'],
                        'ebay_start_date_raw' => $tempResultProduct['ebay_start_date_raw'],
                        'ebay_end_date_raw' => $tempResultProduct['ebay_end_date_raw'],
                        'is_eps_ebay_images_mode' => $tempResultProduct['is_eps_ebay_images_mode']
                    );

                    Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                ->updateAfterListAction($listingProductInArray,
                                                        array_merge($this->params,$tempParams));

                    $message = array(
                        // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
                        parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
                        parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                    );
                    
                    $this->addListingsProductsLogsMessage($listingProductInArray, $message,
                                                          Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
                }
            }
        }

        return $response;
    }

    // ########################################
}