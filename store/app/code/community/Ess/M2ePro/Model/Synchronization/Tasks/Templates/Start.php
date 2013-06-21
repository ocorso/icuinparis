<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates_Start extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 5;
    const PERCENTS_END = 10;
    const PERCENTS_INTERVAL = 5;

    private $_synchronizations = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::registry('synchTemplatesArray');
    }

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
        $this->_profiler->addTitle('Initial Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Initial" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Initial" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->executeCheckStart();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/2);
        $this->_lockItem->activate();

        $this->executeListAllStoppedItems();
    }

    //####################################

    private function executeCheckStart()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check start synchronizations');

        foreach ($this->_synchronizations as &$synchronization) {

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */

                if ($listing->isSynchronizationAlreadyStart()) {
                    continue;
                }

                if ($listing->getSynchronizationTimestampStart() > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                    continue;
                }

                $listing->setSynchronizationAlreadyStart(true);
                $listing->setSynchronizationOnlyStart(true);
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeListAllStoppedItems()
    {
        $this->_profiler->addTimePoint(__METHOD__,'List all items automatically');

        foreach ($this->_synchronizations as &$synchronization) {

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listings */
                
                if (!$listing->isSynchronizationAlreadyStart()) {
                    continue;
                }

                if (!$listing->isSynchronizationOnlyStart()) {
                    continue;
                }

                if (!$synchronization['instance']->isStartAutoList()) {
                    continue;
                }

                $listingsProducts = $listing->getListingsProducts(true,array('status'=>array('nin'=>array(Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED))));

                foreach ($listingsProducts as $listingProduct) {

                    /** @var $listingProduct Ess_M2ePro_Model_ListingsProducts */

                    if ($listingProduct->isListed()) {
                        continue;
                    }

                    $listingProduct->setListing($listing);

                    if (!$listingProduct->isListable()) {
                        continue;
                    }

                    $this->_ebayActions->setProduct($listingProduct,Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST,array());
                }
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}