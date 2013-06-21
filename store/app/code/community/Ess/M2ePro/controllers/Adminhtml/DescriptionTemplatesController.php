<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_DescriptionTemplatesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/templates')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Description Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Templates/AttributeSetsHandlers.js')
             ->addJs('M2ePro/Templates/DescriptionTemplatesHandlers.js')
             ->addItem('js_css', 'prototype/windows/themes/default.css')
             ->addItem('js_css', 'prototype/windows/themes/magento.css')
             ->addJs('prototype/window.js');

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/templates/description');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_descriptionTemplates'))
             ->renderLayout();
    }

    public function gridDescriptionAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_descriptionTemplates_grid')->toHtml();
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
        $model = Mage::getModel('M2ePro/DescriptionsTemplates')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/*/index');
        }

        $templateAttributeSetsCollection = Mage::getModel('M2ePro/TemplatesAttributeSets')->getCollection()
                                                                                          ->addFieldToFilter('template_id', $id)
                                                                                          ->addFieldToFilter('template_type', Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_DESCRIPTION);

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
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_descriptionTemplates_edit'))
             ->renderLayout();
    }

    //#############################################

    public function getAttributesForConfigurableProductAction()
    {
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        if ($attributeSets == '') {
            exit(json_encode(array()));
        }

        $attributeSets = explode(',',$attributeSets);

        if (!is_array($attributeSets) || count($attributeSets) <= 0) {
            exit(json_encode(array()));
        }

        $attributes = NULL;
        foreach ($attributeSets as $attributeSetId) {

            $attributesTemp = $this->getConfigurableAttributesByAttributeSetId($attributeSetId);

            if (is_null($attributes)) {
                $attributes = $attributesTemp;
                continue;
            }

            $intersectAttributes = array();
            foreach ($attributesTemp as $attributeTemp) {
                $findValue = false;
                foreach ($attributes as $attribute) {
                    if ($attributeTemp['value'] == $attribute['value'] &&
                        $attributeTemp['title'] == $attribute['title']) {
                        $findValue = true;
                        break;
                    }
                }
                if ($findValue) {
                    $intersectAttributes[] = $attributeTemp;
                }
            }

            $attributes = $intersectAttributes;
        }

        exit(json_encode($attributes));
    }

    //----------------------------------

    private function getConfigurableAttributesByAttributeSetId($attributeSetId)
    {
        $attributeSetId = (int)$attributeSetId;

        $product = Mage::getModel('catalog/product');
        $product->setAttributeSetId($attributeSetId);
        $product->setTypeId('configurable');
        $product->setData('_edit_mode', true);

        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);

        $result = array();
        foreach ($attributes as $attribute) {
            if ($product->getTypeInstance(true)->canUseAttribute($attribute, $product)) {
                $result[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'title' => $attribute->getFrontend()->getLabel()
                );
            }
        }

        return $result;
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
            'title_mode',
            'title_template',
            'subtitle_mode',
            'subtitle_template',
            'description_mode',
            'description_template',
            'cut_long_titles',
            'hit_counter',
            'editor_type',
	        'image_main_mode',
            'image_main_attribute',
            'gallery_images_mode',
            'variation_configurable_images'
        );
        
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::getModel('M2ePro/DescriptionsTemplates');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // Attribute sets
        //--------------------
        $oldAttributeSets = Mage::getModel('M2ePro/TemplatesAttributeSets')
                                    ->getCollection()
                                    ->addFieldToFilter('template_type',Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_DESCRIPTION)
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
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_DESCRIPTION,
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
            $template = Mage::getModel('M2ePro/DescriptionsTemplates')->loadInstance($id);
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

    public function previewAction()
    {
        $body = '';
        $errorTxt = false;

        if ((int)$this->getRequest()->getPost('show',0) == 1) {
            
            // form sended
            //--------------------------------
            $templateData = $this->_getSession()->getPreviewFormData();
            if (!$id = $this->getRequest()->getPost('id',NULL)) {
                $id = $this->_getRandomProduct($templateData['attribute_sets']);
            }
            //--------------------------------

            // get attributes sets title
            //--------------------------------
            $attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                        ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                        ->addFieldToFilter('attribute_set_id', $templateData['attribute_sets'])
                                        ->toArray();
            $attributeSetsTitles = '';
            foreach ($attributeSets['items'] as $attributeSet) {
                $attributeSetsTitles != '' && $attributeSetsTitles .= ',';
                $attributeSetsTitles .= $attributeSet['attribute_set_name'];
            }
            //--------------------------------

            if (!$id) {
                $tempString = Mage::helper('M2ePro')->__('There are no any products for "%attr%" attribute set(s).');
                $errorTxt = str_replace('%attr%',Mage::helper('M2ePro')->__($attributeSetsTitles),$tempString);
            } else {

                $id = (int)$id;
                $product = Mage::getModel('catalog/product')->load($id);

                if (!$product->getId()) {
                    $tempString = Mage::helper('M2ePro')->__('Product #%id% does not exist');
                    $errorTxt = str_replace('%id%',$id,$tempString);
                } elseif (!in_array($product->getData('attribute_set_id'),$templateData['attribute_sets'])) {
                    $tempString = Mage::helper('M2ePro')->__('Product #%id% does not belong to "%attr%" attribute set(s).');
                    $errorTxt = str_replace(array('%id%','%attr%'),array($id,Mage::helper('M2ePro')->__($attributeSetsTitles)),$tempString);
                } else {

                    $title = Ess_M2ePro_Model_DescriptionsTemplates::TITLE_MODE_CUSTOM == $templateData['title_mode'] ? Mage::helper('M2ePro/TemplatesParser')->parseTemplate($templateData['title_template'], $product) : $product->getData('name');
                    $subTitle = Ess_M2ePro_Model_DescriptionsTemplates::SUBTITLE_MODE_CUSTOM == $templateData['subtitle_mode'] ? Mage::helper('M2ePro/TemplatesParser')->parseTemplate($templateData['subtitle_template'], $product) : '';

                    $cutLongTitles = !empty($templateData['cut_long_titles']);
                    if ($cutLongTitles) {
                        $title = Mage::getModel('M2ePro/DescriptionsTemplates')->cutLongTitles($title);
                        $subTitle = Mage::getModel('M2ePro/DescriptionsTemplates')->cutLongTitles($subTitle, 55);
                    }

                    $description = $product->getDescription();
                    if (Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_SHORT == $templateData['description_mode']) {
                        $description = $product->getShortDescription();
                    } elseif (Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_CUSTOM == $templateData['description_mode']) {
                        $description = Mage::helper('M2ePro/TemplatesParser')->parseTemplate($templateData['description_template'], $product);
                    }

                    $body = $this->getLayout()->createBlock('M2ePro/adminhtml_descriptionTemplates_preview_body', '', array(
                        'title' => $title,
                        'subtitle' => $subTitle,
                        'description' => $description
                    ))->toHtml();
                }
            }
            
        } else {
            
            // first load
            $templateData = $this->getRequest()->getPost();
            if (!is_array($templateData['attribute_sets'])) {
                $templateData['attribute_sets'] = explode(',', $templateData['attribute_sets']);
            }
            $this->_getSession()->setPreviewFormData($templateData);

        }

        echo $this->getLayout()->createBlock('M2ePro/adminhtml_descriptionTemplates_preview_form', '', array('error_txt' => $errorTxt))->toHtml();
        echo $body;
    }

    private function _getRandomProduct($attributeSets)
    {
        $products = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addFieldToFilter('attribute_set_id', $attributeSets)
                            ->setPage(1,4)
                            ->getItems();

        if (count($products) <= 0) {
            return NULL;
        }

        shuffle($products);
        $product = array_shift($products);

        return (int)$product->getId();
    }

    //#############################################
}