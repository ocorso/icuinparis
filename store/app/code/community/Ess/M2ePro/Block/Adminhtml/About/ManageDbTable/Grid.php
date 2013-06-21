<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_ManageDbTable_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('aboutManageDbTableGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of prices templates
        $collection = Mage::getModel('M2ePro/'.Mage::registry('M2ePro_data_model'))->getCollection();

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $resourceModel = Mage::getResourceModel('M2ePro/'.Mage::registry('M2ePro_data_model'));

        $tableAction  = Mage::getSingleton('core/resource')->getTableName(Mage::registry('M2ePro_data_table'));
        $columns = $resourceModel->getReadConnection()->fetchAll('SHOW COLUMNS FROM '.$tableAction);

        foreach ($columns as $column) {

            $this->addColumn($column['Field'], array(
                'header'    => '<big>'.$column['Field'].'</big> &nbsp;<small style="font-weight:normal;">('.$column['Type'].')</small>',
                'align'     => 'left',
                //'width'     => '200px',
                'type'      => 'text',
                'index'     => strtolower($column['Field']),
                'filter_index' => 'main_table.'.strtolower($column['Field']),
                'frame_callback' => array($this, 'callbackColumnData')
            ));
        }

        $this->addColumn('actions_row', array(
            'header'    => '&nbsp;'.Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'text',
            'index'     => 'actions_row',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    public function callbackColumnData($value, $row, $column, $isExport)
    {
        $cellId = 'table_row_cell_'.$column->getData('id').'_'.$row->getData('id');

        $htmlValue = '<div id="'.$cellId.'" onmouseover="mouseOverCell(\''.$cellId.'\');" onmouseout="mouseOutCell(\''.$cellId.'\');">';

        $htmlValue .= '<span id="'.$cellId.'_view_container">';
        if (is_null($value)) {
            $htmlValue .= '<span style="color:silver;"><small>NULL</small></span>';
        } else {
            $htmlValue .= Mage::helper('M2ePro')->escapeHtml($value);
        }
        $htmlValue .= '</span>';

        $inputValue = $value;
        is_null($inputValue) && $inputValue = 'NULL';

        $htmlValue .= '<span id="'.$cellId.'_edit_container" style="display:none;">';
        $htmlValue .= '<textarea id="'.$cellId.'_edit_input">'.$inputValue.'</textarea>';
        $htmlValue .= '</span>';

        $tempUrl = $this->getUrl('*/*/updateDbTableCell',array('model'=>Mage::registry('M2ePro_data_model'),'table'=>Mage::registry('M2ePro_data_table'),'id'=>$row->getData('id'),'column'=>$column->getData('id')));

        $htmlValue .= '&nbsp;<a id="'.$cellId.'_edit_link" href="javascript:void(0);" onclick="switchCellToEdit(\''.$cellId.'\');" style="display:none;">edit</a>';
        $htmlValue .= '&nbsp;<a id="'.$cellId.'_view_link" href="javascript:void(0);" onclick="switchCellToView(\''.$cellId.'\');" style="display:none;">cancel</a>';
        $htmlValue .= '&nbsp;<a id="'.$cellId.'_save_link" href="javascript:void(0);" onclick="saveCell(\''.$cellId.'\',\''.$tempUrl.'\');" style="display:none;">save</a>';
        
        return $htmlValue.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $resultHtml = '';
        $tempId = $row->getData('id');

        $tempUrl = $this->getUrl('*/*/deleteDbTableRow',array('model'=>Mage::registry('M2ePro_data_model'),'table'=>Mage::registry('M2ePro_data_table'),'id'=>$tempId));
        $resultHtml .= '<a href="javascript:void(0);" onclick="deleteDbTableRow(\''.$tempUrl.'\')">Delete</a>';

        return $resultHtml;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridManageDbTable', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/editDbTableRow', array('id' => $row->getId()));
    }

    // ####################################
}