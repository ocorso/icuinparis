<?php

class Icu_Fdesigners_Block_Adminhtml_Fdesigners_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('fdesigners_form', array('legend'=>Mage::helper('fdesigners')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('fdesigners')->__('Designer name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('fdesigners')->__('Designer Image'),
          'required'  => false,
          'name'      => 'filename',
	  ));
	  
	  $fieldset->addField('content', 'text', array(
          'label'     => Mage::helper('fdesigners')->__('Link URL'),
          'class'     => 'required-entry validate-url',
          'required'  => true,
          'name'      => 'content',
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('fdesigners')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('fdesigners')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('fdesigners')->__('Disabled'),
              ),
          ),
      ));
     /*
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('fdesigners')->__('Content'),
          'title'     => Mage::helper('fdesigners')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     */
      if ( Mage::getSingleton('adminhtml/session')->getFdesignersData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFdesignersData());
          Mage::getSingleton('adminhtml/session')->setFdesignersData(null);
      } elseif ( Mage::registry('fdesigners_data') ) {
          $form->setValues(Mage::registry('fdesigners_data')->getData());
      }
      return parent::_prepareForm();
  }
}