<?php
class Seaway_Tree_Model_Cupom extends Mage_Core_Controller_Varien_Action{


  /*  public function __construct($request , $response){
        parent::__construct($request , $response);
    }*/

    public static function getSalesRuleCoupon($code){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT * FROM salesrule_coupon  WHERE code=:code LIMIT 0,1";
        $data = array('code'=>$code);
        $row = $_conn->fetchRow($sql,$data);

        return $row;

    }


    public static function getSalesRuleCouponApp($code){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT * FROM salesrule_coupon  WHERE code like '%$code%' LIMIT 0,1";
        $row = $_conn->fetchRow($sql);

        return $row;

    }



    public static function getTreeCouponLike($code , $notTreeId = ""){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');


        $code = str_replace('_',  '\_' , $code);

        $sqlTreeId  = "";
        if(!empty($notTreeId)){

            $sqlTreeId = "AND  tree_id <> $notTreeId";
        }

        $sql = "SELECT
                    count(coupon) as total
                FROM t_tree_coupon
                              WHERE coupon LIKE '$code%' $sqlTreeId   LIMIT 0,1";

        $row = $_conn->fetchRow($sql);

        return (isset($row['total']))? $row['total'] : 0 ;

    }




    public static function getCode($lenght = 5 )
    {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';


        $caracteres = '';
        $caracteres .= $lmin;
        $caracteres .= $lmai;
        $numeros = $num;

        $code = "";

        for($x = 1 ; $x <= $lenght  ; $x++){
            if($x%2 == 0){
                $len = strlen($caracteres);
                $rand = mt_rand(1, $len);
                $code  .= $caracteres[$rand-1];
            }else{
                $len = strlen($numeros);
                $rand = mt_rand(1, $len);
                $code .= $numeros[$rand-1];
            }
        }

         return $code;
    }



    public static function getNameCupom($type,$nome,$instagram , $treeID){

        $nome = strtoupper(trim($nome));
        $nomes = explode(" ", $nome);
        $firstName = current($nomes);

        return  $firstName.$instagram.'_'.self::getCode();


    }


    public static function getUserForCupom($cupom ){
        try{
            if(empty($cupom) && is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);

            $conn = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sql = "SELECT * FROM  t_tree t INNER JOIN t_tree_coupon c ON t.id = c.tree_id WHERE c.coupon = '$cupom'";
            $values = $conn->fetchRow($sql);

            return $values;

        }catch(Exception $e){
            throw new Exception('getUserForCupom :' . $e->getMessage(), -3);
        }
    }

    public static function getCouponIsValidTree($coupon){

        try {
            self::checkStatusCoupon($coupon);

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_tree_coupon tc INNER JOIN t_tree t ON tc.tree_id = t.id WHERE tc.coupon=:cod  LIMIT 0,1";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);
            return $row;

        }catch (Exception $e){
            throw new Exception('getCouponIsValidTree : '. $e->getMessage() , -3);
        }
    }


    public static function checkStatusCoupon($couponCode){

        try{

            if(empty($couponCode)){
                throw new Exception('Param couponCode invalid', -3 );
            }

            $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

            if($oRule->getIsActive() < 1){
                throw new Exception('Coupon is invalid', -3 );
            }

            return true;

        }catch (Exception $e){
            throw new Exception('checkStatusCoupon : '. $e->getMessage() , -3);
        }


    }


    public static function createCouponName($type , $nome , $instagram , $treeID){


        do{
            $isCupomValid = true;

            $code = self::getNameCupom($type , $nome , $instagram , $treeID);
            $isEmpty = self::getSalesRuleCoupon($code);

            if(empty($isEmpty)){
                $isCupomValid = false;
            }


        }while($isCupomValid);

        return $code;

    }

    public static function dateExpiration($code  ,$format ='pt'){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT  rc.code  as code , DATE_FORMAT(mt.to_date, '%d/%m/%Y')  as date_expiration  , DATE_FORMAT(mt.to_date, '%Y-%m-%d')  as date_expiration_us ,  mt.times_used
                            FROM `salesrule_coupon` AS `rc`
							INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            WHERE rc.code = :code";

        $data   = array('code' => $code );
        $values =   $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        $result = false;
        if(!empty($values['date_expiration'])){
            if($format == 'pt'){
                $result = $values['date_expiration'];
            }else if($format == 'en'){
                $result = $values['date_expiration_us'];
            }
        }
        return  $result ;
    }

