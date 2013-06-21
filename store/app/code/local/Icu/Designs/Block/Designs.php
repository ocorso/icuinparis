<?php
class Icu_Designs_Block_Designs extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDesigns()     
     { 
        if (!$this->hasData('designs')) {
            $this->setData('designs', Mage::registry('designs'));
        }
        return $this->getData('designs');
        
    }
}