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
 * Stock
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Cataloginventory_Stock 
    extends Mage_CatalogInventory_Model_Stock
{
    /**
     * Admin stock id
     */
    const ADMIN_STOCK_ID = 1;

    /**
     * Prefix of model events names
     * 
     * @var string
     */
    protected $_eventPrefix = 'cataloginventory_stock';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'stock';
    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string || true
     */
    protected $_cacheTag = 'cataloginventory_stock';
    /**
     * Get warehouse helper
     *
     * @return MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get catalog inventory helper
     * 
     * @return MP_Warehouse_Helper_Cataloginventory
     */
    protected function getCatalogInventoryHelper()
    {
        return $this->getWarehouseHelper()->getCatalogInventoryHelper();
    }
    /**
     * Get stock identifier
     *
     * @return mixed
     */
    public function getId() 
    {
        $fieldName = ($this->getIdFieldName()) ? $this->getIdFieldName() : 'id';
        return $this->_getData($fieldName);
    }
    /**
     * Get stock item collection
     * 
     * @return MP_Warehouse_Model_Mysql4_Cataloginventory_Stock_Item_Collection
     */
    public function getItemCollection()
    {
        $config = $this->getWarehouseHelper()->getConfig();
        $itemCollection = $this->getCatalogInventoryHelper()->getStockItemCollection();
        if (!$config->isMultipleMode() || $this->getId()) {
            $itemCollection->addStockFilter($this->getId());
        }

        return $itemCollection;
    }
    /**
     * Prepare product qtys
     *
     * @param array $items
     */
    protected function _prepareProductQtys($items)
    {
        $qtys = array();
        foreach ($items as $productId => $productItems) {
            foreach ($productItems as $stockId => $item) {
                if (empty($item['item'])) {
                    $stockItem = $this->getCatalogInventoryHelper()->getStockItem($stockId);
                    $stockItem->loadByProduct($productId);
                } else {
                    $stockItem = $item['item'];
                }

                $canSubtractQty = $stockItem->getId() && $stockItem->canSubtractQty();
                if ($canSubtractQty && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
                    $qtys[$productId][$stockId] = $item['qty'];
                }
            }
        }

        return $qtys;
    }
    /**
     * Subtract product qtys from stock.
     *
     * @param array $items
     * 
     * @return array
     */
    public function registerProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        $item = $this->getCatalogInventoryHelper()->getStockItem();
        $this->_getResource()->beginTransaction();
        $stockInfo = $this->_getResource()->getProductsStock($this, $qtys, true);
        $fullSaveItems = array();
        foreach ($stockInfo as $itemInfo) {
            $item->setData($itemInfo);
            $productId = $item->getProductId();
            $stockId = $item->getStockId();
            $_qty = (isset($qtys[$productId]) && isset($qtys[$productId][$stockId])) ? $qtys[$productId][$stockId] : null;
            if (!$item->checkQty($_qty)) {
                $this->_getResource()->commit();
                Mage::throwException(Mage::helper('cataloginventory')->__('Not all products are available in the requested quantity'));
            }

            $item->subtractQty($_qty);
            if (!$item->verifyStock() || $item->verifyNotification()) {
                $fullSaveItems[] = clone $item;
            }
        }

        $this->_getResource()->correctItemsQty($this, $qtys, '-');
        $this->_getResource()->commit();
        return $fullSaveItems;
    }
    /**
     * Subtract ordered qty for product
     *
     * @param   Varien_Object $item
     * 
     * @return  Mage_CatalogInventory_Model_Stock
     */
    public function registerItemSale(Varien_Object $item)
    {
        $productId = $item->getProductId();
        if ($productId) {
            $stockItem = $this->getCatalogInventoryHelper()->getStockItem($item->getStockId());
            $stockItem->loadByProduct($productId);
            if (Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
                if ($item->getStoreId()) {
                    $stockItem->setStoreId($item->getStoreId());
                }

                if ($stockItem->checkQty($item->getQtyOrdered()) || Mage::app()->getStore()->isAdmin()) {
                    $stockItem->subtractQty($item->getQtyOrdered());
                    $stockItem->save();
                }
            }    
        } else {
            Mage::throwException(Mage::helper('cataloginventory')->__('Cannot specify product identifier for the order item.'));
        }

        return $this;
    }
    /**
     * Get back to stock (when order is canceled or whatever else)
     * 
     * @param int $productId
     * @param numeric $qty
     * 
     * @return Mage_CatalogInventory_Model_Stock
     */
    public function backItemQty($productId, $qty)
    {
        $stockItem = $this->getCatalogInventoryHelper()->getStockItem($this->getId());
        $stockItem->loadByProduct($productId);
        if ($stockItem->getId() && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
            $stockItem->addQty($qty);
            if ($stockItem->getCanBackInStock() && $stockItem->getQty() > $stockItem->getMinQty()) {
                $stockItem->setIsInStock(true)->setStockStatusChangedAutomaticallyFlag(true);
            }

            $stockItem->save();
        }

        return $this;
    }
    /**
     * Create filter chain
     *
     * @return Zend_Filter
     */
    protected function createFilterChain()
    {
        return new Zend_Filter();
    }
    /**
     * Create validator chain
     *
     * @return Zend_Validate
     */
    protected function createValidatorChain()
    {
        return new Zend_Validate();
    }
    /**
     * Filter catalog inventory stock
     *
     * @throws Mage_Core_Exception
     * @return MP_Warehouse_Model_Cataloginventory_Stock
     */
    public function filter()
    {
        $filters = array(
            'stock_name' => $this->createFilterChain()
                ->appendFilter(new Zend_Filter_StringTrim())
                ->appendFilter(new Zend_Filter_StripNewlines())
                ->appendFilter(new Zend_Filter_StripTags()), 
        );
        foreach ($filters as $field => $filter) {
            $this->setData($field, $filter->filter($this->getData($field)));
        }

        return $this;
    }
    /**
     * Validate catalog inventory stock
     * 
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate()
    {
        $validators = array(
            'stock_name' => $this->createValidatorChain()
                ->addValidator(new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING), true)
                ->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 255, )), true), 
        );
        $errorMessages = array();
        foreach ($validators as $field => $validator) {
            if (!$validator->isValid($this->getData($field))) {
                $errorMessages = array_merge($errorMessages, $validator->getMessages());
            }
        }

        if (count($errorMessages)) Mage::throwException(join("\n", $errorMessages));
        return true;
    }
    /**
     * Processing object before save data
     *
     * @return MP_Warehouse_Model_Cataloginventory_Stock
     */
    protected function _beforeSave()
    {
        $this->filter();
        $this->validate();
        parent::_beforeSave();
        return $this;
    }
}
