<?php

class Icu_Fdesigners_Block_Adminhtml_Fdesigners_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('fdesignersGrid');
      $this->setDefaultSort('fdesigners_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('fdesigners/fdesigners')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('fdesigners_id', array(
          'header'    => Mage::helper('fdesigners')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'fdesigners_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('fdesigners')->__('Designers Name'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('fdesigners')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('fdesigners')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('fdesigners')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('fdesigners')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('fdesigners')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('fdesigners')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('fdesigners_id');
        $this->getMassactionBlock()->setFormFieldName('fdesigners');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('fdesigners')->__('Delete Designers'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('fdesigners')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('fdesigners/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('fdesigners')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('fdesigners')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}