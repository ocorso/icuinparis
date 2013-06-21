<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_FeedbacksTemplates extends Mage_Core_Model_Abstract
{
    /**
     *
     * @var Ess_M2ePro_Model_Accounts
     */
    private $_accountModel = NULL;

    // ########################################

	public function _construct()
	{
		parent::_construct();
		$this->_init('M2ePro/FeedbacksTemplates');
	}

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_FeedbacksTemplates
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Feedback template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $accountId
     * @return Ess_M2ePro_Model_FeedbacksTemplates
     */
    public function loadByAccount($accountId)
    {
        $this->load($accountId,'account_id');

        if (is_null($this->getId())) {
             throw new Exception('Feedback does not exist. Probably it was deleted.');
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

    // ########################################

    public function getBody()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('body');
    }

    // ########################################
}