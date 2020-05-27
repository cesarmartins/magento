<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 30/05/2017
 * Time: 11:24
 */
class Seaway_Tree_Model_Flow  {


    public $cart = null;
    public $modelProductFilter = array(  '29' => 'Super-Heat' , '30'  => 'Super-Heat' ,  '37' =>  'Charger' ,'36'  => 'Charger' );



    public function __construct($params){

        $this->cart = $params['cart'];


    }

    public function setMessageTree(){

        $customerSession = Mage::getModel('customer/session');
        $coupon  =  $customerSession->getData('coupon');
        if(!empty($coupon['is_special'])){

            $typeCoupon  = $coupon['type'];
            $cart = $this->cart;
            $this->processValuesTree($cart , $typeCoupon);

        }
    }




    private function processValuesTree($cart , $typeCoupon){


        $itens = $cart->getQuote()->getAllItems();
        $itensModel  = array();
        $errorQty = false;

        foreach ($itens as $item ) {
            if($item->getQty() > 1){   $errorQty  = true ; break;}

            $productItem  = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
            if($productItem->getPModel())
                $itensModel[] = $productItem->getPModel();
        }
        if(!$errorQty){
            $itensCount = $cart->getQuote()->getItemsCount();

            switch($typeCoupon){
                case  '2' : $this->flowSuperHeat($itensCount , $itensModel); break;
                case  '3' : $this->flowCharger($itensCount , $itensModel); break;
             }

        }else{
            Mage::getModel('customer/session')->setData('codeMsgFreeError', '3');
        }
    }


    public function validateSelectTree($product){
        $coupon = Mage::getModel('customer/session')->getData('coupon');
        $result = '';
        if($coupon['is_special']){
            $typeCoupon =  $coupon['type'];


            switch($typeCoupon){
                case '2' : $result = $this->validateSelectTreeSuperHeat($product);break;
                case '3' : $result = $this->validateSelectTreeCharger($product);break;
            }


        }

        return $result;
    }



    public function validateCheckoutTree()
    {

        $coupon = Mage::getModel('customer/session')->getData('coupon');
        $result = '';
        if ($coupon['is_special']) {
            $typeCoupon = $coupon['type'];
            switch ($typeCoupon) {
                case '2' :$result =$this->validCheckoutTreeSuperheat();break;
                case '3' :$result =$this->validCheckoutTreeCharger();break;
            }
        }



        return $result;
    }



    private function flowCharger($itensCount, $itensModel ){

        $modelPromotionCodes = $this->modelProductFilter;
        switch($itensCount){
            case 0 :
                $this->clearSessionTree();
                break;
            case 1 :
                $model = current($itensModel);
                $this->setMessageUserTree($model,$modelPromotionCodes);
                break;
            case 2 :
                $this->validCartSuccessCharger($itensModel);
                break;
            case 2 < $itensCount :
                $this->setMessageError();
                break;
        }


    }


    private function flowSuperHeat($itensCount , $itensModel){



        switch($itensCount){
            case 0 :
                $this->clearSessionTree();
                break;
            case 1 :
                $this->validCartSuccessSuperHeat($itensModel);
                break;
            case 2 <= $itensCount :
                $this->setMessageError();
                break;
        }


    }

    private function  clearSessionTree(){
        Mage::getModel('customer/session')->unsetData('codeMsgFree');
        Mage::getModel('customer/session')->unsetData('codeMsgFreeError');
    }


    private function  setMessageUserTree($model,$modelPromotionCodes){

        if(!empty($modelPromotionCodes[$model])) {
            $customerSession = Mage::getModel('customer/session');
            $customerSession->unsetData('codeMsgFree');
            if (strcasecmp($modelPromotionCodes[$model], 'Super-Heat') == 0) {
                $customerSession->setData('codeMsgFree','1');
            } else if (strcasecmp($modelPromotionCodes[$model], 'Charger') == 0) {
                $customerSession->setData('codeMsgFree','2');
            }
        }
    }

    private function setMessageSuccess(){
        Mage::getModel('customer/session')->unsetData('codeMsgFree');
        // Mage::getModel('customer/session')->setData('codeMsgFree', '3');
    }

    private function setMessageError(){
        Mage::getModel('customer/session')->setData('codeMsgFreeError', '3');
    }



