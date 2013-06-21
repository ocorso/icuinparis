<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Templates extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    private $startTime = NULL;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $startMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/start/','mode');
        $endMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/end/','mode');
        $inspectorMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/inspector/','mode');
        $reviseMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/revise/','mode');
        $relistMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/relist/','mode');
        $stopMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/stop/','mode');
        
        if (!$startMode && !$endMode && !$inspectorMode && !$reviseMode && !$relistMode && !$stopMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        $this->createEbayActions();
        //---------------------------

        // GET TEMPLATES
        //---------------------------
        $this->_profiler->addEol();
        $synchronizations = $this->getTemplatesWithListings();
        Mage::register('synchTemplatesArray',$synchronizations);

        $this->_lockItem->setPercents(self::PERCENTS_START + 5);
        $this->_lockItem->activate();
        //---------------------------

        // Save start time stamp
        $this->startTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        // RUN CHILD SYNCH
        //---------------------------
        if ($startMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_Start();
            $tempSynch->process();
        }

        if ($endMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_End();
            $tempSynch->process();
        }

        if ($inspectorMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_Inspector();
            $tempSynch->process();
        }

        if ($reviseMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_Revise();
            $tempSynch->process();
        }

        if ($relistMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_Relist();
            $tempSynch->process();
        }

        if ($stopMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates_Stop();
            $tempSynch->process();
        }
        //---------------------------

        // Clear products changes
        Mage::getModel('M2ePro/ProductsChanges')->clearAll(Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_SYNCHRONIZATION);
        Mage::getModel('M2ePro/ProductsChanges')->clearAll(Ess_M2ePro_Model_ProductsChanges::CREATOR_TYPE_OBSERVER, $this->startTime);
            
        // UNSET TEMPLATES
        //---------------------------
        Mage::unregister('synchTemplatesArray');
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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_TEMPLATES);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Templates Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('Templates Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Templates Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Templates Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_UNKNOWN);
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
        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__,'Apply products changes on ebay');

        $result = $this->_ebayActions->execute($this->_lockItem,
                                               self::PERCENTS_START + 70,
                                               self::PERCENTS_END);

        $startLink = '<a href="route:*/adminhtml_logs/listings;back:*/adminhtml_logs/synchronizations">';
        $endLink = '</a>';

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            $tempString = Mage::getModel('M2ePro/LogsBase')->encodeDescription(
                // Parser hack -> Mage::helper('M2ePro')->__('Task "Templates Synchronization" has completed with errors. View %sl%listings log%el% for details.');
                'Task "Templates Synchronization" has completed with errors. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Synchronization_Logs::TYPE_ERROR,
                                     Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_HIGH);
            $this->_profiler->addTitle('Updating products on ebay ended with errors.');
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            $tempString = Mage::getModel('M2ePro/LogsBase')->encodeDescription(
                // Parser hack -> Mage::helper('M2ePro')->__('Task "Templates Synchronization" has completed with warnings. View %sl%listings log%el% for details.');
                'Task "Templates Synchronization" has completed with warnings. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Synchronization_Logs::TYPE_WARNING,
                                     Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_MEDIUM);
            $this->_profiler->addTitle('Updating products on ebay ended with warnings.');
        }

        $this->_ebayActions->removeAllProducts();
        Mage::unregister('synchEbayActions');
        $this->_ebayActions = NULL;

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function getTemplatesWithListings()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get templates with listings');

        // Get synchronizations array
        //--------------------------
        $synchronizationsArray = Mage::getModel('M2ePro/SynchronizationsTemplates')
                                    ->getCollection()
                                    ->toArray();

        if ((int)$synchronizationsArray['totalRecords'] <= 0) {
            return array();
        }
        //--------------------------

        // Get synchronizations
        //--------------------------
        $synchronizations = array();
        
        foreach ($synchronizationsArray['items'] as $synchronizationArray) {

            $synchronizationTemp = array(
                'instance' => Mage::getModel('M2ePro/SynchronizationsTemplates')->loadInstance($synchronizationArray['id']),
                'listings' => array()
            );

            // Get listings
            //--------------------------
            $listingsArray = Mage::getModel('M2ePro/Listings')
                                    ->getCollection()
                                    ->addFieldToFilter('synchronization_template_id', $synchronizationTemp['instance']->getData('id'))
                                    ->toArray();

            if ((int)$listingsArray['totalRecords'] <= 0) {
                continue;
            }

            foreach ($listingsArray['items'] as $listingArray) {

                /** @var $listingTemp Ess_M2ePro_Model_Listings */
                $listingTemp = Mage::getModel('M2ePro/Listings')->loadInstance($listingArray['id']);
                $listingTemp->setSynchronizationTemplate($synchronizationTemp['instance']);

                $synchronizationTemp['listings'][] = $listingTemp;
            }
            //--------------------------
            
            if (count($synchronizationTemp['listings']) != 0) {
                $synchronizations[] = $synchronizationTemp;
            }
        }
        //--------------------------
        
        $this->_profiler->saveTimePoint(__METHOD__);
        
        return $synchronizations;
    }

    //####################################
}