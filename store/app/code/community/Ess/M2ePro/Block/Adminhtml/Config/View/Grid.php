<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Config_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configViewGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('group');
        $this->setDefaultDir('ASC');
        $this->setDefaultLimit(200);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################
    
    protected function _prepareCollection()
    {
        // Get collection config
        //--------------------------------
        if (Mage::registry('m2epro_config_mode') == 'ess') {
            $collection = Mage::getModel('M2ePro/EssConfig')->getCollection();
        } else {
            $collection = Mage::getModel('M2ePro/Config')->getCollection();
        }
        //--------------------------------

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('group', array(
            'header'    => Mage::helper('M2ePro')->__('Group'),
            'align'     => 'left',
            'width'     => '300px',
            'type'      => 'text',
            'index'     => 'group',
            'filter_index' => 'main_table.group'
        ));

        $this->addColumn('key', array(
            'header'    => Mage::helper('M2ePro')->__('Key'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'text',
            'index'     => 'key',
            'filter_index' => 'main_table.key',
            'frame_callback' => array($this, 'callbackColumnKey')
        ));

        $this->addColumn('value', array(
            'header'    => Mage::helper('M2ePro')->__('Value'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'text',
            'index'     => 'value',
            'filter_index' => 'main_table.value'
        ));

        $this->addColumn('notice', array(
            'header'    => Mage::helper('M2ePro')->__('Notice'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'notice',
            'filter_index' => 'main_table.notice',
            'frame_callback' => array($this, 'callbackColumnNotice')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '130px',
            'type'      => 'date',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_LONG),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '130px',
            'type'      => 'date',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_LONG),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'right',
            'width'     => '80px',
            'type'      => 'number',
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'id',
            'filter_index' => 'main_table.id',
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnKey($value, $row, $column, $isExport)
    {
        $value = '<strong>'.$value.'</strong>';
        return $value;
    }

    public function callbackColumnNotice($value, $row, $column, $isExport)
    {
        $value = htmlspecialchars($value);
        $value = '<i>'.nl2br($value).'</i>';
        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $data = $row->getData();
        $value = '<a href="javascript:void(0);" onclick="ConfigHandlersObj.setForUpdate(\''.$data['id'].'\',\''.Mage::helper('M2ePro')->escapeJs($data['group']).'\',\''.Mage::helper('M2ePro')->escapeJs($data['key']).'\',\''.Mage::helper('M2ePro')->escapeJs($data['value']).'\',\''.Mage::helper('M2ePro')->escapeJs($data['notice']).'\');">Edit</a>';
        $value .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        $value .= '<a href="javascript:void(0);" onclick="ConfigHandlersObj.removeConfig(\''.Mage::helper('M2ePro')->escapeJs($data['group']).'\',\''.Mage::helper('M2ePro')->escapeJs($data['key']).'\');">'.Mage::helper('M2ePro')->__('Delete').'</a>';
        return $value;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridView', array('_current'=>true,'mode'=>Mage::registry('m2epro_config_mode')));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}