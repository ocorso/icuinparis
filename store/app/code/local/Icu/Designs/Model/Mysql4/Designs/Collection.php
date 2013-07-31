<?php

class Icu_Designs_Model_Mysql4_Designs_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('designs/designs');
    }
}