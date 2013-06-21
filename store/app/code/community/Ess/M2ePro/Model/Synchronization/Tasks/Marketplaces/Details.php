<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces_Details extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 5;
    const PERCENTS_END = 25;
    const PERCENTS_INTERVAL = 20;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Details Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive Details" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Receive Details" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare MySQL data
        //-----------------------
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');
        $tableShippings = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings');
        $tableShippingsCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings_categories');
        //-----------------------

        // Get marketplaces
        //-----------------------
        $marketplacesCollection = Mage::getModel('M2ePro/Marketplaces')
                ->getCollection()
                ->addFieldToFilter('status',Ess_M2ePro_Model_Marketplaces::STATUS_ENABLE)
                ->setOrder('sorder','ASC')
                ->setOrder('title','ASC');
        if (isset($this->_params['marketplace_id'])) {
            $marketplacesCollection->addFieldToFilter('id',(int)$this->_params['marketplace_id']);
        }
        $marketplaces = $marketplacesCollection->getItems();
        if (count($marketplaces) == 0) {
            return;
        }
        if (isset($this->_params['marketplace_id'])) {
            foreach ($marketplaces as $marketplace) {
                $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($marketplace->getTitle()));
            }
        }
        //-----------------------

        // Get and update details
        //-----------------------
        $iteration = 1;
        $percentsForStep = self::PERCENTS_INTERVAL / (count($marketplaces)*2);

        foreach ($marketplaces as $marketplace) {

            if ($iteration != 1) {
                $this->_profiler->addEol();
            }

            $this->_profiler->addTitle('Starting marketplace "'.$marketplace->getTitle().'"');
            
            $this->_profiler->addTimePoint(__METHOD__.'get'.$marketplace->getId(),'Get details from ebay');

            $tempString = str_replace('%mrk%',Mage::helper('M2ePro')->__($marketplace->getTitle()),Mage::helper('M2ePro')->__('The "Receive Details" action for marketplace: "%mrk%" is started. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            // Create connector
            //-----------------------
            $details = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('marketplace','get','info',
                                                  array('marketplace'=>$marketplace['id'],'include_details'=>1),'info',
                                                  NULL,NULL,NULL);
            if (is_null($details)) {
                $details = array();
            } else {
                $details = $details['details'];
            }
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;

            $this->_profiler->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save details to DB');

            $tempString = str_replace('%mrk%',Mage::helper('M2ePro')->__($marketplace->getTitle()),Mage::helper('M2ePro')->__('The "Receive Details" action for marketplace: "%mrk%" is in data processing mode. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            // Save marketplaces
            //-----------------------
            Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($tableMarketplaces,array('marketplace_id = ?'=>$marketplace->getId()));

            $data = array(
                'marketplace_id'      => $marketplace->getId(),
                'dispatch'            => json_encode($details['dispatch']),
                'packages'            => json_encode($details['packages']),
                'return_policy'       => json_encode($details['return_policy']),
                'listing_features'    => json_encode($details['listing_features']),
                'payments'            => json_encode($details['payments']),
                'shipping_locations'  => json_encode($details['shipping_locations']),
                'shipping_locations_exclude' => json_encode($details['shipping_locations_exclude']),
                'categories_features_defaults' => json_encode($details['categories_features_defaults'])
            );

            $connWrite->insertOnDuplicate($tableMarketplaces, $data);
            //-----------------------

            // Save shippings
            //-----------------------
            Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($tableShippings,array('marketplace_id = ?'=>$marketplace->getId()));

            foreach ($details['shipping'] as $data) {
                $connWrite->insertOnDuplicate($tableShippings, $data);
            }
            //-----------------------

            // Save shipping categories
            //-----------------------
            Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($tableShippingsCategories,array('marketplace_id = ?'=>$marketplace->getId()));
            
            foreach ($details['shipping_categories'] as $data) {
                $connWrite->insertOnDuplicate($tableShippingsCategories, $data);
            }
            //-----------------------

            $this->_profiler->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->_lockItem->setPercents(self::PERCENTS_START + $iteration * $percentsForStep);
            $this->_lockItem->activate();
            $iteration++;
        }
        //-----------------------

        // Send success message
        //-----------------------
        $logMarketplacesString = '';
        foreach ($marketplaces as $marketplace) {
            if ($logMarketplacesString != '') {
                $logMarketplacesString .= ', ';
            }
            $logMarketplacesString .= $marketplace->getTitle();
        }

        // Parser hack -> Mage::helper('M2ePro')->__('The "Receive Details" action for marketplace: "%mrk%"  has been successfully completed.');
        $tempString = Mage::getModel('M2ePro/LogsBase')->encodeDescription(
            'The "Receive Details" action for marketplace: "%mrk%"  has been successfully completed.',
            array('mrk'=>$logMarketplacesString)
        );
        $this->_logs->addMessage($tempString,
                                 Ess_M2ePro_Model_Synchronization_Logs::TYPE_SUCCESS,
                                 Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_LOW);
        //-----------------------
    }

    //####################################
}