    public static function randomCode(){

        do{
            $isCupomValid = true;
            $code = self::getCode();
            $isEmpty = self::getSalesRuleCoupon($code);

            if(empty($isEmpty)){
                $isCupomValid = false;
            }
        }while($isCupomValid);
        return $code;
    }

//    public static function criarCupom($value,$code,$qtdDays = '16',$fixedDate = null , $usesPerCoupon = 1) {
//        $VALOR_CUPOM = $value;
//
//
//
//        $de  = date('Y-m-d');
//
//        $tomorrow = date('Y-m-d', strtotime("+$qtdDays days"));
//        if(!empty($fixedDate))
//            $tomorrow =  $fixedDate;
//
//
//        $gruposIds =  array(0,1);
//
//        $data = array(
//            'product_ids' => null,
//            'name' => 'CUPOM DE DESCONTO '.$VALOR_CUPOM.'%',
//            'description' => 'Feedback de compra '.$VALOR_CUPOM.'% de desconto',
//            'is_active' => 1,
//            'website_ids' => array(1),
//            'customer_group_ids' =>$gruposIds,
//            'coupon_type' => 2,
//            'coupon_code' => $code,
//            'uses_per_coupon' => $usesPerCoupon,
//            'uses_per_customer' => 1,
//            'from_date' => $de,
//            'to_date' => $tomorrow,
//            'sort_order' => 2,
//            'is_rss' => 1,
//            'rule' => array(
//                'conditions' => array(
//                    '1'=>
//                        array(
//                            'type' => 'salesrule/rule_condition_combine',
//                            'aggregator' => 'all',
//                            'value' => 1
//                        )
//                ,'1--1'=>
//                        array(
//                            'type' => 'salesrule/rule_condition_address',
//                            'attribute' => 'total_qty',
//                            'operator' => '<=',
//                            'value' => 5
//                        )
//                )
//            ),
//            'simple_action' => 'by_percent',
//            'discount_amount' => $VALOR_CUPOM,
//            'discount_qty' => 0,
//            'discount_step' => null,
//            'apply_to_shipping' => 0,
//            'simple_free_shipping' => 0,
//            'stop_rules_processing' => 0,
//            /* 'rule' => array(
//             'actions' => array(
//                     array(
//                             'type' => 'salesrule/rule_condition_product_found',
//                             'attribute' => 'sku',
//                             'operator' => '==',
//                             'value' => 'tp'
//                     )
//             )
//            ), */
//            'store_labels' => array('Seaway Experience')
//        );
//        $model = Mage::getModel('salesrule/rule');
//        $validateResult = $model->validateData(new Varien_Object($data));
//        if ($validateResult == true) {
//            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
//                && isset($data['discount_amount'])
//            ) {
//                $data['discount_amount'] = min(100, $data['discount_amount']);
//            }
//            if (isset($data['rule']['conditions'])) {
//                $data['conditions'] = $data['rule']['conditions'];
//            }
//            if (isset($data['rule']['actions'])) {
//                $data['actions'] = $data['rule']['actions'];
//            }
//            unset($data['rule']);
//            $model->loadPost($data);
//            $model->save();
//
//            return $code;
//
//        }
//
//    }


