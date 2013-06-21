<?php
class Icu_Videos_Block_Videos extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getVideos()     
     { 
        if (!$this->hasData('videos')) {
            $this->setData('videos', Mage::registry('videos'));
        }
        return $this->getData('videos');
        
    }
}