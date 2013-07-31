<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_ManageDbTable extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('aboutManageDbTable');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_about_ManageDbTable';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Manage DB Table "'.Mage::registry('M2ePro_data_table').'"');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/index',array('show_manage_db_table'=>true)).'\')',
            'class'     => 'back'
        ));

        $this->_addButton('goto_about', array(
            'label'     => Mage::helper('M2ePro')->__('About'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('delete_all', array(
            'label'     => Mage::helper('M2ePro')->__('Clear Table'),
            'onclick'   => 'deleteConfirm(\''. Mage::helper('M2ePro')->__('Are you sure?').'\', \'' . $this->getUrl('*/*/deleteDbTableAll',array('table'=>Mage::registry('M2ePro_data_table'),'model'=>Mage::registry('M2ePro_data_model'))) . '\')',
            'class'     => 'delete_all'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlersObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        $javascript = <<<JAVASCRIPT
<script type="text/javascript">

    //-----------------------------

    function deleteDbTableRow(url)
    {
        if (!confirm('Are you sure?')) {
            return;
        }

        new Ajax.Request( url ,
        {
            method:'get',
            onSuccess: function(transport)
            {
                aboutManageDbTableGridJsObject.reload();
            }
        });
    }

    //-----------------------------

    function mouseOverCell(cell_id)
    {
        if ($(cell_id + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cell_id + '_edit_link').show();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();
    }

    function mouseOutCell(cell_id)
    {
        if ($(cell_id + '_save_link').getStyle('display') != 'none') {
            return;
        }

        $(cell_id + '_edit_link').hide();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();
    }

    //-----------------------------

    function saveCell(cell_id,url)
    {
        new Ajax.Request( url ,
        {
            method: 'post',
            asynchronous : false,
            parameters : {
                value : $(cell_id + '_edit_input').value
            },
            onSuccess: function(transport)
            {
                switchCellToView(cell_id);
                aboutManageDbTableGridJsObject.reload();
            }
        });
    }

    function switchCellToView(cell_id)
    {
        $(cell_id + '_edit_link').show();
        $(cell_id + '_view_link').hide();
        $(cell_id + '_save_link').hide();

        $(cell_id + '_edit_container').hide();
        $(cell_id + '_view_container').show();
    }

    function switchCellToEdit(cell_id)
    {
        $(cell_id + '_edit_link').hide();
        $(cell_id + '_view_link').show();
        $(cell_id + '_save_link').show();

        $(cell_id + '_edit_container').show();
        $(cell_id + '_view_container').hide();
    }

    //-----------------------------

</script>
JAVASCRIPT;

        return $javascript.parent::_toHtml();
    }
}