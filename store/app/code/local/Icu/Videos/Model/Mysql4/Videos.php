<?php

class Icu_Videos_Model_Mysql4_Videos extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the videos_id refers to the key field in your database table.
        $this->_init('videos/videos', 'videos_id');
    }
}