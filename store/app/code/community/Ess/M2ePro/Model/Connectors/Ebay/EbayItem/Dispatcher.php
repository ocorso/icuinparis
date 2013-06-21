<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Dispatcher extends Mage_Core_Model_Abstract
{
    private $logsActionId = NULL;
    
    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_EbayListings $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $result = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR;

        $this->logsActionId = Mage::getModel('M2ePro/EbayListingsLogs')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);

        switch ($action) {
            case Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST:
                $result = $this->processProducts($products, 'Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Relist_Single', $params);
                break;

            case Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP:
                $result = $this->processProducts($products, 'Ess_M2ePro_Model_Connectors_Ebay_EbayItem_Stop_Single', $params);
                break;

            default;
                $result = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR;
                break;
        }

        return $result;
    }

    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    // ########################################

    /**
     * @param array $products
     * @param string $connectorNameSingle
     * @param array $params
     * @return int
     */
    protected function processProducts(array $products, $connectorNameSingle, array $params = array())
    {
        $results = array();
        
        if (count($products) == 0) {
            return Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::getMainStatus($results);
        }

        if (!class_exists($connectorNameSingle)) {
            return Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::getMainStatus($results);
        }

        $needRemoveLockItem = false;
        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>'listingsEbay'));
        if (!$lockItem->isExist()) {
            $lockItem->create();
            $lockItem->makeShutdownFunction();
            $needRemoveLockItem = true;
        }

        try {

            foreach ($products as $product) {
                $connector = new $connectorNameSingle($params,$product);
                $connector->process();
                $results[] = $connector->getStatus();
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            Mage::getModel('M2ePro/EbayListingsLogs')->addGlobalMessage(
                Ess_M2ePro_Model_LogsBase::INITIATOR_UNKNOWN,
                $this->logsActionId,
                Ess_M2ePro_Model_EbayListingsLogs::ACTION_UNKNOWN,
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_EbayListingsLogs::TYPE_ERROR,
                Ess_M2ePro_Model_EbayListingsLogs::PRIORITY_HIGH
            );

            $results[] = Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR;
        }

        $lockItem->isExist() && $needRemoveLockItem && $lockItem->remove();

        return Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::getMainStatus($results);
    }

    // ########################################

    protected function prepareProducts($products)
    {
        $productsTemp = array();

        if (!is_array($products)) {
            $products = array($products);
        }

        foreach ($products as $product) {
            if ($product instanceof Ess_M2ePro_Model_EbayListings) {
                $productsTemp[] = $product;
            } else {
                $productsTemp[] = Mage::getModel('M2ePro/EbayListings')->loadInstance((int)$product);
            }
        }

        return $productsTemp;
    }

    // ########################################
}