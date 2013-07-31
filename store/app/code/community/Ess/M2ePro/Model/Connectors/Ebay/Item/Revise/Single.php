<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Item_Revise_Single extends Ess_M2ePro_Model_Connectors_Ebay_Item_SingleAbstract
{
    private $requestDataTemp = NULL;

    // ########################################

    protected function getCommand()
    {
        return array('item','update','revise');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_ListingsLogs::ACTION_RESIVE_PRODUCT_ON_EBAY;
    }
    
    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->listingProduct->isRevisable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            
            return false;
        }

        if (Mage::helper('M2ePro/Variations')->isAddedNewVariationsAttributes($this->listingProduct)) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('New variation attribute added. Please stop and relist product.');
                parent::MESSAGE_TEXT_KEY => 'New variation attribute added. Please stop and relist product.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_ListingsLogs::PRIORITY_HIGH);
            
            return false;
        }

        return true;
    }
    
    protected function getRequestData()
    {
        $this->requestDataTemp = Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                                    ->getReviseRequestData($this->listingProduct,$this->params);
        return $this->requestDataTemp;
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
                'ebay_start_date_raw' => $response['ebay_start_date_raw'],
                'ebay_end_date_raw' => $response['ebay_end_date_raw']
            );

            if ($response['already_stop']) {
                
                $tempParams['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_EBAY;
                Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                        ->updateAfterStopAction($this->listingProduct,
                                                array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item already was stopped on eBay');
                    parent::MESSAGE_TEXT_KEY => 'Item already was stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            } else {

                Mage::getModel('M2ePro/Connectors_Ebay_Item_Helper')
                            ->updateAfterReviseAction($this->listingProduct,
                                                      array_merge($this->params,$tempParams));

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
                    parent::MESSAGE_TEXT_KEY => $this->getSuccessfullyMessage(),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );
                
                $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                      Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM);
            }
        }

        return $response;
    }

    // ########################################

    protected function getSuccessfullyMessage()
    {
        // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
        $defaultMessage = 'Item was successfully revised';

        if (isset($this->params['all_data']) || !isset($this->params['only_data'])) {
            return $defaultMessage;
        }

        $tempOnlyString = '';

        if (isset($this->params['only_data']['variations']) &&
            isset($this->requestDataTemp['is_variation_item']) &&
            $this->requestDataTemp['is_variation_item']) {

            // Parser hack -> Mage::helper('M2ePro')->__('variations');
            $tempStr = 'variations';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['qty']) &&
            (!isset($this->requestDataTemp['is_variation_item']) ||
             !$this->requestDataTemp['is_variation_item'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('qty');
            $tempStr = 'qty';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['price']) &&
            (!isset($this->requestDataTemp['is_variation_item']) ||
             !$this->requestDataTemp['is_variation_item'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('price');
            $tempStr = 'price';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['title'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('title');
            $tempStr = 'title';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['subtitle'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('subtitle');
            $tempStr = 'subtitle';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['only_data']['description'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('description');
            $tempStr = 'description';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if ($tempOnlyString != '') {
            // Parser hack -> Mage::helper('M2ePro')->__('was successfully revised');
            return $tempOnlyString.' was successfully revised';
        }

        return $defaultMessage;
    }

    // ########################################
}