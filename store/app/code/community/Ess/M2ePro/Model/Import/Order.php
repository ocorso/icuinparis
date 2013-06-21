<?php

/*
 * Class for import and create orders that receive from ebay Account
 *
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Import_Order
{
    // We specify store id
    const STORE_MODE_SELECTED = 0;

    // Get default store id
    const STORE_MODE_DEFAULT = 1;

    /** @var Mage_Sales_Model_Quote */
    protected $_quote = null;
    protected $_webSiteId = null;
    protected $_storeId = null;
    protected $_orderCurrencyCode = 'USD';
    protected $_orderCurrencyRate = 1;
    protected $_shippingCurrencyRate = 1;
    protected $_tempStoreCurrencyCode = null;

    protected $_taxPercent = 0;
    protected $_taxAmount = 0;
    protected $_taxIncludesShipping = false;

    protected $_productTaxClassId = null;

    /** @var int Percent of tax for shipping. Need exclude this percent from total shipping price */
    protected $_percentShippingTaxPrice = 0;

    // List of messages on order creation
    protected $_notifyMessageList = array();

    /**
     * Manual create orders
     *
     * @param array $productInCardList list of product that customer buy on eBay.
     *
     * @param array $billingAddress customer billingAddress
     * @param array $shippingDetails shipping details and price for customer. For shipping used method ebayshipping. Params:
     * [title] => Title for shipping method
     * [price] => Price of shipping
     * @param int $checkoutMode mode of checkout.
     * Possible values:
     * 0 - Guest checkout,
     * 1 - register each user,
     * 2 - assign each order to single customer
     * @param int $storeId store where locate product
     * @param String $currencyId currency code for order
     * @param int $notifyMode whan event need to notify (0 - none, 1 - create customer, 2 - customer & order
     * @return int create order ID
     *
     */
    public function createOrder($paramsArray)
    {
        // Order products data (product_id, price, qty, options)
        // -----------------------
        $productInCardList = $paramsArray['products'];
        // -----------------------

        // Customer data
        // -----------------------
        $customerInfo = $paramsArray['customer'];
        $notifyCreateOrder = $paramsArray['customer']['notifyOrder'];
        $notifyRegistration = $paramsArray['customer']['notifyRegistration'];

        $billingAddress = $paramsArray['billingAddress'];
        $shippingDetails = $paramsArray['shippingDetails'];
        // -----------------------

         // -------------------------------------
        $paymentDetails = $paramsArray['paymentDetails'];
        // -------------------------------------

        // -------------------------------------
        $checkoutMode = $paramsArray['checkoutMode'];
        $checkoutMessage = $paramsArray['checkoutMessage'];
        $storeId = isset($paramsArray['storeId']) ? (int)$paramsArray['storeId'] : 0;
        $currencyId = $paramsArray['currencyId'];
        $newOrderStatus = isset($paramsArray['orderStatus']) ? $paramsArray['orderStatus'] : 'pending';
        // -------------------------------------

        $resultOfCreatingOrderId = array(
            'orderId' => 0, // Created order id
            'message' => '', // Error message happens on order creation
            'message_trace' => '',
        );

        $this->_resetOrderCreateInfo();

        try {

            $billingAddress = $this->_checkCustomerAddress($billingAddress);

            $this->_initQuote($checkoutMode);

            // Init storeId and webSiteId
            $this->_initStore($storeId);

            // Assign customer to quote.
            $this->_assignCustomer($customerInfo, $checkoutMode, $billingAddress, $notifyRegistration);

            $this->_initCurrencyRates($currencyId);

            $this->_initQuoteBillingShippingAddress($billingAddress);

            $this->_initTaxAndCurrency($paramsArray);

            $this->_addProductsToQuote($productInCardList, $paramsArray);

            $this->_initShippingMethod($shippingDetails);

            $this->_initPaymentMethod($paymentDetails);

            $order = $this->_placeOrder($checkoutMessage, $newOrderStatus, $notifyCreateOrder);
            $resultOfCreatingOrderId['orderId'] = $order->getId();

            $this->_initPaymentTransaction($order, $paymentDetails);

            // Error message for created message
        } catch (Exception $exception) {
            // Exeption of creating order
            // Save message to return into LOG
            $resultOfCreatingOrderId['message'] = $exception->getMessage();
            $resultOfCreatingOrderId['message_trace'] = $exception->getTraceAsString();
        }

        $this->_resetOrderCreateInfo();

        return $resultOfCreatingOrderId;
    }

    protected function _resetOrderCreateInfo()
    {
        Mage::unregister('ebayShippingData');
        Mage::unregister('ebayPaymentData');

        if ($this->_quote != null) {
            $this->_quote->setIsActive(false);
            $this->_quote->save();
            $this->_quote = null;
        }

        Mage::helper('M2ePro/Module')->getConfig()->deleteGroupValue('/synchronization/orders/', 'current_magento_order_id');

        $this->_clearNotifyMessages();

        $this->_webSiteId = null;
        $this->_storeId = null;
        $this->_orderCurrencyRate = 1;
        $this->_shippingCurrencyRate = 1;

        $this->_taxPercent = 0;
        $this->_taxIncludesShipping = false;
        $this->_productTaxClassId = null;
    }

    /**
     * @param  $addressInformation
     * @return array
     */
    protected function _checkCustomerAddress($addressInformation)
    {
        if (is_string($addressInformation)) {
            $addressInformation = unserialize($addressInformation);
        }
        if (!isset($addressInformation['telephone']) || $addressInformation['telephone'] == '') {
            $addressInformation['telephone'] = '0000000000';
        }

        if (!isset($addressInformation['region_id']) || $addressInformation['region_id'] == null) {
            $addressInformation['region_id'] = 1;
        }
        if (!isset($addressInformation['region']) || $addressInformation['region'] == null) {
            $addressInformation['region'] = '';
        }

        $addressInformation['region_id'] = Mage::getModel('directory/region')->loadByCode($addressInformation['region_id'], $addressInformation['country_id'])->getId();

        if (is_null($addressInformation['region_id'])) {
            $addressInformation['region_id'] = Mage::getModel('directory/region')->loadByName($addressInformation['region_id'], $addressInformation['country_id'])->getId();
        }

        if (is_null($addressInformation['region_id']) || $addressInformation['region_id'] == '') {
            $addressInformation['region_id'] = 1;
        }

        if (!isset($addressInformation['postcode']) || $addressInformation['postcode'] == '') {
            $addressInformation['postcode'] = '0000';
        }

        $addressInformation['customer_password'] = $addressInformation['confirm_password'] = $this->_getRandomString();

        $addressInformation['save_in_address_book'] = 0;

        if ((!isset($addressInformation['firstname']) || $addressInformation['firstname'] == '') && (($spacePos = strpos($addressInformation['lastname'], '')) !== false)) {
            $addressInformation['firstname'] = substr($addressInformation['lastname'], 0, $spacePos);
            $addressInformation['lastname'] = substr($addressInformation['lastname'], $spacePos);
        } else if ((!isset($addressInformation['firstname']) || $addressInformation['firstname'] == '')) {
            $addressInformation['firstname'] = 'Name';
        }

        return $addressInformation;
    }

    protected function _initQuote($checkoutMode = Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_GUEST)
    {
        $this->_quote = Mage::getModel('sales/quote');
        switch ($checkoutMode) {
            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_GUEST:
                $this->_quote->setCheckoutMethod('guest')->save();
                break;
            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_NEW:
            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_EXIST:
                $this->_quote->setCheckoutMethod('register')->save();
                break;
            default:
                throw new LogicException('Invalid checkout mode');
        }
    }

    protected function _initStore($storeId)
    {
        if ($storeId > 0) {
            // When specify storeId, get correct website id
            // -------------------------------------
            $this->_webSiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
            $this->_storeId = $storeId;
            // -------------------------------------
        } else {
            // If store Id not set, set first from default website
            // Need for proper import order contains bundle product
            // -------------------------------------
            $this->_webSiteId = Mage::helper('M2ePro/Sales')->getDefaultWebsiteId();
            $this->_storeId = Mage::helper('M2ePro/Sales')->getDefaultStoreId();
            // -------------------------------------
        }

        $store = $this->_quote->getStore()->load($this->_storeId);
        // Init store where we import product
        $this->_quote->setStore($store);

        return $this->_storeId;
    }

    /**
     * @param  $customerInfo
     *   [customer] => Array
     *       (
     *           [bindId] => 1 (int)
     *           [website] => 1 (int)
     *           [group] => 1 (int)
     *           [newsletter] => 0|1
     *       )
     *
     * @param  $checkoutMode
     * @param  $billingAddress
     * @param  $needNotifyCustomer
     * @return Ess_M2ePro_Model_MagentoSales
     */
    protected function _assignCustomer($customerInfo, $checkoutMode, $billingAddress, $needNotifyCustomer)
    {
        $customer = Mage::getModel('customer/customer');

        switch ($checkoutMode) {
            default:
            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_GUEST:
                $this->_quote->setCustomerId(null)
                             ->setCustomerEmail($billingAddress['email'])
                             ->setCustomerIsGuest(true)
                             ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                return $this;

            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_NEW:
                // Register each customer
                // If we have customer with selected email - assign customer to quote

                $customer->setWebsiteId($customerInfo['website']);
                // Try to use storeId for customer
                $customer->loadByEmail($billingAddress['email']);

                if ($customer->getId()) {
                    // Customer this such email already exist
                    // If this customer need confirmation, manual activate
                    if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
                        $customer->setConfirmation(null);
                        $customer->save();
                    }
                } else {
                    // We need to create customer and register it
                    $customer = $this->_createCustomer($billingAddress,
                                                       $customerInfo['website'],
                                                       $customerInfo['group'],
                                                       $needNotifyCustomer,
                                                       $customerInfo['newsletter']);
                }
                break;

            case Ess_M2ePro_Model_Accounts::ORDERS_CUSTOMER_MODE_EXIST:
                if (!$customerInfo['bindId']) {
                    throw new LogicException('The customer ID was not specified in eBay account settings for Predefined customer mode. ');
                }
                // Assign all checkout to single account
                // Load account
                $customer = Mage::getModel('customer/customer')->load($customerInfo['bindId']);
                break;
        }

        if (!$customer->getId()) {
            // Not found - Customer
            throw new LogicException('Cannot find customer for order. It is not existed or was deleted.');
        }

        $this->_quote->assignCustomer($customer);
    }

    /**
     * Create new magento customer with specify details
     *
     * @param array $billingAddress array with information about firstname, lastname, email, password
     * @param int $websiteId assign customer to specify website
     * @param int $groupId assign customer to specify group
     * @param bool $sendRegistrationEmail when true send email to customer
     * @param bool $subscribeToNewsletter when true subscribe customer to newsletter
     * @return Mage_Core_Model_Abstract
     */
    protected function _createCustomer($billingAddress,
        $websiteId = 0,
        $groupId = 0,
        $sendRegistrationEmail = false,
        $subscribeToNewsletter = false)
    {
        // Create account
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setId(null);

        $customer->setData('firstname', $billingAddress['firstname']);
        $customer->setData('lastname', $billingAddress['lastname']);
        $customer->setData('email', $billingAddress['email']);
        $customer->setData('password', $billingAddress['customer_password']);
        $customer->setData('confirmation', $billingAddress['customer_password']);

        // Assign customer to website
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        } else {
            // No information where assign customer, get default
            if (!$this->_storeId) {
                // assign for default WebSite
                $customer->setWebsiteId($this->_webSiteId);
            } else {
                // assign for store
                $customer->setStore($this->_quote->getStore());
            }
        }

        // Assign customer to specific group
        if ($groupId) {
            $customer->setGroupId($groupId);
        }

        // Send e-mail notify with created information
        if ($sendRegistrationEmail) {
            $customer->sendNewAccountEmail('registered', '', $this->_storeId);
        }

        if ($subscribeToNewsletter) {
            $customer->setIsSubscribed(1);
        }

        $customer->save();

        // Remove confirmation if required
        if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        // Add customer address
        // ---------------------------
        $customerAddress = Mage::getModel('customer/address')->setData($billingAddress)
                                                             ->setCustomerId($customer->getId())
                                                             ->setIsDefaultBilling('1')
                                                             ->setIsDefaultShipping('1');

        $customerAddress->save();
        // ---------------------------

        return $customer;
    }

    protected function _initCurrencyRates($currencyId = null)
    {
        /** Currency code (Base Magento Currency, load for convert rate) */
        $currencyCode = null;

        $currencyModel = Mage::getModel('directory/currency');

        // Get all base and allowed currencies
        $allowedCurrency = $currencyModel->getConfigAllowCurrencies();
        $baseCurrency = $currencyModel->getConfigBaseCurrencies();

        if (!isset($baseCurrency[0]) && !count($baseCurrency)) {
            $baseCurrency = array(
                0 => 'USD' // This is default value for base currency
            );
        }

        if (!$currencyId) {
            $currencyId = $baseCurrency[0];
            $shippingCurrency = $baseCurrency[0];
        } else {
            $shippingCurrency = $currencyId;
        }

        $orderCurrencyAllowed = in_array($currencyId, $allowedCurrency);
        $orderCurrencyIsBase = in_array($currencyId, $baseCurrency);

        $shippingCurrencyAllowed = in_array($shippingCurrency, $allowedCurrency);
        // $shippingCurrencyIsBase =  in_array($shippingCurrency, $baseCurrency);

        // Check for need convert order price
        $needConvertOrderCurrency = false;
        $storeCurrency = $baseCurrency[0];
        if ($orderCurrencyAllowed && $orderCurrencyIsBase) {
            // No need conversion, currency same as base
            $storeCurrency = $currencyCode = $currencyId;
        } else if ($orderCurrencyAllowed && !$orderCurrencyIsBase) {
            // Need conversion, quote currency is selected. Convert base price
            $currencyCode = $baseCurrency[0];
            $storeCurrency = $currencyId;
            $needConvertOrderCurrency = true;
        } else if (!$orderCurrencyAllowed) {
            // eBay currency not allowed
            $storeCurrency = $currencyCode = $baseCurrency[0];
            $this->_addNotifyMessage(
                "Store and Order's Product Currency are different. Conversion from <b>{$currencyId} to {$currencyCode}</b> is not performed. Currency <b>{$currencyId}</b> is unknown."
            );
        }

        // Check for need convert shipping price
        $needConvertShippingCurrency = false;
        if ($shippingCurrencyAllowed && $shippingCurrency == $currencyCode) {
            // No need convert
        } else if ($shippingCurrencyAllowed && $shippingCurrency != $currencyCode) {
            $needConvertShippingCurrency = true;
        } else if (!$shippingCurrencyAllowed) {
            $this->_addNotifyMessage(
                "Store and Shipping Currency are different. Conversion from <b>{$shippingCurrency} to {$currencyCode}</b> is not performed. Currency <b>{$shippingCurrency}</b> is unknown."
            );
        }

        // Calculate convert rate for currency
        $currencyModel->load($currencyCode);

        $orderConvertRate = 1;
        $shippingConvertRate = 1;

        if ($needConvertOrderCurrency) {
            $orderConvertRate = $currencyModel->getAnyRate($currencyId);
            if ($orderConvertRate != 0) {
                $this->_addNotifyMessage(
                    "Store and Order's Product Currency are different. Conversion from <b>{$currencyId} to {$currencyCode}</b> performed using <b>" .
                    (round($orderConvertRate, 2)) . '</b> as a rate.'
                );

            } else {
                $orderConvertRate = 1;
                $this->_addNotifyMessage(
                    "Store and Order's Product Currency are different. Conversion from <b>{$currencyId} to {$currencyCode}</b>
                         not performed. There is no rate amongst Currency Rates.");
            }
        }

        if ($needConvertShippingCurrency) {
            $shippingConvertRate = $currencyModel->getAnyRate($shippingCurrency);
            if ($shippingConvertRate != 0) {
                $this->_addNotifyMessage(
                    "Store and Order's Shipping Currency are different. Conversion from <b>{$shippingCurrency} to {$currencyCode}</b> performed using <b>" .
                    (round($shippingConvertRate, 2)) . '</b> as a rate.'
                );
            } else {
                $shippingConvertRate = 1;
                $this->_addNotifyMessage(
                    "Store and Shipping Currency are different. Conversion from <b>{$shippingCurrency} to {$currencyCode}</b>
                         not performed. There is no rate amongst Currency Rates.");
            }
        }

        $this->_orderCurrencyCode = $storeCurrency;
        $this->_orderCurrencyRate = $orderConvertRate;
        $this->_shippingCurrencyRate = $shippingConvertRate;
    }

    protected function _initQuoteBillingShippingAddress($billingAddress)
    {
        // Set Billing and Shipping Address for quote
        $billAddress = $this->_quote->getBillingAddress();
        unset($billingAddress['address_id']);
        $billAddress->addData($billingAddress);
        $billAddress->implodeStreetAddress();

        $shipping = $this->_quote->getShippingAddress();
        $shipping->setSameAsBilling(0);

        $shipping->addData($billingAddress);
        $shipping->implodeStreetAddress();
    }

    protected function _initTaxAndCurrency($paramsArray)
    {
        // Order tax percent
        // -------------------------------------
        $this->_taxPercent = $paramsArray['taxPercent'];
        // -------------------------------------

        // Order tax amount
        // -------------------------------------
        $this->_taxAmount = $paramsArray['taxAmount'];
        // -------------------------------------

        // Has tax on shipping
        // -------------------------------------
        $this->_taxIncludesShipping = (bool)(int)$paramsArray['taxIncludesShipping'];
        // -------------------------------------

        // Hack for cron
        // -------------------------------------
        $tempHeadersSentThrowsException = Mage::$headersSentThrowsException;
        Mage::$headersSentThrowsException = false;
        // -------------------------------------

        // Set store current currency
        // -------------------------------------
        $this->_quote->getStore()->setCurrentCurrencyCode($this->_orderCurrencyCode);
        // -------------------------------------

        // Hack for cron
        // -------------------------------------
        Mage::$headersSentThrowsException = $tempHeadersSentThrowsException;
        // -------------------------------------
    }

    protected function _concatenateProductsInQuote($productInCardList)
    {
        // Concat for product in cart
        $beforeProcessing = $productInCardList;
        if (count($beforeProcessing) > 1) {
            $productInCardList = array();

            foreach ($beforeProcessing as $productSingle) {
                $isFound = false;
                $isFoundKey = 0;

                foreach ($productInCardList as $kV => $cProduct) {
                    if ($cProduct['id'] == $productSingle['id']) {
                        if (!isset($cProduct['options']) || $cProduct['options'] == array()) {
                            $isFound = true;
                            $isFoundKey = $kV;
                        } else if (isset($productSingle['options']) && serialize($cProduct['options']) == serialize($productSingle['options'])) {
                            $isFound = true;
                            $isFoundKey = $kV;
                        }
                    }
                }

                if (!$isFound) {
                    $productInCardList[] = $productSingle;
                } else {
                    $productInCardList[$isFoundKey]['qty'] += $productSingle['qty'];
                }
            }
        }

        return $productInCardList;
    }

    protected function _addProductsToQuote($productInCardList, $paramsArray)
    {
        $productInCardList = $this->_concatenateProductsInQuote($productInCardList);

        foreach ($productInCardList as $productItem) {
            $product = Mage::getModel('catalog/product')->load($productItem['id']);

            if (!$product->getId()) {
                throw new LogicException('Product does not exist. Probably it was deleted.');
            }

            if ($product->getStatus() === Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                throw new LogicException('Product is disabled.');
            }

            $productTypeId = $product->getTypeId();

            if ($productTypeId == Ess_M2ePro_Model_MagentoProduct::TYPE_GROUPED) {
                // Grouped product converted to assigned simple
                if (!isset($productItem['options'])) {
                    throw new LogicException('The item does not have options. At the current version, Order Import for grouped product supports only multi variation listing.');
                }

                $product = $this->_getRelatedProductFromGroupedForEbayData($product, $productItem);

                if (is_null($product)) {
                    throw new LogicException('There is no associated products found for grouped product.');
                } else {
                    $productTypeId = $product->getTypeId();
                }
            }

            $productItem['price'] = $this->_getConvertedPrice($productItem['price']);

            $request = new Varien_Object();
            $request->setQty($productItem['qty']);

            switch ($productTypeId) {
                case Ess_M2ePro_Model_MagentoProduct::TYPE_SIMPLE:
                    $haveRequiredOptionsInstant = $product->getTypeInstance(true)->hasRequiredOptions($product);
                    $haveRequiredOptions = $product->hasRequiredOptions();

                    if ($haveRequiredOptions && !$product->getRequiredOptions()) {
                        $haveRequiredOptions = false; // important: possible incorect behavior
                    }


                    if ($haveRequiredOptionsInstant || $haveRequiredOptions || $this->_checkSimpleProductHasRequiredCustomOptions($product)) {
                        $customOptionsData = array();
                        if (isset($productItem['options']) && count($productItem['options'])) {
                            // Have multivariation data for simple product
                            // Use to set Custom Options data
                            $customOptionsData = $this->_getCustomOptionsForEbayData($productItem);
                            $this->_addNotifyMessage('Product has <b>Required Options</b>. Selected Options Values are taken from eBay Multi Variation Data');
                        } else {
                            // No multivariation data, set first required option
                            $customOptionsData = $this->_getRandomCustomOptions($productItem);
                            $this->_addNotifyMessage('Product has <b>Required Options</b>. First option value is selected.');
                        }

                        $request->setOptions($customOptionsData['options']);

                        // Dec price for percent change (after apply percent price inc = need price)
                        $productItem['price'] = $productItem['price'] / (1 + $customOptionsData['price_change_percent'] / 100);

                        // Change for custom options price. price_change_fixed low that 0 when option inc price, more 0 - when inc
                        $productItem['price'] += ($customOptionsData['price_change_fixed'] / (1 + $customOptionsData['price_change_percent'] / 100));

                    } // end of $haveRequriedOptions
                    break;
                case Ess_M2ePro_Model_MagentoProduct::TYPE_CONFIGURABLE:
                    if (!isset($productItem['options'])) {
                        throw new LogicException('The item does not have options. At the current version, Order Import for configurable product supports only multi variation listing.');
                    }
                    $configurableOptions = $this->_getConfigurableAttributeForEbayData($productItem);
                    // Set selected attributes
                    $request->setSuperAttribute($configurableOptions['options']);

                    // Each option value can change total price value, remove
                    // this changes for equal: order price = eBay sold price
                    $productItem['price'] += $configurableOptions['price_change'];
                    break;
                case Ess_M2ePro_Model_MagentoProduct::TYPE_BUNDLE:
                    if (!isset($productItem['options'])) {
                        throw new LogicException('The item does not have options. At the current version, Order Import for bundle product supports only multi variation listing.');
                    }
                    $bundleOptions = $this->_getBundleOptionsForEbayData($productItem);
                    $request->setBundleOption($bundleOptions['options']);
//                    $bundleQty = array();
//                    foreach ($bundleOptions['options'] as $optionId => $optionValue) {
//                        $bundleQty[$optionId] = $productItem['qty'];
//                    }
//                    $request->setBundleOptionQty($bundleQty);
//                    $request->setQty(1);
                    $this->_addNotifyMessage('Price for Bundle item is taken from Magento store.');
                    break;
                default:
                    throw new LogicException('At the current version, Order Import does not support product type: '.$productTypeId.'');
            }

            $product->setPrice($productItem['price']);
            $product->setSpecialPrice($productItem['price']);

            $this->_initProductTaxClassId($product, $paramsArray['taxPercent']);

            $result = $this->_quote->addProduct($product, $request);

            if (is_string($result)) {
                throw new Exception($result);
            }

            // TODO: ugly hack
            //if ($productTypeId == Ess_M2ePro_Model_MagentoProduct::TYPE_BUNDLE ||
            //    Ess_M2ePro_Model_MagentoProduct::TYPE_CONFIGURABLE ||
            //    Ess_M2ePro_Model_MagentoProduct::TYPE_GROUPED) {
                foreach ($paramsArray['products'] as $tempProduct) {
                    if ($tempProduct['id'] == $product->getId()) {
						$tempQuoteItem = $this->_quote->getItemByProduct($product);
						if ($tempQuoteItem !== false) {
                            $tempQuoteItem->setNoDiscount(1);
							$tempQuoteItem->setOriginalCustomPrice($this->_getConvertedPrice($tempProduct['price']));
						}
                        break;
                    }
                }
            //}

        } // foreach $productInCardList
    }

    protected function _checkSimpleProductHasRequiredCustomOptions($loadedProduct)
    {
        $productOptions = $loadedProduct->getOptions();

        foreach ($productOptions as $singleOption) {
            $optionValue = $singleOption->toArray();
            if ($optionValue['is_require'] == 1) {
                return true;
            }
        }
        return false;
    }

    protected function _getConvertedPrice($eBaySoldPrice)
    {
        // Add tax to price
        // -------------------------
        if ($this->_taxPercent > 0) {

            // Order has tax
            // -------------
            if ($this->isPriceIncludesTax() && $this->_taxAmount > 0) {
                $eBaySoldPrice += $eBaySoldPrice * $this->_taxPercent / 100;
            }
            // -------------------------

            // Order has vat
            // -------------------------
            if (!$this->isPriceIncludesTax() && $this->_taxAmount == 0) {
                $eBaySoldPrice -= ($eBaySoldPrice / (100 + $this->_taxPercent)) * $this->_taxPercent;
            }
            // -------------------------
        }
        // -------------------------

        // Convert price from eBay order currency to currency, available in magento
        // -------------------------
        $orderConvertRate = ($this->_orderCurrencyRate == 0) ? 1 : $this->_orderCurrencyRate;
        $eBaySoldPrice = $eBaySoldPrice / $orderConvertRate;
        // -------------------------

        return round($eBaySoldPrice, 2);
    }

    protected function _initProductTaxClassId(Mage_Catalog_Model_Product $product)
    {
        if (!is_null($this->_productTaxClassId)) {
            $product->setTaxClassId($this->_productTaxClassId);
            return;
        }

        if ($this->_taxPercent > 0) {
            // Getting tax percent for magento product
            // -------------------------
            $requestTax = new Varien_Object();
            $requestTax->setCountryId($this->_quote->getShippingAddress()->getCountryId())
                       ->setRegionId($this->_quote->getShippingAddress()->getRegionId())
                       ->setPostcode($this->_quote->getShippingAddress()->getPostcode())
                       ->setStore($this->_quote->getStore())
                       ->setCustomerClassId($this->_quote->getCustomerTaxClassId())
                       ->setProductClassId($product->getTaxClassId());

            $productTaxPercent = Mage::getSingleton('tax/calculation')->getRate($requestTax);
            // -------------------------

            // If magento product tax class has other tax percent
            // set temp tax class with ebay order tax percent
            // -------------------------
            if ($this->_taxPercent != (float)$productTaxPercent) {
                $product->setTaxClassId($this->_getProductTaxClassId());
            } else {
                $this->_productTaxClassId = $product->getTaxClassId();
            }
            // -------------------------
        } else {
            // If order has no tax - disable magento product tax class
            // -------------------------
            $product->setTaxClassId(0);
            // -------------------------
        }
    }

    protected function _getProductTaxClassId()
    {
        if (is_null($this->_productTaxClassId)) {

            // Init product tax class
            // -------------------------
            $productTaxClass = Mage::getModel('tax/class')->getCollection()
                                                          ->addFieldToFilter('class_name', 'eBay Product Tax Class')
                                                          ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
                                                          ->getFirstItem();

            if (is_null($productTaxClass->getId())) {
                $productTaxClass->setClassName('eBay Product Tax Class')
                                ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
                $productTaxClass->save();
            }
            // -------------------------

            // Init customer tax class
            // -------------------------
            if (is_null($customerTaxClassId = $this->_quote->getCustomerTaxClassId())) {
                $customerTaxClass = Mage::getModel('tax/class')->getCollection()
                                                               ->addFieldToFilter('class_name', 'eBay Customer Tax Class')
                                                               ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
                                                               ->getFirstItem();

                if (is_null($customerTaxClass->getId())) {
                    $customerTaxClass->setClassName('eBay Customer Tax Class')
                                     ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
                    $customerTaxClass->save();
                }

                $customerTaxClassId = $customerTaxClass->getId();
            }
            // -------------------------

            // Init tax rate
            // -------------------------
            $taxRate = Mage::getModel('tax/calculation_rate')->load('eBay Tax Rate', 'code');

            $taxRate->setRate((float)$this->_taxPercent)
                    ->setCode('eBay Tax Rate')
                    ->setTaxCountryId('US');
            $taxRate->save();
            // -------------------------

            // Combine tax classes and tax rate in tax rule
            // -------------------------
            $taxRule = Mage::getModel('tax/calculation_rule')->load('eBay Tax Rule', 'code');

            $taxRule->setCode('eBay Tax Rule')
                    ->setTaxCustomerClass(array($customerTaxClassId))
                    ->setTaxProductClass(array($productTaxClass->getId()))
                    ->setTaxRate(array($taxRate->getId()));
            $taxRule->save();
            // -------------------------

            $this->_productTaxClassId = $productTaxClass->getId();
        }

        return $this->_productTaxClassId;
    }

    protected function _getCustomOptionsForEbayData($productInformation)
    {
        $selectedAttributesOnEbay = $productInformation['options'];

        $selectedOptionsOnMagento = array(
            'options' => array(),
            'price_change_fixed' => 0,
            'price_change_percent' => 0
        );

        $productModel = Mage::getModel('catalog/product')->load($productInformation['id']);
        $productOptions = $productModel->getOptions();
        // For each product simple options checking for having such option in product
        // options array. When present save attribute id and options id
        // just now support only select, radio button. For checkbox only single
        // options select (and possible errors)
        foreach ($productOptions as $singleOption) {
            $optionValue = $singleOption->toArray();

            $attributeOptionTitle = '';
            if ($optionValue['store_title'] != null) {
                $attributeOptionTitle = $optionValue['store_title'];
            } else if ($optionValue['title'] != null) {
                $attributeOptionTitle = $optionValue['title'];
            } else {
                $attributeOptionTitle = $optionValue['default_title'];
            }

            if (isset($selectedAttributesOnEbay[$attributeOptionTitle])) {
                $values = $singleOption->getValues();
                foreach ($values as $singleValue) {
                    $singleArrayValue = $singleValue->toArray();

                    $singleTitle = '';
                    $priceType = 'fixed';
                    $priceChange = 0;

                    if ($singleArrayValue['store_title'] != null) {
                        $singleTitle = $singleArrayValue['store_title'];
                        $priceType = $singleArrayValue['store_price_type'];
                        $priceChange = $singleArrayValue['store_price'];
                    } else if ($optionValue['title'] != null) {
                        $singleTitle = $singleArrayValue['title'];
                        $priceType = $singleArrayValue['price_type'];
                        $priceChange = $singleArrayValue['price'];
                    } else {
                        $singleTitle = $singleArrayValue['default_title'];
                        $priceType = $singleArrayValue['default_price_type'];
                        $priceChange = $singleArrayValue['default_price'];
                    }

                    if ($singleTitle == $selectedAttributesOnEbay[$attributeOptionTitle]) {

                        if ($priceType == 'fixed') {
                            $selectedOptionsOnMagento['price_change_fixed'] -= $priceChange;
                        } elseif ($priceType == 'percent') {
                            $selectedOptionsOnMagento['price_change_percent'] += $priceChange;
                        }
                        $selectedOptionsOnMagento['options'][$optionValue['option_id']] = $singleArrayValue['option_type_id'];
                    }
                }
            }
        }
        return $selectedOptionsOnMagento;

    }

    protected function _getRandomCustomOptions($productInformation)
    {
        // TODO case insensitive comparisons
        $selectedOptionsOnMagento = array(
            'options' => array(),
            'price_change_fixed' => 0,
            'price_change_percent' => 0
        );

        $productModel = Mage::getModel('catalog/product')->load($productInformation['id']);
        $productOptions = $productModel->getOptions();

        foreach ($productOptions as $singleOption) {
            $optionValue = $singleOption->toArray();
            if ($optionValue['is_require'] == 1) {
                // Process only  requied option, another we can skip
                $values = $singleOption->getValues();
                foreach ($values as $singleValue) {
                    $singleArrayValue = $singleValue->toArray();
                    // Get first option and set it as "selected" by user
                    $priceChange = 0;
                    $priceType = 'fixed';
                    if ($singleArrayValue['store_price'] != null) {
                        $priceChange = $singleArrayValue['store_price'];
                        $priceType = $singleArrayValue['store_price_type'];
                    } elseif ($singleArrayValue['price'] != null) {
                        $priceChange = $singleArrayValue['price'];
                        $priceType = $singleArrayValue['price_type'];
                    } else {
                        $priceChange = $singleArrayValue['default_price'];
                        $priceType = $singleArrayValue['default_price_type'];
                    }

                    if ($priceType == 'fixed') {
                        $selectedOptionsOnMagento['price_change_fixed'] -= $priceChange;
                    } elseif ($priceType == 'percent') {
                        $selectedOptionsOnMagento['price_change_percent'] += $priceChange;
                    }
                    $selectedOptionsOnMagento['options'][$optionValue['option_id']] = $singleArrayValue['option_type_id'];
                    break;
                }
            }

        }
        return $selectedOptionsOnMagento;
    }

    /**
     * Get right attributes for configurable product based on information from eBay (e.q title = value)
     *
     * @param <type> $productInformation
     * @return <type>
     */
    protected function _getConfigurableAttributeForEbayData($productInformation)
    {
        $selectedAttributesOnEbay = array();
        foreach ($productInformation['options'] as $optionName => $optionValue) {
            $selectedAttributesOnEbay[strtolower($optionName)] = $optionValue;
        }

        $selectedAttributesOnMagento = array(
            'options' => array(),
            'price_change' => 0 // less 0, minus total price, more zero add to total price
        );

        $productModel = Mage::getModel('catalog/product')->setStoreId($this->_storeId)->load($productInformation['id']);
        $productAttributes = $productModel->getTypeInstance(true)->getConfigurableAttributesAsArray($productModel);

        foreach ($productAttributes as $singleAttr) {

            if (isset($singleAttr['store_label']) && array_key_exists(strtolower($singleAttr['store_label']), $selectedAttributesOnEbay)) {
                $attributeLabel = $singleAttr['store_label'];
            } else if (isset($singleAttr['frontend_label']) && array_key_exists(strtolower($singleAttr['frontend_label']), $selectedAttributesOnEbay)) {
                $attributeLabel = $singleAttr['frontend_label'];
            } else if (isset($singleAttr['label']) && array_key_exists(strtolower($singleAttr['label']), $selectedAttributesOnEbay)) {
                $attributeLabel = $singleAttr['label'];
            } else {
                continue;
            }

            // Such attribute is used on eBay
            // Get selected Value
            foreach ($singleAttr['values'] as $selectedValue) {
                if ($selectedValue['store_label'] == $selectedAttributesOnEbay[strtolower($attributeLabel)]) {
                    $selectedAttributesOnMagento['price_change'] = -$selectedValue['pricing_value'];
                    $selectedAttributesOnMagento['options'][$singleAttr['attribute_id']] = $selectedValue['value_index'];
                }
            }
        }

        return $selectedAttributesOnMagento;
    }

    protected function _getBundleOptionsForEbayData($productInformation)
    {
        // TODO case insensitive comparisons
        $selectedAttributesOnEbay = $productInformation['options'];
        $selectedBundleOnMagento = array(
            'options' => array(),
            'products' => array(),
        );

        $product = Mage::getModel('catalog/product')->load($productInformation['id']);

        // Prepare bundle options format usable for search
        $productInstance = $product->getTypeInstance(true);
        $optionCollection = $productInstance->getOptionsCollection($product);
        $optionsData = $optionCollection->getData();

        $bundleOptionsArray = array();

        foreach ($optionsData as $singleOption) {
            $singleBundle = array();
            $singleBundle['id'] = $singleOption['option_id'];
            $singleBundle['name'] = $singleOption['default_title'];
            $singleBundle['values'] = array();
            $bundleOptionsArray[$singleOption['option_id']] = $singleBundle;
        }

        $selectionsCollection = $productInstance->getSelectionsCollection($optionCollection->getAllIds(), $product);
        $_items = $selectionsCollection->getItems();

        foreach ($_items as $_item) {
            $itemInfoAsArray = $_item->toArray();
            if (isset($bundleOptionsArray[$itemInfoAsArray['option_id']])) {
                // Such option having in bundle options array
                $singleSelectionValue = array();
                $singleSelectionValue['name'] = $itemInfoAsArray['name'];
                $singleSelectionValue['selection_id'] = $itemInfoAsArray['selection_id'];
                $singleSelectionValue['product_id'] = $itemInfoAsArray['entity_id'];

                $bundleOptionsArray[$itemInfoAsArray['option_id']]['values'][] = $singleSelectionValue;
            }
        }

        // $bundleOptionsArray constaint information about all available options information
        foreach ($bundleOptionsArray as $singleBundle) {
            if (isset($selectedAttributesOnEbay[$singleBundle['name']])) {
                foreach ($singleBundle['values'] as $bundleValues) {
                    if ($bundleValues['name'] == $selectedAttributesOnEbay[$singleBundle['name']]) {
                        $selectedBundleOnMagento['options'][$singleBundle['id']] = $bundleValues['selection_id'];
                        $selectedBundleOnMagento['products'][] = $bundleValues['product_id'];
                    }
                }
            }
            if (count($singleBundle['values']) == 1) {
                // Possible selection for option only single
                // Set it if on prev step not set
                if (!isset($selectedBundleOnMagento['options'][$singleBundle['id']])) {
                    $selectedBundleOnMagento['options'][$singleBundle['id']] = $singleBundle['values'][0]['selection_id'];
                    $selectedBundleOnMagento['products'][] = $singleBundle['values'][0]['product_id'];
                }
            }
        }
        return $selectedBundleOnMagento;
    }

    protected function _getRelatedProductFromGroupedForEbayData($product, $productInformation)
    {
        $groupedProductOptionName = Ess_M2ePro_Model_MagentoProduct::GROUPED_PRODUCT_ATTRIBUTE_LABEL;

        $selectedAttributesOnEbay = $productInformation['options'];

        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts();
        if (count($associatedProducts)) {
            foreach ($associatedProducts as $childProduct) {
                if ($childProduct->getName() == $selectedAttributesOnEbay[$groupedProductOptionName]) {
                    return $childProduct;
                }
            }
        } else {
            $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            foreach ($aProductIds as $ids) {
                foreach ($ids as $id) {
                    $aProduct = Mage::getModel('catalog/product')->load($id);
                    if ($aProduct->getName() == $selectedAttributesOnEbay[$groupedProductOptionName]) {
                        return $aProduct;
                    }
                }
            }
        }

        return null;
    }

    protected function _initPaymentMethod($paymentDetails)
    {
        // Register information about payment
        Mage::unregister('ebayPaymentData');
        Mage::register('ebayPaymentData', $paymentDetails);

        $quotePaymentObj = $this->_quote->getPayment();

        $quotePaymentObj->importData(
            array(
                'method' =>'m2epropayment',
                'ebay_payment_method' => $paymentDetails['title'],
                'ebay_order_id' => $paymentDetails['ebay_order_id'],
                'external_transactions' => $paymentDetails['external_transactions']
            ));

        $this->_quote->setPayment($quotePaymentObj);
    }

    protected function _initPaymentTransaction(Mage_Sales_Model_Order $order, $paymentDetails)
    {
        $payment = $order->getPayment();

        foreach ($paymentDetails['external_transactions'] as $externalTransaction) {
            $payment->setTransactionId($externalTransaction['ebay_id']);
            $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);

            if (defined('Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS')) {
                $info = array(
                    'Fee'  => $externalTransaction['fee'],
                    'Sum'  => $externalTransaction['sum'],
                    'Time' => $externalTransaction['time']
                );
                $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $info);
            }

            $transaction->save();
        }
    }

    /**
     * Set shipping method for order.
     * Perform price convert and tax decr/inc
     * @param array $shippingDetails information about selected eBay shipping method
     *
     * @return void
     */
    protected function _initShippingMethod($shippingDetails)
    {
        $store = $this->_quote->getStore();
        // getConfig will initialize requested key with default value
        $store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS);

        // Calculate shipping tax
        if ($this->_taxPercent > 0) {
            $store->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_productTaxClassId);

            if ($this->_taxIncludesShipping && $this->isShippingIncludesTax()) {
                $shippingDetails['price'] += $shippingDetails['price'] * $this->_taxPercent / 100;
            }

            if (!$this->_taxIncludesShipping && !$this->isShippingIncludesTax()) {
                $shippingDetails['price'] -= ($shippingDetails['price'] / (100 + $this->_taxPercent)) * $this->_taxPercent;
            }
        } else {
            $store->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, 0);
        }

        // Convert shipping price to currency
        $shippingConvertRate = ($this->_shippingCurrencyRate == 0) ? 1 : $this->_shippingCurrencyRate;
        $shippingDetails['price'] = $shippingDetails['price'] / $shippingConvertRate;

        // Round price to 2 digs after comma
        $shippingDetails['price'] = round($shippingDetails['price'], 2);

        // Register information about shipping
        Mage::unregister('ebayShippingData');
        Mage::register('ebayShippingData', $shippingDetails);

        $this->_quote->getShippingAddress()->setShippingMethod('m2eproshipping_m2eproshipping');
        $this->_quote->getShippingAddress()->setCollectShippingRates(true);

        $this->_quote->collectTotals();
        $this->_quote->save();
    }

    protected function _placeOrder($checkoutMessage, $orderStatus = 'pending', $notifyCreateOrder = false)
    {
        $this->_quote->collectTotals();
        $this->_quote->reserveOrderId();

        error_reporting(E_ERROR);
        $service = Mage::getModel('sales/service_quote', $this->_quote); // If file not exist may catch warring
        error_reporting(E_ALL);

        if ($service != false && method_exists($service, 'submitAll')) {
            // Magento version 1.4.1.x
            //  $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $orderObj = $service->getOrder();
        } else {
            // Magento version 1.4.0.x , 1.3.x
            $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
            $orderObj = $convertQuoteObj->addressToOrder($this->_quote->getShippingAddress());


            $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($this->_quote->getBillingAddress()));
            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($this->_quote->getShippingAddress()));
            $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($this->_quote->getPayment()));

            $items = $this->_quote->getShippingAddress()->getAllItems();

            foreach ($items as $item) {
                //@var $item Mage_Sales_Model_Quote_Item
                $orderItem = $convertQuoteObj->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $orderObj->addItem($orderItem);
            }

            $orderObj->setCanShipPartiallyItem(false);

            $orderObj->place();
        }

        $orderMessages = '';
        $notifyMessages = $this->_processNotifyMessage();

        if ($checkoutMessage || $notifyMessages) {
            $orderMessages .= '<br /><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br /><br />';

            if ($checkoutMessage) {
                $orderMessages .= '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ':</b>';
                $orderMessages .= $checkoutMessage . '<br />';
            }

            if ($notifyMessages) {
                $orderMessages .= $notifyMessages;
            }
        }

        // Adding notification to order
        $orderObj->addStatusToHistory($orderStatus, $orderMessages, false);

        $orderObj->save();

        // --------------------
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/orders/', 'current_magento_order_id', $orderObj->getId());
        $this->setFatalErrorHandler();
        // --------------------

        // Send Notification to customer after create order
        if ($notifyCreateOrder) {
            // Send new order E-mail only if select such mode
            $orderObj->sendNewOrderEmail();
        }

        return $orderObj;
    }

    protected function setFatalErrorHandler()
    {
        $functionCode = '$orderId = Mage::helper(\'M2ePro/Module\')->getConfig()->getGroupValue(\'/synchronization/orders/\', \'current_order_id\');
                         $magentoOrderId = Mage::helper(\'M2ePro/Module\')->getConfig()->getGroupValue(\'/synchronization/orders/\', \'current_magento_order_id\');

                         if (!is_null($orderId) && !is_null($magentoOrderId)) {
                             $order = Mage::getModel(\'M2ePro/EbayOrders\')->load($orderId);
                             $order->setData(\'magento_order_id\', $magentoOrderId)->save();

                             Mage::helper(\'M2ePro/Module\')->getConfig()->deleteGroupValue(\'/synchronization/orders/\', \'current_order_id\');
                             Mage::helper(\'M2ePro/Module\')->getConfig()->deleteGroupValue(\'/synchronization/orders/\', \'current_magento_order_id\');
                         }';
        $shutdownFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownFunction);
    }

    /**
     * Generate random string used for customer password
     *
     * The letter l (lowercase L) and the number 1
     * have been removed, as they can be mistaken
     * for each other.
     */
    protected function _getRandomString()
    {
        $chars = 'abcdefghijkmnopqrstuvwxyz023456789';
        srand((double)microtime() * 1000000);
        $i = 0;
        $randstring = '';
        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $randstring = $randstring . $tmp;
            $i++;
        }
        return $randstring;
    }


    protected function _clearNotifyMessages()
    {
        $this->_notifyMessageList = array();
    }

    protected function _addNotifyMessage($message)
    {
        $this->_notifyMessageList[] = $message;
    }

    protected function _processNotifyMessage()
    {
        $totalMessage = '';
        foreach ($this->_notifyMessageList as $singleMessage) {
            $totalMessage .= $singleMessage . '<br /><br />';
        }
        $this->_clearNotifyMessages();
        return $totalMessage;
    }

    protected function isPriceIncludesTax()
    {
        return Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->_quote->getStore()) === '1';
    }

    protected function isShippingIncludesTax()
    {
        return Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $this->_quote->getStore()) === '1';
    }
}