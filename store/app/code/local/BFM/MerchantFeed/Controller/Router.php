<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms Controller Router
 *
 * @category    Mage
 * @package     Mage_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class BFM_MerchantFeed_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        $front->addRouter('merchantfeed', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        
        $cmd = (strpos($identifier, '/') === false) ? array($identifier) : explode('/', $identifier);
        
        $currencies = array(
        	'merchantfeed_euros'   => 'EUR',
        	'merchantfeed_dollars' => 'USD',
        	'merchantfeed_pounds'  => 'GBP',
        );
        
        if(array_key_exists($cmd[0], $currencies) && empty($cmd[1])) {
        	$request->setModuleName('merchantfeed_universal')
            	->setControllerName('index')
            	->setActionName('index')
            	->setParam('currency', $currencies[$cmd[0]]);
            
            $request->setAlias(
	            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
	            $identifier
	        );
	        
	        return true;
        }
        
        return false;
    }
}
