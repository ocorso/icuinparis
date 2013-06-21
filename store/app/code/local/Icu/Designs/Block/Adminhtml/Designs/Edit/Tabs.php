<?php

class Icu_Designs_Block_Adminhtml_Designs_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('designs_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('designs')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('designs')->__('Item Information'),
          'title'     => Mage::helper('designs')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('designs/adminhtml_designs_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}