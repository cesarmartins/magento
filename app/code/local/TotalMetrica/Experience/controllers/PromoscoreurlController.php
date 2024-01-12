<?php
class Seaway_Experience_PromoscoreurlController extends Mage_Core_Controller_Front_Action {


    public function indexAction(){
        $url =  $this->getRequest()->getRequestUri();
        $subs = substr($url , 1 , strlen($url)-1);
        $values = explode('/' , $subs);
        Mage::getModel('track/track')->trackLog( $url   , null);

        $urlRedirect = Mage::getBaseUrl('web');
        if(!empty($values) && count($values) == 2){
            list($media , $insta) = $values;

            $media = preg_replace("@(\\w+)(\\d+)@i","$1-$2",$media);
            list($media , $fase) = explode('-', $media) ;

            switch($media){
                case 'insta':        $urlRedirect = $this->flowCouponUrl('instagram-post' , $insta, $fase  , $url); break;
                case 'whatsapp':     $urlRedirect = $this->flowCouponUrl('whatsapp' , $insta , $fase , $url); break;
                case 'direct':       $urlRedirect = $this->flowCouponUrl('instagram-direct' , $insta , $fase , $url);break;
                case 'facebook':     $urlRedirect = $this->flowCouponUrl('facebook' , $insta, $fase , $url);break;
                case 'sms':          $urlRedirect = $this->flowCouponUrl('sms' , $insta , $fase , $url); break;
                case 'email':        $urlRedirect = $this->flowCouponUrl('email' , $insta , $fase , $url); break;
                case 'pessoalmente': $urlRedirect = $this->flowCouponUrl('personally' , $insta , $fase , $url); break;
            }

        }
        $this->_redirectUrl($urlRedirect);
    }


    private  function flowCouponUrl($media , $insta ,$fase , $url){

        $urlRedirect = Mage::getBaseUrl('web');
        $coupon = Mage::getModel('experience/promoscore')->getCouponMedia($media, $insta , $fase);
        if($coupon){

            $expire =  Seaway_Tree_Model_Cupom::dateExpiration($coupon);
            $urlRedirect .= '?pcode='.$coupon.'&promo=1&participant='.$insta.'&expire='.$expire;


            $mediaId  = Mage::getModel('experience/promoscore')->getMediaCouponId($fase , $media);

            Mage::getModel('customer/session')->unsetData('promoscore_active');
            Mage::getModel('customer/session')->setData('promoscore_active' , array('instagram' => $insta, 'link' => $url , 'media_id' => $mediaId ));

        }
        return $urlRedirect;

    }


}
