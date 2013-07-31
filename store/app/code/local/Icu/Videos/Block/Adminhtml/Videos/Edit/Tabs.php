<?php

class Icu_Videos_Block_Adminhtml_Videos_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('videos_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('videos')->__('Video Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('videos')->__('Video Information'),
          'title'     => Mage::helper('videos')->__('Video Information'),
          'content'   => $this->getLayout()->createBlock('videos/adminhtml_videos_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}