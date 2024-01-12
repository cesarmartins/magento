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

require_once rtrim(dirname(__FILE__), '/').'/../../Export.php';

/**
 * Product group price export
 *
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Shell_Core_Catalog_Product_Price_Group_Export
    extends MP_Shell_Core_Catalog_Product_Export
{
    /**
     * Get data table name
     * 
     * @return string
     */
    protected function getDataTableName()
    {
        return 'catalog/product_attribute_group_price';
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
                "(entity_id     = {$adapter->quote($datum['entity_id'])})", 
                "(all_groups    = {$adapter->quote($datum['all_groups'])})", 
                "((all_groups <> 0) OR ((all_groups = 0) AND (customer_group_id = {$adapter->quote($datum['customer_group_id'])})))", 
                "(website_id    = {$adapter->quote($datum['website_id'])})", 
                ), 
                $this->getDatumAdditionalConditionsArray($datum)
            )
        );
    }
    /**
     * Get additional field names
     * 
     * @return array
     */
    protected function getAdditionalFieldNames()
    {
        return array();
    }
    /**
     * Get field names
     * 
     * @return array
     */
    protected function getFieldNames()
    {
        return array_merge(
            array(
                'sku', 
                'all_groups', 
                'customer_group', 
                'website', 
            ), 
            $this->getAdditionalFieldNames(), 
            array(
                'price', 
            )
        );
    }
    /**
     * Get row additional fields
     * 
     * @param array $item
     * 
     * @return array
     */
    protected function getRowAdditionalFields($item)
    {
        return array();
    }
    /**
     * Get row
     * 
     * @param array $item
     * 
     * @return array
     */
    protected function getRow($item)
    {
        return array_merge(
            array(
                'sku'               => $this->getCoreHelper()
                    ->getProductHelper()
                    ->getProductSkuById($item['entity_id']), 
                'all_groups'        => ($item['all_groups']) ? 1 : 0, 
                'customer_group'    => (!$item['all_groups']) ? $this->getCoreHelper()
                    ->getCustomerHelper()
                    ->getGroupCodeById($item['customer_group_id']) : '', 
                'website'           => ($item['website_id']) ? $this->getCoreHelper()
                    ->getWebsiteCodeById($item['website_id']) : '', 
            ), 
            $this->getRowAdditionalFields($item), 
            array(
                'price'             => (float) $item['value'], 
            )
        );
    }
    /**
     * Get rows
     * 
     * @return array
     */
    protected function getRows()
    {
        $rows       = array();
        $adapter    = $this->getWriteAdapter();
        $select     = $this->getSelect()
            ->from($this->getDataTable());
        foreach ($adapter->fetchAll($select) as $item) {
            array_push($rows, $this->getRow($item));
        }

        return $rows;
    }
}
