<?php

class Icu_Videos_Block_Adminhtml_Videos_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('videos_form', array('legend'=>Mage::helper('videos')->__('Video Details')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('videos')->__('Video Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('videos')->__('Image File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
	  
/*
	  $fieldset->addField('filename', 'text', array(
          'label'     => Mage::helper('videos')->__('Video Sub Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'filename',
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('videos')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('videos')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('videos')->__('Disabled'),
              ),
          ),
      ));
  */   
	  $fieldset->addField('status', 'text', array(
          'label'     => Mage::helper('videos')->__('Youtube Video URL'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'status',
      ));
	  
	  $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('videos')->__('Description'),
          'title'     => Mage::helper('videos')->__('Description'),
          'style'     => 'width:400px; height:200px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
	  
     
      if ( Mage::getSingleton('adminhtml/session')->getVideosData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getVideosData());
          Mage::getSingleton('adminhtml/session')->setVideosData(null);
      } elseif ( Mage::registry('videos_data') ) {
          $form->setValues(Mage::registry('videos_data')->getData());
      }
      return parent::_prepareForm();
  }
}