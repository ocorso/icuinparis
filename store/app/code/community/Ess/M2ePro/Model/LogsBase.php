<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */
 
class Ess_M2ePro_Model_LogsBase extends Mage_Core_Model_Abstract
{
    const INITIATOR_UNKNOWN = 0;
    const INITIATOR_USER = 1;
    const INITIATOR_EXTENSION = 2;
    
    const TYPE_NOTICE = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_WARNING = 3;
    const TYPE_ERROR = 4;

    const PRIORITY_HIGH = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_LOW = 3;

    //####################################
    
	public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LogsBase');
    }

    //####################################

    protected function getActionTitleByClass($class,$type)
    {
        $reflectionClass = new ReflectionClass ($class);
        $tempConstants = $reflectionClass->getConstants();

        foreach ($tempConstants as $key => $value) {
            if ($key == '_'.$type) {
                return Mage::helper('M2ePro')->__($key);
            }
        }

        return '';
    }

    protected function getActionsTitlesByClass($class,$prefix)
    {
        $reflectionClass = new ReflectionClass ($class);
        $tempConstants = $reflectionClass->getConstants();

        $actionsNames = array();
        foreach ($tempConstants as $key => $value) {
            if (substr($key,0,strlen($prefix)) == $prefix) {
                $actionsNames[$key] = $value;
            }
        }

        $actionsValues = array();
        foreach ($actionsNames as $action => $valueAction) {
            foreach ($tempConstants as $key => $valueConstant) {
                if ($key == '_'.$action) {
                    $actionsValues[$valueAction] = Mage::helper('M2ePro')->__($valueConstant);
                }
            }
        }

        return $actionsValues;
    }

    //####################################

    protected function makeCreator()
    {
         $debugBackTrace = debug_backtrace();

         if (!isset($debugBackTrace[2])) {
             return 'unknown';
         }

         $creator = $debugBackTrace[2]['class'].'::'.$debugBackTrace[2]['function'].'()';
         //$creator .= ' ['.$debugBackTrace[2]['line'].']';

         return $creator;
    }

    protected function clearMessagesByTable($tableNameOrModelName, $columnName = NULL, $columnId = NULL)
    {
        // Prepare tables names
        //-----------------------------
        $logsTable  = Mage::getSingleton('core/resource')->getTableName($tableNameOrModelName);
        //-----------------------------

        $where = array();
        if (!is_null($columnId)) {
            $where[$columnName.' = ?'] = $columnId;
        }

        // Execute query
        //-----------------------------
        Mage::getModel('Core/Mysql4_Config')
                ->getReadConnection()
                ->delete($logsTable,$where);
        //-----------------------------
    }

    //####################################

    public function encodeDescription($string, array $params = array())
    {
        if (count($params) <= 0) {
            return $string;
        }

        $tempArray = array(
            'string' => $string,
            'params' => $params
        );

        return json_encode($tempArray);
    }

    public function decodeDescription($string)
    {
        if (!is_string($string) || $string == '') {
            return '';
        }

        if ($string{0} != '{') {
            return Mage::helper('M2ePro')->__($string);
        }

        $tempArray = json_decode($string,true);
        $string = Mage::helper('M2ePro')->__($tempArray['string']);
        foreach ($tempArray['params'] as $key=>$value) {
            if ($key{0} == '!') {
                $key = substr($key,1);
            } else {
                $value = Mage::helper('M2ePro')->__($value);
            }
            $string = str_replace('%'.$key.'%',$value,$string);
        }

        return $string;
    }

    //####################################

    public function getNextActionId()
    {
        $dbSelect = $this->getResource()->getReadConnection()
                            ->select()
                            ->from($this->getResource()->getMainTable(),new Zend_Db_Expr('MAX(`action_id`)'));

        $maxActionId = $this->getResource()->getReadConnection()->fetchOne($dbSelect);

        if ($maxActionId === false || is_null($maxActionId)) {
            $maxActionId = 0;
        }

        return (int)$maxActionId+1;
    }

    //####################################
}