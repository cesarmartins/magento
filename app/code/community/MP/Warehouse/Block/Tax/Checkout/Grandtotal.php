<?php
/**
 * Mage Plugins, Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mage Plugins Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://mageplugins.net/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageplugins@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.mageplugins.net for more information.
 *
 * @category   MP
 * @package    MP_Warehouse
 * @copyright  Copyright (c) 2017-2018 Mage Plugins, Co. and affiliates (https://mageplugins.net/)
 * @license    https://mageplugins.net/commercial-license/ Mage Plugins Commercial License (MPCL 1.0)
 */

/**
 * Grand total row renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Tax_Checkout_Grandtotal 
    extends Mage_Tax_Block_Checkout_Grandtotal
{
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get grandtotal exclude tax
     * 
     * @return float
     */
    public function getTotalExclTax()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        if ($config->isMultipleMode() && $config->isSplitOrderEnabled() && $this->getQuote()->getShippingAddress()) {
            $totalExclTax = 0;
            foreach ($this->getQuote()->getAllShippingAddresses() as $address) {
                $totalExclTax += ($address->getGrandTotal() - $address->getTaxAmount());
            }

            return max($totalExclTax, 0);
        } else {
            return parent::getTotalExclTax();
        }
    }
}
