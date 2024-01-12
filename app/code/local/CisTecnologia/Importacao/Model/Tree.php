<?php
class TotalMetrica_Tree_Model_Tree
{

    CONST DEFAULT_ACTION_ID =  '123';

    private $resource = null;
    private $niveis = array();

    public function __construct()
    {
       // parent::_construct();
       // $this->_init('tree/tree');

       $this->resource = Mage::getSingleton('core/resource')->getConnection('core_write');

    }

    public  function  updateNodeJson(){

        //7028
        // $sql = "select * from tree where status = 1";
        // $sql = "select * from tree where cod_gerado_3 is not null and cod_gerado_3 <> ''";
        $sql = "SELECT customer_id FROM t_tree WHERE  status = 1 ";
        $customers = $this->resource->fetchAll($sql);



        foreach($customers as $customer){

            if(!empty($customer['customer_id'])){
               $this->getBermudaSelectedAll($customer['customer_id']);
            }

        }

    }






    public function getAllChildsByParentId($treeId){

        $sql = "SELECT   * , REPLACE(trim(instagram), '@', '') as insta  FROM t_tree WHERE parent_id = :parent";
        $fetchAll = $this->resource->fetchAll($sql , array('parent' => $treeId));
        return $fetchAll;

    }


    public function getListSeawayChildsByParentId($treeId){

        $sql = "SELECT id, instagram, REPLACE(trim(instagram), '@', '') as insta   FROM t_tree WHERE parent_id = :parent and status_app = 2 and new_list_app = 0 ORDER BY id DESC ";
        $fetchAll = $this->resource->fetchAll($sql , array('parent' => $treeId));
        return $fetchAll;

    }


    public function getListSeawayChildsByInstagram($instagram){

        $tree = $this->getTreeByInsta($instagram);
        $result = array();
        if(!empty($tree['id'])){
            $result = $this->getListSeawayChildsByParentId($tree['id']);
        }
        return $result;

    }


    public function getBermudaSelectedAll($customerId){

        try{
            $orders = Mage::getModel('sales/order')->getCollection()
                     ->addFieldToFilter('status' , array('in' => array('complete' , 'processing')))
                     ->addFieldToFilter('customer_id' , array('eq' => $customerId));
            $orders->getSelect()
                    ->join(array('payment' => 'sales_flat_order_payment'),'main_table.entity_id = payment.parent_id',array('payment_method' => 'payment.method'));
            $orders->addFieldToFilter('payment.method', array(array('in' => array('paypal_direct','paypal_express', 'freepayment'))));

            $ordersId = array();
            $ids = array();


            $count = $orders->getData();
            if(!empty($count)){

               $typeInstanceProd =  Mage::getModel('catalog/product')->getTypeInstance();
                foreach($orders as $order){
                    $itens = $order->getAllVisibleItems();
                    foreach($itens as $item){
                        $prodId = "";
                        $prodId = $item->getProductId();
                        if($item->getData('product_type') =='simple'){
                           $parentId = $this->getParentIdProduct($prodId);
                           if(!empty($parentId))
                               $prodId = $parentId;
                        }

                        $ids[$prodId] = $prodId;
                    }


                }


                $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id',array('in' => $ids ));

                $collection->getSelect()
                    ->joinLeft(array("pet2" => 'catalog_product_entity_int'), "e.entity_id = pet2.entity_id AND pet2.attribute_id = 135")
                    ->joinLeft(array("op2" => 'eav_attribute_option_value'),  "pet2.value = op2.option_id AND op2.store_id = 1", array( "fechamento_name" => "op2.value"))
                    ->join( array('cg'=> 'catalog_product_entity_media_gallery' ), 'cg.entity_id = e.entity_id', array('cg.value_id', 'cg.value' ))
                    ->join( array('eavo'=> 'eav_attribute_option_value' ), 'cg.option_color_id  = eavo.option_id and eavo.store_id = 1', array('lower(eavo.value) as cor'))
                    ->join( array('cgv'=> 'catalog_product_entity_media_gallery_value' ), 'cg.value_id = cgv.value_id AND cgv.position= 1', array('cgv.position'));



                $jsonProducts = array();



                foreach($collection as $product){
                    $tipo = '';
                    $tipo = $product->getData('fechamento_name');
                    $jsonProducts[] = array('id' => $product->getData('entity_id') ,
                        'img' => $product->getData('value') ,
                        'tipo' => $product->getData('fechamento_name') ,
                        'cor' => $product->getData('cor') ,
                        'sku' => $product->getData('sku'));
                }

                $newJsonProducts  = json_encode($jsonProducts);

                $sqlJson = "UPDATE t_tree SET json_products = :json WHERE customer_id = :customer ";
                $values  = array('json' => $newJsonProducts , 'customer' => $customerId);

                if(!$this->resource->query($sqlJson , $values)){

                    throw new Exception('Erro ao atualizar o campo de json ', -2);

                }


            }else{
               // echo $customerId;
               // echo ' ,';
            }


        }catch(Exception $e){
            Mage::log('Tree -> getBermudaSelectedAll ->'.$e->getMessage() ,null ,'exception_json_products_tree.log' , true);

        }
    }


    public function getParentIdProduct($childId){

        $sql = "SELECT parent_id FROM catalog_product_relation  WHERE `child_id` = :child";
        $values = array('child' => $childId);
        $valueRow = $this->resource->fetchRow($sql ,$values );

        return  (!empty($valueRow['parent_id']))? $valueRow['parent_id'] : false ;

    }


    public function getBermudaSelected($customerId , $order){

        try{

                if(empty($order))
                    throw new Exception('Order invalid, empty' , -2);

                if(empty($customerId))
                    throw new Exception('Customer invalid, empty' , -2);

                $isParticipant = $this->isParticipantByCustomerId($customerId);
                if(empty($isParticipant)){
                    throw new Exception('Customer is not in tree, empty' , -2);
                }

                $itens = $order->getAllVisibleItems();
                foreach($itens as $item){
                    $prodId = $item->getProductId();
                    if($item->getData('product_type') =='simple'){
                        $parentId = $this->getParentIdProduct($prodId);
                        if(!empty($parentId))
                            $prodId = $parentId;
                    }

                    $ids[] = $prodId;
                }


                $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id', array('in' => $ids));

                $collection->getSelect()
                    ->joinLeft(array("pet2" => 'catalog_product_entity_int'), "e.entity_id = pet2.entity_id AND pet2.attribute_id = 135")
                    ->joinLeft(array("op2" => 'eav_attribute_option_value'), "pet2.value = op2.option_id AND op2.store_id = 1", array("fechamento_name" => "op2.value"))
                    ->join(array('cg' => 'catalog_product_entity_media_gallery'), 'cg.entity_id = e.entity_id', array('cg.value_id', 'cg.value'))
                    ->join(array('eavo' => 'eav_attribute_option_value'), 'cg.option_color_id  = eavo.option_id and eavo.store_id = 1', array('lower(eavo.value) as cor'))
                    ->join(array('cgv' => 'catalog_product_entity_media_gallery_value'), 'cg.value_id = cgv.value_id AND cgv.position= 1', array('cgv.position'));


                $jsonProducts = array();
                foreach ($collection as $product) {

                    $jsonProducts[] = array('id' => $product->getData('entity_id'),
                        'img' => $product->getData('value'),
                        'tipo' =>$product->getData('fechamento_name'),
                        'cor' => $product->getData('cor'),
                        'sku' => $product->getData('sku'));
                }


                $sql = "SELECT json_products FROM t_tree WHERE customer_id = $customerId";
                $line = $this->resource->fetchRow($sql);
                $newJson = $jsonProducts;

                if (!empty($line['json_products'])) {
                    $lineJson = json_decode($line['json_products'], true);
                    $newJson = array();
                    $newJson = array_merge($lineJson, $jsonProducts);

                }
                $newJsonProducts = json_encode($newJson);

                $sqlJson = "UPDATE t_tree SET json_products = :json WHERE customer_id = :customer ";
                $values = array('json' => $newJsonProducts, 'customer' => $customerId);

                if (!$this->resource->query($sqlJson, $values)) {

                    throw new Exception('Erro ao atualizar o campo de json ', -2);

                }


        }catch(Exception $e){

            Mage::log('Tree -> getBermudaSelected ->'.$e->getMessage() ,null ,'exception_json_products_tree.log' , true);
            //echo $e->getMessage();


        }





    }


    public  function getTreeValidationCoupon($campanha){
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        echo "<pre>";
        $cart = Mage::getSingleton('checkout/cart')->getQuote();


        $coupon = $cart->getData('coupon_code');

        //var_dump($coupon);
        //die('aqui');

        $retorno = true;
        $loggin = Mage::getSingleton('customer/session')->isLoggedIn();
        $couponName  = "";


        $couponIsRider = Mage::getModel('checkout/cart')->couponIsValidTree($coupon);

      
        if($loggin && $coupon && !$couponIsRider ) {

            $customerId = $customer->getId();
            $sql = "SELECT  *  FROM t_tree_coupon_valid WHERE customer_id = $customerId AND campanha = '$campanha' ORDER BY id DESC LIMIT 0,1";
            $values = $resource->fetchRow($sql);

            if(!empty($values['coupon'])){
                $couponName  = $values['coupon'];
                if(strcasecmp($coupon, $values['coupon']) == 0){
                    $retorno = true;
                }else{
                    $retorno = false;
                }
            }

        }

        return array('success'=> $retorno , 'coupon_session' => $coupon ,  'coupon_campanha' => $couponName );

    }


    public  function getTreeValidation($campanha){
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $cart = Mage::getSingleton('checkout/cart')->getQuote();
        $coupon = $cart->getData('coupon_code');
        $values = array();


        if(Mage::getSingleton('customer/session')->isLoggedIn() && $coupon) {



            $qty = 0;
            foreach ($cart->getAllItems() as $item) {
                if($item->getProductType() == 'configurable'){
                    $qty =  $qty + (int)$item->getQty();
                }
            }

            $customerId = $customer->getId();
            $sql = "SELECT if((qtd + $qty) > 10 ,true, false) as is_execeded ,
                           qtd as qty_db , ($qty) as qty_cart ,
                           ABS(10- qtd) as qty_available,
                           ($qty - (10 - qtd)) as qty_remove
                                                      FROM tree_coupon_valid WHERE
                                                      customer_id = $customerId AND campanha = '$campanha' ORDER BY id DESC LIMIT 0,1";
            $values = $resource->fetchRow($sql);


            if($values['qty_remove'] < 0 || $values['qty_available'] == 0 ) $values['qty_remove'] = 0;


            $values['is_participant'] = $this->isParticipantByCustomerId($customerId);


        }

        return $values;
    }




    public  function setTreeValidation($campanha , $order){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $coupon = $order->getData('coupon_code');
        $retorno = false;

        if(Mage::getSingleton('customer/session')->isLoggedIn() && $coupon){
            $qty = 0;
            foreach ($order->getAllItems() as $item) {
                if($item->getProductType() == 'configurable'){
                    $qty =  $qty + (int)$item->getQtyOrdered();
                }
            }
            $customerId = $customer->getId();
            $sql = "SELECT if(qtd < 10 , true, false) as is_qtd , qtd, coupon  FROM t_tree_coupon_valid WHERE customer_id = $customerId  AND campanha = '$campanha'  ORDER BY id DESC LIMIT 0,1";
            $values = $resource->fetchRow($sql);
            $sqlValue = "";
            if(!empty($values['qtd'])){

                $proximaQtd  = (int)$values['qtd'] + $qty;
                if($values['is_qtd'] == 1 && $proximaQtd <= 10){
                    $sqlValue= "UPDATE t_tree_coupon_valid SET qtd = (qtd+$qty) WHERE customer_id = $customerId  AND campanha = '$campanha'";
                    $retorno = true;
                }else if($proximaQtd == 1){
                    $sqlValue = "INSERT INTO t_tree_coupon_valid(customer_id,coupon,qtd,campanha)VALUES('$customerId','$coupon','$qty' ,'$campanha')";
                    $retorno = true;
                }

            }else{
                $sqlValue = "INSERT INTO t_tree_coupon_valid(customer_id,coupon,qtd,campanha) VALUES ('$customerId','$coupon','$qty','$campanha')";
                $retorno = true;
            }

            if($sqlValue){
                $resource->query($sqlValue);
            }
         }
        return $retorno;
    }


