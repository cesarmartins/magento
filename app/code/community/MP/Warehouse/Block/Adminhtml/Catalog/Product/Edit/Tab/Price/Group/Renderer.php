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
 * Product group price tab renderer
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_Group_Renderer
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('warehouse/catalog/product/edit/tab/price/group/renderer.phtml');
    }

    /**
     * Get warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    public function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }

    /**
     * Get stock identifiers
     *
     * @return array
     */
    public function getStockIds()
    {
        return $this->getWarehouseHelper()->getStockIds();
    }

    /**
     * Get warehouse title by stock identifier
     *
     * @param int $stockId
     *
     * @return string
     */
    public function getWarehouseTitleByStockId($stockId)
    {
        return $this->getWarehouseHelper()->getWarehouseTitleByStockId($stockId);
    }

    /**
     * Get default stock identifier
     *
     * @return int
     */
    public function getDefaultStockId()
    {
        return 0;
    }

    /**
     * Check if group price is fixed
     *
     * @return bool
     */
    public function isGroupPriceFixed()
    {
        return $this->getWarehouseHelper()->getProductHelper()->isGroupPriceFixed($this->getProduct());
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues()
    {
        $helper      = $this->getWarehouseHelper();
        $priceHelper = $helper->getProductPriceHelper();
        $values      = array();
        $data        = $this->getElement()->getValue();

        if (is_array($data)) {
            usort($data, array($this, '_sortGroupPrices'));

            $values = $data;
        }

        $storeId   = $this->getProduct()->getStoreId();
        $websiteId = $helper->getWebsiteIdByStoreId($storeId);
        $_values   = array();

        foreach ($values as $k => $v) {
            if (!$priceHelper->isInactiveData($v, $websiteId)) {
                $_values[$k] = $v;
            }
        }

        $values = $_values;

        foreach ($values as &$v) {
            $v['readonly'] = ($priceHelper->isAncestorData($v, $websiteId)) ? true : false;
        }

        return $values;
    }

    /**
     * Sort group price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortGroupPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }

        if ($a['stock_id'] != $b['stock_id']) {
            return $a['stock_id'] < $b['stock_id'] ? -1 : 1;
        }

        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }

        return 0;
    }
}
