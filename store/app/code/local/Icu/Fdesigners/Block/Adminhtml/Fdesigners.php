<?php
class Icu_Fdesigners_Block_Adminhtml_Fdesigners extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fdesigners';
    $this->_blockGroup = 'fdesigners';
    $this->_headerText = Mage::helper('fdesigners')->__('Manage Featured Designers');
    $this->_addButtonLabel = Mage::helper('fdesigners')->__('Add Featured Designer');
    parent::__construct();
  }
}