    public function getChildrensFristLine($id  = 2 ){

        try{


            $sql = "SELECT id,nome   FROM t_tree WHERE parent_id = $id and (status = 1 or (cod_gerado_1 <> '' or cod_gerado_2 <> '' or codigo_ref <> '' or cod_gerado_3 <> '' ))";
            $return = $this->resource->fetchAll($sql);

           /* foreach ($return as $value) {
                //$value['id']
            }*/


            return $return;
        


        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }

    }



    public function organizeArrayChild($valores){

        $indices = array();
        if(!empty($valores[0]['id'])) {
            foreach ($valores as $k => $v) {
                $indices[$v['id']] = $v['id'];
            }
        }
        return $indices;

    }


    public function verifyInvited($invited){
        $bind =  array('invited' => $invited);
        $sql = "select * from t_tree WHERE invited = :invited";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }

    public function verifySlugAtleta($slug){
        $sql = "select * from t_tree where REPLACE(slug , '-', '')  = '$slug' AND status = 1";
        $valores = $this->resource->fetchRow($sql);
        return $valores;
    }

    public function getTreeBySlug($slug ){
        $bind =  array('slug' => $slug);
        $sql = "select *  , if((DATEDIFF(ex_link_app , NOW())) < 0 , 0  , 1  ) as  is_date_valid_app  ,  DATE_FORMAT(ex_link_app , '%m/%d/%Y') as date_app from t_tree WHERE slug = :slug";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }


    public function getTreeByInsta($insta ){
        $bind =  array('insta' => $insta);
        $sql = "select *  , if((DATEDIFF(ex_link_app , NOW())) < 0 , 0  , 1  ) as  is_date_valid_app  ,  DATE_FORMAT(ex_link_app , '%m/%d/%Y') as date_app from t_tree WHERE REPLACE(trim(instagram), '@', '') = REPLACE(trim(:insta), '@', '') ";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }



    public function getTreeBySlugOrCode($slug , $code){
        $bind =  array('slug' => $slug , 'code' => $code);
        $sql = "select *  , if((DATEDIFF(ex_link_app , NOW())) < 0 , 0  , 1  ) as  is_date_valid_app  ,  DATE_FORMAT(ex_link_app , '%m/%d/%Y') as date_app from t_tree WHERE slug = :slug or hash_new_link = :code";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }


    public function getTreeByCustomerId($customerId){
        $bind =  array('customer' => $customerId);
        $sql = "select * from t_tree WHERE customer_id = :customer";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }



    public function getTreeById($treeId){
        $bind =  array('id' => $treeId);
        $sql = "select *   from t_tree WHERE id = :id";
        $valores = $this->resource->fetchRow($sql, $bind);
        return $valores;
    }


    public function updateInstagram($treeId ,  $newInstagram){

        $sqlVerify  = "SELECT instagram FROM t_tree WHERE id = :id ";
        $dataVerify  = array('id' => $treeId);
        $oldInsta =  $this->resource->fetchRow($sqlVerify , $dataVerify);

        $result = false;
        if($oldInsta['instagram'] != $newInstagram ){

            if(strpos('@' , $newInstagram) === FALSE){
                $newInstagram = '@'.$newInstagram;
            }
            $sql = "UPDATE t_tree SET instagram = :insta  WHERE id = :id ";
            $data  = array('insta' => $newInstagram , 'id' => $treeId);
            $this->resource->query($sql , $data);
            $result = true;
        }

    }


    public function confirmSuggestTree($id){

        $bind =  array('id' => $id);
        $sql = "UPDATE t_tree SET new_list_app = 2 WHERE id = :id ";
        $valores = $this->resource->query($sql, $bind);

        $sqlTree = "SELECT * FROM t_tree where id = :id";
        $tree = $this->resource->fetchRow($sqlTree, $bind);
        $this->updateTreeAction($tree["id"], $tree["last_action_id"], $tree["grandfather_id"],'confirm');

        if($valores){
            Mage::getModel('tree/app')->setLimitWhenApproved($id);
        }
        return $valores;
    }

    public function refusedSuggestTree($id){

        $bind =  array('id' => $id);
        $sql = "UPDATE t_tree SET new_list_app = 3 WHERE id = :id ";
        $valores = $this->resource->query($sql, $bind);

        $sqlTree = "SELECT * FROM t_tree where id = :id";
        $tree = $this->resource->fetchRow($sqlTree, $bind);
        $this->updateTreeAction($tree["id"], $tree["last_action_id"], $tree["grandfather_id"],'refused');

        if($valores){
           $acaoId = $this->getActionIdByStatusSys($id ,'refused');
           if($acaoId){
               $this->cadastrarAcao($id, $acaoId);
           }
        }

        return $valores;
    }

    // , $orders = array()
    public function getChildrens($pag = 1 , $limit = 100 , $parentId = null, $orders = array() , $parentType = 'default' , $lastAction = null , $situation = null , $status = null , $limitFirst = null , $amigo = null){

        try{
            $totais =  [];
            //$return = array('total' => $total["total"] ,'total_pag' => $total["total_pag"] , 'valores' => $valores);
            $return = array('total' => 0 ,'total_pag' => 0, 'valores' => 0);
            $return = array_merge($totais , $return);

        }catch(Exception $e){
            throw new Exception('getChildrens :' . $e->getMessage(), -3);
        }


        return $return;

    }


    public function addCupom($cupom , $id , $type = 2 ){
        try{
            if(!is_numeric($id) || !is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);

            $isValid = $this->isValid($cupom , $id );
            $isValid = (isset($isValid['status']) && $isValid['status'])? true : false;

            if($isValid){
                $type  =  str_replace('rider','',$type);
                $type  = trim($type);
                $sql = "INSERT t_tree_coupon(tree_id,coupon,type)VALUES('$id' , '$cupom' , '$type')";
                $this->resource->query($sql);
            }

        }catch(Exception $e){
            throw new Exception('addCupom :' . $e->getMessage(), -3);
        }
    }



    public function vincularCustomerTree($customerId , $code , $UF){

        if(empty($customerId) || empty($code))
            return false;

        $sql = "UPDATE t_tree SET customer_id = $customerId  , estado = '$UF' WHERE  cod_gerado_1 = '$code'  or  cod_gerado_2 = '$code' or cod_gerado_3 = '$code'";
        if($this->resource->query($sql)){

            return true;
        }

        return false;

        
    }

    // PAI 2
    CONST COD_ID_FATURAR_1 = '115';
    CONST COD_ID_PEDIDO_1  = '65';
    CONST COD_ID_ENVIAR_1  = '67';

    // PAI 1
    CONST COD_ID_FATURAR_2 = '118';
    CONST COD_ID_PEDIDO_2  = '39';
    CONST COD_ID_ENVIAR_2  = '41';


    public function registrarAcao($customerId , $couponCode , $tipo , $order){

        try {
            if (empty($customerId))
               throw new Exception('Parametros incorretos customerId',-3);

            if(empty($couponCode))
                throw new Exception('Parametros incorretos couponCode',-3);

            if(empty($tipo))
                throw new Exception('Parametros incorretos tipo',-3);

            if(empty($order))
                throw new Exception('Parametros incorretos order',-3);

            if(!( $order instanceof Mage_Sales_Model_Order) )
                throw new Exception('Parametros incorretos order instance',-3);


            $sql = "SELECT id,parent_id FROM t_tree WHERE customer_id= '$customerId' AND (cod_gerado_1  = '$couponCode' OR cod_gerado_2 = '$couponCode' OR cod_gerado_3 = '$couponCode')";
            $row = $this->resource->fetchRow($sql);

            if (!empty($row['id'])) {
                //$itens = $order->getAllItems();
                // 8481 super heat,  8703 trunk
                $pai =  ($row['parent_id'] != 2)? 2 : 1 ;

                 // PAI 2
                if ($pai == 2) {
                    switch ($tipo) {
                        case 'novo-pedido' :
                            $this->cadastrarAcao($row['id'], self::COD_ID_PEDIDO_1);
                            break;
                        case 'faturar'     :
                            $this->cadastrarAcao($row['id'], self::COD_ID_FATURAR_1);
                            break;
                        case 'enviar'     :
                            $this->cadastrarAcao($row['id'], self::COD_ID_ENVIAR_1);
                            break;
                    }
                }
                // PAI 1
                if ($pai == 1) {
                    switch ($tipo) {
                        case 'novo-pedido' :
                            $this->cadastrarAcao($row['id'], self::COD_ID_PEDIDO_2);
                            break;
                        case 'faturar'     :
                            $this->cadastrarAcao($row['id'], self::COD_ID_FATURAR_2);
                            break;
                        case 'enviar'     :
                            $this->cadastrarAcao($row['id'], self::COD_ID_ENVIAR_2);
                            break;
                    }
                }

            }
        }catch(Exception $e){


            Mage::log('Tree -> registrarAcao : customer ('.$customerId.') tipo acao ('.$tipo.') pai ('.$pai.') :'.$e->getMessage() , null , 'exception_tree.log' , true);
            return false;
        }

    }


    public function updateTreeAction($treeId , $actionId, $grandfather_id=null, $trigger_sys=null){
        try{

            $log = "$treeId , $actionId, $grandfather_id, $trigger_sys";
            Mage::log($log, null ,'cadastrarArvoreAcao.log' , true);

            if($trigger_sys != null){
                $sqlAction  = "SELECT a.name,a.id, a.proxima_acao, a.created_at  FROM t_action a WHERE a.trigger_sys = '" . $trigger_sys . "' and tipo = " . $grandfather_id;
                $action = $this->resource->fetchRow($sqlAction);
            }else{
                $sqlAction  = "SELECT a.name,a.id, a.proxima_acao, ta.created_at  FROM t_action a INNER JOIN t_tree_action ta ON a.id = ta.action_id  WHERE a.id = '$actionId' AND ta.tree_id = '$treeId' ";
                $action = $this->resource->fetchRow($sqlAction);
            }

            $sqlFutureAction = "SELECT a.id, a.name FROM t_action a  WHERE a.id = '".$action['proxima_acao']."'";
            $futureAction = $this->resource->fetchRow($sqlFutureAction);

            $date  = preg_replace("@(\\d{4}-)(\\d{2})(-)(\\d{2})(.*)?(:\\d{2})@",'$4/$2 $5' ,  $action['created_at'] );

            $sql  = "UPDATE t_tree SET last_action = '".$action['name']." - ".$date."'  ,
                                     last_action_id = '".$action['id']."' ,
                                     last_action_date = '".$action['created_at']."'  ,
                                     future_action = '".$futureAction['name']."-".$futureAction['id']."'   ,
                                     future_action_id = '".$futureAction['id']."',
                                     last_exception = null,
                                     last_exception_des =  null
                                     WHERE  id = '$treeId' ";

//            if($trigger_sys != null) {
//                $this->cadastrarArvoreAcao($treeId, $action['id']);
//            }

            if(!$this->resource->query($sql)){

                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');

            }


        }catch(Exception $e){

            Mage::log($e->getMessage() , null ,'actionUpdate.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');

        }

    }

