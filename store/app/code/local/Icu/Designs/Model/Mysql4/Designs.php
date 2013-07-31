<?php

class Icu_Designs_Model_Mysql4_Designs extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the designs_id refers to the key field in your database table.
        $this->_init('designs/designs', 'designs_id');
    }
}