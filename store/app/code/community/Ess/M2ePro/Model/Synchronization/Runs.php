<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Runs extends Mage_Core_Model_Abstract
{
    const INITIATOR_UNKNOWN = 0;
    const INITIATOR_CRON = 1;
    const INITIATOR_USER = 2;
    const INITIATOR_DEVELOPER = 3;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Runs');
    }

    //####################################

    public function start($initiator = self::INITIATOR_UNKNOWN)
    {
        $dataForAdd = array(
            'initiator' => $initiator,
            'start_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );
        $this->setData($dataForAdd)->save();
        return true;
    }

    public function stop()
    {
        $synchId = $this->getLastId();

        if (is_null($synchId)) {
            return false;
        }

        $this->load($synchId)
             ->addData(array('end_date'=>Mage::helper('M2ePro')->getCurrentGmtDate()))
             ->save();

        return true;
    }

    //--------------------

    public function getLastId()
    {
        $tempCollection = $this->getCollection();

        $tempCollection->getSelect()
                       ->order(array('start_date DESC'))
                       ->limit(1);
        $tempArray = $tempCollection->toArray();

        if (!isset($tempArray['items'][0])) {
            return NULL;
        }

        return (int)$tempArray['items'][0]['id'];
    }

    //####################################

    public function makeShutdownFunction()
    {
        $functionCode = "Mage::registry('synchRuns')->stop();";
        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}