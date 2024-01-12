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

//if(Mage::helper('core')->isModuleEnabled('IWD_AddressVerification'))
//{
//	require_once 'IWD/AddressVerification/controllers/JsonController.php';
//	class MP_Warehouse_Opc_JsonController_Extends
//		extends IWD_AddressVerification_JsonController {}
//
//} else {
//
//	require_once 'IWD/Opc/controllers/JsonController.php';
//	class MP_Warehouse_Opc_JsonController_Extends
//		extends IWD_Opc_JsonController {}
//}


/**
 * 
 * @author anonymous
 *
 */
class MP_Warehouse_Opc_JsonController extends IWD_Opc_JsonController
{
    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('opc_json_shippingmethods');
        $layout->generateXml();
        $layout->generateBlocks();
        $shippingMethods = $layout->getBlock('checkout.onepage.shipping_method');
        $shippingMethods->setTemplate('warehouse/opc/onepage/shipping_method.phtml');
        return $shippingMethods->toHtml();
    }
}
