<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Exception extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function setFatalErrorHandler()
    {
        $functionCode = '$error = error_get_last();
                         if ((int)$error[\'type\'] == 1) {
                             $stackTrace = @debug_backtrace(false);
                             $traceInfo = Mage::helper(\'M2ePro/Exception\')->getFatalStackTraceInfo($stackTrace);
                             Mage::helper(\'M2ePro/Exception\')->processFatal($error,$traceInfo,true);
                         }';
        $shutdownFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownFunction);
    }

    // ########################################

    public function process(Exception $exception, $send = true)
    {
        if (isset($GLOBALS['m2epro_send_exception_to_server'])) {
            return;
        }

        $GLOBALS['m2epro_send_exception_to_server'] = true;

        $sendToServer = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/debug/exceptions/','send_to_server');
        is_null($sendToServer) && $sendToServer = false;
        
        if ($sendToServer && $send) {

            $type = get_class($exception);

            $info = $this->getExceptionInfo($exception, $type);
            $info .= $this->getExceptionStackTraceInfo($exception);
            $info .= $this->getCurrentUserActionInfo();
            $info .= Mage::helper('M2ePro/Module')->getSummaryTextInfo();

            $this->send($info, $type);
        }

        unset($GLOBALS['m2epro_send_exception_to_server']);
    }

    public function processFatal($error, $traceInfo, $send = true)
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) &&
            strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.') !== false &&
            strpos($error['message'], 'getUsername() on a non-object') !== false) {
            return;
        }

        if (isset($GLOBALS['m2epro_send_exception_to_server'])) {
            return;
        }

        $GLOBALS['m2epro_send_exception_to_server'] = true;

        $sendToServer = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/debug/fatal_error/','send_to_server');
        is_null($sendToServer) && $sendToServer = false;

        if ($sendToServer && $send) {

            $type = 'Fatal Error';

            $info = $this->getFatalInfo($error, $type);
            $info .= $traceInfo;
            $info .= $this->getCurrentUserActionInfo();
            $info .= Mage::helper('M2ePro/Module')->getSummaryTextInfo();
            
            $this->send($info, $type);
        }

        unset($GLOBALS['m2epro_send_exception_to_server']);
    }
    
    //---------------------

    public function getUserMessage(Exception $exception)
    {
        return Mage::helper('M2ePro')->__('Fatal error occurred').': "'.$exception->getMessage().'".';
    }

    //---------------------

    public function send($info, $type)
    {
        Mage::getModel('M2ePro/Connectors_Api_Dispatcher')
                ->processVirtual('domain','add','exception',
                                 array('info' => $info, 'type' => $type));
    }

    // ########################################

    private function getExceptionInfo(Exception $exception, $type)
    {
        $exceptionInfo = <<<EXCEPTION
-------------------------------- EXCEPTION INFO ----------------------------------
Type: {$type}
File: {$exception->getFile()}
Line: {$exception->getLine()}
Message: {$exception->getMessage()}
Code: {$exception->getCode()}


EXCEPTION;

        return $exceptionInfo;
    }

    private function getFatalInfo($error, $type)
    {
        $exceptionInfo = <<<FATAL
-------------------------------- FATAL ERROR INFO --------------------------------
Type: {$type}
File: {$error['file']}
Line: {$error['line']}
Message: {$error['message']}


FATAL;

        return $exceptionInfo;
    }

    //---------------------
    
    private function getExceptionStackTraceInfo(Exception $exception)
    {
        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}


TRACE;

        return $stackTraceInfo;
    }

    public function getFatalStackTraceInfo($stackTrace)
    {
        if (!is_array($stackTrace)) {
            $stackTrace = array();
        }

        $stackTrace = array_reverse($stackTrace);
        $info = '';

        if (count($stackTrace) > 1) {
            foreach ($stackTrace as $key => $trace) {
                $info .= "#{$key} {$trace['file']}({$trace['line']}):";
                $info .= " {$trace['class']}{$trace['type']}{$trace['function']}(";

                if (count($trace['args'])) {
                    foreach ($trace['args'] as $key => $arg) {
                        $key != 0 && $info .= ',';

                        if (is_object($arg)) {
                            $info .= get_class($arg);
                        } else {
                            $info .= $arg;
                        }
                    }
                }
                $info .= ")\n";
            }
        }

        if ($info == '') {
            $info = 'Unavailable';
        }

        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$info}


TRACE;

        return $stackTraceInfo;
    }

    //---------------------
    
    private function getCurrentUserActionInfo()
    {
        $server = isset($_SERVER) ? print_r($_SERVER, true) : '';
        $get = isset($_GET) ? print_r($_GET, true) : '';
        $post = isset($_POST) ? print_r($_POST, true) : '';

        $actionInfo = <<<ACTION
-------------------------------- ACTION INFO -------------------------------------
SERVER: {$server}
GET: {$get}
POST: {$post}

ACTION;

        return $actionInfo;
    }

    // ########################################
}