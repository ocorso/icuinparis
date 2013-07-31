<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SellingFormatTemplatesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/templates')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Selling Format Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Templates/AttributeSetsHandlers.js')
             ->addJs('M2ePro/Templates/SellingFormatTemplatesHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/templates/selling_format');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_sellingFormatTemplates'))
             ->renderLayout();
    }

    public function gridSellingFormatAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_sellingFormatTemplates_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/SellingFormatTemplates')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/*/index');
        }

        $templateAttributeSetsCollection = Mage::getModel('M2ePro/TemplatesAttributeSets')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('template_id', $id)
                                        ->addFieldToFilter('template_type', Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_SELLING_FORMAT);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');
        $templateAttributeSets = $templateAttributeSetsCollection->toArray();

        $attributeSetsIds = array();
        foreach ($templateAttributeSets['items'] as $attributeSet) {
            $attributeSetsIds[] = $attributeSet['attribute_set_id'];
        }

        $model->addData(array('attribute_sets' => $attributeSetsIds));

        Mage::register('M2ePro_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_sellingFormatTemplates_edit'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();

        $keys = array(
            'title',

            'listing_type',
	        'listing_type_attribute',

	        'listing_is_private',

	        'duration_ebay',
	        'duration_attribute',

	        'qty_mode',
	        'qty_custom_value',
	        'qty_custom_attribute',

            'currency',

            'price_variation_mode',

            'start_price_mode',
            'start_price_coefficient',
            'start_price_custom_attribute',

            'reserve_price_mode',
            'reserve_price_coefficient',
            'reserve_price_custom_attribute',

            'buyitnow_price_mode',
            'buyitnow_price_coefficient',
            'buyitnow_price_custom_attribute',

            'best_offer_mode',

            'best_offer_accept_mode',
            'best_offer_accept_value',
            'best_offer_accept_attribute',

            'best_offer_reject_mode',
            'best_offer_reject_value',
            'best_offer_reject_attribute'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        
        $data['start_price_coefficient'] = str_replace(',', '.', $data['start_price_coefficient']);
        $data['reserve_price_coefficient'] = str_replace(',', '.', $data['reserve_price_coefficient']);
        $data['buyitnow_price_coefficient'] = str_replace(',', '.', $data['buyitnow_price_coefficient']);
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::getModel('M2ePro/SellingFormatTemplates');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // Attribute sets
        //--------------------
        $oldAttributeSets = Mage::getModel('M2ePro/TemplatesAttributeSets')
                                    ->getCollection()
                                    ->addFieldToFilter('template_type',Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_SELLING_FORMAT)
                                    ->addFieldToFilter('template_id',(int)$id)
                                    ->getItems();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_TemplatesAttributeSets */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_SELLING_FORMAT,
                'template_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/TemplatesAttributeSets')->setData($dataForAdd)->save();
        }
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        foreach ($idsForDelete as $id) {
            $template = Mage::getModel('M2ePro/SellingFormatTemplates')->loadInstance($id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) were successfully deleted.');
        $deleted && $this->_getSession()->addSuccess(str_replace('%count%',$deleted,$tempString));

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) are in use in Listing(s). Template must not be in use.');
        $locked && $this->_getSession()->addError(str_replace('%count%',$locked,$tempString));

        $this->_redirect('*/*/index');
    }

    //#############################################
}