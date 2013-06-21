<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Ebay_Item_SingleAbstract extends Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract
{
    /**
     * @var Ess_M2ePro_Model_ListingsProducts
     */
    protected $listingProduct = NULL;

    // ########################################
    
    public function __construct(array $params = array(), Ess_M2ePro_Model_ListingsProducts $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        parent::__construct($params,$this->listingProduct->getListing());
    }

    // ########################################

    public function process()
    {
        $result = parent::process();

        foreach ($this->messages as $message) {
            $priorityMessage = Ess_M2ePro_Model_ListingsLogs::PRIORITY_MEDIUM;
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priorityMessage = Ess_M2ePro_Model_ListingsLogs::PRIORITY_HIGH;
            }
            $this->addListingsProductsLogsMessage($this->listingProduct, $message, $priorityMessage);
        }

        return $result;
    }

    // ########################################
}