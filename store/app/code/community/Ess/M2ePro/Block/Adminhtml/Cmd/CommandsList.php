<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Cmd_CommandsList extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('cmdCommandsList');
        //------------------------------

        $this->setTemplate('M2ePro/cmd/commands_list.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->commands = $this->getData('commands');
    }
}