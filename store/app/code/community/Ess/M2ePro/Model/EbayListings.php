<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayListings extends Mage_Core_Model_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Accounts
     */
    private $_accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplaces
     */
    private $_marketplaceModel = NULL;
    
    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayListings');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_EbayListings
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('3rd Party Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $accountId
     * @return Ess_M2ePro_Model_EbayListings
     */
    public function loadByAccount($accountId)
    {
        $this->load($accountId,'account_id');

        if (is_null($this->getId())) {
             throw new Exception('3rd Party Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $marketplaceId
     * @return Ess_M2ePro_Model_EbayListings
     */
    public function loadByMarketplace($marketplaceId)
    {
        $this->load($marketplaceId,'marketplace_id');

        if (is_null($this->getId())) {
             throw new Exception('3rd Party Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_accountModel = NULL;
        $this->_marketplaceModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_accountModel)) {
            $this->_accountModel = Mage::getModel('M2ePro/Accounts')
                 ->loadInstance($this->getData('account_id'));
        }

        return $this->_accountModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Accounts $instance
     * @return void
     */
    public function setAccount(Ess_M2ePro_Model_Accounts $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_accountModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Marketplaces
     */
    public function getMarketplace()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_marketplaceModel)) {
            $this->_marketplaceModel = Mage::getModel('M2ePro/Marketplaces')
                 ->loadInstance($this->getData('marketplace_id'));
        }

        return $this->_marketplaceModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_Marketplaces $instance
     * @return void
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplaces $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_marketplaceModel = $instance;
    }

    // ########################################

    public function getUsingMarketplacesIds()
    {
        $tableName = Mage::getResourceModel('M2ePro/EbayListings')->getMainTable();
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
                             ->from($tableName,new Zend_Db_Expr('DISTINCT `marketplace_id`'));

        return array_values($connRead->fetchCol($dbSelect));
    }

    // ########################################

    public function getEbayItem()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (double)$this->getData('ebay_item');
    }

    public function getEbayPrice()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (float)$this->getData('ebay_price');
    }

    public function getEbayCurrency()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_currency');
    }

    public function getEbayTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('ebay_title');
    }

    public function getEbayQty()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_qty');
    }

    public function getEbayQtySold()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_qty_sold');
    }

    public function getEbayBids()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('ebay_bids');
    }

    public function getStatus()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('status');
    }

    //----------------

    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED;
    }

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED;
    }

    //----------------

    public function isListable()
    {
        return $this->isNotListed() || $this->isSold() || $this->isStopped() || $this->isFinished();
    }

    public function isRelistable()
    {
        return $this->isSold() || $this->isStopped() || $this->isFinished();
    }

    public function isRevisable()
    {
        return $this->isListed();
    }

    public function isStoppable()
    {
        return $this->isListed();
    }

    // ########################################

    public function relistEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST,$params);
    }

    public function stopEbay(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP,$params);
    }

    //----------------

    protected function processDispatcher($action, array $params = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return Mage::getModel('M2ePro/Connectors_Ebay_EbayItem_Dispatcher')->process($action, $this->getId(), $params);
    }

    // ########################################
}