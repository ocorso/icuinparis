<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Templates_AbstractBase extends Mage_Adminhtml_Block_Widget
{
    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array(
            '_relative' => true,
            '_area' => 'adminhtml',
            '_package' => 'default',
            '_theme' => 'default'
        );

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        return $templateName;
    }
}