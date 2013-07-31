<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Feedbacks_Help extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('feedbacksHelp');
        //------------------------------

        $this->setTemplate('M2ePro/feedbacks/help.phtml');
    }
}