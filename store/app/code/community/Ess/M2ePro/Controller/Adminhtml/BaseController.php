<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_BaseController extends Mage_Adminhtml_Controller_Action
{
    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        if (is_null(Mage::registry('M2ePro_request_params'))) {
            Mage::register('M2ePro_request_params',$this->getRequest()->getParams());
        }

        if (is_null(Mage::registry('M2ePro_base_controller'))) {
            Mage::register('M2ePro_base_controller',$this);
        }

        return $this;
    }

    //#############################################
}