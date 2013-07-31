<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Feedbacks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('feedbacksGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('buyer_feedback_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
	}

	protected function _prepareCollection()
	{
        $collection = Mage::getModel('M2ePro/Feedbacks')->getCollection();

        $collection->getSelect()
                   ->joinLeft(
                       array('ma' => Mage::getResourceModel('M2ePro/Accounts')->getMainTable()),
                       '(`ma`.`id` = `main_table`.`account_id`)',
                       array('account_mode'=>'mode')
                   )
                   ->joinLeft(
                       array('mei' => Mage::getResourceModel('M2ePro/EbayItems')->getMainTable()),
                       '(`mei`.item_id = `main_table`.ebay_item_id)',
                       array('have_seller_feedback' => new Zend_Db_Expr('if(`main_table`.`seller_feedback_text` = \'\', 0, 1)'))
                   )
                   ->joinLeft(
                       array('mlp' => Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable()),
                       '(`mlp`.ebay_items_id = `mei`.id)',
                       array('product_id'=>'id')
                   );

        if ($accountId = $this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter('account_id', $accountId);
        }

		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    if ($field == 'have_seller_feedback') {
                        if ((int)$cond['eq'] == 0) {
                            $this->getCollection()->getSelect()->where('`main_table`.`seller_feedback_text` = \'\'');
                        } else if ((int)$cond['eq'] == 1) {
                            $this->getCollection()->getSelect()->where('`main_table`.`seller_feedback_text` != \'\'');
                        }
                        //$this->getCollection()->getSelect()->having('`have_seller_feedback` = '.(int)$cond['eq'].' ');
                    } else {
                        $this->getCollection()->addFieldToFilter($field , $cond);
                    }
                }
            }
        }
        return $this;
    }

	protected function _prepareColumns()
	{
		$this->addColumn('transaction_id', array(
			'header' => Mage::helper('M2ePro')->__('Transaction ID'),
			'align'  => 'right',
            'type'   => 'text',
            'width'  => '105px',
			'index'  => 'ebay_transaction_id',
            'frame_callback' => array($this, 'callbackColumnTransactionId')
		));

		$this->addColumn('ebay_item_id', array(
			'header' => Mage::helper('M2ePro')->__('Item ID'),
			'align'  => 'right',
            'type'   => 'text',
			'width'  => '50px',
			'index'  => 'ebay_item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
		));

		$this->addColumn('ebay_item_title', array(
			'header' => Mage::helper('M2ePro')->__('Item Title'),
            'type'   => 'text',
			'width'  => '185px',
			'index'  => 'ebay_item_title',
            'frame_callback' => array($this, 'callbackColumnEbayItemTitle')
		));

        $this->addColumn('buyer_feedback_date', array(
            'header' => Mage::helper('M2ePro')->__('Buyer Feedback Date'),
            'width'  => '155px',
            'type'   => 'datetime',
            'index'  => 'buyer_feedback_date',
            'frame_callback' => array($this, 'callbackColumnBuyerFeedbackDate')
        ));

        $this->addColumn('seller_feedback_date', array(
            'header' => Mage::helper('M2ePro')->__('Seller Feedback Date'),
            'width'  => '155px',
            'type'   => 'datetime',
            'index'  => 'seller_feedback_date',
            'frame_callback' => array($this, 'callbackColumnSellerFeedbackDate')
        ));

        $this->addColumn('buyer_feedback_type', array(
            'header'       => Mage::helper('M2ePro')->__('Type'),
            'width'        => '50px',
            'align'        => 'center',
            'type'         => 'options',
            'filter_index' => 'buyer_feedback_type',
            'sortable'     => false,
            'options'      => array(
                'Neutral'  => Mage::helper('M2ePro')->__('Neutral'),
                'Positive' => Mage::helper('M2ePro')->__('Positive'),
                'Negative' => Mage::helper('M2ePro')->__('Negative')
            ),
            'frame_callback' => array($this, 'callbackColumnFeedbackType'),
            'filter_condition_callback' => array($this, 'callbackFilterFeedbackType'),
        ));

        $this->addColumn('feedbacks', array(
            'header'       => Mage::helper('M2ePro')->__('Feedbacks'),
            'align'        => 'left',
            'type'         => 'options',
            'filter_index' => 'have_seller_feedback',
            'sortable'     => false,
            'options'      => array(
                0 => Mage::helper('M2ePro')->__('Unresponded Feedbacks'),
                1 => Mage::helper('M2ePro')->__('Responded Feedbacks')
            ),
            'frame_callback' => array($this, 'callbackColumnFeedbacks')
        ));

		return parent::_prepareColumns();
	}

    // ####################################
    
    public function callbackColumnTransactionId($value, $row, $column, $isExport)
    {
        $value == 0 && $value = Mage::helper('M2ePro')->__('No ID For Auction');
        $url = $this->getUrl('*/*/goToOrder/', array('feedback_id' => $row->getData('id')));
        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('product_id'))) {
            $url = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($value,$row->getData('account_mode'));
            return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
        } else {
            $url = $this->getUrl('*/adminhtml_listings/goToEbay/', array('item_id' => $row->getData('ebay_item_id')));
            return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
        }
    }

    public function callbackColumnEbayItemTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnBuyerFeedbackDate($value, $row, $column, $isExport)
    {
        if (strtotime($row->getData('buyer_feedback_date')) < strtotime('2001-01-02')) {
            return Mage::helper('M2ePro')->__('N/A');
        } else {
            return $value;
        }
    }

    public function callbackColumnSellerFeedbackDate($value, $row, $column, $isExport)
    {
        if (strtotime($row->getData('seller_feedback_date')) < strtotime('2001-01-02')) {
            return Mage::helper('M2ePro')->__('N/A');
        } else {
            return $value;
        }
    }

    public function callbackColumnFeedbackType($value, $row, $column, $isExport)
    {
        $feedbackType = $row->getData('buyer_feedback_type');

        switch ($feedbackType) {
            case Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE:
                $feedbackTypeSign = '+';
                $color = 'green';
                break;
            case Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE:
                $feedbackTypeSign = '-';
                $color = 'red';
                break;
            default:
                $feedbackTypeSign = '=';
                $color = 'gray';
                break;
        }
        
        return "<span style=\"color: {$color};\">{$feedbackTypeSign}</span>";
    }

    public function callbackColumnFeedbacks($value, $row, $column, $isExport)
    {
        if ($buyerFeedback = $row->getData('buyer_feedback_text')) {
            switch ($row->getData('buyer_feedback_type')) {
                case Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE:
                    $color = 'green';
                    break;
                case Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE:
                    $color = 'red';
                    break;
                default:
                    $color = 'gray';
                    break;
            }
            $feedbacksHtml = '<div><label><b>'.Mage::helper('M2ePro')->__('Buyer').': </b><span style="color: '.$color.';">'.Mage::helper('M2ePro')->escapeHtml($buyerFeedback).'</span></label></div>';
        } else {
            $feedbacksHtml = '<div><label><b>'.Mage::helper('M2ePro')->__('Buyer').': </b>N/A</label></div>';
        }

        if ($sellerFeedback = $row->getData('seller_feedback_text')) {
            switch ($row->getData('seller_feedback_type')) {
                case Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE:
                    $color = 'black';
                    break;
                case Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE:
                    $color = 'red';
                    break;
                default:
                    $color = 'gray';
                    break;
            }
            $feedbacksHtml .= '<div><label><b>'.Mage::helper('M2ePro')->__('Seller').': </b><span style="color: '.$color.';">'.Mage::helper('M2ePro')->escapeHtml($sellerFeedback).'</span></label></div></label></div>';
        } else {
            $responseUrl = '<a href="javascript:void(0);" onclick="FeedbacksHandlersObj.openFeedback(this,
                                                                                \''.$row->getData('id').'\',
                                                                                \''.$row->getData('ebay_transaction_id').'\',
                                                                                \''.$row->getData('ebay_item_id').'\',
                                                                                \''.Mage::helper('M2ePro')->escapeJs($row->getData('buyer_feedback_text')).'\');">'.Mage::helper('M2ePro')->__('Send Response').'</a>';

            $feedbacksHtml .= '<div><b>'.Mage::helper('M2ePro')->__('Seller').': </b>'.$responseUrl.'</div>';
        }

        return $feedbacksHtml;
    }

    // ####################################

    public function callbackFilterFeedbackType($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        switch ($value) {
            case Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE:
                $this->getCollection()->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Feedbacks::TYPE_NEGATIVE);
                break;
            case Ess_M2ePro_Model_Feedbacks::TYPE_NEUTRAL:
                $this->getCollection()->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Feedbacks::TYPE_NEUTRAL);
                break;
            case Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE:
                $this->getCollection()->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Feedbacks::TYPE_POSITIVE);
                break;
        }
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridFeedbacks', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}