<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_GeneralController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay');
    }

    //#############################################

    public function blockNoticesGetDataFromServerAction()
    {
        exit( Mage::getModel('M2ePro/Connectors_Api_Dispatcher')
                        ->processVirtual('service','get','notice',
                                         array('key'=>$this->getRequest()->getParam('key')),
                                         'value') );
    }

    public function validationCheckRepetitionValueAction()
    {
        $model = $this->getRequest()->getParam('model','');

        $dataField = $this->getRequest()->getParam('data_field','');
        $dataValue = $this->getRequest()->getParam('data_value','');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            exit(json_encode(array('result'=>false)));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, array('in'=>array($dataValue)));
        }

        $idField = $this->getRequest()->getParam('id_field','id');
        $idValue = $this->getRequest()->getParam('id_value','');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, array('nin'=>array($idValue)));
        }

        exit(json_encode(array('result'=>!(bool)$collection->getSize())));
    }

    //#############################################

    public function synchCheckStateAction()
    {
        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if ($lockItemModel->isExist()) {
            exit('executing');
        }

        exit('inactive');
    }

    public function synchGetLastResultAction()
    {
        $logsModel = Mage::getModel('M2ePro/Synchronization_Logs');
        $runsModel = Mage::getModel('M2ePro/Synchronization_Runs');

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronizations_runs_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Synchronization_Logs::TYPE_ERROR)));
        $tempArray = $tempCollection->toArray();

        if ($tempArray['totalRecords'] > 0) {
            exit('error');
        }

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronizations_runs_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Synchronization_Logs::TYPE_WARNING)));
        $tempArray = $tempCollection->toArray();

        if ($tempArray['totalRecords'] > 0) {
            exit('warning');
        }

        exit('success');
    }

    public function synchGetExecutingInfoAction()
    {
        $response = array(
            'mode' => 'executing'
        );

        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if (!$lockItemModel->isExist()) {
            $response['mode'] = 'inactive';
            exit(json_encode($response));
        }

        $response['title'] = $lockItemModel->getContentData('info_title');

        $response['percents'] = (int)$lockItemModel->getContentData('info_percents');
        $response['percents'] < 0 && $response['percents'] = 0;

        $response['status'] = $lockItemModel->getContentData('info_status');

        exit(json_encode($response));
    }

    //#############################################

    public function modelGetAllAction()
    {
        $model = $this->getRequest()->getParam('model','');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $data = $collection->toArray();

        exit(json_encode($data['items']));
    }

    public function modelGetAllByAttributeSetIdAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $attributeSetId = $this->getRequest()->getParam('attribute_set_id','');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $attributeSetId == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $templateType = 0;
        switch ($model) {
            case 'SellingFormatTemplates':
                $templateType = Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_SELLING_FORMAT;
                break;
            case 'DescriptionsTemplates':
                $templateType = Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_DESCRIPTION;
                break;
            case 'ListingsTemplates':
                $templateType = Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING;
                break;
        }

        $tasTable = Mage::getResourceModel('M2ePro/TemplatesAttributeSets')->getMainTable();

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $collection->getSelect()
                   ->join(array('tas'=>$tasTable),'`main_table`.`'.$idField.'` = `tas`.`template_id`',array())
                   ->where('`tas`.`template_type` = ?',(int)$templateType)
                   ->where('`tas`.`attribute_set_id` = ?',(int)$attributeSetId)
                   ->group('main_table.'.$idField);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');
        
        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder($sortField,$sortDir);
        }

        $data = $collection->toArray();

        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        exit(json_encode($data['items']));
    }

    //#############################################

    public function magentoGetAttributesByAttributeSetIdAction()
    {
        $attributeSetId = $this->getRequest()->getParam('attribute_set_id','');
        
        if ($attributeSetId == '') {
            exit(json_encode(array()));
        }

        $attributes = $this->getAttributesByAttributeSetId($attributeSetId);

        exit(json_encode($attributes));
    }
    
    public function magentoGetAttributesByAttributeSetsAction()
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

            $attributesTemp = $this->getAttributesByAttributeSetId($attributeSetId);

            if (!count($attributesTemp)) {
                continue;
            }

            if (is_null($attributes)) {
                $attributes = $attributesTemp;
                continue;
            }

            $intersectAttributes = array();
            foreach ($attributesTemp as $attributeTemp) {
                $findValue = false;
                foreach ($attributes as $attribute) {
                    if ($attributeTemp['label'] == $attribute['label'] &&
                        $attributeTemp['code'] == $attribute['code']) {
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
    
    private function getAttributesByAttributeSetId($attributeSetId)
    {
        $attributeSetId = (int)$attributeSetId;

        $attributesTemp = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
            ->setAttributeSetFilter($attributeSetId)
            ->toArray();

        $attributesTemp = $attributesTemp['items'];

        $attributes = array();
        foreach ($attributesTemp as $attributeTemp) {
            if ((int)$attributeTemp['is_visible'] != 1) {
                continue;
            }
            $attributes[] = array(
                'label' => $attributeTemp['frontend_label'],
                'code'  => $attributeTemp['attribute_code']
            );
        }

        return $attributes;
    }

    //#############################################
}