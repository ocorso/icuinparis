<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Tasks extends Mage_Core_Model_Abstract
{
    const DEFAULTS = 1;
    const TEMPLATES = 2;
    const ORDERS = 3;
    const FEEDBACKS = 4;
    const MARKETPLACES = 5;
    const EBAY_LISTINGS = 6;
    const MESSAGES = 7;

    /**
     * @var array
     */
    protected $_tasks = array();

    /**
     * @var null|int
     */
    protected $_initiator = NULL;

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var null|int
     */
    protected $_synchId = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Profiler
     */
    protected $_profiler = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Runs
     */
    protected $_runs = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Logs
     */
    protected $_logs = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_LockItem
     */
    protected $_lockItem = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_EbayActions
     */
    protected $_ebayActions = NULL;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Tasks');

        $this->_tasks = Mage::registry('synchTasks');
        $this->_initiator = Mage::registry('synchInitiator');
        $this->_params = Mage::registry('synchParams');

        $this->_synchId = Mage::registry('synchId');

        $this->_profiler = Mage::registry('synchProfiler');
        $this->_runs = Mage::registry('synchRuns');
        $this->_logs = Mage::registry('synchLogs');
        $this->_lockItem = Mage::registry('synchLockItem');

        $this->_ebayActions = Mage::registry('synchEbayActions');
    }

    //####################################

    abstract public function process();

    //####################################
}