    private function validCartSuccessCharger($itensModel){

        $modelPromotionCodes = $this->modelProductFilter;
        $firstModelCode = current($itensModel);
        $lastModelCode  = end($itensModel);
        $isError = true;
        if(isset($modelPromotionCodes[$firstModelCode]) && isset($modelPromotionCodes[$lastModelCode]) ){
            if(strcasecmp($modelPromotionCodes[$firstModelCode], $modelPromotionCodes[$lastModelCode]) != 0){
                $this->setMessageSuccess();
                $isError = false;
            }
        }

        if($isError){
            $this->setMessageError();
        }


    }

    private function validCartSuccessSuperHeat($itensModel){
        $modelPromotionCodes = $this->modelProductFilter;
        $firstModelCode = current($itensModel);
        $isError = true;
        if(isset($modelPromotionCodes[$firstModelCode])){
            if (strcasecmp($modelPromotionCodes[$firstModelCode], 'Super-Heat') == 0) {
                $this->setMessageSuccess();
                $isError = false;
            }
        }
        if($isError){
            $this->setMessageError();
        }


    }


    public function validateSelectTreeCharger($product){
        $cart = $this->cart;
        $itensCount = $cart->getQuote()->getItemsQty();
        $returnValue = ($itensCount == 0 )? true : false;
        Mage::getModel('customer/session')->unsetData('codeMsgFreeError');
        if ($itensCount == 1 ) {
            $model = $product->getPModel();
            $valuesAlowed = $this->modelProductFilter;
            $valuesKeys =  array_keys($valuesAlowed);

            if (in_array($model, $valuesKeys )) {
                $itens = $cart->getQuote()->getAllItems();
                foreach( $itens  as $item ){
                    $productItem  = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                    $modelProductCart = $productItem->getPModel();
                    break;
                }
                if(!empty($valuesAlowed[$modelProductCart])){

                    $modelTypeProductCart  =  $valuesAlowed[$modelProductCart];
                    $modelTypeProduct  =  $valuesAlowed[$model];
                    // caso o item escolhido seja do mesmo tipo do que já esta no carrinho retorna falso
                    if(strcasecmp($modelTypeProduct , $modelTypeProductCart) !=  0){
                        //$returnValue =  false;
                        $returnValue =  true;
                    }else{
                        $firstModelType  = current($valuesAlowed);
                        // super-heat
                        if(strcasecmp($firstModelType , $modelTypeProduct) == 0){
                            Mage::getModel('customer/session')->setData('codeMsgFreeError', '1');
                            // charger
                        }else{
                            Mage::getModel('customer/session')->setData('codeMsgFreeError', '2');
                        }
                    }
                }
            }
        }

        return $returnValue;

    }


    public function validateSelectTreeSuperHeat($product){
        $cart = $this->cart;
        $itensCount = $cart->getQuote()->getItemsQty();
        $returnValue = false;
        Mage::getModel('customer/session')->unsetData('codeMsgFreeError');
        if ($itensCount == 0 ) {
            $model = $product->getPModel();
            $models = $this->modelProductFilter;
            if(strcasecmp($models[$model] , 'Super-Heat') ==  0){
                $returnValue =  true;
            }
        }
        return $returnValue;

    }


    public function validCheckoutTreeCharger(){

        $quote  = Mage::getSingleton('checkout/session')->getQuote();
        $valuesAlowed = $this->modelProductFilter;
        $itensCount = $quote->getItemsQty();
        $oldModel = '';
        $checkReturn = false;
        if($itensCount == 2){
            $itens = $quote->getAllItems();
            foreach( $itens  as $item ){
                $productItem  = null;
                $productItem  = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                $modelProductCart = $productItem->getPModel();
                if(!empty($valuesAlowed[$modelProductCart])){
                    if($valuesAlowed[$modelProductCart] && !empty($oldModel) && strcasecmp($oldModel , $valuesAlowed[$modelProductCart]) != 0 ){
                        $checkReturn = true;
                    }
                    $oldModel = $valuesAlowed[$modelProductCart];
                }
            }
        }
            // validar as boardshorts


        return $checkReturn;
    }


    public function validCheckoutTreeSuperheat(){


        $quote  = Mage::getSingleton('checkout/session')->getQuote();
        $valuesAlowed = $this->modelProductFilter;
        $itensCount = $quote->getItemsQty();
        $checkReturn = false;
        if($itensCount == 1){
            $itens = $quote->getAllItems();
            foreach( $itens  as $item ){
                $productItem  = null;
                $productItem  = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                $modelProductCart = $productItem->getPModel();
                if(!empty($valuesAlowed[$modelProductCart])){
                    if( strcasecmp($valuesAlowed[$modelProductCart] , 'Super-Heat') == 0 ){
                        $checkReturn = true;
                    }
                }
            }
        }


            // validar as boardshorts


        return $checkReturn;
    }


}
