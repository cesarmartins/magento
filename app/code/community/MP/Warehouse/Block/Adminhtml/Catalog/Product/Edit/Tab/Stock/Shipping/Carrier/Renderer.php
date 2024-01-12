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
 * Product stock priority renderer
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Stock_Shipping_Carrier_Renderer 
    extends MP_Warehouse_Block_Adminhtml_Catalog_Product_Edit_Tab_Renderer_Abstract 
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->setTemplate('warehouse/catalog/product/edit/tab/stock/shipping/carrier/renderer.phtml');
    }
    /**
     * Sort values function
     *
     * @param mixed $a
     * @param mixed $b
     * 
     * @return int
     */
    protected function sortValues($a, $b)
    {
        if ($a['stock_id'] != $b['stock_id']) {
            return $a['stock_id'] < $b['stock_id'] ? -1 : 1;
        }

        return 0;
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        $helper     = $this->getWarehouseHelper();
        $values     = array();
        $stocksIds  = $helper->getCatalogInventoryHelper()->getStockIds();
        if (count($stocksIds)) {
            $readonly = $this->getElement()->getReadonly();
            $data = $this->getElement()->getValue();
            foreach ($stocksIds as $stockId) {
                $value = array('stock_id' => $stockId);
                if (isset($data[$stockId]) && count($data[$stockId])) {
                    $value['shipping_carrier'] = $data[$stockId];
                    $value['use_default'] = 0;
                } else {
                    $warehouse = $helper->getWarehouseByStockId($stockId);
                    if ($warehouse) {
                        $value['shipping_carrier'] = $warehouse->getShippingCarriers();
                    } else {
                        $value['shipping_carrier'] = array();
                    }

                    $value['use_default'] = 1;
                }

                $value['readonly'] = $readonly;
                array_push($values, $value);
            }
        }

        usort($values, array($this, 'sortValues'));
        return $values;
    }
    /**
     * Get shipping carriers
     * 
     * @return array
     */
    public function getShippingCarriers()
    {
        $methods = array();
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrier) {
            if (!$carrier->isActive() || !($carrier->getAllowedMethods())) {
                continue;
            }

            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label' => $carrierTitle, 
                'value' => $carrierCode, 
            );
        }

        return $methods;
    }
}
