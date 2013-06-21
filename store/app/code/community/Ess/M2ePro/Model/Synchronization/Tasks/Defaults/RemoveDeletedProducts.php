<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_RemoveDeletedProducts extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 5;
    const PERCENTS_INTERVAL = 5;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        $this->createEbayActions();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->executeEbayActions();
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Remove Deleted Products');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Remove Deleted Products" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Remove Deleted Products" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function createEbayActions()
    {
        $ebayActionsModel = Mage::getModel('M2ePro/Synchronization_EbayActions');
        $ebayActionsModel->removeAllProducts();
        Mage::register('synchEbayActions',$ebayActionsModel);
        $this->_ebayActions = $ebayActionsModel;
    }

    private function executeEbayActions()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Stop products from ebay');

        $result = $this->_ebayActions->execute($this->_lockItem,
                                               self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/3,
                                               self::PERCENTS_END);

        $startLink = '<a href="route:*/adminhtml_logs/listings;back:*/adminhtml_logs/synchronizations">';
        $endLink = '</a>';

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            $tempString = Mage::getModel('M2ePro/LogsBase')->encodeDescription(
                // Parser hack -> Mage::helper('M2ePro')->__('Task "Remove Deleted Products" is completed with errors. View %sl%listings log%el% for details.');
                'Task "Remove Deleted Products" is completed with errors. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Synchronization_Logs::TYPE_ERROR,
                                     Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_HIGH);
            $this->_profiler->addTitle('Task "Remove Deleted Products" is completed with errors.');
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            $tempString = Mage::getModel('M2ePro/LogsBase')->encodeDescription(
                // Parser hack -> Mage::helper('M2ePro')->__('Task "Remove Deleted Products" is completed with warnings. View %sl%listings log%el% for details.');
                'Task "Remove Deleted Products" is completed with warnings. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Synchronization_Logs::TYPE_WARNING,
                                     Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_MEDIUM);
            $this->_profiler->addTitle('Stopping removed items on ebay ended with warnings.');
        }

        $this->_ebayActions->removeAllProducts();
        Mage::unregister('synchEbayActions');
        $this->_ebayActions = NULL;

        $this->executeEbayItems();
        $this->executeProductsChanges();

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function execute()
    {
        $this->executeListingsProducts();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/3);
        $this->_lockItem->activate();

        $this->executeListingsProductsVariationsOptions();
    }

    //####################################

    private function executeListingsProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check deleted listings products');

        // Get all deleted products
        //---------------------------
        $deletedProducts = Mage::getModel('M2ePro/ProductsChanges')
                                    ->getCollection()
                                    ->addFieldToFilter('action', Ess_M2ePro_Model_ProductsChanges::ACTION_DELETE)
                                    ->toArray();

        foreach ($deletedProducts['items'] as $item) {

            // Delete all listings products
            //---------------------------
            $deletedListingsProducts = Mage::getModel('M2ePro/ListingsProducts')
                                                ->getCollection()
                                                ->addFieldToFilter('product_id', $item['product_id'])
                                                ->toArray();

            foreach ($deletedListingsProducts['items'] as $listingProduct) {

                /** @var $instance Ess_M2ePro_Model_ListingsProducts */
                $instance = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($listingProduct['id']);

                if (!$instance->isStoppable()) {
                    $instance->deleteInstance();
                    continue;
                }

                $this->_ebayActions->setProduct($instance, Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP, array('remove' => true));
            }
            //---------------------------
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeListingsProductsVariationsOptions()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check deleted listings products variations options');

        // Get all deleted products
        //---------------------------
        $deletedProducts = Mage::getModel('M2ePro/ProductsChanges')
                                    ->getCollection()
                                    ->addFieldToFilter('action', Ess_M2ePro_Model_ProductsChanges::ACTION_DELETE)
                                    ->toArray();

        foreach ($deletedProducts['items'] as $item) {

            // Delete all related listings products
            //---------------------------
            $deletedVariationsOptions = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')
                                                ->getCollection()
                                                ->addFieldToFilter('product_id', $item['product_id'])
                                                ->toArray();

            foreach ($deletedVariationsOptions['items'] as $option) {

                try {
                    /** @var $instance Ess_M2ePro_Model_ListingsProductsVariationsOptions */
                    $instance = Mage::getModel('M2ePro/ListingsProductsVariationsOptions')->loadInstance($option['id']);
                } catch (Exception $exception) {
                    continue;
                }

                /** @var $listingProductInstance Ess_M2ePro_Model_ListingsProducts */
                $listingProductInstance = $instance->getListingProduct();
                
                if (!$listingProductInstance->isStoppable()) {
                    $instance->getListingProductVariation()->deleteInstance();
                    continue;
                }

                $this->_ebayActions->setProduct($listingProductInstance, Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP, array());
            }
            //---------------------------
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //-----------------------------------

    private function executeEbayItems()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check deleted ebay items');

        // Get all deleted products
        //---------------------------
        $deletedProducts = Mage::getModel('M2ePro/ProductsChanges')
                                    ->getCollection()
                                    ->addFieldToFilter('action', Ess_M2ePro_Model_ProductsChanges::ACTION_DELETE)
                                    ->getItems();

        foreach ($deletedProducts as $deletedItem) {

            $tempEbayItems = Mage::getModel('M2ePro/EbayItems')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', (int)$deletedItem->getData('product_id'))
                                    ->getItems();

            foreach ($tempEbayItems as $tempItem) {
                $tempItem->deleteInstance();
            }
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeProductsChanges()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check deleted products changes');

        // Get all deleted products
        //---------------------------
        $deletedProducts = Mage::getModel('M2ePro/ProductsChanges')
                                    ->getCollection()
                                    ->addFieldToFilter('action', Ess_M2ePro_Model_ProductsChanges::ACTION_DELETE)
                                    ->getItems();

        foreach ($deletedProducts as $deletedItem) {

            $tempChangedProducts = Mage::getModel('M2ePro/ProductsChanges')
                                        ->getCollection()
                                        ->addFieldToFilter('product_id', (int)$deletedItem->getData('product_id'))
                                        ->getItems();

            foreach ($tempChangedProducts as $tempItem) {
                $tempItem->delete();
            }
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}