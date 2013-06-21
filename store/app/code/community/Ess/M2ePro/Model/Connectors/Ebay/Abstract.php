<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connectors_Ebay_Abstract extends Ess_M2ePro_Model_Connectors_Abstract
{
    const COMPONENT = 'Ebay';
    const COMPONENT_VERSION = 3;

    const MODE_SANDBOX = 'sandbox';
    const MODE_PRODUCTION = 'production';

    /**
     * @var Ess_M2ePro_Model_Marketplaces|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Accounts|null
     */
    protected $account = NULL;

    /**
     * @var null|int
     */
    protected $mode = NULL;

    // ########################################

    public function __construct(array $params = array(),
                                Ess_M2ePro_Model_Marketplaces $marketplace = NULL,
                                Ess_M2ePro_Model_Accounts $account = NULL,
                                $mode = NULL)
    {
        if ($mode != Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_SANDBOX &&
            $mode != Ess_M2ePro_Model_Connectors_Ebay_Abstract::MODE_PRODUCTION) {
            $mode = NULL;
        }
        
        $this->marketplace = $marketplace;
        $this->account = $account;
        $this->mode = $mode;

        parent::__construct($params);
    }

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

    public function process()
    {
        if (!is_null($this->marketplace)) {
            $this->requestExtraData['marketplace'] = $this->marketplace->getId();
        }

        if (!is_null($this->account)) {
            $this->requestExtraData['account'] = $this->account->getServerHash();
        }

        if (!is_null($this->mode)) {
            $this->requestExtraData['mode'] = $this->mode;
        }

        return parent::process();
    }

    // ########################################

    public static function ebayTimeToString($time)
    {
        return (string)self::getEbayDateTimeObject($time)->format('Y-m-d H:i:s');
    }

    public static function ebayTimeToTimeStamp($time)
    {
        return (int)self::getEbayDateTimeObject($time)->format('U');
    }

    /**
     * @param string|int|DateTime $time
     * @return DateTime|null|string
     */
    private static function getEbayDateTimeObject($time)
    {
        $dateTime = NULL;

        if ($time instanceof DateTime) {
            $dateTime = clone $time;
        } else {
            is_int($time) && $time = '@'.$time;
            $dateTime = new DateTime($time);
        }

        if (is_null($dateTime)) {
            throw new Exception('eBay DateTime object is null');
        }

        $dateTime->setTimezone(new DateTimeZone('UTC'));

        return $dateTime;
    }

    // ########################################
}