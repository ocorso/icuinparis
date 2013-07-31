<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Data extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function escapeJs($string)
    {
        return str_replace(array("\\"  , "\n"  , "\r" , "\""  , "'"),
                           array("\\\\", "\\n" , "\\r", "\\\"", "\\'"),
                           $string);
    }

    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    // ########################################

    public function getClassConstantAsJson($class)
    {
        $class = 'Ess_M2ePro_'.$class;

        $reflectionClass = new ReflectionClass($class);
        $tempConstants = $reflectionClass->getConstants();

        $constants = array();
        foreach ($tempConstants as $key => $value) {
            $constants[] = array(strtoupper($key), $value);
        }

        return json_encode($constants);
    }

    public function convertCurrencyNameToCode($currencyName, $priceValue)
    {
        return Mage::app()->getLocale()->currency($currencyName)->toCurrency($priceValue);
    }

    public function convertStringToSku($title)
    {
        $skuVal = strtolower($title);
        $skuVal = str_replace(array(" ", ":", ",", ".", "?", "*", "+", "(", ")", "&", "%", "$", "#", "@", "!", '"', "'", ";", "\\", "|", "/", "<", ">"), "-", $skuVal);
        return substr($skuVal, 0, 64);
    }

    // ########################################

    public function getCurrentGmtDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp();
        }
        return Mage::getModel('core/date')->gmtDate($format);
    }

    public function getCurrentTimezoneDate($returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp();
        }
        return Mage::getModel('core/date')->date($format);
    }

    //-----------------------------------------

    public function getDate($date, $returnTimestamp = false, $format = NULL)
    {
        if (is_numeric($date)) {
            $result = (int)$date;
        } else {
            $result = strtotime($date);
        }

        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }

        $result = date($format, $result);

        if ($returnTimestamp) {
            return strtotime($result);
        }

        return $result;
    }

    //-----------------------------------------
    
    public function gmtDateToTimezone($dateGmt, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->timestamp($dateGmt);
        }
        return Mage::getModel('core/date')->date($format,$dateGmt);
    }

    public function timezoneDateToGmt($dateTimezone, $returnTimestamp = false, $format = NULL)
    {
        if ($returnTimestamp) {
            return (int)Mage::getModel('core/date')->gmtTimestamp($dateTimezone);
        }
        return Mage::getModel('core/date')->gmtDate($format,$dateTimezone);
    }

    // ########################################

    public function makeBackUrlParam($backIdOrRoute, array $backParams = array())
    {
        $paramsString = count($backParams) > 0 ? '|'.http_build_query($backParams,'','&') : '';
        return base64_encode($backIdOrRoute.$paramsString);
    }

    public function getBackUrlParam($defaultBackIdOrRoute = 'index', array $defaultBackParams = array())
    {
        $requestParams = Mage::registry('M2ePro_request_params');
        return isset($requestParams['back']) ? $requestParams['back'] : $this->makeBackUrlParam($defaultBackIdOrRoute,$defaultBackParams);
    }

    //------------------------------------------

    public function getBackUrl($defaultBackIdOrRoute = 'index', array $defaultBackParams = array(), array $extendedRoutersParams = array())
    {
        $back = base64_decode($this->getBackUrlParam($defaultBackIdOrRoute,$defaultBackParams));

        $route = '';
        $params = array();

        if (strpos($back,'|') !== false) {
            $route = substr($back,0,strpos($back,'|'));
            parse_str(substr($back,strpos($back,'|')+1),$params);
        } else {
            $route = $back;
        }

        $extendedRoutersParamsTemp = array();
        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            } else {
                $extendedRoutersParamsTemp[$route] = $params;
            }
        }
        $extendedRoutersParams = $extendedRoutersParamsTemp;
        
        $route == 'index' && $route = '*/*/index';
        $route == 'list' && $route = '*/*/index';
        $route == 'edit' && $route = '*/*/edit';
        $route == 'view' && $route = '*/*/view';

        foreach ($extendedRoutersParams as $extRouteName => $extParams) {
            if ($route == $extRouteName) {
                $params = array_merge($params,$extParams);
            }
        }

        return Mage::helper('M2ePro/Magento')->getUrl($route,$params);
    }

    // ########################################
}