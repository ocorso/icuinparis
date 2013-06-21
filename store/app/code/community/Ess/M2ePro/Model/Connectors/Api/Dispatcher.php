<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Api_Dispatcher extends Mage_Core_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connectors_Api_Dispatcher');
    }

    //####################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @return Ess_M2ePro_Model_Connectors_Api_Abstract
     */
    public function getConnector($entity, $type, $name, array $params = array())
    {
        $entity = uc_words(trim($entity));
        $type = uc_words(trim($type));
        $name = uc_words(trim($name));

        //$classFilePath = dirname(__FILE__);
        //$entity != '' && $classFilePath .= '/'.$entity;
        //$type != '' && $classFilePath .= '/'.$type;
        //$name != '' && $classFilePath .= '/'.$name;
        //$classFilePath .= '.php';

        //if (!is_file($classFilePath)) {
        //    throw new Exception('Connector command class file not found.');
        //}

        $className = 'Ess_M2ePro_Model_Connectors_Api';
        $entity != '' && $className .= '_'.$entity;
        $type != '' && $className .= '_'.$type;
        $name != '' && $className .= '_'.$name;

        $object = new $className($params);

        return $object;
    }

    //####################################
    
    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function processConnector($entity, $type, $name, array $params = array())
    {
        $object = $this->getConnector($entity , $type, $name, $params);
        return $object->process();
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @return mixed
     */
    public function processVirtual($entity, $type, $name, array $requestData = array(), $responseDataKey = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;
        return $this->processConnector('virtual','','',$params);
    }

    //####################################
}