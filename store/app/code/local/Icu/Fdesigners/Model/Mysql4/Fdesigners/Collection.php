<?php

class Icu_Fdesigners_Model_Mysql4_Fdesigners_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fdesigners/fdesigners');
    }
}