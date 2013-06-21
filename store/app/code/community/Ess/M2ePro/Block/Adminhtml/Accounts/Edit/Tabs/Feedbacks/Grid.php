<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Edit_Tabs_Feedbacks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $accountData = Mage::registry('M2ePro_data');

        // Initialization block
        //------------------------------
        $this->setId('feedbacksTemplatesGrid'.$accountData->getId());
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $accountData = Mage::registry('M2ePro_data');

        // Get collection of synchronizations
        $collection = Mage::getModel('M2ePro/FeedbacksTemplates')
                                    ->getCollection()
                                    ->addFieldToFilter('main_table.account_id', $accountData->getId());

        //exit($collection->getSelect()->__toString());
        
        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ft_id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('ft_title', array(
            'header'    => Mage::helper('M2ePro')->__('Text'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'body',
            'filter_index' => 'main_table.body',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('ft_create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('ft_update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('ft_action_edit', array(
            'header'    => Mage::helper('M2ePro')->__('Edit'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'frame_callback' => array($this, 'callbackActionEdit')
        ));

        $this->addColumn('ft_action_delete', array(
            'header'    => Mage::helper('M2ePro')->__('Delete'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'frame_callback' => array($this, 'callbackActionDelete')
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

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    // ####################################

    public function callbackActionEdit($value, $row, $column, $isExport)
    {
        $value = '<a href="javascript:void(0);" onclick="AccountsHandlersObj.feedbacksOpenEditForm(\''.$row->getData('id').'\',\''.Mage::helper('M2ePro')->escapeJs($row->getData('body')).'\');">'.Mage::helper('M2ePro')->__('Edit').'</a>';
        return $value;
    }

    public function callbackActionDelete($value, $row, $column, $isExport)
    {
        $value = '<a href="javascript:void(0);" onclick="AccountsHandlersObj.feedbacksDeleteAction(\''.$row->getData('id').'\');">'.Mage::helper('M2ePro')->__('Delete').'</a>';
        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridFeedbacksTemplates', array('_current'=>true));
    }
    
    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}