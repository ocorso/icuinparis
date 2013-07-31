<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ConfigBase extends Mage_Core_Model_Abstract
{
    const SORT_NONE = 0;
    const SORT_KEY_ASC = 1;
    const SORT_KEY_DESC = 2;
    const SORT_VALUE_ASC = 3;
    const SORT_VALUE_DESC = 4;

    private $_ormConfig = '';

    public static $cache = array();

    // ########################################

    public function __construct($params)
    {
        parent::__construct();

        if (isset($params['orm'])) {
            $this->_ormConfig = $params['orm'];
        }
    }

    // ########################################

    public function setGlobalValue($key, $value, $notice = NULL)
    {
        $key = (string)$key;
        $value = (string)$value;

        if (!is_null($notice)) {
            $notice = (string)$notice;
        }

        if ($key == '') {
            return false;
        }

        return $this->setValue(NULL, $key, $value, $notice);
    }

    public function getGlobalValue($key)
    {
        $key = (string)$key;

        if ($key == '') {
            return NULL;
        }

        return $this->getValue(NULL, $key);
    }

    public function getGlobalNotice($key)
    {
        $key = (string)$key;

        if ($key == '') {
            return NULL;
        }

        return $this->getNotice(NULL, $key);
    }

    public function getAllGlobalValues($sort = self::SORT_NONE)
    {
        $result = array();

        // Get all global keys
        //------------------
        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', array('null' => true))
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $result[$item['key']] = $item['value'];
            }

        }
        //------------------

        $this->sortResult($result,$sort);

        return $result;
    }

    public function getAllGlobalNotices($sort = self::SORT_NONE)
    {
        $result = array();

        // Get all global keys
        //------------------
        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', array('null' => true))
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $result[$item['key']] = $item['notice'];
            }

        }
        //------------------

        $this->sortResult($result,$sort);

        return $result;
    }

    public function deleteGlobalValue($key)
    {
        $key = (string)$key;

        if ($key == '') {
            return false;
        }

        return $this->deleteValue(NULL, $key);
    }

    public function deleteAllGlobalValues()
    {
        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', array('null' => true))
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $this->deleteValue(NULL, $item['key']);
            }

            return true;

        } else {
            return false;
        }

        return false;
    }

    // ########################################

    public function setGroupValue($group, $key, $value, $notice = NULL)
    {
        $group = (string)$group;
        $key = (string)$key;
        $value = (string)$value;

        if (!is_null($notice)) {
            $notice = (string)$notice;
        }

        if ($group == '' || $key == '') {
            return false;
        }

        $group = $this->prepareGroup($group);

        return $this->setValue($group, $key, $value, $notice);
    }

    public function getGroupValue($group, $key)
    {
        $group = (string)$group;
        $key = (string)$key;

        if ($group == '' || $key == '') {
            return NULL;
        }

        $group = $this->prepareGroup($group);

        return $this->getValue($group, $key);
    }

    public function getGroupNotice($group, $key)
    {
        $group = (string)$group;
        $key = (string)$key;

        if ($group == '' || $key == '') {
            return NULL;
        }

        $group = $this->prepareGroup($group);

        return $this->getNotice($group, $key);
    }

    public function getAllGroupValues($group, $sort = self::SORT_NONE)
    {
        $group = (string)$group;

        if ($group == '') {
            return array();
        }

        $group = $this->prepareGroup($group);

        // Get all keys of group
        //------------------
        $result = array();

        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', $group)
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $result[$item['key']] = $item['value'];
            }

        }
        //------------------

        $this->sortResult($result,$sort);

        return $result;
    }

    public function getAllGroupNotices($group, $sort = self::SORT_NONE)
    {
        $group = (string)$group;

        if ($group == '') {
            return array();
        }

        $group = $this->prepareGroup($group);

        // Get all keys of group
        //------------------
        $result = array();

        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', $group)
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $result[$item['key']] = $item['notice'];
            }

        }
        //------------------

        $this->sortResult($result,$sort);

        return $result;
    }

    public function deleteGroupValue($group, $key)
    {
        $group = (string)$group;
        $key = (string)$key;

        if ($group == '' || $key == '') {
            return false;
        }

        $group = $this->prepareGroup($group);

        return $this->deleteValue($group, $key);
    }

    public function deleteAllGroupValues($group)
    {
        $group = (string)$group;

        if ($group == '') {
            return false;
        }

        $group = $this->prepareGroup($group);

        // Delete all keys and subgroups
        //------------------
        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', array("like"=>$group.'%'))
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            foreach ($tempCollection['items'] as $item) {
                $this->deleteValue($item['group'], $item['key']);
            }

            return true;

        } else {
            return false;
        }
        //------------------

        return false;
    }

    // ########################################

    private function prepareGroup($group)
    {
        if ($group{0} != '/') {
            $group = '/'.$group;
        }
        if ($group{strlen($group)-1} != '/') {
            $group .= '/';
        }

        return $group;
    }

    private function sortResult(&$array, $sort)
    {
        switch ($sort)
        {
            case self::SORT_KEY_ASC:
                ksort($array);
                break;

            case self::SORT_KEY_DESC:
                krsort($array);
                break;

            case self::SORT_VALUE_ASC:
                asort($array);
                break;

            case self::SORT_VALUE_DESC:
                arsort($array);
                break;
        }
    }

    // ------------------------

    private function setValue($group, $key, $value, $notice)
    {
        // Get cache row
        $cacheRow = $this->getRowFromCache($group, $key);

        // Get Entity Id
        //----------------------------
        $entityId = 0;
        $entityData = array();
        if (is_null($cacheRow)) {

            $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                               ->addFieldToFilter('`group`', (is_null($group)?array('null' => true):$group))
                                                               ->addFieldToFilter('`key`', $key)
                                                               ->toArray();

            if ((int)$tempCollection['totalRecords'] > 0) {
                $entityId = (int)$tempCollection['items'][0]['id'];
                $entityData = $tempCollection['items'][0];
            }

        } else {

            $entityId = (int)$cacheRow['id'];
            $entityData = $cacheRow;
        }
        //----------------------------

        // Insert or Update data
        //----------------------------
        if ($entityId == 0) {

            $dataForAdd = array(
                'group' => $group ,
                'key'   => $key ,
                'value' => $value
            );

            if (is_null($notice)) {
                $dataForAdd['notice'] = '';
            } else {
                $dataForAdd['notice'] = $notice;
            }

            $entityId = Mage::getModel($this->_ormConfig)
                                 ->setData($dataForAdd)
                                 ->save()
                                 ->getId();

            $dataTemp = Mage::getModel($this->_ormConfig)->load($entityId)->getData();
            $this->setRowToCache($dataTemp);

        } else {

            if ($entityData['value'] != $value || (!is_null($notice) && $entityData['notice'] != $notice)) {

                $dataForUpdate = array( 'value' => $value );

                if (is_null($notice)) {
                    $dataForUpdate['notice'] = $entityData['notice'];
                } else {
                    $dataForUpdate['notice'] = $notice;
                }

                Mage::getModel($this->_ormConfig)
                         ->load($entityId)
                         ->addData($dataForUpdate)
                         ->save();

                $entityData['value'] = $dataForUpdate['value'];
                $entityData['notice'] = $dataForUpdate['notice'];
            }

            $this->setRowToCache($entityData);
        }
        //----------------------------

        return true;
    }

    private function getValue($group, $key)
    {
        // Get cache row
        $cacheRow = $this->getRowFromCache($group, $key);

        if (is_null($cacheRow)) {

            $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                               ->addFieldToFilter('`group`', (is_null($group)?array('null' => true):$group))
                                                               ->addFieldToFilter('`key`', $key)
                                                               ->toArray();
            if ((int)$tempCollection['totalRecords'] > 0) {

                $row = $tempCollection['items'][0];
                $this->setRowToCache($row);
                return $row['value'];

            } else {
                return NULL;
            }

        } else {
            return $cacheRow['value'];
        }

        return NULL;
    }

    private function getNotice($group, $key)
    {
        // Get cache row
        $cacheRow = $this->getRowFromCache($group, $key);

        if (is_null($cacheRow)) {

            $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                               ->addFieldToFilter('`group`', (is_null($group)?array('null' => true):$group))
                                                               ->addFieldToFilter('`key`', $key)
                                                               ->toArray();
            if ((int)$tempCollection['totalRecords'] > 0) {

                $row = $tempCollection['items'][0];
                $this->setRowToCache($row);
                return $row['notice'];

            } else {
                return NULL;
            }

        } else {
            return $cacheRow['notice'];
        }

        return NULL;
    }

    private function deleteValue($group, $key)
    {
        $tempCollection = Mage::getModel($this->_ormConfig)->getCollection()
                                                           ->addFieldToFilter('`group`', (is_null($group)?array('null' => true):$group))
                                                           ->addFieldToFilter('`key`', $key)
                                                           ->toArray();
        if ((int)$tempCollection['totalRecords'] > 0) {

            $row = $tempCollection['items'][0];
            Mage::getModel($this->_ormConfig)->setId($row['id'])->delete();
            $this->deleteRowFromCache($row);

            return true;

        } else {
            return false;
        }

        return false;
    }

    // ------------------------

    private function setRowToCache($row)
    {
        for ($i=0;$i<count(self::$cache);$i++) {
            if ((int)self::$cache[$i]['id'] === (int)$row['id'] && self::$cache[$i]['orm'] == $this->_ormConfig) {
                $row['orm'] = $this->_ormConfig;
                self::$cache[$i] = $row;
                return true;
            }
        }

        $row['orm'] = $this->_ormConfig;
        self::$cache[] = $row;
        return true;
    }

    private function getRowFromCache($group, $key)
    {
        for ($i=0;$i<count(self::$cache);$i++) {
            if (self::$cache[$i]['orm'] == $this->_ormConfig && self::$cache[$i]['group'] === $group && self::$cache[$i]['key'] === $key) {
                return self::$cache[$i];
            }
        }

        return NULL;
    }

    private function deleteRowFromCache($row)
    {
        for ($i=0;$i<count(self::$cache);$i++) {
            if (self::$cache[$i]['orm'] == $this->_ormConfig && (int)self::$cache[$i]['id'] === (int)$row['id']) {
                array_splice(self::$cache,$i,1);
                return true;
            }
        }

        return false;
    }

    // ########################################
}