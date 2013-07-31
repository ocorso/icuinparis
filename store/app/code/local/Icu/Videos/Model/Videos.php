<?php

class Icu_Videos_Model_Videos extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('videos/videos');
    }
}