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

require_once 'Mage/Customer/controllers/AccountController.php';
/**
 * Customer address controller
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_CustomerLocator_Customer_AccountController
    extends Mage_Customer_AccountController
{
    /**
     * Get customer locator helper
     * 
     * @return MP_Warehouse_Helper_CustomerLocator_Data
     */
    protected function getCustomerLocatorHelper()
    {
        return Mage::helper('warehouse/customerLocator_data');
    }
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $pattern = '/^(applyAddress)/i';
        if (preg_match($pattern, $action)) {
            $this->setFlag('', 'no-dispatch', false);
            $this->_getSession()->setNoReferer(true);
        }
    }
    /**
     * Apply address action
     */
    public function applyAddressAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $session    = $this->getCustomerLocatorHelper()->getCoreHelper()->getCoreSession();
            $street1    = trim($request->getPost('street1'));
            $street2    = trim($request->getPost('street2'));
            $street     = array();
            if ($street1) {
                array_push($street, $street1);
            }

            if ($street2) {
                array_push($street, $street2);
            }

            $address    = new Varien_Object(
                array(
                'country_id'   => trim($request->getPost('country_id')), 
                'region_id'    => trim($request->getPost('region_id')), 
                'region'       => trim($request->getPost('region')), 
                'city'         => trim($request->getPost('city')), 
                'postcode'     => trim($request->getPost('postcode')), 
                'street'       => (count($street)) ? $street : null, 
                )
            );
            $helper->setCustomerAddress($address);
            $session->addSuccess($helper->__('Your location has been saved.'));
        }

        $this->_redirectReferer();
    }
    /**
     * Apply address id action
     */
    public function applyAddressIdAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $session    = $helper->getCoreHelper()->getCoreSession();
            $addressId  = trim($request->getPost('address_id'));
            $helper->setCustomerAddressId($addressId);
            $session->addSuccess($helper->__('Your location has been saved.'));
        }

        $this->_redirectReferer();
    }
    /**
     * Apply address coordinates action
     */
    public function applyAddressCoordinatesAction()
    {
        $helper     = $this->getCustomerLocatorHelper();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $latitude       = trim($request->getPost('latitude'));
            $longitude      = trim($request->getPost('longitude'));
            if ($latitude && $longitude) {
                $coordinates    = new Varien_Object(
                    array(
                    'latitude'      => $latitude, 
                    'longitude'     => $longitude, 
                    )
                );
                $helper->setCustomerCoordinates($coordinates);
            }
        }

        $this->_redirectReferer();
    }
}
