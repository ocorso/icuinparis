<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connectors_Ebay_Dispatcher extends Mage_Core_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connectors_Ebay_Dispatcher');
    }

    //####################################

    /**
     * @throws Exception
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|Ess_M2ePro_Model_Marketplaces $marketplaceModel
     * @param null|Ess_M2ePro_Model_Accounts $accountModel
     * @param null|int $mode
     * @return Ess_M2ePro_Model_Connectors_Ebay_Abstract
     */
    public function getConnector($entity, $type, $name,
                                 array $params = array(),
                                 Ess_M2ePro_Model_Marketplaces $marketplaceModel = NULL,
                                 Ess_M2ePro_Model_Accounts $accountModel = NULL,
                                 $mode = NULL)
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
        //    throw new Exception('Connector command class file not found');
        //}

        $className = 'Ess_M2ePro_Model_Connectors_Ebay';
        $entity != '' && $className .= '_'.$entity;
        $type != '' && $className .= '_'.$type;
        $name != '' && $className .= '_'.$name;

        $object = new $className($params, $marketplaceModel, $accountModel, $mode);

        return $object;
    }

    //####################################
    
    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $params
     * @param null|int|Ess_M2ePro_Model_Marketplaces $marketplace
     * @param null|int|Ess_M2ePro_Model_Accounts $account
     * @param null|int $mode
     * @return mixed
     */
    public function processConnector($entity, $type, $name,
                                     array $params = array(),
                                     $marketplace = NULL,
                                     $account = NULL,
                                     $mode = NULL)
    {
        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = Mage::getModel('M2ePro/Marketplaces')->load((int)$marketplace);
        }

        if (is_int($account) || is_string($account)) {
            $account = Mage::getModel('M2ePro/Accounts')->loadInstance((int)$account);
        }

        $object = $this->getConnector($entity , $type, $name, $params, $marketplace, $account, $mode);
        
        return $object->process();
    }

    /**
     * @param string $entity
     * @param string $type
     * @param string $name
     * @param array $requestData
     * @param string|null $responseDataKey
     * @param null|int|Ess_M2ePro_Model_Marketplaces $marketplace
     * @param null|int|Ess_M2ePro_Model_Accounts $account
     * @param null|int $mode
     * @return mixed
     */
    public function processVirtual($entity, $type, $name,
                                   array $requestData = array(),
                                   $responseDataKey = NULL,
                                   $marketplace = NULL,
                                   $account = NULL,
                                   $mode = NULL)
    {
        $params = array();
        $params['__command__'] = array($entity,$type,$name);
        $params['__request_data__'] = $requestData;
        $params['__response_data_key__'] = $responseDataKey;
        return $this->processConnector('virtual','','',$params,$marketplace,$account,$mode);
    }

    //####################################
}