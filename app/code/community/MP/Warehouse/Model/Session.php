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
 * Warehouse session
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Session 
    extends Mage_Core_Model_Session_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $namespace = 'warehouse';
        $this->init($namespace);
        Mage::dispatchEvent('warehouse_session_init', array('geocoder_session' => $this));
    }
    /**
     * Set product stock ids
     * 
     * @param array $productStockIds
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function setProductStockIds($productStockIds)
    {
        $this->setData('product_stock_ids', $productStockIds);
        return $this;
    }
    /**
     * Get product stock ids
     * 
     * @return array
     */
    public function getProductStockIds()
    {
        $productStockIds = $this->getData('product_stock_ids');
        if (!is_array($productStockIds)) {
            $productStockIds = array();
        }

        return $productStockIds;
    }
    /**
     * Get product stock ids hash
     * 
     * @return string
     */
    public function getProductStockIdsHash()
    {
        return md5(serialize($this->getProductStockIds()));
    }
    /**
     * Remove product stock ids
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function removeProductStockIds()
    {
        $this->unsetData('product_stock_ids');
        return $this;
    }
    /**
     * Set product stock id
     * 
     * @param int $productId
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function setProductStockId($productId, $stockId)
    {
        $productStockIds = $this->getProductStockIds();
        $productStockIds[$productId] = $stockId;
        $this->setProductStockIds($productStockIds);
        return $this;
    }
    /**
     * Get product stock id
     * 
     * @param int $productId
     * 
     * @return int
     */
    public function getProductStockId($productId)
    {
        $productStockIds = $this->getProductStockIds();
        if (isset($productStockIds[$productId]) && ($productStockIds[$productId])) {
            return (int) $productStockIds[$productId];
        } else {
            return null;
        }
    }
    /**
     * Set stock id
     * 
     * @param int $stockId
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function setStockId($stockId)
    {
        $this->setData('stock_id', $stockId);
        return $this;
    }
    /**
     * Get stock id
     * 
     * @return int
     */
    public function getStockId()
    {
        $stockId = $this->getData('stock_id');
        return ($stockId) ? (int) $stockId : null;
    }
    /**
     * Remove stock id
     * 
     * @return MP_Warehouse_Model_Session
     */
    public function removeStockId()
    {
        $this->unsetData('stock_id');
        return $this;
    }
}
