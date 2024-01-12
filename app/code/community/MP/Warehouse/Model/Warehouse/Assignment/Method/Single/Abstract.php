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
 * Abstact single warehouse assignment method
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
    extends MP_Warehouse_Model_Warehouse_Assignment_Method_Abstract
{
    /**
     * Get stock identifier
     * 
     * @var int
     */
    protected $_stockId;
    /**
     * Set quote
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Multiple_Abstract
     */
    public function setQuote($quote)
    {
        if (is_null($this->_quote) && is_null($quote)) {
            return $this;
        }

        $this->_quote = $quote;
        $this->_stockId = null;
        return $this;
    }
    /**
     * Apply quote stock items
     * 
     * @param MP_Warehouse_Model_Sales_Quote $quote
     * 
     * @return MP_Warehouse_Model_Warehouse_Assignment_Method_Single_Abstract
     */
    public function applyQuoteStockItems($quote = null)
    {
        if (is_null($quote)) {
            $quote                  = $this->getQuote();
        }

        if (!$quote) {
            return $this;
        }

        $stockId = $this->getStockId();
        if ($stockId) {
            foreach ($quote->getAllItems() as $item) {
                $item->setStockId($stockId);
            }
        }

        return $this;
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    protected function _getStockId()
    {
        return $this->getWarehouseHelper()->getDefaultStockId();
    }
    /**
     * Get stock identifier
     * 
     * @return int
     */
    public function getStockId()
    {
        if (is_null($this->_stockId)) {
            $stockId = null;
            $helper     = $this->getWarehouseHelper();
            $config     = $helper->getConfig();
            if ($config->isAllowAdjustment()) {
                $stockId    = $helper->getSessionStockId();
            }

            if (!$stockId) {
                $quote = $this->getQuote();
                if ($quote) {
                    $stockId = $quote->getStockId();
                }

                if (!$stockId) {
                    $stockId = $this->_getStockId();
                }
            }

            $this->_stockId = $stockId;
        }

        return $this->_stockId;
    }
}
