<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_M2ePro_Abstract extends Ess_M2ePro_Model_Connectors_Abstract
{
    const COMPONENT = 'M2ePro';
    const COMPONENT_VERSION = 1;

    // ########################################

    protected function getComponent()
    {
        return self::COMPONENT;
    }

    protected function getComponentVersion()
    {
        return self::COMPONENT_VERSION;
    }

    // ########################################
}