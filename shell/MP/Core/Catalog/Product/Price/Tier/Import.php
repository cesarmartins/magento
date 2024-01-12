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

require_once rtrim(dirname(__FILE__), '/').'/../../Import.php';

/**
 * Product tier price import
 *
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Shell_Core_Catalog_Product_Price_Tier_Import
    extends MP_Shell_Core_Catalog_Product_Import
{
    /**
     * Get data table name
     * 
     * @return string
     */
    protected function getDataTableName()
    {
        return 'catalog/product_attribute_tier_price';
    }
    /**
     * Get datum additional conditions array
     * 
     * @param array $data
     * 
     * @return array
     */
    protected function getDatumAdditionalConditionsArray($datum)
    {
        $adapter = $this->getWriteAdapter();
        return array();
    }
    /**
     * Get datum conditions
     * 
     * @param array $data
     * 
     * @return string
     */
    protected function getDatumConditions($datum)
    {
        $adapter = $this->getWriteAdapter();
        return implode(
            ' AND ', array_merge(
                array(
                "(entity_id             = {$adapter->quote($datum['entity_id'])})", 
                "(all_groups    = {$adapter->quote($datum['all_groups'])})", 
                "((all_groups <> 0) OR ((all_groups = 0) AND (customer_group_id = {$adapter->quote($datum['customer_group_id'])})))", 
                "(website_id            = {$adapter->quote($datum['website_id'])})", 
                "(qty                   = {$adapter->quote($datum['qty'])})", 
                ), 
                $this->getDatumAdditionalConditionsArray($datum)
            )
        );
    }
    
    /**
     * Get datum additional values
     * 
     * @param type $row
     * 
     * @return array|null
     */
    protected function getDatumAdditionalValues($row)
    {
        return array();
    }
    /**
     * Import row
     * 
     * @param array $row
     * 
     * @return self
     */
    protected function importRow($row)
    {
        $productId          = $this->getRowFieldValue($row, 'sku');
        if (!$productId) {
            $this->printMessage("Sku is empty");
            return $this;
        }
        
        $productId          = $this->getCoreHelper()
            ->getProductHelper()
            ->getProductIdBySku($productId);
        if (!$productId) {
            $this->printMessage("Can't find product by sku: {$this->getRowFieldValue($row, 'sku')}");
            return $this;
        }
        
        $allGroups          = (int) $this->getRowFieldValue($row, 'all_groups');
        $allGroups          = ($allGroups) ? 1 : 0;
        
        if (!$allGroups) {
            $customerGroupId    = $this->getCoreHelper()
                ->getCustomerHelper()
                ->getGroupIdByCodeOrId($this->getRowFieldValue($row, 'customer_group'));
            if (is_null($customerGroupId)) {
                $this->printMessage("Can't find customer group: {$this->getRowFieldValue($row, 'customer_group')}");
                return $this;
            }
        } else {
            $customerGroupId    = 0;
        }
        
        $websiteId          = $this->getRowFieldValue($row, 'website');
        if ($websiteId) {
            $websiteId          = $this->getCoreHelper()
                ->getWebsiteIdByCodeOrId($websiteId);
            if (is_null($websiteId)) {
                $this->printMessage("Can't find website: {$this->getRowFieldValue($row, 'website')}");
                return $this;
            }
        } else {
            $websiteId          = 0;
        }
        
        $qty                = (float) $this->getRowFieldValue($row, 'qty');
        
        $value              = $this->getRowFieldValue($row, 'price');
        $value              = ($value !== '') ? $this->getCoreHelper()
            ->getProductHelper()
            ->getPriceHelper()
            ->round((float) $value) : null;
        
        $datum = array(
            'entity_id'         => $productId, 
            'all_groups'        => $allGroups, 
            'customer_group_id' => $customerGroupId, 
            'website_id'        => $websiteId, 
            'qty'               => $qty, 
            'value'             => $value, 
        );
        $_datum             = $this->getDatumAdditionalValues($row);
        if (is_null($_datum)) {
            return $this;
        }

        $datum = array_merge($datum, $_datum);
        
        if ($this->isDatumExists($datum)) {
            if (!is_null($datum['value'])) {
                $this->updateDatum($datum);
            } else {
                $this->removeDatum($datum);
            }
        } else if (!is_null($datum['value'])) {
            $this->addDatum($datum);
        }
        
        return $this;
    }
    /**
     * Reindex
     * 
     * @return self
     */
    protected function reindex()
    {
        $this->printMessage('Reindexing.');
        $this->getCoreHelper()
            ->getProcessHelper()
            ->reindexProductPrice();
        return $this;
    }
}
