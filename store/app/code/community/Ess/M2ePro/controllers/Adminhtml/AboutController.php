<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_AboutController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/help')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Help'))
             ->_title(Mage::helper('M2ePro')->__('About'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/help/about');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_about'))
             ->renderLayout();
    }

    //#############################################

    public function manageDbTableAction()
    {
        $mainTable = $this->getRequest()->getParam('table');

        if (is_null($mainTable)) {
            $this->_redirect('*/*/index');
            return;
        }

        $mainModel = NULL;

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
        foreach ($tempModels->asArray() as $tempModel => $tempTable) {
            if ($tempTable['table'] == $mainTable) {
                $mainModel = $tempModel;
                break;
            }
        }

        if (is_null($mainModel)) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::register('M2ePro_data_table', $mainTable);
        Mage::register('M2ePro_data_model', $mainModel);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_about_manageDbTable'))
             ->renderLayout();
    }

    public function gridManageDbTableAction()
    {
        $mainTable = $this->getRequest()->getParam('table');

        $mainModel = NULL;

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
        foreach ($tempModels->asArray() as $tempModel => $tempTable) {
            if ($tempTable['table'] == $mainTable) {
                $mainModel = $tempModel;
                break;
            }
        }

        Mage::register('M2ePro_data_table', $mainTable);
        Mage::register('M2ePro_data_model', $mainModel);

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_about_manageDbTable_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteDbTableRowAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->delete();
        exit();
    }

    public function deleteDbTableAllAction()
    {
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        $tableAction  = Mage::getSingleton('core/resource')->getTableName($table);
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()->delete($tableAction);

        $this->_redirect('*/*/manageDbTable',array('table'=>$table));
    }

    public function updateDbTableCellAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $column = $this->getRequest()->getParam('column');
        $value = $this->getRequest()->getParam('value');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        if (strtolower($value) == 'null') {
            $value = NULL;
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->setData($column,$value)->save();
        exit();
    }

    //#############################################
}