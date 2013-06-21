<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Profiler extends Ess_M2ePro_Model_Profiler
{
    const LOG_FILE_NAME = 'profiler';
    const LOG_FILE_FOLDER = 'synchronization';

    //####################################

    public function __construct($params)
    {
        $paramsParent = array( 'nameLogFile' => self::LOG_FILE_NAME , 'folderLogFile' => self::LOG_FILE_FOLDER );
        parent::__construct($paramsParent);

        $mode = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/profiler/','mode');

        if ($mode == Ess_M2ePro_Model_Profiler::MODE_DEVELOPING) {
            
           if (isset($params['muteOutput']) && $params['muteOutput'] === true) {
                $this->setDebuggingMode();
           } else {
                $this->setDevelopingMode();
           }

        } elseif ($mode == Ess_M2ePro_Model_Profiler::MODE_DEBUGGING) {
            $this->setDebuggingMode();
        } else {
            $this->setProductionMode();
        }

        $printType = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/profiler/','print_type');
        $this->setPrintType($printType);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Profiler');
    }

    //####################################

    public function setClearResources()
    {
        $deleteResources = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/profiler/','delete_resources');
        if ($deleteResources == 1) {
            $this->clearResourcesAfterEnd();
        }
    }

    public function makeShutdownFunction()
    {
        $functionCode = "Mage::registry('synchProfiler')->stop();";
        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}