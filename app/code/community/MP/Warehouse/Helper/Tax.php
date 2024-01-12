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
 * Tax helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */

class MP_Warehouse_Helper_Tax 
    extends MP_Warehouse_Helper_Core_Tax
{
    /**
     * Product tax classes
     *
     * @var array of Mage_Tax_Model_Class
     */
    protected $_productTaxClasses;
    /**
     * Get tax class
     * 
     * @return Mage_Tax_Model_Class
     */
    public function getTaxClass()
    {
        return Mage::getModel('tax/class');
    }
    /**
     * Get tax class collection
     * 
     * @return Mage_Tax_Model_Mysql4_Class_Collection
     */
    public function getTaxClassCollection()
    {
        return $this->getTaxClass()
            ->getCollection();
    }
    /**
     * Get product tax class collection
     * 
     * @return Mage_Tax_Model_Mysql4_Class_Collection
     */
    public function getProductTaxClassCollection()
    {
        return $this->getTaxClassCollection()
            ->setClassTypeFilter(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
    }
    /**
     * Get product tax classes
     * 
     * @return array of Mage_Tax_Model_Class
     */
    public function getProductTaxClasses()
    {
        if (is_null($this->_productTaxClasses)) {
            $taxClasses = array();
            foreach ($this->getProductTaxClassCollection() as $taxClass) {
                $taxClasses[(int) $taxClass->getClassId()] = $taxClass;
            }

            $this->_productTaxClasses = $taxClasses;
        }

        return $this->_productTaxClasses;
    }
    /**
     * Get product tax class ids
     * 
     * @return array
     */
    public function getProductTaxClassIds()
    {
        return array_keys($this->getProductTaxClasses());
    }
    /**
     * Get product tax class by id
     * 
     * @param int $taxClassId
     * 
     * @return Mage_Tax_Model_Class
     */
    public function getProductTaxClassById($taxClassId)
    {
        $taxClasses = $this->getProductTaxClasses();
        if (isset($taxClasses[$taxClassId])) {
            return $taxClasses[$taxClassId];
        } else {
            return null;
        }
    }
}
