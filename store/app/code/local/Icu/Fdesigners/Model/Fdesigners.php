<?php

class Icu_Fdesigners_Model_Fdesigners extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fdesigners/fdesigners');
    }
}