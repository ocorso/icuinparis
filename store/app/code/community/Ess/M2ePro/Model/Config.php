<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Config extends Ess_M2ePro_Model_ConfigBase
{
    // ########################################
    
    public function __construct()
    {
        parent::__construct(array('orm'=>'M2ePro/Config'));
    }

	public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Config');
    }

    // ########################################
}