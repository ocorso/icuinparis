<?php

class Icu_Videos_Block_Adminhtml_Videos_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('videosGrid');
      $this->setDefaultSort('videos_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('videos/videos')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('videos_id', array(
          'header'    => Mage::helper('videos')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'videos_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('videos')->__('Video Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));


      $this->addColumn('status', array(
			'header'    => Mage::helper('videos')->__('Source URL'),
			'width'     => '350px',
			'index'     => 'status',
      ));

/*
      $this->addColumn('status', array(
          'header'    => Mage::helper('videos')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	*/  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('videos')->__('Action'),
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('videos')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('videos')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('videos')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('videos_id');
        $this->getMassactionBlock()->setFormFieldName('videos');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('videos')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('videos')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('videos/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('videos')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('videos')->__('Status'),
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