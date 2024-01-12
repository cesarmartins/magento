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
 * Product category helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Catalog_Category
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Categories
     *
     * @var array of Mage_Catalog_Model_Category
     */
    protected $_categories;
    /**
     * Active categories
     *
     * @var array of Mage_Catalog_Model_Category
     */
    protected $_activeCategories;
    /**
     * Get category collection
     * 
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        return Mage::getSingleton('catalog/category')->getCollection();
    }
    /**
     * Get categories
     * 
     * @return array of Mage_Catalog_Model_Category
     */
    public function getCategories()
    {
        if (is_null($this->_categories)) {
            $categories = array();
            $collection = $this->getCategoryCollection()
                ->addNameToResult();
            foreach ($collection as $category) {
                $categories[(int) $category->getId()] = $category;
            }

            $this->_categories = $categories;
        }

        return $this->_categories;
    }
    /**
     * Get category by id
     * 
     * @param int $categoryId
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoryById($categoryId)
    {
        $categories = $this->getCategories();
        if (isset($categories[$categoryId])) {
            return $categories[$categoryId];
        } else {
            return null;
        }
    }
    /**
     * Get category by name
     * 
     * @param string $name
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoryByName($name)
    {
        $category = null;
        foreach ($this->getCategories() as $_category) {
            if ($_category->getName() == $name) {
                $category = $_category;
                break;
            }
        }

        return $category;
    }
    /**
     * Get active categories
     * 
     * @return array of Mage_Catalog_Model_Category
     */
    public function getActiveCategories()
    {
        if (is_null($this->_activeCategories)) {
            $categories = array();
            $collection = $this->getCategoryCollection()
                ->addNameToResult()
                ->addAttributeToFilter('is_active', 1);
            foreach ($collection as $category) {
                $categories[(int) $category->getId()] = $category;
            }

            $this->_activeCategories = $categories;
        }

        return $this->_activeCategories;
    }
    /**
     * Get active category ids
     * 
     * @return array
     */
    public function getActiveCategoryIds()
    {
        return array_keys($this->getActiveCategories());
    }
}
