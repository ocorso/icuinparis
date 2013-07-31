<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplaces_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('marketplacesForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/marketplaces.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $marketplaces = Mage::getModel('M2ePro/Marketplaces')
                                    ->getCollection()
                                    ->setOrder('sorder','ASC')
                                    ->setOrder('title','ASC')
                                    ->getItems();
        $groups = array();
        $previewGroup = '';
        $idGroup = 1;
        foreach($marketplaces as $marketplace) {

            if ($marketplace->getGroupTitle() != $previewGroup) {
                $previewGroup = $marketplace->getGroupTitle();
                $groups[] = array(
                    'id'=>$idGroup,
                    'title'=>$previewGroup,
                    'marketplaces' => array()
                );
                $idGroup++;
            }
            
            $locked = (bool)(int)Mage::getModel('M2ePro/ListingsTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('marketplace_id', $marketplace->getId())
                                    ->getSize();

            $marketplace = array(
                'instance' => $marketplace,
                'params' => array('locked'=>$locked)
            );
  
            $groups[count($groups)-1]['marketplaces'][] = $marketplace;
        }

        $this->groups = $groups;
        //----------------------------

        return parent::_beforeToHtml();
    }
}