    public static function criarCupom($value,$code,$qtdDays = '16',$fixedDate = null , $usesPerCoupon = 1 , $customerPerCoupon = 1  , $label ="Seaway Experience"  ,   $description = "") {
        $VALOR_CUPOM = $value;

        $de = date('Y-m-d' , strtotime('-1 day'));

        $tomorrow = date('Y-m-d', strtotime("+$qtdDays days"));
        if(!empty($fixedDate))
            $tomorrow =  $fixedDate;

        if(strcasecmp($tomorrow , 'noexpire') == 0){
            $tomorrow  = null;
        }

        $gruposIds =  array(0,1);

        if(empty($description)){
            $description = 'Feedback de compra '.$VALOR_CUPOM.'% de desconto';
        }

        $data = array(
            'product_ids' => null,
            'name' => 'CUPOM DE DESCONTO '.$VALOR_CUPOM.'%',
            'description' => $description,
            'is_active' => 1,
            'website_ids' => array(1),
            'customer_group_ids' =>$gruposIds,
            'coupon_type' => 2,
            'coupon_code' => $code,
            'uses_per_coupon' => $usesPerCoupon,
            'uses_per_customer' => $customerPerCoupon,
            'from_date' => $de,
            'to_date' => $tomorrow,
            'sort_order' => 2,
            'is_rss' => 1,
            'rule' => array(
                'conditions' => array(
                    '1'=>
                        array(
                            'type' => 'salesrule/rule_condition_combine',
                            'aggregator' => 'all',
                            'value' => 1
                        )
                )
            ),
            'simple_action' => 'by_percent',
            'discount_amount' => $VALOR_CUPOM,
            'discount_qty' => 0,
            'discount_step' => null,
            'apply_to_shipping' => 0,
            'simple_free_shipping' => 0,
            'stop_rules_processing' => 0,
            'store_labels' => array($label)
        );
        $model = Mage::getModel('salesrule/rule');
        $validateResult = $model->validateData(new Varien_Object($data));
        if ($validateResult == true) {
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                && isset($data['discount_amount'])
            ) {
                $data['discount_amount'] = min(100, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);
            $model->loadPost($data);
            $model->save();

            return $code;

        }

        //Mage::getModel('salesrule/rule')->

    }



    public static function _createRuleCredit( $name , $description, $groupIds ,$de , $ate , $valorFixo  , $usesPerCoupon = 1, $usesPerCustomer = 1) {


        $resource          = Mage::getSingleton('core/resource');
        $conn_magento      = $resource->getConnection('core_read');
        $retorno = "";
        Mage::helper('core')->getRandomString();
        $data = array(
            'product_ids' => null,
            // 	   			'name' => sprintf('PRIMEIRA COMPRA 15', Mage::getSingleton('customer/session')->getCustomerId()),
            'name' => 'CUPOM DE DESCONTO : '.$name,
            'description' => $description,
            'is_active' => 1,
            'website_ids' => array(1),
            'customer_group_ids' => $groupIds,
            'coupon_type' => 2,
            'coupon_code' => $name,
            'uses_per_coupon' => $usesPerCoupon,
            'uses_per_customer' =>$usesPerCustomer,
            'from_date' => $de,
            'to_date' => $ate,
            'sort_order' => 2,
            'is_rss' => 1,

            'simple_action' => 'by_percent',
            'discount_amount' => $valorFixo,
            'discount_qty' => 0,
            'discount_step' => null,
            'apply_to_shipping' => 0,
            'simple_free_shipping' => 0,
            'stop_rules_processing' => 0,
            /* 'rule' => array(
             'actions' => array(
                     array(
                             'type' => 'salesrule/rule_condition_product_found',
                             'attribute' => 'sku',
                             'operator' => '==',
                             'value' => 'tp'
                     )
             )
            ), */
            'store_labels' => array('Cupom de Desconto')
        );
        $model = Mage::getModel('salesrule/rule');

        $data =  self::_filterDates($data);
        $validateResult = $model->validateData(new Varien_Object($data));

        if ($validateResult == true) {
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                && isset($data['discount_amount'])) {
                $data['discount_amount'] = min(100, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);
            $model->loadPost($data);
            $model->save();

            $retorno = $name;
        }

        return $retorno;
    }


    public function removeCouponCart(){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $dataQuote = $quote->getData();
        if(!empty($dataQuote)) {
            if($quote->getCouponCode() && $quote) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode('')->collectTotals()->save();
            }
        }
    }


    public static function staticRemoveCouponCart(){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $dataQuote = $quote->getData();
        if(!empty($dataQuote)) {
            if($quote->getCouponCode() && $quote) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode('')->collectTotals()->save();
            }
        }
    }


}
