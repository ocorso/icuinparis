<?php

class Icu_Fdesigners_Model_Mysql4_Fdesigners extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the fdesigners_id refers to the key field in your database table.
        $this->_init('fdesigners/fdesigners', 'fdesigners_id');
    }
}