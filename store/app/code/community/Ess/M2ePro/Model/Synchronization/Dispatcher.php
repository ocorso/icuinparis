<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Dispatcher extends Mage_Core_Model_Abstract
{
    /**
     * @var array
     */
    private $_tasks = array();

    /**
     * @var null|int
     */
    private $_initiator = NULL;

    /**
     * @var array
     */
    private $_params = array();

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Dispatcher');

        $this->_initiator = Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_UNKNOWN;
    }

    //####################################

    public function process(array $tasks, $initiator = Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_UNKNOWN, array $params = array())
    {
        // Stop if tasks empty
        //---------------------------
        if (count($tasks) == 0) {
            return false;
        }
        $this->_tasks = $tasks;
        //---------------------------

        // Stop if wrong initiator
        //---------------------------
        if ($initiator !== Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_CRON &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_DEVELOPER &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_UNKNOWN) {
                return false;
        }
        $this->_initiator = $initiator;
        //---------------------------

        // Prepare params
        //---------------------------
        $this->_params = $params;
        //---------------------------

        // Execute before dispatch actions
        //---------------------------
        if (!$this->beforeDispatch()) {
            return false;
        }
        //---------------------------

        // Set memory limit
        //----------------------------------
        $memLimitResult = $this->setMemoryLimit();
        /*if ($memLimitResult === false) {
            Mage::registry('synchLogs')->addMessage('Set memory limit failed.',
                                                    Ess_M2ePro_Model_Synchronization_Logs::TYPE_WARNING,
                                                    Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_MEDIUM);
            Mage::registry('synchProfiler')->addTitle('Set memory limit failed.',Ess_M2ePro_Model_Profiler::TYPE_WARNING);
        }*/
        //----------------------------------

        try {

            // DEFAULTS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/defaults/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults();
                $tempSynch->process();
            }
            //---------------------------

            // ORDERS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::ORDERS);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/orders/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Orders();
                $tempSynch->process();
            }
            //---------------------------

            // TEMPLATES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/templates/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Templates();
                $tempSynch->process();
            }
            //---------------------------

            // FEEDBACKS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/feedbacks/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Feedbacks();
                $tempSynch->process();
            }
            //---------------------------

            // MESSAGES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/messages/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Messages();
                $tempSynch->process();
            }
            //---------------------------

            // MARKETPLACES SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/marketplaces/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Marketplaces();
                $tempSynch->process();
            }
            //---------------------------

            // EBAY LISTINGS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS);
            $tempMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/ebay_listings/','mode');
            if ($tempTask && $tempMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_EbayListings();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}
            
            Mage::registry('synchLogs')->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                    Ess_M2ePro_Model_Synchronization_Logs::TYPE_ERROR,
                                                    Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_HIGH);
            Mage::registry('synchProfiler')->addTitle(Mage::helper('M2ePro')->__($exception->getMessage()),Ess_M2ePro_Model_Profiler::TYPE_ERROR);

            return false;
        }

        return true;
    }

    //####################################

    private function beforeDispatch()
    {
        // Create and save tasks
        //----------------------------------
        Mage::register('synchTasks',$this->_tasks);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::register('synchInitiator',$this->_initiator);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::register('synchParams',$this->_params);
        //----------------------------------

        // Create and save profiler
        //----------------------------------
        $profilerParams = array();
        if ($this->_initiator == Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_USER) {
            $profilerParams['muteOutput'] = true;
        } else {
            $profilerParams['muteOutput'] = false;
        }

        $profiler = Mage::getModel('M2ePro/Synchronization_Profiler',$profilerParams);
        Mage::register('synchProfiler',$profiler);

        Mage::registry('synchProfiler')->enable();
        Mage::registry('synchProfiler')->start();
        Mage::registry('synchProfiler')->makeShutdownFunction();

        Mage::registry('synchProfiler')->setClearResources();
        //----------------------------------

        // Create and save synch session
        //----------------------------------
        $runs = Mage::getModel('M2ePro/Synchronization_Runs');
        Mage::register('synchRuns',$runs);

        Mage::registry('synchRuns')->start($this->_initiator);
        Mage::registry('synchRuns')->makeShutdownFunction();

        Mage::register('synchId',Mage::registry('synchRuns')->getLastId());
        //----------------------------------

        // Create and save logs
        //----------------------------------
        $logs = Mage::getModel('M2ePro/Synchronization_Logs');
        Mage::register('synchLogs',$logs);

        Mage::registry('synchLogs')->setSynchronizationRuns(Mage::registry('synchId'));
        Mage::registry('synchLogs')->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_UNKNOWN);
        //----------------------------------

        // Create and save lock item
        //----------------------------------
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        Mage::register('synchLockItem',$lockItem);

        if (Mage::registry('synchLockItem')->isExist()) {

            Mage::registry('synchLogs')->addMessage('Another Synchronization Is Already Running',
                                                    Ess_M2ePro_Model_Synchronization_Logs::TYPE_WARNING,
                                                    Ess_M2ePro_Model_Synchronization_Logs::PRIORITY_MEDIUM);
            Mage::registry('synchProfiler')->addTitle('Another Synchronization Is Already Running.',Ess_M2ePro_Model_Profiler::TYPE_ERROR);
            return false;
        }

        Mage::registry('synchLockItem')->create();
        Mage::registry('synchLockItem')->makeShutdownFunction();
        //----------------------------------

        return true;
    }

    private function setMemoryLimit()
    {
        $mode = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/memory/','mode');

        if ($mode != 1) {
            return false;
        }

        $minSize = 32;
        $maxSize = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/memory/','max_size');

        if ($maxSize < $minSize) {
            return false;
        }
        
        for ($i=$minSize; $i<=$maxSize; $i*=2) {

            if (@ini_set("memory_limit","{$i}M") === false) {
                if ($i == $minSize) {
                    return false;
                } else {
                    return $i/2;
                }
            }
        }

        return true;
    }

    private function checkTask($task)
    {
        return in_array($task, $this->_tasks);
    }

    //####################################
}