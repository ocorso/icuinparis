<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Protocol
{
    const API_VERSION = 1;

    const REQUEST_TYPE = 'POST';
    //const REQUEST_TYPE = 'GET';
    const DATA_FORMAT = 'JSON';
    //const DATA_FORMAT = 'SERIALIZATION';

    const API_VERSION_KEY = 'api_version';
    
    const REQUEST_INFO_KEY = 'request';
    const REQUEST_DATA_KEY = 'data';
    const RESPONSE_INFO_KEY = 'response';
    const RESPONSE_DATA_KEY = 'data';

    const MODE_DEVELOPMENT = 'development';
    const MODE_PRODUCTION = 'production';

    const MESSAGE_TEXT_KEY = 'text';
    const MESSAGE_TYPE_KEY = 'type';
    const MESSAGE_SENDER_KEY = 'sender';
    const MESSAGE_CODE_KEY = 'code';

    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_NOTICE = 'notice';

    const MESSAGE_SENDER_SYSTEM = 'system';
    const MESSAGE_SENDER_COMPONENT = 'component';

    private $serverScript = '';

    private $request = array();
    private $response = array();

    protected $requestExtraData = array();

    protected $resultType = self::MESSAGE_TYPE_ERROR;
    protected $messages = array();
    
    // ########################################

    public function __construct()
    {
        $this->serverScript = Mage::helper('M2ePro/Module')->getServerScriptsPath().'index.php';
    }

    // ########################################

    protected function sendRequest()
    {
        $requestInfo = $this->getRequestInfo();
        $requestData = $this->getRequestData();

        is_null($requestData) && $requestData = array();
        $requestData = array_merge($requestData,$this->requestExtraData);

        $request = array(
            self::API_VERSION_KEY => self::API_VERSION,
            self::REQUEST_INFO_KEY => $requestInfo,
            self::REQUEST_DATA_KEY => $requestData
        );

        if (Mage::getIsDeveloperMode()) {
            $this->request = $request;
        }

        $request[self::REQUEST_INFO_KEY] = $this->encodeData($request[self::REQUEST_INFO_KEY]);
        $request[self::REQUEST_DATA_KEY] = $this->encodeData($request[self::REQUEST_DATA_KEY]);

        $response = NULL;

        if (self::REQUEST_TYPE == 'POST') {
            $response = $this->sendRequestAsPost($request);
        }
        if (self::REQUEST_TYPE == 'GET') {
            $response = $this->sendRequestAsGet($request);
        }

        $response = $this->decodeData($response);

        if (Mage::getIsDeveloperMode()) {
            $this->response = $response;
        }

        if (!isset($response[self::RESPONSE_INFO_KEY]) || !isset($response[self::RESPONSE_DATA_KEY])) {
            throw new Exception('Server response data has invalid format.');
        }

        $this->processResponseResult($response[self::RESPONSE_INFO_KEY]);

        if ($this->resultType == self::MESSAGE_TYPE_ERROR) {
            $errorHasSystem = '';
            $messages = $this->messages;
            foreach ($messages as $message) {
                if (!isset($message[self::MESSAGE_TYPE_KEY]) || !isset($message[self::MESSAGE_SENDER_KEY])) {
                    continue;
                }
                if ($message[self::MESSAGE_TYPE_KEY] == self::MESSAGE_TYPE_ERROR &&
                    $message[self::MESSAGE_SENDER_KEY] == self::MESSAGE_SENDER_SYSTEM) {
                    $errorHasSystem != '' && $errorHasSystem .= ', ';
                    $errorHasSystem .= $message[self::MESSAGE_TEXT_KEY];
                }
            }
            if ($errorHasSystem != '') {
                throw new Exception("Internal server error(s) [{$errorHasSystem}]");
            }
        }

        return $response[self::RESPONSE_DATA_KEY];
    }

    protected function processResponseResult(array $responseInfo)
    {
        if (!isset($responseInfo['result']) || !isset($responseInfo['result']['type'])) {
            return;
        }

        $this->resultType = $responseInfo['result']['type'];

        if (!isset($responseInfo['result']['messages']) || !is_array($responseInfo['result']['messages'])) {
            return;
        }

        foreach ($responseInfo['result']['messages'] as $message) {
            $this->messages[] = $message;
        }
    }

    // ########################################

    private function sendRequestAsPost($params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $this->serverScript);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query($params,'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        if ($response === false) {
            throw new Exception('Server connection is failed. Please try again later.');
        }

        return $response;
    }

    private function sendRequestAsGet($params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $this->serverScript.'?'.http_build_query($params,'','&'));

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);
        curl_close($curlObject);

        if ($response === false) {
            throw new Exception('Server connection is failed. Please try again later.');
        }

        return $response;
    }

    // ########################################

    protected function getRequestInfo()
    {
        $commandTemp = $this->getCommand();

        if (!isset($commandTemp[0]) || !isset($commandTemp[1]) || !isset($commandTemp[2])) {
            throw new Exception('Requested command has invalid format.');
        }

        $command = array(
            'entity' => $commandTemp[0],
            'type' => $commandTemp[1],
            'name' => $commandTemp[2]
        );

        $request = array(
            'mode' => Mage::helper('M2ePro/Module')->isDeveloper() ? self::MODE_DEVELOPMENT : self::MODE_PRODUCTION,
            'client' => array(
                'platform' => array(
                    'name' => Mage::helper('M2ePro/Magento')->getName().' ('.Mage::helper('M2ePro/Magento')->getEditionName().')',
                    'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
                ),
                'module' => array(
                    'name' => Mage::helper('M2ePro/Module')->getName(),
                    'version' => Mage::helper('M2ePro/Module')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Module')->getRevision()
                ),
                'location' => array(
                    'domain' => Mage::helper('M2ePro/Server')->getDomain(),
                    'ip' => Mage::helper('M2ePro/Server')->getIp(),
                    'directory' => Mage::helper('M2ePro/Server')->getBaseDirectory()
                ),
                'locale' => Mage::helper('M2ePro/Magento')->getLocale()
            ),
            'auth' => array(),
            'component' => array(
                'name' => $this->getComponent(),
                'version' => $this->getComponentVersion()
            ),
            'command' => $command
        );

        $adminKey = Mage::helper('M2ePro/Ess')->getAdminKey();
        !is_null($adminKey) && $adminKey != '' && $request['auth']['admin_key'] = $adminKey;

        $applicationKey = Mage::helper('M2ePro/Module')->getApplicationKey();
        !is_null($applicationKey) && $applicationKey != '' && $request['auth']['application_key'] = $applicationKey;

        $licenseKey = Mage::getModel('M2ePro/License_Model')->getKey();
        !is_null($licenseKey) && $licenseKey != '' && $request['auth']['license_key'] = $licenseKey;
            
        return $request;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function getRequestData();

    //----------------------------------------

    /**
     * @abstract
     * @return string
     */
    abstract protected function getComponent();

    /**
     * @abstract
     * @return int
     */
    abstract protected function getComponentVersion();

    /**
     * @abstract
     * @return array
     */
    abstract protected function getCommand();

    // ########################################

    private function encodeData($data)
    {
        if (self::DATA_FORMAT == 'JSON') {
            return @json_encode($data);
        }
        if (self::DATA_FORMAT == 'SERIALIZATION') {
            return @serialize($data);
        }

        return $data;
    }

    private function decodeData($data)
    {
        if (self::DATA_FORMAT == 'JSON') {
            return @json_decode($data,true);
        }
        if (self::DATA_FORMAT == 'SERIALIZATION') {
            return @unserialize($data);
        }

        return $data;
    }

    // ########################################

    public function printDebugData()
    {
        if (!Mage::getIsDeveloperMode()) {
            return;
        }

        if (count($this->request) > 0) {
            echo '<h1>Request:</h1>',
            '<pre>';
            var_dump($this->request);
            echo '</pre>';
        }

        if (count($this->response) > 0) {
            echo '<h1>Response:</h1>',
            '<pre>';
            var_dump($this->response);
            echo '</pre>';
        }
    }

    // ########################################
}