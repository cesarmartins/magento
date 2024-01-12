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
 * Customer group import
 *
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
abstract class MP_Shell_Core_Customer_Group_Import
    extends MP_Shell_Core_Import
{
    /**
     * Get model
     * 
     * @return Mage_Catalog_Model_Product
     */
    protected function getModel()
    {
        if (is_null($this->_model)) {
            $this->_model = $this->getCoreHelper()
                ->getCustomerHelper()
                ->getGroup();
        }

        return $this->_model;
    }
    /**
     * Get customer group id by row
     * 
     * @param array $row
     * 
     * @return int
     */
    protected function getCustomerGroupIdByRow($row)
    {
        if (!isset($row['customer_group'])) {
            return null;
        }

        $customerGroupId = $row['customer_group'];
        if (is_null($customerGroupId)) {
            return $customerGroupId;
        }

        $customerHelper     = $this->getCoreHelper()->getCustomerHelper();
        $customerGroup      = $customerHelper->getGroupById($customerGroupId);
        if ($customerGroup) {
            $customerGroupId    = (int) $customerGroup->getId();
        } else {
            $customerGroup      = $customerHelper->getGroupByCode($customerGroupId);
            if ($customerGroup) {
                $customerGroupId     = (int) $customerGroup->getId();
            } else {
                $customerGroupId     = null;
            }
        }

        return $customerGroupId;
    }
}
