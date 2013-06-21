<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Logs extends Ess_M2ePro_Model_LogsBase
{
    const SYNCH_TASK_UNKNOWN = 0;
    const _SYNCH_TASK_UNKNOWN = 'System';

    const SYNCH_TASK_DEFAULTS = 1;
    const _SYNCH_TASK_DEFAULTS = 'Default Synchronization';
    const SYNCH_TASK_TEMPLATES = 2;
    const _SYNCH_TASK_TEMPLATES = 'Templates Synchronization';
    const SYNCH_TASK_ORDERS = 3;
    const _SYNCH_TASK_ORDERS = 'Orders Synchronization';
    const SYNCH_TASK_FEEDBACKS = 4;
    const _SYNCH_TASK_FEEDBACKS = 'Feedbacks Synchronization';
    const SYNCH_TASK_MARKETPLACES = 5;
    const _SYNCH_TASK_MARKETPLACES = 'Marketplaces Synchronization';
    const SYNCH_TASK_EBAY_LISTINGS = 6;
    const _SYNCH_TASK_EBAY_LISTINGS = '3rd Party Listings Synchronization';

    // TODO uncomment constants
    //const SYNCH_TASK_MESSAGES = 7;
    //const _SYNCH_TASK_MESSAGES = 'Messages Synchronization';

    /**
     * @var null|int
     */
    private $_synchRuns = NULL;

    /**
     * @var null|int
     */
    private $_synchTask = NULL;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Logs');
    }

    //####################################

    /**
     * Set synchronization runs id
     *
     * @param int $id
     *
     * @return void
     */
    public function setSynchronizationRuns($id)
    {
        $this->_synchRuns = (int)$id;
    }

    /**
     * Set synchronization task
     *
     * @param int $task
     *
     * @return void
     */
    public function setSynchronizationTask($task = self::SYNCH_TASK_UNKNOWN)
    {
        $this->_synchTask = (int)$task;
    }

    //####################################

    public function addMessage($description = NULL , $type = NULL , $priority = NULL)
    {
        if (is_null($this->_synchRuns)) {
            return;
        }

        if (is_null($this->_synchTask)) {
            return;
        }

        $this->addManualMessage($this->_synchRuns,$this->_synchTask,$description,$type,$priority);
    }

    public function addManualMessage($synchRuns , $synchTask , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd(  $this->makeCreator() ,
                                              $synchRuns ,
                                              $synchTask ,
                                              $description ,
                                              $type ,
                                              $priority );

        $this->createMessage($dataForAdd);
    }

    public function clearMessages($synchTask = NULL)
    {
        $columnName = !is_null($synchTask) ? 'synch_task' : NULL;
        parent::clearMessagesByTable('M2ePro/Synchronization_Logs',$columnName,$synchTask);
    }

    public function getActionTitle($type)
    {
        return $this->getActionTitleByClass(__CLASS__,$type);
    }

    public function getActionsTitles()
    {
        return $this->getActionsTitlesByClass(__CLASS__,'SYNCH_TASK_');
    }

    //####################################

    private function makeDataForAdd($creator , $synchRuns , $sychTask , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = array();

        $dataForAdd['creator'] = $creator;
        $dataForAdd['synchronizations_runs_id'] = (int)$synchRuns;
        $dataForAdd['synch_task'] = (int)$sychTask;

        if (!is_null($description)) {
            $dataForAdd['description'] = Mage::helper('M2ePro')->__($description);
        } else {
            $dataForAdd['description'] = NULL;
        }

        if (!is_null($type)) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if (!is_null($priority)) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        return $dataForAdd;
    }

    private function createMessage($dataForAdd)
    {
        Mage::getModel('M2ePro/Synchronization_Logs')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    //####################################
}