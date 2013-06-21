<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('aboutForm');
        //------------------------------

        $this->setTemplate('M2ePro/help/about.phtml');
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
        // Set data for form
        //----------------------------
        $license['mode'] = Mage::getModel('M2ePro/License_Model')->getMode();
        $license['key'] = Mage::helper('M2ePro')->escapeHtml(Mage::getModel('M2ePro/License_Model')->getKey());
        $license['expired_date'] = Mage::getModel('M2ePro/License_Model')->getTextExpiredDate();

        $license['component'] = Mage::getModel('M2ePro/License_Model')->getComponent();
        $license['domain'] = Mage::getModel('M2ePro/License_Model')->getDomain();
        $license['ip'] = Mage::getModel('M2ePro/License_Model')->getIp();
        $license['directory'] = Mage::getModel('M2ePro/License_Model')->getDirectory();

        $this->license = $license;

        $system['name'] = Mage::helper('M2ePro/Server')->getSystem();

        $this->system = $system;

        $location['host'] = Mage::helper('M2ePro/Server')->getHost();
        $location['domain'] = Mage::helper('M2ePro/Server')->getDomain();
        $location['ip'] = Mage::helper('M2ePro/Server')->getIp();

        $this->location = $location;

        $platform['mode'] = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName()));
        $platform['version'] = Mage::helper('M2ePro/Magento')->getVersion();
        $platform['is_secret_key'] = Mage::helper('M2ePro/Magento')->isSecretKeyToUrl();

        $this->platform = $platform;

        $php['version'] = Mage::helper('M2ePro/Server')->getPhpVersion();
        $php['api'] = Mage::helper('M2ePro/Server')->getPhpApiName();
        $php['settings'] = Mage::helper('M2ePro/Server')->getPhpSettings();

        $this->php = $php;

        $mySql['database_name'] = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $mySql['version'] = Mage::helper('M2ePro/Server')->getMysqlVersion();
        $mySql['api'] = Mage::helper('M2ePro/Server')->getMysqlApiName();
        $mySql['prefix'] = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mySql['settings'] = Mage::helper('M2ePro/Server')->getMysqlSettings();
        $mySql['total'] = Mage::helper('M2ePro/Server')->getMysqlTotals();

        $this->mySql = $mySql;

        $module['name'] = Mage::helper('M2ePro/Module')->getName();
        $module['version'] = Mage::helper('M2ePro/Module')->getVersion();
        $module['revision'] = Mage::helper('M2ePro/Module')->getRevision();
        $module['application_key'] = Mage::helper('M2ePro/Module')->getApplicationKey();

        $this->module = $module;

        $moduleDbTables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $magentoDbTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $mysql['tables'] = array();
        foreach ($moduleDbTables as $moduleTable) {

            $arrayKey = $moduleTable;
            $arrayValue = array(
                'is_exist' => false,
                'count_items' => 0,
                'manage_link' => $this->getUrl('*/*/manageDbTable',array('table'=>$arrayKey)),
                'has_model' => false
            );

            // Find model
            //--------------------
            $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
            foreach ($tempModels->asArray() as $tempTable) {
                if ($tempTable['table'] == $arrayKey) {
                    $arrayValue['has_model'] = true;
                    break;
                }
            }
            //--------------------

            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);
			$arrayValue['is_exist'] = in_array($moduleTable, $magentoDbTables);
            
            if ($arrayValue['is_exist']) {
                $dbSelect = $connRead->select()->from($moduleTable,new Zend_Db_Expr('COUNT(*)'));
                $arrayValue['count_items'] = (int)$connRead->fetchOne($dbSelect);
            }

            $mysql['tables'][$arrayKey] = $arrayValue;
		}

        $this->mysql = $mysql;
        //----------------------------

        //----------------------------
        $this->show_manage_db_table = !is_null($this->getRequest()->getParam('show_manage_db_table'));
        //----------------------------
        
        return parent::_beforeToHtml();
    }
}