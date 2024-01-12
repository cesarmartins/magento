<?php
class CesarMartins_Customer_Model_Observer extends Varien_Event_Observer
{
    public function customerLogin($observer)
    {
        $session = Mage::getSingleton('customer/session');
        if (strpos(Mage::helper('core/http')->getHttpReferer(), 'listafavoritos') !== false) {
            $newRoute = Mage::getUrl(' ', array('_direct' => 'listafavoritos/index/inicio'));
            $session->setAfterAuthUrl($newRoute);
        }else{
            $session->setAfterAuthUrl(Mage::helper('core/http')->getHttpReferer());
        }
        $session->setBeforeAuthUrl('');
    }
}
