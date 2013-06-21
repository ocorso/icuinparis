<?php

class Icu_Designs_Model_Designs extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('designs/designs');
    }
}