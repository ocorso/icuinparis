<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces_Default extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 5;
    const PERCENTS_INTERVAL = 5;

    const INTERVAL = 86400;

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
        $this->_profiler->addTitle('Default Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Default" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Default" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare last time for first time
        $this->checkAndPrepareLastTime();

        $lastTime = strtotime($this->getCheckLastTime());
        if ($lastTime + self::INTERVAL > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return;
        }

        $this->updateMarketplacesList();

        $this->setCheckLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
    }
    
    //####################################

    private function getCheckLastTime()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/marketplaces/default/','last_time');
    }

    private function setCheckLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $time = strftime("%Y-%m-%dT%H:%M:%S", $time);
        }
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/settings/marketplaces/default/','last_time',$time);
    }

    private function checkAndPrepareLastTime()
    {
        $lastTime = $this->getCheckLastTime();
        if (is_null($lastTime) || $lastTime == '') {
            $lastTime = new DateTime();
            $lastTime->modify("-1 year");
            $this->setCheckLastTime($lastTime);
        }
    }

    //####################################

    private function updateMarketplacesList()
    {
        $this->_profiler->addTimePoint(__METHOD__.'get','Get marketplaces list from server');

        $marketplaces = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                ->processVirtual('marketplace','get','list',
                                                  array(),'marketplaces',
                                                  NULL,NULL,NULL);
        is_null($marketplaces) && $marketplaces = array();

        $this->_profiler->saveTimePoint(__METHOD__.'get');

        $this->_profiler->addTimePoint(__METHOD__.'save','Save marketplaces list from server');

        foreach ($marketplaces as $marketplace) {

            unset($marketplace['create_date']);
            unset($marketplace['update_date']);

            $marketplaceModel = Mage::getModel('M2ePro/Marketplaces')->load($marketplace['id']);

            if (!is_null($marketplaceModel->getId())) {
                $marketplace['status'] = $marketplaceModel->getData('status');
                $marketplaceModel->addData($marketplace)->save();
            } else {
                $marketplace['status'] = 0;
                Mage::getModel('M2ePro/Marketplaces')
                            ->setData($marketplace)
                            ->save();
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__.'save');
    }

    //####################################
}