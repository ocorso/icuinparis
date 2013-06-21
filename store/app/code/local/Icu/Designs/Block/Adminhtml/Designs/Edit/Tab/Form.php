<?php

class Icu_Designs_Block_Adminhtml_Designs_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('designs_form', array('legend'=>Mage::helper('designs')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('designs')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('designs')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('designs')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('designs')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('designs')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('designs')->__('Content'),
          'title'     => Mage::helper('designs')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getDesignsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDesignsData());
          Mage::getSingleton('adminhtml/session')->setDesignsData(null);
      } elseif ( Mage::registry('designs_data') ) {
          $form->setValues(Mage::registry('designs_data')->getData());
      }
      return parent::_prepareForm();
  }
}