<?php

class Icu_Fdesigners_Block_Adminhtml_Fdesigners_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('fdesigners_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('fdesigners')->__('Designer Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('fdesigners')->__('Designer Information'),
          'title'     => Mage::helper('fdesigners')->__('Designer Information'),
          'content'   => $this->getLayout()->createBlock('fdesigners/adminhtml_fdesigners_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}