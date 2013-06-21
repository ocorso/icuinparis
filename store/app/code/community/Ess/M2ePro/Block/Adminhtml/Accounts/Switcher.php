<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Accounts_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_accountIds;

    protected $_accountVarName = 'account';

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/accounts/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultAccountName($this->__('All Accounts'));
    }

    /**
     * Get accounts
     *
     * @return array
     */
    public function getAccounts()
    {
        return Mage::getModel('M2ePro/Accounts')->getCollection()->getItems();
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_accountVarName => null));
    }

    public function setAccountVarName($varName)
    {
        $this->_accountVarName = $varName;
        return $this;
    }

    public function getAccountId()
    {
        return $this->getRequest()->getParam($this->_accountVarName);
    }

    public function setAccountIds($accountIds)
    {
        $this->_accountIds = $accountIds;
        return $this;
    }

    public function getAccountIds()
    {
        return $this->_accountIds;
    }

    public function isShow()
    {
        return !Mage::getModel('M2ePro/Accounts')->isSingleAccountMode();
    }

    protected function _toHtml()
    {
        if (!Mage::getModel('M2ePro/Accounts')->isSingleAccountMode()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
}