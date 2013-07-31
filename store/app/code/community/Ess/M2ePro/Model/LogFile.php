<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_LogFile extends Mage_Core_Model_Abstract
{
    const FILE_EXTENSION = 'log';

    private $_variablesDirModel = NULL;
    private $_pathLogFile = NULL;

    //####################################

    public function __construct($params)
    {
        parent::__construct();

        if (!isset($params['nameFile']) || $params['nameFile'] === '') {
            throw new Exception('The name of log file is not specified.');
        }

        !isset($params['folder']) && $params['folder'] = NULL;
        $params['folder'] === '' && $params['folder'] = NULL;

        $this->_variablesDirModel = Mage::getModel('M2ePro/VariablesDir',array('childFolder'=>$params['folder']));
        $this->_pathLogFile = $this->_variablesDirModel->getPath().$params['nameFile'].'.'.self::FILE_EXTENSION;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LogFile');
    }

    //####################################

    public function getPath()
    {
        return $this->_pathLogFile;
    }

    public function isExist()
    {
        return @is_file($this->getPath());
    }

    //---------------------

    public function create()
    {
        if ($this->isExist()) {
            return false;
        }

        $this->_variablesDirModel->create();

        $fileHandler = @fopen($this->getPath(), "w");
        if ($fileHandler === false) {
            throw new Exception('Creation of Log file is failed.');
        }
        @fwrite($fileHandler, '');
        @fclose($fileHandler);

        return true;
    }

    public function remove()
    {
        if (!$this->isExist()) {
            return false;
        }

        if (!@unlink($this->getPath())) {
            throw new Exception('Removing of Log file is failed.');
        }

        return true;
    }

    //---------------------

    public function add($string)
    {
        if (!$this->isExist()) {
            return false;
        }

        return $this->addDataToLog($string);
    }

    public function addLine($string)
    {
        if (!$this->isExist()) {
            return false;
        }

        return $this->addDataToLog($string.PHP_EOL);
    }

    private function addDataToLog($data)
    {
        $fileHandler = fopen($this->getPath(), "a");
        fwrite($fileHandler, $data);
        fclose($fileHandler);

        return true;
    }
    
    //---------------------

    public function makeShutdownFunction()
    {
        $functionCode = '$logFile = '. var_export($this->getPath(), true).';
                         if (is_file($logFile))
                         {@unlink($logFile);}';
        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}