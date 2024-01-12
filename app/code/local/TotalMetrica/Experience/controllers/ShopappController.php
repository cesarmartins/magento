<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 14/11/2017
 * Time: 18:35
 */

class Seaway_Experience_ShopappController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $couponValues['is_special'] = false;
        $couponValues['show_banner'] = true;
        $couponValues['from_app'] = true;
        $couponValues['discount'] = '30';
        Mage::getModel('customer/session')->setData('coupon', $couponValues);
        $this->_redirect('boardshorts');

        /*
         *  $discount = 30;
        $couponlabel = '- 30% Desc App';
        $couponDescription = 'cupom gerado pelo app';
        $code = Seaway_Tree_Model_Cupom::randomCode();
        $couponCode = Seaway_Tree_Model_Cupom::criarCupom($discount, $code, '', 'noexpire', 1, 1, $couponlabel, $couponDescription);

        $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        $data = $oCoupon->getData();
        if (!empty($data)) {
            Mage::getModel('customer/session')->unsetData('coupon');
            Mage::getModel('customer/session')->setData('coupon', array('coupon' => $data['code'], 'is_special' => false, 'from_app' => true, 'show_banner' => true, 'discount' => $discount));
        }

        $this->_redirect('boardshorts');
         * */


    }

}