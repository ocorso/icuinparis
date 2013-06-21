<?php
class Icu_Designs_Block_Adminhtml_Designs extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_designs';
    $this->_blockGroup = 'designs';
    $this->_headerText = Mage::helper('designs')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('designs')->__('Add Item');
    parent::__construct();
  }
}