<?php
class Icu_Videos_Block_Adminhtml_Videos extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_videos';
    $this->_blockGroup = 'videos';
    $this->_headerText = Mage::helper('videos')->__('Video Manager');
    $this->_addButtonLabel = Mage::helper('videos')->__('Add Video');
    parent::__construct();
  }
}