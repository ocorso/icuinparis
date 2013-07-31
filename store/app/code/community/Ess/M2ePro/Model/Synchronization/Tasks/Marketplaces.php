<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $defaultMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/marketplaces/default/','mode');
        $detailsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/marketplaces/details/','mode');
        $categoriesMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/marketplaces/categories/','mode');
        if (!$defaultMode && !$detailsMode && !$categoriesMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($defaultMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces_Default();
            $tempSynch->process();
        }

        if ($detailsMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces_Details();
            $tempSynch->process();
        }

        if ($categoriesMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces_Categories();
            $tempSynch->process();
        }
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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_MARKETPLACES);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Marketplaces Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('Marketplaces Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Marketplaces Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Marketplaces Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################
}