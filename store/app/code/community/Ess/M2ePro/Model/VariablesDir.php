<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_VariablesDir extends Mage_Core_Model_Abstract
{
    const BASE_NAME = 'M2ePro';

    private $_pathVariablesDirBase = NULL;
    private $_pathVariablesDirChildFolder = NULL;
    private $_childFolder = NULL;

    //####################################

    public function __construct($params)
    {
        parent::__construct();

        !isset($params['childFolder']) && $params['childFolder'] = NULL;
        $params['childFolder'] === '' && $params['childFolder'] = NULL;

        if (Mage::helper('M2ePro/Magento')->isMagentoGoMode()) {
            $this->_pathVariablesDirBase = Mage::getBaseDir('media').DS.self::BASE_NAME;
        } else {
            $this->_pathVariablesDirBase = Mage::getBaseDir('var').DS.self::BASE_NAME;
        }

        if (!is_null($params['childFolder'])) {

            if ($params['childFolder']{0} != DS) {
                $params['childFolder'] = DS.$params['childFolder'];
            }
            if ($params['childFolder']{strlen($params['childFolder'])-1} != DS) {
                $params['childFolder'] .= DS;
            }

            $this->_pathVariablesDirChildFolder = $this->_pathVariablesDirBase.$params['childFolder'];
            $this->_pathVariablesDirBase .= DS;
            $this->_childFolder = $params['childFolder'];
            
        } else {
            $this->_pathVariablesDirBase .= DS;
            $this->_pathVariablesDirChildFolder = $this->_pathVariablesDirBase;
            $this->_childFolder = '';
        }

        $this->_pathVariablesDirBase = str_replace(array('/','\\'),DS,$this->_pathVariablesDirBase);
        $this->_pathVariablesDirChildFolder = str_replace(array('/','\\'),DS,$this->_pathVariablesDirChildFolder);
        $this->_childFolder = str_replace(array('/','\\'),DS,$this->_childFolder);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/VariablesDir');
    }

    //####################################

    public function getBasePath()
    {
        return $this->_pathVariablesDirBase;
    }
    
    public function getPath()
    {
        return $this->_pathVariablesDirChildFolder;
    }

    //---------------------

    public function isBaseExist()
    {
        return @is_dir($this->getBasePath());
    }

    public function isExist()
    {
        return @is_dir($this->getPath());
    }

    //---------------------

    public function createBase()
    {
        if ($this->isBaseExist()) {
            return;
        }

        if (!@mkdir($this->getBasePath())) {
            throw new Exception('M2ePro base var dir creation is failed.');
        }
    }

    public function create()
    {
        if ($this->isExist()) {
            return;
        }

        $this->createBase();

        if ($this->_childFolder != '') {

            $tempPath = $this->getBasePath();
            $tempChildFolders = explode(DS,substr($this->_childFolder,1,strlen($this->_childFolder)-2));

            foreach ($tempChildFolders as $key=>$value) {
                if (!is_dir($tempPath.$value.DS)) {
                    if (!@mkdir($tempPath.$value.DS)) {
                        throw new Exception('Custom var dir creation is failed.');
                    }
                }
                $tempPath = $tempPath.$value.DS;
            }
        } else {
            if (!@mkdir($this->getPath())) {
                throw new Exception('Custom var dir creation is failed.');
            }
        } 
    }

    //---------------------

    public function removeBase()
    {
        if (!$this->isBaseExist()) {
            return;
        }

        if (!@rmdir($this->getBasePath())) {
            throw new Exception('M2ePro base var dir removing is failed.');
        }
    }

    public function remove()
    {
        if (!$this->isExist()) {
            return;
        }

        if (!@rmdir($this->getPath())) {
            throw new Exception('Custom var dir removing is failed.');
        }
    }

    //####################################
}