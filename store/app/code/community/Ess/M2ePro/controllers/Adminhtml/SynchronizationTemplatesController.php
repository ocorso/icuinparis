<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SynchronizationTemplatesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/templates')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Templates/SynchronizationTemplatesHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/templates/synchronization');
    }
    
    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates'))
             ->renderLayout();
    }

    public function gridSynchronizationAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_grid')->toHtml();
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
        $model = Mage::getModel('M2ePro/SynchronizationsTemplates')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('M2ePro_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit'))
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_synchronizationTemplates_edit_tabs'))
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
        //--------------------

        // tab: general
        //--------------------
        $keys = array(
            'title',
            'start_auto_list',
            'end_auto_stop'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        //--------------------

        // tab: revise
        //--------------------
        $keys = array(
            'revise_update_ebay_qty',
            'revise_update_ebay_price',
            'revise_update_title',
            'revise_update_sub_title',
            'revise_update_description',
            'revise_change_selling_format_template',
            'revise_change_description_template',
            'revise_change_listing_template'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // tab: relist
        //--------------------
        $keys = array(
            'relist_mode',
            'relist_filter_user_lock',
            'relist_send_data',
            'relist_status_enabled',
            'relist_is_in_stock',
            'relist_qty',
            'relist_qty_value',
            'relist_qty_value_max',
            'relist_schedule_type',
            'relist_schedule_through_value',
            'relist_schedule_through_metric',
            'relist_schedule_week_time',
            'relist_schedule_week_start_time',
            'relist_schedule_week_end_time'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if ($post['relist_schedule_type'] == Ess_M2ePro_Model_SynchronizationsTemplates::RELIST_SCHEDULE_TYPE_WEEK) {

            $keys = array('mo','tu','we','th','fr','sa','su');

            $data['relist_schedule_week'] = '';
            foreach ($keys as $key=>$value) {
                $data['relist_schedule_week'] .= ($data['relist_schedule_week'] == '' ? '' : '_');
                if (array_search($value,$post['relist_schedule_week']) !== false) {
                    $data['relist_schedule_week'] .= $value.'1';
                } else {
                    $data['relist_schedule_week'] .= $value.'0';
                }
            }

            $timeStampTimezone = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
            $timeStampCurrentDay = mktime(0, 0, 0, date('m',$timeStampTimezone), date('d',$timeStampTimezone), date('Y',$timeStampTimezone));

            if ($data['relist_schedule_week_time'] == '1') {

                $temp = explode(':',$data['relist_schedule_week_start_time']);
                $timeStampTemp = $timeStampCurrentDay + (int)$temp[0]*60*60 + (int)$temp[1]*60;
                $data['relist_schedule_week_start_time'] = Mage::helper('M2ePro')->timezoneDateToGmt($timeStampTemp,false,'H:i');

                $temp = explode(':',$data['relist_schedule_week_end_time']);
                $timeStampTemp = $timeStampCurrentDay + (int)$temp[0]*60*60 + (int)$temp[1]*60;
                $data['relist_schedule_week_end_time'] = Mage::helper('M2ePro')->timezoneDateToGmt($timeStampTemp,false,'H:i');

            } else {
                $data['relist_schedule_week_start_time'] = NULL;
                $data['relist_schedule_week_end_time'] = NULL;
            }

        } else {
            $data['relist_schedule_week'] = 'mo0_tu0_we0_th0_fr0_sa0_su0';
            $data['relist_schedule_week_start_time'] = NULL;
            $data['relist_schedule_week_end_time'] = NULL;
        }
        
        unset($data['relist_schedule_week_time']);
        //--------------------

        // tab: stop
        //--------------------
        $keys = array(
            'stop_status_disabled',
            'stop_out_off_stock',
            'stop_qty',
            'stop_qty_value',
            'stop_qty_value_max'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::getModel('M2ePro/SynchronizationsTemplates');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
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
            $template = Mage::getModel('M2ePro/SynchronizationsTemplates')->loadInstance($id);
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