    public function cadastrarArvoreAcao($id, $action_id){

        try{

            $sql = "INSERT INTO t_tree_action(tree_id,action_id, created_at) VALUES (:id , :action_id, now())";
            $data = array('id' => $id , 'action_id' => $action_id);
            $this->resource->query($sql, $data);
            Mage::log($data, null ,'cadastrarArvoreAcao.log' , true );
            Mage::log($sql, null ,'cadastrarArvoreAcao.log' , true );

        }catch(Exception $e){
            Mage::log($e->getMessage() , null ,'actionUpdate.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
        }
    }

    public function updateTreeObservation($treeId , $obsId , $treeActionExceptionId ,$obs){
        try{

            $sqlObs  = "SELECT * FROM  t_action WHERE id = '$obsId'";
            $obsRow = $this->resource->fetchRow($sqlObs);

            if(empty($obsRow)) {
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            $exception = $obsRow['name'];

            $sql = "UPDATE t_tree SET last_exception = '$exception',
                                    last_exception_id = '$obsId',
                                    last_exception_des = '$obs',
                                    last_exception_action_id = $treeActionExceptionId
                                    WHERE  id = '$treeId'";

            if (!$this->resource->query($sql)) {

                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');

            }



        }catch(Exception $e){

            Mage::log($e->getMessage() , null ,'actionUpdateObservation.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');

        }

    }


    public function cadastrarAcao($id , $acaoId ){

        try {

            if (!is_numeric($id) || !is_numeric($acaoId))
                throw new Exception('erro ao passar dados', -3);

           if ($acaoId != self::DEFAULT_ACTION_ID) {
               $this->verificarProximaAcao($acaoId, $id);
           }

            $sql = "INSERT INTO t_tree_action(tree_id,action_id) VALUES (:id , :action_id)";
            $data = array('id' => $id , 'action_id' => $acaoId);

            if($this->resource->query($sql, $data)){

                $this->triggerSaveActionApp($id ,$acaoId);

                $this->updateTreeAction($id ,$acaoId);
                if($acaoId != self::DEFAULT_ACTION_ID)
                    $this->changeUserDefaultSituation($id);
            }

        }catch(Exception $e){
            throw new Exception('cadastrarAcao :' . $e->getMessage(), -3);
        }
    }

    public function triggerSaveActionApp($id , $actionId){
        $sql  = "SELECT trigger_app  FROM t_action WHERE id  = :id";
        $data = array('id' => $actionId);
        $row = $this->resource->fetchRow($sql , $data);
        if(!empty($row['trigger_app'])){
            switch($row['trigger_app']){
                /* actions APP */
                case  'chosen'   : Mage::getModel('tree/app')->setStatusChosen($id);  break;
                case  'following': Mage::getModel('tree/app')->setStatusFollowingSeaway($id); break;
                case  'link_send': Mage::getModel('tree/app')->setStatusLinkSend($id); break;
                case  'won'      : Mage::getModel('tree/app')->setStatusWon($id); break;
                case  'choose'   : Mage::getModel('tree/app')->setStatusChoose($id); break;
                /* observations APP */
                case  'msg_read' : Mage::getModel('tree/app')->setStatusMessageRead($id);  break;
                case  'no_follow': Mage::getModel('tree/app')->setStatusNotFollowedSeaway($id); break;


            }
        }
    }

    public  function  triggerSaveApp($treeId){

        $type = $this->identifyType($treeId);
        if($type != ""){
            $sql  = "SELECT id FROM t_action WHERE tipo = $type AND trigger_sys = 'invoice' limit 0,1  ";
            $row = $this->resource->fetchRow($sql);
            $actionId = $row['id'];
            $this->cadastrarAcao($treeId, $actionId);

        }

    }

    public function getActionIdByStatusApp($treeId , $statusApp){
        $type = Mage::getModel('tree/tree')->identifyType($treeId);
        if($type){
            $sql  = "SELECT id FROM t_action WHERE tipo = :type AND trigger_app = :trigger limit 0,1  ";
            $data  = array('type' => $type , 'trigger' => $statusApp);
            $row = $this->resource->fetchRow($sql , $data);
        }
        return (!empty($row['id']))? $row['id'] : false;
    }


    public function getActionIdByStatusSys($treeId , $statusSys){
        $type = Mage::getModel('tree/tree')->identifyType($treeId);
        if($type){
            $sql  = "SELECT id FROM t_action WHERE tipo = :type AND trigger_sys = :trigger limit 0,1  ";
            $data  = array('type' => $type , 'trigger' => $statusSys);
            $row = $this->resource->fetchRow($sql , $data);
        }
        return (!empty($row['id']))? $row['id'] : false;
    }



    public function  verificarProximaAcao($acaoId , $treeId){

        try{

            $sql2 = "select getActions($acaoId , $treeId)";
            $retorno = $this->resource->fetchRow($sql2);
            if(current($retorno) == 0){
                throw new Exception('Nao e permitido cadastrar acoes anteriores' , -2);
            }

        }catch(Exception $e){

            if($e->getCode() == -2){

                throw new Exception($e->getMessage() , -3);

            }else{
                throw new Exception('verificarProximaAcao :' . $e->getMessage() , -3);
            }

        }
    }


    public function changeUserDefaultSituation($treeId){


        $sql = "SELECT count(*) as total FROM t_tree WHERE id = $treeId and situation=-1";
        $row = $this->resource->fetchRow($sql);
        if($row['total'] > 0 ){
            $sql2  = "UPDATE t_tree set situation = 0 WHERE id = $treeId  ";
            $this->resource->query($sql2);
        }
    }


    public function cadastrarObservacao($id , $obsId  ,  $obs = "" ,$actionId  = ""){
        try{
            if(!is_numeric($id) || !is_string($obsId))
                throw new Exception( 'erro ao passar dados' , -3);

            $sqlAction = "";
            if(!empty($actionId) && is_numeric($actionId)){
                $sqlAction = " and t.action_id = $actionId ";
            }

            $lastAction = "SELECT t.id FROM t_tree_action t INNER JOIN t_action a ON t.action_id = a.id  AND a.tipo_acao='p' WHERE t.tree_id = $id $sqlAction ORDER BY t.created_at DESC LIMIT 0,1";
            $row = $this->resource->fetchRow($lastAction);

            if(!empty($row['id'])){
                $lastActionId =  $row['id'];

                $sql = "INSERT INTO t_tree_action_exception (tree_action_id , exception_id , obs ) VALUES  ($lastActionId , $obsId , '$obs' );";
                $this->resource->query($sql);

                $lastAction = "SELECT t.id FROM t_tree_action_exception t  WHERE t.tree_action_id = $lastActionId and t.exception_id = $obsId  ORDER BY t.created_at DESC LIMIT 0,1";
                $rowException = $this->resource->fetchRow($lastAction);

                // cadastrar observacao no app
                $this->triggerSaveActionApp($id , $obsId);

               /* if($obsId == 119 || $obsId == 120 || $obsId ==  121 ){

                    Mage::getModel('tree/app')->setStatusMessageRead($id);

                }

                if($obsId == 113 || $obsId == 114 || $obsId ==  115 ){

                    Mage::getModel('tree/app')->setStatusNotFollowedSeaway($id);

                }*/


                $this->updateTreeObservation($id , $obsId,$rowException['id'],$obs);

            }/*else{
                $lastActionId = "($lastAction)";
                $this->cadastrarAcao($id,self::DEFAULT_ACTION_ID);
                $sql = "INSERT INTO t_tree_action_exception ( tree_action_id , exception_id , obs ) VALUES  ( $lastActionId ,$obsId , '$obs' );";
                $this->updateTreeObservation($id , $obsId , $obs);
             }*/



        }catch(Exception $e){
            throw new Exception('cadastrarObservacao :' . $e->getMessage(), -3);
        }
    }



    public function getTreeActionId($treeId , $actionId){

        $sql = "SELECT id FROM t_tree_action  WHERE  tree_id = :tree AND action_id = :acao LIMIT 0,1 ";
        $data = array('tree' => $treeId  , 'acao' => $actionId );
        $dados = $this->resource->fetchRow($sql,$data);

       return (!empty($dados["id"]))? $dados["id"]  : false;

    }





    public function removerAcao($id ){
        try{
            if(!is_numeric($id) )
                throw new Exception( 'erro ao passar dados' , -3);


            $sql = "SELECT tree_id FROM t_tree_action  WHERE  id = :id  LIMIT 0,1 ";
            $data = array('id' => $id );
            $dados = $this->resource->fetchRow($sql,$data);



            if(empty($dados['tree_id']) && !is_numeric($dados['tree_id']))
                throw new Exception(' acao inexistente' , -3);



            $sqlDel = "DELETE FROM t_tree_action  WHERE  id = :id ";
            $this->resource->query($sqlDel, $data);



            $dataTree = array(':id' => $dados['tree_id']);

            $sqlCount  = "SELECT  count(r.id) as total FROM t_tree_action r INNER JOIN t_action a ON r.action_id = a.id WHERE r.tree_id = :id and a.tipo_acao = 'p'   ORDER BY r.created_at DESC ";
            $count = $this->resource->fetchRow($sqlCount,$dataTree);
            if($count['total'] == 0){
              
                $sqlTree = "UPDATE t_tree SET situation = '-1' WHERE id = :id ";
                $this->resource->query($sqlTree,$dataTree);
            }

            $this->updateNodeLastAction($dados['tree_id']);
            $this->updateNodeLastException($dados['tree_id']);

        }catch(Exception $e){
            throw new Exception('removerAcao :' . $e->getMessage(), -3);
        }
    }


    public function updateNodeLastAction($treeId){

        $sql  = "SELECT   CAST(CONCAT(a.name  ,' - ', DATE_FORMAT(ta.created_at,'%d/%m %H:%i')) as char(200)) as last_action ,
                          a.id as last_action_id,
                          ta.created_at as last_action_date,

                          (SELECT CONCAT(f.name  ,'-', cast(f.id as char))
                                          FROM t_action f WHERE f.id = a.proxima_acao ) as future_action,

                          a.proxima_acao as future_action_id

                          FROM t_action a
                          INNER JOIN  t_tree_action ta  on a.id = ta.action_id
                          WHERE ta.tree_id = :id and a.tipo_acao = 'p'  ORDER BY a.ordem DESC";

        $data = array('id' => $treeId );
        $dados = $this->resource->fetchRow($sql,$data);




        if(!empty($dados)){


            $sqlUpdateAction  = "UPDATE t_tree SET
                                            last_action = :lastaction,
                                            last_action_id = :lastactionid,
                                            last_action_date = :lastactiondate,
                                            future_action =  :futureaction,
                                            future_action_id = :futureactionid
                                        WHERE id = :id" ;

            $dataAction = array('lastaction' => $dados['last_action'],
                'lastactionid' => $dados['last_action_id'],
                'lastactiondate' => $dados['last_action_date'],
                'futureaction' => $dados['future_action'],
                'futureactionid' => $dados['future_action_id'],
                'id' => $treeId );



            $this->resource->query($sqlUpdateAction , $dataAction);

        }else{


            $sqlUpdateAction  = "UPDATE t_tree SET
                                            last_action = :lastaction,
                                            last_action_id = :lastactionid,
                                            last_action_date = :lastactiondate,
                                            future_action =  :futureaction,
                                            future_action_id = :futureactionid,
                                            situation = :situationcode
                                        WHERE id = :id" ;

            $dataAction = array('lastaction' => null,
                'lastactionid' => null,
                'lastactiondate' => null,
                'futureaction' => null,
                'futureactionid' => null,
                'situationcode' => '-1',
                'id' => $treeId );

            $this->resource->query($sqlUpdateAction , $dataAction);

        }
    }



    public function updateNodeLastException($treeId){


        $lastAction = "SELECT     (select ex.name from t_action ex where ex.id = e.exception_id ) as name ,
                                  e.exception_id ,
                                  e.obs as obs,
                                  e.id FROM t_action a
                                       INNER JOIN  t_tree_action ta  on a.id = ta.action_id
                                       INNER JOIN t_tree_action_exception e ON ta.id =  e.tree_action_id
                                       WHERE ta.tree_id = :id ORDER BY a.ordem DESC, e.created_at DESC ";

        $data = array('id' => $treeId );
        $rowException = $this->resource->fetchRow($lastAction , $data);




        $sqlClean  = "UPDATE t_tree SET
                                            last_exception = :lastexception ,
                                            last_exception_id = :lastexceptionid,
                                            last_exception_des = :lastexceptiondes ,
                                            last_exception_action_id = :lastexceptionactionid
                                        WHERE id = :id" ;


        if(!empty($rowException)){



            $dataException  = array( 'lastexception'  => $rowException['name'] ,
                                      'lastexceptionid' => $rowException['exception_id'],
                                      'lastexceptiondes' => $rowException['obs'],
                                      'lastexceptionactionid' =>  $rowException['id'] ,
                                      'id' => $treeId);


            $this->resource->query($sqlClean , $dataException);

        }else{


            $dataException  = array( 'lastexception'  => null ,
                'lastexceptionid' => null,
                'lastexceptiondes' => null,
                'lastexceptionactionid' => null,
                'id' => $treeId);


            $this->resource->query($sqlClean , $dataException);

        }

    }




    public function removerObservacao($id  , $userId  ){
        try{
            if(!is_numeric($id) )
                throw new Exception( 'erro ao passar dados' , -3);


            $sqlDel = "DELETE FROM t_tree_action_exception  WHERE  id = :id ";
            $dataTree = array(':id' => $id);
            $this->resource->query($sqlDel, $dataTree);


            $lastAction = "SELECT a.name ,
                                  e.exception_id ,
                                  if(e.obs is null or e.obs = '' , (select ex.name from t_action ex where ex.id = e.exception_id ) , e.obs ) as obs,
                                  e.id FROM t_action a
                                       INNER JOIN  t_tree_action ta  on a.id = ta.action_id
                                       INNER JOIN t_tree_action_exception e ON ta.id =  e.tree_action_id
                                       WHERE ta.tree_id = $userId ORDER BY a.ordem DESC, e.created_at DESC ";

            $rowException = $this->resource->fetchRow($lastAction);



            if(!empty($rowException)){

                $sqlClean  = "UPDATE t_tree SET
                                            last_exception = '".$rowException['name']."',
                                            last_exception_id = '".$rowException['exception_id']."',
                                            last_exception_des = '".$rowException['obs']."',
                                            last_exception_action_id = '".$rowException['id']."'
                                        WHERE id = $userId ";

                $this->resource->query($sqlClean);

            }





        }catch(Exception $e){
            throw new Exception('removerAcao :' . $e->getMessage(), -3);
        }
    }



    public function getNode($id){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);

            $values = array();

            $sql = "SELECT t.* , (

                      CASE t.situation
                        WHEN 0 THEN 'Padrao'
                        WHEN 1 THEN 'Aguardando'
                        WHEN 2 THEN 'Problema'
                        WHEN 3 THEN 'Lixeira'
                        ELSE 'No-action'
                      END
                     ) as name_situation FROM  t_tree t WHERE t.id= $id";
            $values['tree'] = $this->resource->fetchRow($sql);

            // get links generator by tree_id
            $sql = "SELECT * FROM t_tree_links WHERE tree_id = :tree_id";
            $data = array('tree_id' => $id);
            $values['tree_links'] = $this->resource->fetchAll($sql, $data);

            return $values;

        }catch(Exception $e){
            throw new Exception('getNode :' . $e->getMessage(), -3);
        }
    }

    public function getCodesUser($id){

        try{

            $sql = "SELECT * FROM t_tree_coupon WHERE tree_id = :id";
            $data  = array('id' => $id);
            $values  = $this->resource->fetchAll($sql , $data);

            return $values;

        }catch (Exception $e){
            throw new Exception('getCodesUser :' . $e->getMessage(), -3);
        }



    }


    public function getTopNode($id){
        try{

            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);
            $sql = "call getParents($id)";
            $values = $this->resource->fetchRow($sql);

            return $values;
        }catch(Exception $e){

            throw new Exception("getTopNode:". $e->getMessage());
        }



    }

    public function getParentName($id ){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);



            $sql = "SELECT parent_name FROM  t_tree WHERE id= $id";
            $values = $this->resource->fetchRow($sql);

            $nome = "" ;
            if(!empty($values['parent_name'])){
                $nome = $values['parent_name'];
            }

            return $nome;

        }catch(Exception $e){
            throw new Exception('getParentName :' . $e->getMessage(), -3);
        }
    }





    public function getNome($id ){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);



            $sql = "SELECT nome FROM  t_tree WHERE id= $id";
            $values = $this->resource->fetchRow($sql);

            $nome = "" ;
            if(!empty($values['nome'])){
                $nome = $values['nome'];
            }

            return $nome;

        }catch(Exception $e){
            throw new Exception('getNome :' . $e->getMessage(), -3);
        }
    }



    public function getInstagram($id){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);

            $sql = "SELECT instagram FROM  t_tree WHERE id= $id";
            $values = $this->resource->fetchRow($sql);

            $nome = "" ;
            if(!empty($values['instagram'])){
                $nome = $values['instagram'];
            }

            return $nome;

        }catch(Exception $e){
            throw new Exception('getInstagram :' . $e->getMessage(), -3);
        }
    }

    public function getParentInstagram($id){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);

            $sql = "SELECT (SELECT i.instagram  FROM t_tree i WHERE i.id = t.parent_id) as instagram_parent FROM  t_tree t WHERE t.id= $id";
            $values = $this->resource->fetchRow($sql);

            $nome = "" ;
            if(!empty($values['instagram_parent'])){
                $nome = $values['instagram_parent'];
            }

            return $nome;

        }catch(Exception $e){
            throw new Exception('getParentInstagram :' . $e->getMessage(), -3);
        }
    }


    public function getSlug($id ){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);



            $sql = "SELECT slug FROM  t_tree WHERE id= $id";
            $values = $this->resource->fetchRow($sql);

            $slug = "" ;
            if(!empty($values['slug'])){
                $slug = $values['slug'];
            }

            return $slug;

        }catch(Exception $e){
            throw new Exception('getNome :' . $e->getMessage(), -3);
        }
    }

    // status 2 lista B
    public function changeListB($id){
        try{
            $sql1 = "SELECT parent_id FROM t_tree WHERE id = $id";
            $resultado = $this->resource->fetchRow($sql1);
            $parentId = $resultado['parent_id'];
            if($parentId > 0 ){
                $sql = "UPDATE t_tree SET status = 2 WHERE ((cod_gerado_1 = '' or cod_gerado_1 is null ) and (cod_gerado_2 is null or cod_gerado_2 = '' )  and (cod_gerado_3 is null or cod_gerado_3 = '' )) AND parent_id = $parentId ";
                $this->resource->query($sql);
            }
        }catch (Exception $e){
            throw new Exception('changeListB :' . $e->getMessage(), -3);
        }
    }


    public function isValid($cupom , $id , $type = null){
        try{
            if(!is_numeric($id) || !is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);

            $cod_gerado = "";
            $c="";
            $sql = "SELECT COUNT(*) as total_cupom  FROM t_tree_coupon WHERE coupon LIKE '$cupom'";
            
            $valores = $this->resource->fetchRow($sql);
            $status = true;
            $msg    = 'sucesso';
            if($valores['total_cupom'] > 0){
                $status = false;
                $msg    = "Cupom $cupom (  $c  ) ja foi gerado";
            }


            $retorno = array('status' => $status , 'msg' => $msg  );
            return $retorno;
        }catch(Exception $e){
            throw new Exception('addCupom :' . $e->getMessage(), -3);
        }
    }

    public function isValidCuponByTreeId($id , $type = null){
        try{
            if(!is_numeric($id))
                throw new Exception( 'erro ao passar dados' , -3);

            $cod_gerado = "";
            $c="";
            $sql = "SELECT COUNT(*) as total_cupom  FROM t_tree_coupon WHERE tree_id = id";
            
            $valores = $this->resource->fetchRow($sql);
            $status = true;
            $msg    = 'sucesso';
            if($valores['total_cupom'] > 0){
                $status = false;
                $msg    = "Coupon has already been used!";
            }
            $retorno = array('status' => $status , 'msg' => $msg  );
            return $retorno;
        }catch(Exception $e){
            throw new Exception('addCupom :' . $e->getMessage(), -3);
        }
    }


    public function  inactiveCoupon($id , $coupon ){

        $couponModel = Mage::getModel('salesrule/coupon')->load($coupon , 'code');
        $ruleId = $couponModel->getRuleId();
        $ruleModel  = Mage::getModel('salesrule/rule')->load($ruleId);
        $ruleModel->setIsActive(0);
        $ruleModel->save();

        $sql = "UPDATE  t_tree_coupon SET is_canceled = 1 WHERE tree_id = $id AND coupon = '$coupon'";
        $this->resource->query($sql);
    }



    private function getSumAllSonsForParentId($sons){


        try{


           /* $this->getAllIdsTreeForParentId($parentId);*/

            $ids = "";
            if (!empty($sons)) {
                //array_unshift($this->niveis, $parentId);
                $ids = implode(',', $sons);
            }
           // $this->niveis = array();
            $total = 0;
            if(strlen($ids) > 0 ) {

                $sql = "SELECT count(s.entity_id) as total FROM sales_flat_order_payment sfop
                          INNER JOIN sales_flat_order s ON s.entity_id = sfop.parent_id
                          INNER JOIN customer_group cg ON s.customer_group_id = cg.customer_group_id
                          INNER JOIN t_tree t ON s.customer_id = t.customer_id
                          WHERE t.customer_id is not null and t.id in ($ids)  and
                          s.status in ('complete', 'processing') and t.status = 1 and sfop.method not in ('freepayment' ,'purchaseorder') and (s.coupon_code = t.cupom_participante)";

                $valores = $this->resource->fetchRow($sql);
                $total = $valores['total'];

            }

            return $total;

        }catch(Exception $e){
            throw new Exception('getAllIdsTreeForParentId :' . $e->getMessage(), -3);
        }


    }


    public function getAllTreePagination($pag = 1){
        try{
            $id = 2;
            $this->getAllIdsTreeForParentId($id);
            $ids = "";
            if (!empty($this->niveis)) {
                array_unshift($this->niveis, $id);
                $ids = implode(',', $this->niveis);
            } else {
                $ids = $id;
            }
            $this->niveis = array();

            $offset = 0;
            $limit = 2;
            if($pag > 1){
                $offset = $pag*$limit;
            }

             $sql = "SELECT t.parent_id , count(t.parent_id) FROM t_tree t WHERE  t.id in ($ids)  group by t.parent_id order by count(t.parent_id) desc limit  $offset, $limit";

            $tree = $this->resource->fetchAll($sql);
            array_shift($tree);
            return  $tree;

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }

    }




    private function formatTreeIds($idsTreeParent){

            $newIdsParents = array();    
            foreach ($idsTreeParent  as $idParent) {
                if(!empty($idParent)){
                     $newIdsParent = array();
                     $newIdsParent = explode(',', $idParent);   
                     $newIdsParent = array_unique($newIdsParent);
                     $newIdsParents = array_merge($newIdsParents , $newIdsParent);

                }     
                  
            }
            if(!empty($newIdsParents)){
                $newIdsParents = array_unique($newIdsParents);
            }

            return $newIdsParents;
    }


    public function getIdsFormatedTree($id){

        $this->niveis = array();
        $this->getAllIdsTreeForParentId($id);
        $ids = "";
        if (!empty($this->niveis)) {
            array_unshift($this->niveis, $id);
            $ids = implode(',', $this->niveis);
        } else {
            $ids = $id;
        }

        $idsTreeParent = $this->niveis;
        $this->niveis = array();

        $newIdsParents = $this->formatTreeIds($idsTreeParent);

        return $newIdsParents;



    }


    /**
     * @TODO: Ajustar funcao soma da compra de todos filhos
     */
    public function getAlltreeForId( /*$id = 3 */ $id = 3){

        try{

          
            $this->niveis = array();
            $this->getAllIdsTreeForParentId($id);
            $ids = "";
            if (!empty($this->niveis)) {
                array_unshift($this->niveis, $id);
                $ids = implode(',', $this->niveis);
            } else {
                $ids = $id;
            }
            



            $idsTreeParent = $this->niveis; 
            $this->niveis = array();
            
            $newIdsParents = $this->formatTreeIds($idsTreeParent);
            $sonsNodeIds   = array();
            if(!empty($newIdsParents)){
                foreach ($newIdsParents as $idSub) {
                    $this->niveis = array();
                  
                    $values = array();
                    $this->getAllIdsTreeForParentId($idSub);    
                    $sonsNodeIds[$idSub] = $this->formatTreeIds($this->niveis);
                  
                    $this->niveis = array();
                }    
            }



            $search = " t.id in ($ids) ";

           /* $totalUser = "(SELECT count(s.entity_id) FROM sales_flat_order_payment sfop
            INNER JOIN sales_flat_order s ON s.entity_id = sfop.parent_id
	        INNER JOIN customer_group cg ON s.customer_group_id = cg.customer_group_id
	        WHERE s.customer_id = t.customer_id and
	        s.status in ('complete', 'processing') and  sfop.method not in ('freepayment' ,'purchaseorder') and (cg.customer_group_code = 'rider1' or s.coupon_code = t.cupom_participante) ) as qtd_buys";*/



            $totalUser = "(SELECT count(s.entity_id) FROM sales_flat_order_payment sfop
            INNER JOIN sales_flat_order s ON s.entity_id = sfop.parent_id
	        INNER JOIN customer_group cg ON s.customer_group_id = cg.customer_group_id
	        WHERE s.customer_id = t.customer_id and
	        s.status in ('complete', 'processing') and  sfop.method not in ('freepayment' ,'purchaseorder') and (cg.customer_group_code = 'rider1' or s.coupon_code = t.cupom_participante)) as qtd_buys";



            /*$totalAmigos = "(SELECT  count(s.increment_id) FROM customer_entity c
        	INNER JOIN  customer_entity_varchar cv ON c.entity_id = cv.entity_id
	        INNER JOIN  sales_flat_order s ON c.entity_id = s.customer_id
            INNER JOIN  sales_flat_order_payment sfop ON s.entity_id = sfop.parent_id
	        INNER JOIN 	customer_group cg ON s.customer_group_id = cg.customer_group_id
            WHERE s.status in ('complete', 'processing')
            and  sfop.method not in  ('freepayment' ,'purchaseorder') and (cg.customer_group_code = 'rider2' or cg.customer_group_code = 'rider1' or cv.value = t.codigo_ref or  s.coupon_code = t.cupom_amigo)
            and c.entity_id <> t.customer_id) as qtd_friends";*/


            $totalAmigos = "(SELECT count(s.increment_id) FROM customer_entity c
        	LEFT JOIN  customer_entity_varchar cv ON c.entity_id = cv.entity_id AND cv.attribute_id = 191
	        INNER JOIN  sales_flat_order s ON c.entity_id = s.customer_id
            INNER JOIN  sales_flat_order_payment sfop ON s.entity_id = sfop.parent_id
	        INNER JOIN 	customer_group cg ON s.customer_group_id = cg.customer_group_id
            WHERE s.status in ('complete', 'processing')
            and  sfop.method not in  ('freepayment' ,'purchaseorder') and cg.customer_group_code in ('rider2' , 'rider1' , 'general' ,'participante' , 'amigo')  and (cv.value = t.codigo_ref or  s.coupon_code = t.cupom_amigo)) as qtd_friends";




            $isCodGerado = " and (t.status = 1  or  (t.cod_gerado_1 <> '' or t.cod_gerado_2 <> '' or t.cod_gerado_3 <> '' or t.codigo_ref <> '' ))";

            //  
            $sql = "SELECT t.id , t.nome , t.estado  ,  t.parent_id , $totalUser , $totalAmigos FROM t_tree t WHERE $search $isCodGerado ";

            $tree = $this->resource->fetchAll($sql);

            $resposta = array();
            foreach($tree as $node){

                   

                  $node['qtd_sons']  = 0 ;
                 if(!empty($sonsNodeIds[$node['id']])) {
                    $sons = array();
                    $sons = $sonsNodeIds[$node['id']];  
                    $node['qtd_sons']    = $this->getSumAllSonsForParentId($sons);  
                    
                    /*if($node['id'] == 1290){

                        echo '<pre>';
                        var_dump($sons);
                        die;
                    }*/
                  
                    
                 }  



                $node['qtd_buys']    = str_pad($node['qtd_buys'],2,'0',STR_PAD_LEFT);
                $node['qtd_friends'] = str_pad($node['qtd_friends'],3,'0',STR_PAD_LEFT);
                $node['qtd_sons']    = str_pad($node['qtd_sons'],5,'0',STR_PAD_LEFT);
                $node['qtd_total']   = $node['qtd_buys'] + $node['qtd_friends'] + $node['qtd_sons'];


                $resposta[] = $node;
            }

            // echo '<pre>';
            // var_dump($resposta);
            // die;

            return $resposta;

        }catch(Exception $e){

            throw new Exception($e->getMessage() , -3);


        }




    }

    public function getAlltreeForIdT($id = 2){

        $this->getAllIdsTreeForParentId($id);
        $ids = "";
        if (!empty($this->niveis)) {
            array_unshift($this->niveis, $id);
            $ids = implode(',', $this->niveis);
        } else {
            $ids = $id;
        }
        $search = " t.id in ($ids) ";

        $sql = "SELECT t.id , t.nome , t.parent_id  FROM t_tree t WHERE $search ";

        $tree = $this->resource->fetchAll($sql);

        return $tree;

    }




    private function getAllIdsTreeForParentId($parentId){


        try{

            $sql = "SELECT CONVERT(GROUP_CONCAT(lv SEPARATOR ',') , char(400)) as ids
                    FROM (
                           SELECT @pv:=(SELECT CONVERT(GROUP_CONCAT(id SEPARATOR ','), char(400))
                                 FROM t_tree WHERE parent_id IN (@pv)) AS lv
                                    FROM t_tree
                                            JOIN (SELECT @pv:=$parentId) tmp WHERE parent_id IN (@pv) ) a ;";


            $valores = $this->resource->fetchRow($sql);

            $subniveis  = array();
            if(!empty($valores['ids'])){
                $this->niveis[] = $valores['ids'];
                if(strpos($valores['ids'],',') === FALSE){
                    array_push($subniveis ,$valores['ids']);
                }else{
                    $subniveis = explode(',',$valores['ids']);
                }
                foreach($subniveis as $id) {
                    $this->getAllIdsTreeForParentId($id);
                }

            }

        }catch(Exception $e){
            throw new Exception('getAllIdsTreeForParentId :' . $e->getMessage(), -3);
        }


    }


    public function getParentsByInsta($name=null, $status = false)
    {

        $valores = array();
        try {
            $cond = " status = 1 ";
            if($status){
                $cond = " 1 = 1 ";
            }
            if(!empty($name)){
                $cond .= " AND instagram like '%$name%'";
            }

            $sql = "SELECT id , ( CASE status
                        WHEN 2 THEN CONCAT( instagram  , ' - ' ,'List B')
                        ELSE CONCAT( instagram  , ' - ' ,'List A')
                      END
                     ) as label , instagram as value , qtd  FROM t_tree WHERE  $cond";


            $valores = $this->resource->fetchAll($sql);
        }catch(Exception $e){
            throw new Exception("getParentsByInsta :".$e->getMessage());
        }
        return $valores;
    }



    public function getParents($name=null, $status = false)
    {

        $valores = array();
        try {
            $cond = " status = 1 ";
            if($status){
                $cond = " 1 = 1 ";
            }
            if(!empty($name)){
                $cond .= " AND nome like '%$name%'";
            }

            $sql = "SELECT id , ( CASE status
                        WHEN 2 THEN CONCAT( nome  , ' - ' ,'List B')
                        ELSE CONCAT( nome  , ' - ' ,'List A')
                      END
                     ) as label , nome as value , qtd  FROM t_tree WHERE  $cond";


            $valores = $this->resource->fetchAll($sql);
        }catch(Exception $e){
            throw new Exception("getParents :".$e->getMessage());
        }
        return $valores;
    }

    public function alterarCampos($id, $telefone, $instagram, $qtd , $instaStatus , $nome){
        try{

            $sql = "UPDATE t_tree SET nome = :nome ,
                                      slug = :slug,
                                      telefone = :tel,
                                      instagram = :insta,
                                      qtd = :qtd ,
                                      instagram_status = :instastatus WHERE id = :id";

            $nome = trim($nome);
            $slug = $this->removeAcentos($nome, '-');

            $data = array('nome' => $nome ,
                          'slug' => $slug,
                          'tel' => $telefone ,
                          'insta' => $instagram ,
                          'qtd' => $qtd ,
                          'instastatus' => $instaStatus  ,
                          'id' => $id );


            $this->resource->query($sql , $data);

        }catch ( Exception $e){
            echo $e->getMessage();
            // throw new Exception('alterarTelefone : '.$e->getMessage() , -3);
        }
    }

    public function alterarTelefone($id , $telefone){
        try{
            $sql = "UPDATE t_tree SET telefone= '$telefone' WHERE id = $id";
            $this->resource->query($sql);
        }catch ( Exception $e){
            throw new Exception('alterarTelefone : '.$e->getMessage() , -3);
        }
    }


    public function alterarQtd($id , $qtd){
        try{
            $sql = "UPDATE t_tree SET qtd= '$qtd' WHERE id = $id";
            $this->resource->query($sql);
        }catch ( Exception $e){
            throw new Exception('alterarQtd : '.$e->getMessage() , -3);
        }
    }







    public function getParentCoupon($coupon){

      try{
          if(empty($coupon)){
             throw new Exception('Coupon param is empty');
          }

          $sql = "SELECT t.* FROM t_tree t INNER JOIN t_tree_coupon c ON t.id = c.tree_id WHERE c.coupon = :coupon ";
          $data = array('coupon' => $coupon);
          $valores  = $this->resource->fetchRow($sql , $data);

          return $valores;
      }catch(Exception $e){
          throw new Exception('getParentCoupon : Erro ao pegar pai:'.$e->getMessage(), -3);

      }

    }



    public function saveNodeTree($order){

        try{

            if(empty($order)) {
                throw new Exception('saveNodeTree : Passagem de parametros incorreta1.' , -3);
            }

            $couponCode = $order->getData('coupon_code');
            $customerId = $order->getCustomerId();
            $customer   = Mage::getModel('customer/customer')->load($customerId);
            $parent = $this->getParentCoupon($couponCode);

            if(empty($customer) || empty($parent)) {
                throw new Exception('saveNodeTree : Passagem de parametros incorreta2.' , -3);
            }
            $instagram = $customer->getInstagram();

            $json  = $this->getJsonItens($order);
            $child = $customer->getName();
            $child = trim($child);
            $slug = $this->removeAcentos($child, '-');
            $telefone = $customer->getPrimaryBillingAddress()->getTelephone();
            $regionId = $customer->getPrimaryBillingAddress()->getRegionId();
            $region = Mage::getModel('directory/region')->load($regionId);
            $regionName = $region->getName();

            require_once(Mage::getBaseDir('lib') . '/Util/Util.php');
            $initial = Util_Util::getInitialsEua($regionName);

            $insta = "";
            $sqlSearch = "slug = :slug";
            $data = array('slug' => $slug);
            if(strpos( $couponCode ,'@') !== FALSE){
                $parts = explode('@',$couponCode);
                $lastpart = end($parts);
                $insta = substr($lastpart , 0 , -6);
                $sqlSearch  = "REPLACE(instagram , '@','') = REPLACE(:insta , '@','')";
                $data = array('insta' => $insta);
            }

            $sqlVerify = "SELECT * FROM t_tree WHERE ($sqlSearch) and (status = 0 or status = 1 )  ORDER BY ID ASC LIMIT 0,1 ";
            $row = $this->resource->fetchRow($sqlVerify,$data);

            if(empty($row) && empty($insta)) {
               // throw new Exception('Participante ja existente.', -3);
                $sql = "INSERT INTO t_tree(parent_id,parent_name,grandfather_id,nome,slug,telefone,instagram , customer_id  , estado , cod_gerado_1 , json_products) VALUES ";
                $sql .= "(:parent,:name ,:parentid,:child,:slug,:telefone,:instagram , :customerid , :initial , :couponCode  , :json)";
                //$sql .= "(" . $parent['id'] . ",'" . $parent['nome'] . "','" . $parent['parent_id'] . "' ,'$child','$slug','$telefone','$instagram' , $customerId , '$initial' , '$couponCode'  , '$json')";

                $data = array(':parent' => $parent['id'],
                              ':name' => $parent['nome'],
                              ':parentid'=> $parent['parent_id'] ,
                              ':child'=> $child,
                              ':slug'=>$slug,
                              ':telefone'=>$telefone,
                              ':instagram'=> $instagram,
                              ':customerid'=>$customerId  ,
                              ':initial'=>$initial  ,
                              ':couponCode'=> $couponCode ,
                              ':json'=>$json);


                $this->resource->query($sql,$data);

            }else if(!empty($row) && !empty($couponCode)){


                    $sql = "UPDATE t_tree SET
                                          nome = :nome ,
                                          telefone = :telefone ,
                                          customer_id  = :customerid ,
                                          estado = :initial ,
                                          cod_gerado_1  = :couponCode ,
                                          json_products = :json  WHERE id = :id ";

                    $data = array(
                        ':nome'=>$child,
                        ':telefone'=>$telefone,
                        ':customerid'=>$customerId ,
                        ':initial'=>$initial ,
                        ':couponCode'=> $couponCode ,
                        ':json'=>$json ,
                        ':id' => $row['id']);


                    $this->resource->query($sql,$data);

            }

            $treeId = $this->resource->lastInsertId();
            if (!empty($treeId))
                $this->triggerSaveAction($treeId);

           /* $exb = new Seaway_Tree_Model_Exhibition();
            $exb->createExhibition($parentId , $verifySlugs);*/

        }catch(Exception $e){

            echo $e->getMessage();
            die;

            throw new Exception('saveNodeTree : Erro ao cadastrar filho:'.$e->getMessage(), -3);
        }

    }


    public  function  identifyType($treeId){

        $sql = "select getType($treeId) as type" ;
        $row = $this->resource->fetchRow($sql);
        $type ="";
        if(!empty($row['type'])){
            $type = $row['type'];
        }
        return $type;
    }



    public  function  triggerSaveAction($treeId){

        $type = $this->identifyType($treeId);
        if($type != ""){
            $sql  = "SELECT id FROM t_action WHERE tipo = $type AND trigger_sys = 'invoice' limit 0,1  ";
            $row = $this->resource->fetchRow($sql);
            $actionId = $row['id'];
            $this->cadastrarAcao($treeId, $actionId);

        }

    }




    public function verifyOrderEmail($email , $isAjax = false ){

        $result = false;
        if(empty($email)){
            return $result;
        }

        $coupon  = Mage::getSingleton('customer/session')->getData('coupon');

        if(!empty($coupon) && $coupon['is_special']) {
            // verifica se o email ja possui
            $table_prefix = Mage::getConfig()->getTablePrefix();
            $order_table = $table_prefix . 'sales_flat_order';
            $on_condition = "main_table.parent_id = $order_table.entity_id";
            $orderCollection = Mage::getModel('sales/order_payment')->getCollection()->addFieldToFilter('method', "freepayment")
                ->addFieldToFilter('sales_flat_order.status', array('complete', 'processing', 'pending'))
                ->addFieldToFilter('sales_flat_order.customer_email', $email);
            $orderCollection->getSelect()->join($order_table, $on_condition);

            if ($orderCollection->count() > 0) {

                $this->removeCouponCart();
                Mage::getSingleton('customer/session')->unsetData('coupon');
                Mage::getSingleton('customer/session')->setData('freepayment_purchase', '1');
                $result = true;
                if (!$isAjax)
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl("/"));
            }

        }
        return $result;

    }

    public function removeCouponCart(){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if(!empty($quote->getData())) {
            if($quote->getCouponCode() && $quote) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode('')->collectTotals()->save();
            }
        }
    }


    public function getFreepaymentPurchase(){

        $msg = Mage::getSingleton('customer/session')->getData('freepayment_purchase' );
        Mage::getSingleton('customer/session')->unsetData('freepayment_purchase' );
        return $msg;
    }




    public function verifyOrderInstagram($insta ){

        $result = false;
        if(empty($insta)){
            return $result;
        }

        $coupon  = Mage::getSingleton('customer/session')->getData('coupon');

        if(!empty($coupon)) {

            $sql = "SELECT count(*) as total FROM t_tree t WHERE REPLACE(trim(t.instagram), '@', '') = REPLACE(trim(:insta), '@', '') AND customer_id is not null ";
            $data = array('insta' => $insta);
            $count = $this->resource->fetchRow($sql, $data);

            $result = (!empty($count['total']) && $count['total'] > 0) ? true : false;

            if ($result) {
                $this->removeCouponCart();
                Mage::getSingleton('customer/session')->unsetData('coupon');
                Mage::getSingleton('customer/session')->setData('freepayment_purchase', '1');
            }
        }

        return $result;


    }


    private function getJsonItens($order){

        $values = array();
        $_items = $order->getItemsCollection();
        foreach ($_items as $item) {
            if (!$item->getParentItem()) continue;
            $sku ="";
            $parentProduct  = null;
            $childProduct   = null;

            $sku =  substr($item->getSku() , 0 , 4);
            $parentProduct  =  Mage::getModel('catalog/product')->loadByAttribute('sku' , $sku );
            $skuChild =  $sku.'28'.substr($item->getSku() , 6 , 2);
            $childProduct   = Mage::getModel('catalog/product')->loadByAttribute('sku' , $skuChild );

            $value = array();
            $value['id']   = $parentProduct->getEntityId();
            $value['img']  = $childProduct->getData('thumbnail');
            $value['tipo'] = $parentProduct->getData('p_model_value');
            $value['cor']  = $childProduct->getColor();
            $value['sku']  = $sku;

            $values[] = $value;

        }

        return json_encode($values);
    }


    public function saveChildrens($childrens,$parentId,$qtd = 0){
       try{
           $sql = "INSERT INTO t_tree(parent_id,parent_name,grandfather_id,nome,slug,telefone,instagram , instagram_status,instagram_list,current_step ) VALUES ";
           $v = '';
           if(empty($childrens['name'])) {
              throw new Exception('saveChildrens : Passagem de parametros incorreta.' , -3);
           }

           $sqlGetParent = "select j.nome , j.parent_id from t_tree j where j.id = '$parentId'";
           $getParent = $this->resource->fetchRow($sqlGetParent);

           $slugs = array();
           foreach ($childrens['name'] as $key => $child) {

               $slug = "";

               $child = trim($child);
               $slug = $this->removeAcentos($child, '-');
               $slugs[] = $slug;

               $telefone = '';
               $telefone = $childrens['telephone'][$key];

               $atleta = '';
               $atleta = (isset($childrens['atleta'][$key]))? $childrens['atleta'][$key] : 0;

               $instagram = '';
               $instagram = trim($childrens['instagram'][$key]);
               // possui instagram ?
               $instagramStatus = 0;
               $instagramStatus = $childrens['instagram_status'][$key];

               $instagramList = 0;
               $instagramList = $childrens['instagram_list'][$key];

               $sql .= $v . "($parentId,'".$getParent['nome']."', '".$getParent['parent_id']."' ,'$child','$slug','$telefone','$instagram' , '$instagramStatus' ,'$instagramList',1)";
               $v = ',';
           }

           $verifySlugs = "'".implode("','", $slugs)."'";
           $sqlVerify = "SELECT count(*) as total FROM t_tree WHERE slug in ($verifySlugs) and (status = 0 or status = 1 )";
           $row = $this->resource->fetchRow($sqlVerify);


           if($row['total'] > 0)
               throw new Exception('Participante ja existente aqui.',-3);

           $this->resource->query($sql);

           $sql2 = "UPDATE t_tree SET qtd = $qtd WHERE id = $parentId";
           $this->resource->query($sql2);


           $this->generateImgInsta($parentId);


           $exb = new Seaway_Tree_Model_Exhibition();
           $exb->createExhibition($parentId , $verifySlugs);



       }catch(Exception $e){
           throw new Exception('Erro ao cadastrar filho:'.$e->getMessage(), -3);
       }
   }


    public  function  generateImgInsta($parentId){


        $sqlGenerateImg =  "SELECT * FROM t_tree WHERE parent_id = $parentId";
        $values = $this->resource->fetchAll($sqlGenerateImg);
        if(!empty($values)){
            foreach($values as $val){
                $id  = $val['id'];
                Mage::getModel('tree/experienceinsta', array('tree_id' => $id))->generate();
            }

        }

    }




   public function isParticipantByCustomerId($customerId , $email = false){



       if($email){

           $customer = Mage::getModel("customer/customer");
           $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
           $customer  = $customer->loadByEmail($email);
           $customerId = $customer->getEntityId();

        }

        $query = "SELECT count(*) as total FROM t_tree WHERE customer_id = :id AND (status = 0 or status = 1) ";
        $data = array('id' => $customerId);

        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }


    public function isExistsName($nome){

        $slug = $this->removeAcentos($nome , '-');
        $query = "SELECT count(*) as total FROM t_tree WHERE slug = :name AND (status = 0 or status = 1) ";
        $data = array('name' => $slug);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }


    public function isExistsInsta($instagram){

        $query = "SELECT count(*) as total FROM t_tree WHERE REPLACE(instagram ,'@', '') = REPLACE(:insta , '@', '')  AND (status = 0 or status = 1) ";
        $data = array('insta' => $instagram);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }



    public function removeAcentos($string, $slug = false) {

        //$string = ($string);

        $string = utf8_decode($string);
        $string = strtolower($string);

        // Código ASCII das vogais
        $ascii['a'] = range(224, 230);
        $ascii['e'] = range(232, 235);
        $ascii['i'] = range(236, 239);
        $ascii['o'] = array_merge(range(242, 246), array(240, 248));
        $ascii['u'] = range(249, 252);
        // Código ASCII dos outros caracteres
        $ascii['b'] = array(223);
        $ascii['c'] = array(231);
        $ascii['d'] = array(208);
        $ascii['n'] = array(241);
        $ascii['y'] = array(253, 255);
        foreach ($ascii as $key=>$item) {
            $acentos = '';
            foreach ($item AS $codigo)
                $acentos .= chr($codigo);

            $troca[$key] = '/['.$acentos.']/i';
        }

        $string = preg_replace(array_values($troca), array_keys($troca), $string);


        // Slug?
        if ($slug) {
            // Troca tudo que não for letra ou número por um caractere ($slug)
            $string = preg_replace('/[^a-z0-9]/i', $slug, $string);
            // Tira os caracteres ($slug) repetidos
            $string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
            $string = trim($string, $slug);
        }
        return $string;
    }


    public function getAction($type , $index = false ){
        $filtro  = "";
        if($index){
            $filtro  = " AND (tipo_acao = 'p')";
        }

        $query = "SELECT a.id , cast(concat(a.tipo_acao , '-' , name) as char(255)) as name , a.tipo_acao , a.proxima_acao,  (SELECT  p.name  FROM t_action p WHERE p.id = a.proxima_acao ) as proxima_acao_name FROM t_action a WHERE a.tipo = $type $filtro  order by a.ordem asc, a.id asc ";
        $valores = $this->resource->fetchAll($query);
        return $valores;

    }


    public function getTreeAction($id = null){

        $query = "SELECT  a.name , ta.id , DATE_FORMAT(ta.created_at , '%d/%m/%Y %T') as data_cadastro  FROM t_tree_action ta INNER JOIN  t_action a ON ta.action_id = a.id WHERE ta.tree_id = :id order by ta.created_at asc ";
        $data = array('id' => $id);
        $valores = $this->resource->fetchAll($query,$data);

        $retorno  = array();
        if(!empty($valores)){
            foreach($valores as $val ){
                $query2 = "";
                $r = array();
                $query2 = "SELECT  a.name , ta.id , ta.obs , DATE_FORMAT(ta.created_at , '%d/%m/%Y %T') as data_cadastro FROM t_tree_action_exception ta INNER JOIN
                                   t_action a ON ta.exception_id = a.id WHERE ta.tree_action_id = " .$val['id'];
                $r = $this->resource->fetchAll($query2);
                $val['excecoes'] = $r;
                $retorno[] = $val;
            }
        }

        return $retorno;


    }


    public function getObservation($actionId){

        $query = "SELECT a.id , cast(concat(a.tipo_acao , '-' , name) as char(255)) as name , a.tipo_acao , a.proxima_acao FROM t_action a WHERE a.tipo_acao = :tipo_acao and (a.proxima_acao = :action  or  a.proxima_acao is null )   order by a.ordem asc";
        $data = array('tipo_acao' => 'e' , 'action' => $actionId );
        $valores = $this->resource->fetchAll($query,$data);
        return $valores;


    }


    public function getSituationQty($sqlStatus , $sqlAction ,$sqlParentType){

         // filtros

        $filter  = $sqlStatus.$sqlAction.$sqlParentType;


       $sql = "SELECT (SELECT count(*) FROM t_tree t WHERE situation = 1 $filter) as total_aguardando ,
               (SELECT DATE_FORMAT(updated_at , '%d/%m %H:%i')  FROM t_tree t WHERE situation = 1 $filter ORDER BY updated_at DESC LIMIT 0,1  ) as lastdate_aguardando ,

               (SELECT count(*) FROM t_tree t WHERE situation = 2 $filter) as total_problema ,
               (SELECT DATE_FORMAT(updated_at , '%d/%m %H:%i')  FROM t_tree t WHERE situation = 2 $filter ORDER BY updated_at DESC LIMIT 0,1 ) as lastdate_problema ,

               (SELECT count(*) FROM t_tree t WHERE situation = 3 $filter) as total_lixeira,
               (SELECT DATE_FORMAT(updated_at , '%d/%m %H:%i') FROM t_tree t WHERE situation = 3 $filter ORDER BY updated_at DESC LIMIT 0,1 ) as lastdate_lixeira

               FROM t_tree LIMIT 0,1 ";



        $r = $this->resource->fetchRow($sql);

        $return = array();
        if($r){
            $return = $r;
        }
        return $return ;
    }


    public function isExistsAction($treeId , $actionId)
    {

        $query = "SELECT count(*) as total FROM t_tree_action ta WHERE ta.tree_id = :tree and ta.action_id = :action";
        $data = array('tree' => $treeId , 'action' => $actionId);
        $valores = $this->resource->fetchRow($query, $data);
        return (!empty($valores) && $valores['total'] > 0) ? true : false;


    }


    public function isValidApproved($id=null , $parentId = null){

        if(empty($parentId)){
            $queryParent = "SELECT   parent_id   FROM t_tree  WHERE id = :id";
            $dataParent = array('id' => $id);
            $valoresParent = $this->resource->fetchRow($queryParent,$dataParent);
            $parentId = $valoresParent['parent_id'];
        }
        // qtd filhos de permitidos  é maior que a quantidade de filhos com codigo gerado
        //$query = "SELECT IF(count(t.id) < (SELECT p.qtd FROM t_tree p WHERE p.id = $parentId) , true , false) as question  FROM t_tree t WHERE t.parent_id = $parentId and ( t.cod_gerado_1 <> '' or  t.cod_gerado_2 <> ''  or  t.cod_gerado_3 <> '' )";
        //$valores = $this->resource->fetchRow($query);


        return /*($valores['question'])? */true /*: false*/;
    }




    public function updateListParticipantesMailChimp(){

        /*$sql = "select t.id from customer_entity ct INNER JOIN  tree t on t.customer_id = ct.entity_id INNER JOIN tree_action ar ON t.id = ar.tree_id INNER JOIN action a ON ar.action_id = a.id
                    WHERE  (date(ar.created_at) >= '2016-10-19' and date(ar.created_at) <= '2016-11-04') AND (a.name like 'whatsapp/2°ligação + Cod. + Link' or a.name like 'Venda loja do amigo' or a.name like 'Houve venda loja do amigo' )
                    and t.codigo_ref <> '' and t.id not in(14 , 33 , 101 , 54 ,61 ,50 ) group by ct.email order by t.nome " ;*/

        $sql = "select t.id from t_tree t INNER JOIN t_tree_action ar ON t.id = ar.tree_id INNER JOIN t_action a ON ar.action_id = a.id
                    WHERE  (date(ar.created_at) >= '2016-10-19' and date(ar.created_at) <= '2016-11-04') AND (a.id like 'whatsapp/2°ligação + Cod. + Link' or a.name like 'Venda loja do amigo' or a.name like 'Houve venda loja do amigo' )
                    and t.codigo_ref <> '' and t.id not in(14 , 33 , 101 , 54 ,61 ,50 ) group by t.id order by t.nome";

        $valores = $this->resource->fetchAll($sql);
        if(!empty($valores)){
            foreach ($valores as $valor ) {
                $code  = $this->generateCodeCoupon();
              //  echo $valor['$valor']
                echo $sqlUp = "UPDATE t_tree SET codigo_ref_url_2 = '$code'  WHERE id = ".$valor['id'].";";
                echo '<br>';
                //$this->resource->query($sqlUp);
            }
        }


    }



    public function generateCodeCoupon(){

        do{
            $isCupomValid = true;
            $code = Mage::helper('core')->getRandomString(5);
            $isEmpty = self::isExistsCode($code);
            if(empty($isEmpty)){
                $isCupomValid = false;
            }
        }while($isCupomValid);

        return $code;
    }


    public function isExistsCode($code){
        $query = "SELECT count(*) as total FROM t_tree WHERE codigo_ref = :cod or codigo_ref_url = :cod ";
        $data = array('cod' => $code);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }



    public function isExistsCoupon($coupon){
        $query = "SELECT count(*) as total FROM t_tree WHERE cod_gerado = :cod";
        $data = array('cod' => $coupon);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }



    public function isExistsCouponTree($coupon){
        $query = "SELECT count(*) as total FROM t_tree WHERE cod_gerado_1 = :cod1 or cod_gerado_2 = :cod2 or cod_gerado_3 = :cod3 ";
        $data = array('cod1' => $coupon ,'cod2' => $coupon , 'cod3' => $coupon );
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }



    public function updateStatus($treeId,$status){
        try{
            $query = "UPDATE t_tree SET status = :sit  WHERE id = :id";
            $data = array('sit' => $status , 'id' => $treeId);
            $this->resource->query($query,$data);
        }catch(Exception $e){
            throw new Exception('updateStatus : ' . $e->getMessage() , -3);
        }

    }



    public function updateStatusStore($treeId){
        try{
            $query = "UPDATE t_tree SET status_store = :status_store  WHERE id = :id";
            $data = array('status_store' => 1 , 'id' => $treeId);
            $this->resource->query($query,$data);
        }catch(Exception $e){
            throw new Exception('updateStatus : ' . $e->getMessage() , -3);
        }

    }



    public function updateSituation($treeId,$situation){
        try{
            $query = "UPDATE t_tree SET situation = :sit , updated_at = NOW()  WHERE id = :id";
            $data = array('sit' => $situation , 'id' => $treeId);
            $this->resource->query($query,$data);
         }catch(Exception $e){
               throw new Exception('updateSituation : ' . $e->getMessage() , -3);
        }

    }

    public function isExistsCodeFriend($codefriend){
        $codefriend  = trim($codefriend);
        $codefriend  = strtolower($codefriend);
        $codefriend  = str_replace(" ","",$codefriend);
        return $this->isExistsCouponFriend($codefriend);
    }


    public function salvarCouponFriend($nome, $urlCode, $id){

        try {

            $retorno = false;
            $nomeOriginal = $nome;
            $nome  = trim($nome);
            $nome  = strtolower($nome);
            $nome  = str_replace(" ","",$nome);

            if (empty($nome) || empty($id) || !is_numeric($id)) {
                throw new Exception('passagem de parametros invalida ',-3);
            }

            if ($this->isExistsCouponFriend($nome)) {
                throw new Exception('Nome do cupom ja utilizado',-3);
            }

           /*  if ($this->userAlreadyCouponFriend($id)) {
                throw new Exception('Usuario ja possui um cupom ',-3);
            }*/

            $rs = $this->isExistsUrlCode($urlCode, $id);
            if($rs){$urlCode = $urlCode.$id;}

            $sqlup = "UPDATE  t_tree SET codigo_ref = '$nome', codigo_ref_url = '$urlCode' WHERE id=".$id;
            $this->resource->query($sqlup);

            $rs = array();
            $rs['status'] = true;
            $rs['nome'] = $nome;
            $rs['urlCode'] = $nome;
            return $rs;

        }catch(Exception $e){
            throw new Exception('salvarCouponFriend '.$e->getMessage());
        }
    }

    public function salvarLinkMsg($linkMsg, $id){
        try{
            $sql = "INSERT INTO t_tree_links (tree_id, link_msg) VALUES (:id, :link_msg)";
            $data = array('id' => $id, 'link_msg' => $linkMsg);
            $this->resource->query($sql, $data);
        }catch(Exception $e){
            echo $e->getMessage();
        }    
    }

    public function userAlreadyCouponFriend($id){

        $sql = "SELECT  count(*) as total  FROM t_tree  WHERE id=$id AND (codigo_ref is not null AND codigo_ref <> '' )";
        $totals  = $this->resource->fetchRow($sql);

        if($totals['total'] > 0){
            return true;
        }
        return false;
    }


    public function isExistsCouponFriend($nome){
        $query = "SELECT count(*) as total FROM t_tree WHERE codigo_ref = :codigo_ref";
        $data = array('codigo_ref' => $nome);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;
    }

    public function isExistsUrlCode($urlCode){
        $query = "SELECT count(*) as total FROM t_tree WHERE codigo_ref_url = :codigo_ref_url";
        $data = array('codigo_ref_url' => $urlCode);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;
    }


    public function isExistsCouponDiscountFriend($cupom){
        $query = "SELECT count(*) as total FROM t_tree WHERE cupom_amigo = :cupom";
        $data = array('cupom'=> $cupom);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;

    }


    public function isExistsCouponParticipantOrFriend($cupom){
        $query = "SELECT count(*) as total FROM t_tree WHERE cupom_participante = :cupom OR cupom_amigo = :amigo";
        $data = array('cupom'=> $cupom , 'amigo' =>  $cupom);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;
    }




    public function isExistsCouponDiscountParticipant($cupom){
        $query = "SELECT count(*) as total FROM t_tree WHERE cupom_participante = :cupom";
        $data = array('cupom'=> $cupom);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;
    }


    public function isCreatedCouponDiscountParticipant( $id){
        $query = "SELECT count(*) as total FROM t_tree WHERE (cupom_participante <> '' and  cupom_participante is not null ) and id = :id";
        $data = array('id'=>  $id);
        $valores = $this->resource->fetchRow($query,$data);
        return (!empty($valores) && $valores['total'] > 0  )? true : false;
    }

     public function saveParticipantCoupon( $id , $couponPart  ){
        $query = "UPDATE t_tree SET  cupom_participante = '$couponPart'  WHERE id = $id";
        $this->resource->query($query);
     }


    public function saveFriendCoupon( $id ,  $couponFriend ){
        $query = "UPDATE t_tree SET cupom_amigo = '$couponFriend' WHERE id = '$id'";
        $this->resource->query($query);

    }


    public function typeCouponParticipant($coupon){
        
        $sql = "SELECT * FROM t_tree WHERE cupom_participante = :amg or cupom_amigo = :amg ";
        $row = $this->resource->fetchRow($sql , array('amg' => $coupon) );


        if(!empty($row['cupom_participante'])){

            $customer = Mage::getModel('customer/session')->getCustomer();
            if(strcasecmp($row['cupom_participante'], $coupon) == 0){

                $group = Mage::getModel('customer/group')->load('Participante', 'customer_group_code');


                $groupId = $group->getId();
                $customer->setGroupId($groupId);
                $customer->save();    

            }else if (strcasecmp($row['cupom_amigo'], $coupon) == 0){

                $group = Mage::getModel('customer/group')->load('Amigo', 'customer_group_code');

                $groupId = $group->getId();
                $customer->setGroupId($groupId);
                $customer->save();    

    
            }

        }


    }


    public function typeCouponFriend($coupon , $customerId){

        $sql = "SELECT * FROM t_tree WHERE  cupom_amigo = :amg ";
        $row = $this->resource->fetchRow($sql , array('amg' => $coupon) );


        if(!empty($row['cupom_amigo']) && $customerId){

            $customer = Mage::getModel('customer/customer')->load($customerId);

            if((strcasecmp($row['cupom_amigo'], $coupon) == 0) && $customerId == $row['customer_id']){

                $group = Mage::getModel('customer/group')->load('Participante', 'customer_group_code');

                $groupId = $group->getId();
                $customer->setGroupId($groupId);
                $customer->save();

            }else if (strcasecmp($row['cupom_amigo'], $coupon) == 0){

                $group = Mage::getModel('customer/group')->load('Amigo', 'customer_group_code');

                $groupId = $group->getId();
                $customer->setGroupId($groupId);
                $customer->save();


            }

        }


    }



    public function createCouponParticipant( $id  , $slug){


        try {

            if($this->isCreatedCouponDiscountParticipant($id)){
                throw new Exception("Já foi criado um cupom para este participante." , -3);
            }
            $name = "";

            if(strpos($slug  , '-') === FALSE){
                $name = strtoupper($slug);
            }else{
                $arrName = explode('-',$slug);


                $firstName = current($arrName);
                $endName = end($arrName);

                $firstLetter = substr($firstName,0,1);
                $endLetter = substr($endName,0,1);

                $name = $firstLetter.$endLetter;
                $name = strtoupper($name);

            }

            $isTrue = true;
            $c = 0;
            do{
                $code = "";
                $code  = ($c == 0)?  $name : $name.$c;
                if(!$this->isExistsCouponDiscountParticipant($code )) {
                    $isTrue = false;
                }
                $c++;
            }while($isTrue);

            $description = "Cupom  do participante (TREE) 40% de desconto";
             // online
            $groupIds = array(0, 1, 2, 3, 15, 16, 17, 233);
            //local
            //$groupIds = array(0, 1, 2, 3, 15, 16, 17, 103);
            $de = date('Y-m-d');
            $ate = '2017-03-30';
            $valorFixo = 40;
            $usesPerCoupon = 9999;
            $usesPerCustomer = 9999;

            Seaway_Tree_Model_Cupom::_createRuleCredit($code, $description, $groupIds, $de, $ate, $valorFixo, $usesPerCoupon, $usesPerCustomer);



            return $code;

        }catch (Exception $e){

            if($e->getCode() == -3){

                throw new Exception($e->getMessage() , -2);

            }else{

                 Mage::log("Model->Tree->createCouponParticipant: ".$e->getMessage(), null , 'tree.log', true);
                 throw new Exception($e->getMessage() , -2);

            }


        }

    }






    public function createCouponFriend($id , $slug = ""){


        try {


            $name = "SEAWAY" . $id;
            if(!empty($slug)) {

                if (strpos($slug, '-') === FALSE) {
                    $name = strtoupper($slug);
                } else {
                    $arrName = explode('-', $slug);


                    $firstName = current($arrName);
                    $endName = end($arrName);

                    $firstLetter = substr($firstName, 0, 1);
                    $endLetter = substr($endName, 0, 1);

                    $name = $firstLetter . $endLetter;
                    $name = "SEAWAY" .strtoupper($name);

                }

                if($this->isExistsCouponDiscountFriend($name)){
                    $name = $name . $id;
                }
            }




            if($this->isExistsCouponDiscountFriend($name)){
                throw new Exception("Este cupom ja foi criado" , -3);
            }


            $description = "Cupom do participante (TREE) 40% de desconto";
            // online
            $groupIds = array(0, 1, 2, 3, 15, 16, 17, 234);
            // local
            //$groupIds = array(0, 1, 2, 3, 15, 16, 17, 104);
            $de = date('Y-m-d');
            $ate = '2017-03-30';
            $valorFixo = 40;
            $usesPerCoupon = 9999;
            $usesPerCustomer = 9999;

            Seaway_Tree_Model_Cupom::_createRuleCredit($name, $description, $groupIds, $de, $ate, $valorFixo, $usesPerCoupon, $usesPerCustomer);


            return $name;

        }catch (Exception $e){

            if($e->getCode() == -3){

               throw new Exception($e->getMessage() , -2);

            }else{

                Mage::log("Model->Tree->createCouponFriend: ".$e->getMessage(), null , 'tree.log', true);
                throw new Exception("Ocorreu um erro no sistema por favor tente novamente." , -2);

            }


        }

    }

    public  function getCouponIsValidTree($coupon){

        try {
            $this->checkStatusCoupon($coupon);

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_tree_coupon tc INNER JOIN t_tree t ON tc.tree_id = t.id WHERE tc.coupon=:cod  LIMIT 0,1";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);
            return $row;

        }catch (Exception $e){
            echo 'getCouponIsValidTree : '.$e->getMessage();
            throw new Exception('getCouponIsValidTree : '. $e->getMessage() , -3);
        }
    }


    public function checkStatusCoupon($couponCode){

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
            echo 'checkStatusCoupon : '.$e->getMessage();
            throw new Exception('checkStatusCoupon : '. $e->getMessage() , -3);
        }

    }



    public  function getUserForCupom($cupom ){
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



    public  function getUserForCupomApp($cupom ){
        try{
            if(empty($cupom) && is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);

            $conn = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sql = "SELECT * FROM  t_tree t INNER JOIN t_coupon_app c ON t.id = c.tree_id WHERE c.coupon = '$cupom'";
            $values = $conn->fetchRow($sql);

            return $values;

        }catch(Exception $e){
            throw new Exception('getUserForCupom :' . $e->getMessage(), -3);
        }
    }




    public  function enableBring(){
        try{

            $coupon = Mage::getModel('customer/session')->getData('coupon');

            if(!empty($coupon) && !empty($coupon['is_special']) && !empty($coupon['coupon_name'])){
                $data = array();
                $code = $coupon['coupon_name'];
                $url  = Mage::getBaseUrl().'ex?c='.$code;
                $data['url'] = $url;
                Mage::getModel('customer/session')->setData('coupon_disable',$data);

                $this->removeCouponCart();
                Mage::getSingleton('customer/session')->unsetData('coupon');
            }
        }catch(Exception $e){
            throw new Exception('getUserForCupom :' . $e->getMessage(), -3);
        }
    }


    //Gatilho para mudar a action escolheu o BS
    public function triggerTreeWin($treeId){

        try {

            $bind =  array('slug' => $treeId);
            $sql = "SELECT * FROM t_tree where id = :slug";
            $tree = $this->resource->fetchRow($sql, $bind);

            $tipo = 0;
            if($tree["parent_id"] == 1){
                $tipo = 0;
            }
            if($tree["grandfather_id"] == 1){
                $tipo = 1;
            }
            if($tree["grandfather_id"] != 1 && $tree["grandfather_id"] != 0){
                $tipo = 2;
            }

            $bind =  array('slug' => $tipo);
            $sqlAction = "SELECT * FROM t_action where name like '%16 - ESCOLHEU O BOARDSHORT%' and tipo = :slug";
            $action = $this->resource->fetchRow($sqlAction, $bind);

            $sqlFutureAction = "SELECT a.id, a.name FROM t_action a  WHERE a.id = '".$action['proxima_acao']."'";
            $futureAction = $this->resource->fetchRow($sqlFutureAction);

            $sql  = "UPDATE t_tree SET last_action = '".$action['name']."',
                     last_action_id = '".$action['id']."' ,
                     last_action_date = now(),
                     future_action = '".$futureAction['name']."-".$futureAction['id']."'   ,
                     future_action_id = '".$futureAction['id']."',
                     last_exception = null,
                     last_exception_des =  null,
                     situation = 0,
                     current_step = '2'
                     WHERE  id = '$treeId' ";

            if(!$this->resource->query($sql)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            $sqlInsert  = "INSERT INTO `seaway_shop_eng`.`t_tree_action` (`action_id`, `tree_id`, `created_at`) VALUES ('" . $action['id'] . "', '" . $treeId . "', now())";

            if(!$this->resource->query($sqlInsert)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

        }catch(Exception $e){
            Mage::log($e->getMessage() , null ,'triggerTreeWin.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
        }

    }

    //Gatilho para mudar a action Faturou o BS
    public function triggerTreeFaturou($treeId, $actionId){

        try{

            $bind =  array('slug' => $actionId);
            $sqlAction = "SELECT * FROM t_action where id = :slug";
            $action = $this->resource->fetchRow($sqlAction, $bind);

            $sqlFutureAction = "SELECT a.id, a.name FROM t_action a  WHERE a.id = '".$action['proxima_acao']."'";
            $futureAction = $this->resource->fetchRow($sqlFutureAction);

            $sql  = "UPDATE t_tree SET last_action = '".$action['name']."'  ,
                                     last_action_id = '".$action['id']."' ,
                                     last_action_date = '".$action['created_at']."'  ,
                                     future_action = '".$futureAction['name']."-".$futureAction['id']."'   ,
                                     future_action_id = '".$futureAction['id']."'
                                     WHERE  id = '$treeId' ";

            if(!$this->resource->query($sql)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            $sqlInsert  = "INSERT INTO `seaway_shop_eng`.`t_tree_action` (`action_id`, `tree_id`, `created_at`) VALUES ('" . $action['id'] . "', '" . $treeId . "', now())";
            if(!$this->resource->query($sqlInsert)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

        }catch(Exception $e){
            Mage::log($e->getMessage() , null ,'triggerTreeFaturou.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
        }

    }

    public function triggerUpdadeIndicarAmigos($customerId){

        try {

            $bind = array('slug' => $customerId);
            $sql = "SELECT * FROM t_tree where id = :slug";
            $tree = $this->resource->fetchRow($sql, $bind);

            $tipo = 0;
            if($tree["parent_id"] == 1){
                $tipo = 0;
            }
            if($tree["grandfather_id"] == 1){
                $tipo = 1;
            }
            if($tree["grandfather_id"] != 1 && $tree["grandfather_id"] != 0){
                $tipo = 2;
            }

            $bind =  array('slug' => $tipo);

            $sqlAction = "SELECT * FROM t_action where name like '%10 - AMIGOS INDICADOS%' and tipo = :slug";
            $action = $this->resource->fetchRow($sqlAction, $bind);

            $sqlFutureAction = "SELECT a.id, a.name FROM t_action a  WHERE a.id = '".$action['proxima_acao']."'";
            $futureAction = $this->resource->fetchRow($sqlFutureAction);

            $sql  = "UPDATE t_tree SET last_action = '".$action['name']."',
                     last_action_id = '".$action['id']."',
                     last_action_date = now(),
                     future_action = '".$futureAction['name']."-".$futureAction['id']."',
                     future_action_id = '".$futureAction['id']."',
                     last_exception = null,
                     last_exception_des =  null,
                     situation = 0
                     WHERE  id = '" . $customerId . "'";

            if(!$this->resource->query($sql)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            $sqlInsert  = "INSERT INTO `seaway_shop_eng`.`t_tree_action` (`action_id`, `tree_id`, `created_at`) VALUES ('" . $action['id'] . "', '" . $customerId . "', now())";

            if(!$this->resource->query($sqlInsert)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

        }catch(Exception $e){
            Mage::log($e->getMessage() , null ,'triggerTreeWin.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
        }
    }


    public function triggerUpdadeTree($customerId, $statusOrder = null){

        try {

            $bind =  array('slug' => $customerId);
            $sql = "SELECT * FROM t_tree where customer_id = :slug";
            $tree = $this->resource->fetchRow($sql, $bind);

            $tipo = 0;
            if($tree["parent_id"] == 1){
                $tipo = 0;
            }
            if($tree["grandfather_id"] == 1){
                $tipo = 1;
            }
            if($tree["grandfather_id"] != 1 && $tree["grandfather_id"] != 0){
                $tipo = 2;
            }

            $bind =  array('slug' => $tipo);

            if($statusOrder == "Shipment"){
                $sqlAction = "SELECT * FROM t_action where name like '%18 - ENVIAMOS O BOARDSHORT / ENVIAMOS O PUSH DE RASTREIO%' and tipo = :slug";
                $action = $this->resource->fetchRow($sqlAction, $bind);
            }

            $sqlFutureAction = "SELECT a.id, a.name FROM t_action a  WHERE a.id = '".$action['proxima_acao']."'";
            $futureAction = $this->resource->fetchRow($sqlFutureAction);

            $sql  = "UPDATE t_tree SET last_action = '".$action['name']."',
                     last_action_id = '".$action['id']."',
                     last_action_date = now(),
                     future_action = '".$futureAction['name']."-".$futureAction['id']."',
                     future_action_id = '".$futureAction['id']."',
                     last_exception = null,
                     last_exception_des =  null,
                     situation = 0
                     WHERE  id = '" . $tree["id"] . "'";

            if(!$this->resource->query($sql)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            $sqlInsert  = "INSERT INTO `seaway_shop_eng`.`t_tree_action` (`action_id`, `tree_id`, `created_at`) VALUES ('" . $treeAction['id'] . "', '" . $treeAction["id"] . "', now())";

            if(!$this->resource->query($sqlInsert)){
                throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
            }

            return true;
        }catch(Exception $e){
            Mage::log($e->getMessage() , null ,'triggerTreeWin.log' , true );
            throw new Exception('Não foi possivel atualizar a árvore por favor contate o desenvolvedor');
        }

    }

}