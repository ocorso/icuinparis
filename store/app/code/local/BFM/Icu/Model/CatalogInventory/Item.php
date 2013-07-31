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
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Inventory Stock Model
 *
 * @category   Mage
 * @package    Mage_CatalogInvemtory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class BFM_Icu_Model_CatalogInventory_Item extends Mage_CatalogInventory_Model_Stock_Item
{
	public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
	{
		$result = new Varien_Object();
		$result->setHasError(false);

		if (!is_numeric($qty)) {
			$qty = Mage::app()->getLocale()->getNumber($qty);
		}

		/**
		 * Check quantity type
		 */
		$result->setItemIsQtyDecimal($this->getIsQtyDecimal());

		if (!$this->getIsQtyDecimal()) {
			$result->setHasQtyOptionUpdate(true);
			$qty = intval($qty);

			/**
			 * Adding stock data to quote item
			 */
			$result->setItemQty($qty);

			if (!is_numeric($qty)) {
				$qty = Mage::app()->getLocale()->getNumber($qty);
			}
			$origQty = intval($origQty);
			$result->setOrigQty($origQty);
		}

		if ($this->getMinSaleQty() && ($qty) < $this->getMinSaleQty()) {
			$result->setHasError(true)
					->setMessage(Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1))
					->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
					->setQuoteMessageIndex('qty');
			return $result;
		}

		if ($this->getMaxSaleQty() && ($qty) > $this->getMaxSaleQty()) {
			$result->setHasError(true)
					->setMessage(Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1))
					->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
					->setQuoteMessageIndex('qty');
			return $result;
		}

		if (!$this->getManageStock()) {
			return $result;
		}

		if (!$this->getIsInStock()) {
			$result->setHasError(true)
					->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
					->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock'))
					->setQuoteMessageIndex('stock');
			$result->setItemUseOldQty(true);
			return $result;
		}

		$result->addData($this->checkQtyIncrements($qty)->getData());
		if ($result->getHasError()) {
			return $result;
		}

		if (!$this->checkQty($summaryQty)) {
			$message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
			$result->setHasError(true)
					->setMessage($message)
					->setQuoteMessage($message)
					->setQuoteMessageIndex('qty');
			return $result;
		} else {
			if (($this->getQty() - $summaryQty) < 0) {
				if ($this->getProductName()) {
					$backorderQty = ($this->getQty() > 0) ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
					if ($backorderQty > $qty) {
						$backorderQty = $qty;
					}
					$result->setItemBackorders($backorderQty);
					if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
						$result->setMessage(Mage::helper('cataloginventory')->__('Please note, this product is currently on backorder. Once your order is placed, a representative of the ICUinParis team will contact you with your expected product delivery time.', $this->getProductName()));
//                        $result->setMessage(Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1), $this->getProductName()));
					}
				}
			}
			// no return intentionally
		}

		return $result;
	}

}
