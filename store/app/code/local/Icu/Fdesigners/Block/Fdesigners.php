<?php
class Icu_Fdesigners_Block_Fdesigners extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFdesigners()     
     { 
        if (!$this->hasData('fdesigners')) {
            $this->setData('fdesigners', Mage::registry('fdesigners'));
        }
        return $this->getData('fdesigners');
        
    }
}