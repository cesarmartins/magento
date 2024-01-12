<?php
class TotalMetrica_Experience_Model_Promoscore
{

    private $score  = array();
    private $treeScore = array();
    private $dateLastAccess  = null ;
    private $distribuitionCoupons = array();

    CONST INITIAL_SCORE = 50;

    /*
     *
     *Cria o score inicial dando 50% de desconto , cria a primeira esfera e add ao score
     *
     *
     */


    public function createInitialScore($treeId){
        // verifica se o usuario ja acessou anteriormente
        if(!$this->isUserAlreadyHaveLastAccess($treeId)){
            $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
            $initialScore = self::INITIAL_SCORE;

            $lastSphereId  =  $this->getLastSphere($treeId);
            // caso nao possua uma ultima esfera cria uma
            if(!$lastSphereId){
                $lastSphereId = $this->createNewSphere($treeId);
            }

            $sqlInsert = "INSERT INTO  `t_score`(`tree_id`,
                                                 `sphere_id`,
                                                 `score`)VALUES (". $treeId .",".$lastSphereId.",{$initialScore})";
            $conn->query($sqlInsert);
        }
    }




    public function returnLastPhase($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT fase FROM t_coupon_media WHERE  tree_id = :id ORDER BY fase DESC LIMIT 0,1 ";
        $data = array('id' => $treeId );
        $verify = $resource->fetchRow($sql , $data);
        $resource->closeConnection();

        return (is_numeric($verify['fase']))? $verify['fase'] : 0;
    }



    public function returnNewPhase($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT  IF(expire_date < DATE(NOW()) , fase + 1  , fase )  as new_fase FROM t_coupon_media WHERE  tree_id = :id  ORDER BY fase DESC, expire_date ASC LIMIT 0,1 ";
        $data = array('id' => $treeId );
        $verify = $resource->fetchRow($sql , $data);
        $resource->closeConnection();

        return (is_numeric($verify['new_fase']))? $verify['new_fase'] : 1;
    }


    /*
     * Generate  coupons for all 7  medias (Personally , Instagram Post , Instagram Direct, SMS , Facebook , Whatsapp , E-mail)
     * @params
     *
     */

    public function generateAllCouponsForMedia($tree){

        try{

            if(!empty($tree['customer_id']) && !empty($tree['id']) && !empty($tree['nome'])){
                $medias = array( 'sms' ,'email', 'personally','facebook','whatsapp','instagram-post','instagram-direct','instagram-post-bio');
                $fase =  $this->returnNewPhase($tree['id']);
                foreach( $medias  as $media){
                    Mage::getModel('tree/app')->createCouponMedia( $tree['customer_id'] ,  $tree['id'] ,$tree['instagram'] ,  $media , $fase );
                }
            }

        }catch (Exception $e){
            echo $e->getMessage();
        }



    }

    /*
    * Adiciona os distintos pedidos feito com cupons de midia (facebook , twitter ...) na tabela de t_coupon_media onde contabili -
    * zar� os pontos do usuario
    */

    public function fillsCouponMediaUsed($treeId){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sqlExist  = "select b.order_id FROM t_coupon_media_used b WHERE b.tree_id =  :treeid";
        $valuesExist  = $conn->fetchAll($sqlExist , array('treeid'=> $treeId));

        $v = "";
        $idExists = "";
        if(!empty($valuesExist)){
            foreach($valuesExist as $valueExist){
                if(!empty($valueExist['order_id'])) {
                    $idExists .= $v . $valueExist['order_id'];
                    $v = ",";
                }
            }
        }

        $sqlPartExists = "";
        if(!empty($idExists)){
            $sqlPartExists = "  order_id not in ($idExists) AND ";
            //$sqlPartExists = " o.entity_id not in ($idExists) AND ";
        }

        //, 'treeid' => $treeId

        // todos os cupons de m�dias que foram utilizados (que houve compra ) , menos os que j� estao cadastrados
        /*$sql      = "SELECT
                        o.entity_id  ,
                        o.customer_id ,
                        o.coupon_code ,
                        CONCAT( o.customer_firstname ,' ',o.customer_lastname) as  customer_name
                        FROM sales_flat_order o WHERE $sqlPartExists o.coupon_code in (select c.coupon from  t_coupon_media c WHERE c.tree_id = :id ) AND o.status in ('complete', 'processing' , 'delivered') ";
        $data     = array('id'=>$treeId );
        $values  =   $conn->fetchAll($sql,$data);*/

        $sql  = "SELECT order_id  , customer_id , customer_name , coupon  , link , coupon_media_id  FROM t_buy_link_promoscore  WHERE $sqlPartExists  tree_owner_id = :id AND is_approved_buy = 1 ";
        $data     = array('id'=>$treeId );
        $values  =   $conn->fetchAll($sql,$data);

        if(!empty($values)){
            $sqlInsert = "INSERT INTO `t_coupon_media_used`
                    (`coupon`,
                    `order_id`,
                    `customer_id`,
                    `customer_name`,
                    `tree_id` ,
                    `link`,
                    `coupon_media_id`) VALUES  ";
            $v = "";
            foreach($values as $value){
                $sqlInsert .= $v ."('".$value['coupon'] ."',". $value['order_id'] .",". $value['customer_id'].",'".addslashes($value['customer_name'])."' , $treeId , '".addslashes($value['link'])."' , ". $value['coupon_media_id']." )";
                $v  = ",";
            }
            $sqlInsert .= ";";

            $conn->query($sqlInsert);
        }

        $conn->closeConnection();

    }


    public function getMediaCouponId($fase , $media){
        $result = false;
        if(!empty($fase) && !empty($media)){
            $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql ="SELECT id FROM t_coupon_media WHERE fase = :fase  AND media = :media";
            $data     = array('fase'=>$fase ,
                              'media'=> $media);
            $values = $conn->fetchRow($sql,$data);
            $conn->closeConnection();
            $result =  (!empty($values['id']))? $values['id'] : false;
        }
        return $result;
    }

    // registrar vendas feitas pelo link
    public function registerLinkPromoscore($orderId , $treeOwnerId , $link   , $customerId , $customerName  , $instagramOwner  , $mediaId ,$coupon = null){

        if( !empty($orderId)  &&
            !empty($treeOwnerId) &&
            !empty($link)  &&
            !empty($customerId) &&
            !empty($customerName) &&
            !empty($instagramOwner) && !empty($mediaId)){


            $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql ="INSERT INTO t_buy_link_promoscore(`order_id`,
                                                     `tree_owner_id`,
                                                     `link`,
                                                     `customer_id`,
                                                     `customer_name` ,
                                                     `coupon`,
                                                     `instagram_owner` ,
                                                     `coupon_media_id` )
                                                      VALUES
                                                     (:id,
                                                      :owner_id,
                                                      :link,
                                                      :customerid ,
                                                      :customername ,
                                                      :coupon,
                                                      :instagram,
                                                      :media); ";


            $data     = array('id'=>$orderId ,
                              'owner_id'=> $treeOwnerId,
                              'link' => $link,
                              'customerid' => $customerId,
                              'customername' => $customerName,
                              'coupon' => $coupon,
                              'instagram' => $instagramOwner  ,
                              'media' => $mediaId );

            $conn->query($sql,$data);
            $conn->closeConnection();

        }
    }

    public function setLinkPromoscoreIsValid($orderId , $customerId){

        if(!empty($orderId)){
            if($this->isExistsLinkPromoscore($orderId)){
                $isUserAlreadyBoughtWithPromoscoreLink = $this->userAlreadyBoughtWithPromoscoreLink($orderId , $customerId);
                if(!$isUserAlreadyBoughtWithPromoscoreLink){
                    $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $sql ="UPDATE  t_buy_link_promoscore  SET is_approved_buy = :buy WHERE  order_id  = :id";
                    $data     = array('buy'=>1 ,
                        'id'=> $orderId);
                    $conn->query($sql,$data);
                    $conn->closeConnection();

                }
            }
        }
    }

    public function isExistsLinkPromoscore($orderId){
        $values = array();
        if(!empty($orderId)){

            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql    = "SELECT count(id) as total FROM t_buy_link_promoscore  WHERE  order_id  = :id";
            $data   = array('id' =>$orderId);
            $values = $conn->fetchRow($sql,$data);
            $conn->closeConnection();
        }

       return (!empty($values['total']))? true : false;
    }


    // usuario possui compra com cupom do promoscore
    public function userAlreadyBoughtWithPromoscoreLink($orderId , $customerId){

        $treeOwnerId = $this->getOwnerIdByOrderId($orderId);

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT count(id) as total FROM t_buy_link_promoscore  WHERE  tree_owner_id  = :id AND customer_id = :customer AND is_approved_buy = 1";
        $data   = array('id' =>$treeOwnerId , 'customer' => $customerId );
        $values = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return (!empty($values['total']))? true : false;

    }


    // usuario possui compra com cupom do promoscore
    public function getOwnerIdByOrderId($orderId){

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT * FROM t_buy_link_promoscore  WHERE  order_id  = :id ";
        $data   = array('id' =>$orderId );
        $values = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return (!empty($values['tree_owner_id']))? $values['tree_owner_id'] : false;

    }





    //public function


    /*public function registerCouponMediaUsed(){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $data = Mage::getModel('customer/checkout')->getData('bought_link_promoscore');

        // link
        // instagram do pai
        // tree_id
        //$treeId



        $sqlExist  = "select b.order_id FROM t_coupon_media_used b WHERE b.tree_id =  :treeid";
        $valuesExist  = $conn->fetchAll($sqlExist , array('treeid'=> $treeId));


        if(!empty($values)){
            $sqlInsert = "INSERT INTO `t_coupon_media_used`
                    (`coupon`,
                    `order_id`,
                    `customer_id`,
                    `customer_name`,
                    `tree_id`) VALUES  ";
            $v = "";
            foreach($values as $value){
                $sqlInsert .= $v ."('".$value['coupon_code'] ."',". $value['entity_id'] .",". $value['customer_id'].",'".addslashes($value['customer_name'])."' , $treeId)";
                $v  = ",";
            }
            $sqlInsert .= ";";


            $conn->query($sqlInsert);
        }

        $conn->closeConnection();

    }*/


    public function fillsScore($treeId){


        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sqlExist  = "select c.media_used_id from t_score c WHERE c.tree_id = :id ";
        $valuesExist  = $conn->fetchAll($sqlExist , array('id'=> $treeId));

        $v = "";
        $idExists = "";


        if(!empty($valuesExist)){
            foreach($valuesExist as $valueExist){
                if(!empty($valueExist['media_used_id'])){
                    $idExists.=$v.$valueExist['media_used_id'];
                    $v = ",";
                }
            }
        }

        $sqlPartExists = "";
        if(!empty($idExists)){
            $sqlPartExists = "u.id not in ($idExists) AND ";
        }

        // cadastra na tabela t_score todas vendas dos cupons de midias usados que nao estao cadastrados t_score
        $sql      = "SELECT u.id ,
                            u.customer_name ,
                            u.order_id ,
                            m.media FROM t_coupon_media_used u
                            INNER JOIN t_coupon_media m
                            ON u.coupon_media_id = m.id WHERE $sqlPartExists u.tree_id = :treeid ";

        $data     = array('treeid' => $treeId);
        $values  =   $conn->fetchAll($sql,$data);
        if(!empty($values)){

            $sqlInsert = "INSERT INTO  `t_score`(`media_used_id`,
                                                 `tree_id`,
                                                 `sphere_id`,
                                                 `customer_name`,
                                                 `score` ,
                                                 `media` ,
                                                 `city` ,
                                                 `purchase_date`)
                                                    VALUES";
            $v = "";

            $lastSphereId  =  $this->getLastSphere($treeId);
            // caso nao possua uma ultima esfera cria uma
            if(!$lastSphereId){
                $lastSphereId = $this->createNewSphere($treeId);
            }
            $totalLastSphere = $this->getTotalScoreSphere($lastSphereId);

            $score = 10;
            foreach($values as $value){
                $totalLastSphere += $score;
                // excedente cadastra em uma nova esfera
                if($totalLastSphere > 100){
                    // cria uma nova esfera
                    $lastSphereId    = $this->createNewSphere($treeId);
                    // reseta o valor do valor incrementado
                    $totalLastSphere = 10;
                }

                $city = "";
                $media= "";
                $purchaseDate ="";
                if(!empty($value['order_id'])){
                    $order = Mage::getModel('sales/order')->load($value['order_id']);
                    $purchaseDate = $order->getCreatedAt();
                    $customerId = $order->getCustomerId();
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $city = $customer->getPrimaryBillingAddress()->getCity();
                }
                if(!empty($value['media'])){
                    $media = $value['media'];
                }
                $sqlInsert .= $v ."('".$value['id'] ."',". $treeId .",".$lastSphereId.",'". addslashes($value['customer_name'])."','10' , '$media' , '$city' , '$purchaseDate')";

                $v  = ",";
            }

            $conn->query($sqlInsert);
        }

        $conn->closeConnection();

    }


    private $idsWinDiscount = array();

    public  function  loadScore($treeId){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT * FROM t_sphere s INNER JOIN t_score c ON  s.id = c.sphere_id WHERE c.is_used = 0 and c.tree_id = :tree ORDER BY s.created_at ASC , c.created_at ASC";
        $data   = array('tree' => $treeId );
        $values =   $conn->fetchAll($sql,$data);

        $rows = array();
        if(!empty($values)){
            $score = array();
            foreach($values as $value){
                $score[$value['sphere_id']] += $value['score'];
                $rowObj = null;
                $valuesRepetition  = 0;

                /*if($score > 100){
                    $score =  $score - $value['score'];
                    break;
                }*/

                $rowObj  = new ArrayObject($value);
                $valuesRepetition  = ((int)$value['score'])/10;
                if($valuesRepetition  > 1){
                    for($i = 1 ; $i <= $valuesRepetition ; $i++ ){
                        $copy = null;
                        $copy = $rowObj->getArrayCopy();
                        $copy['score'] = '10';
                        $rows[$copy['sphere_id']][] = $copy;
                    }
                }else{
                    $rows[$value['sphere_id']][] = $value;
                }

                $this->idsWinDiscount[$value['sphere_id']][] = $value['id'];



            }


            $this->treeScore = $rows;
            $this->score     = $score;
            $this->setLastAccess($treeId);

        }


        $conn->closeConnection();

    }


    public function getScore(){
        return $this->score;
    }


    public function getTreeScore(){
        return $this->treeScore;
    }


    public function getSpheresPlayers(){

        $spheres = $this->getTreeScore();
        $jsonPlayers = array();
        if(!empty($spheres)){
            $pos  = 1 ;
            $timeZone = "America/Recife";

            $dateTimeZone  = new DateTimeZone($timeZone);
            $dateLastAccessCompare =  new DateTime($this->dateLastAccess , $dateTimeZone);

            foreach($spheres as $sphereId => $values) {
                foreach ($values as $value) {

                    if(!isset($jsonPlayers[$sphereId])){
                        $pos = 1;
                    }

                    $new = 1;
                    $dateTimeCreatedScore = null;
                    $dateTimeCreatedScore = new DateTime($value['created_at'], $dateTimeZone);
                    if ($dateLastAccessCompare > $dateTimeCreatedScore) {
                        $new = 0;
                    }
                    $jsonPlayers[$sphereId][] = array("id" => $pos, "name" => $value['customer_name'], "news" => $new);
                    $pos++;

                }
            }
        }

        return $jsonPlayers;

    }


    public function isFirstDiscount(){
        $scoreEndValue = $this->getScoreEndValue();
        $isFirstDiscount = false;
        if(empty($this->distribuitionCoupons) &&  $scoreEndValue > 0){
            $isFirstDiscount = true;
        }
        return $isFirstDiscount;
    }


    public function getScoreEndValue(){
        $scoreEndValue = (!empty($this->score))? end($this->score) : 0;
        return $scoreEndValue;
    }


    public function setLastAccess($treeId){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT created_at FROM t_last_access_promoscore WHERE tree_id = :tree ORDER BY created_at DESC LIMIT 0,1 ";
        $data   = array('tree' => $treeId );
        $values =   $conn->fetchRow($sql,$data);

        $this->dateLastAccess  = (!empty($values['created_at']))?$values['created_at'] : '2017-11-20' ;

        $conn->closeConnection();
    }


    public function isUserAlreadyHaveLastAccess($treeId){

        $conn  = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT count(*) as total FROM t_last_access_promoscore WHERE tree_id = :tree ORDER BY created_at DESC LIMIT 0,1 ";
        $data   = array('tree' => $treeId );
        $values =   $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return  (!empty($values['total']))? $values['total'] : 0 ;

    }


    public function saveLastAccess($treeId){

        if(!empty($treeId)){

            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql    = "INSERT INTO `t_last_access_promoscore`(`tree_id`) VALUES (:tree)";
            $data   = array('tree' => $treeId );
            $conn->query($sql,$data);
            $conn->closeConnection();

        }

    }


    public function qtyTotalMedias($treeId){


        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT COUNT(*) as total FROM t_coupon_media_used  WHERE tree_id = :tree";
        $data   = array('tree' => $treeId );
        $values = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return  (!empty($values['total']))? $values['total'] : 0 ;

    }


    public function qtyDistributionCoupons($treeId){


        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql    = "SELECT count(c.media) as total ,c.media  FROM t_coupon_media c INNER JOIN t_coupon_media_used u ON c.coupon = u.coupon WHERE u.tree_id = :tree GROUP BY c.media";
        $data   = array('tree' => $treeId );
        $values = $conn->fetchAll($sql,$data);
        $conn->closeConnection();

        $totalMedias  = (int)$this->qtyTotalMedias($treeId);
        $jsonChannel  = array();
        if(!empty($values)){
            foreach($values as $value){
                $total = 0 ;
                $percentPorMedia = 0;

                if(strpos($value['media'], 'instagram-post') !== FALSE ) {
                    $value['media'] = 'instagram-post';
                }

                $total = (int)$value['total'];
                $percentPorMedia = ($total*100)/$totalMedias;

                if(isset($jsonChannel[$value['media']])){
                    list(,$totalOld) =  $jsonChannel[$value['media']];
                    $total += $totalOld  ;
                    $percentPorMedia = ($total*100)/$totalMedias;
                    $jsonChannel[$value['media']] =  array("$percentPorMedia", "$total");
                }else{
                    $jsonChannel[$value['media']] =  array("$percentPorMedia", "$total");
                }
            }

        }

        $this->distribuitionCoupons = $jsonChannel;
        return  json_encode($jsonChannel);
    }


    public function markUseSphere($sphereIds){

        if(!empty($sphereIds) && is_array($sphereIds)){

            $sphereIds = implode(',',$sphereIds);
            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_score SET is_used = 1 WHERE sphere_id in($sphereIds)";
            $conn->query($sql);
            $conn->closeConnection();

        }

    }


    public function markUseDiscount($sphereIds){

        if(!empty($sphereIds) && is_array($sphereIds)){
            $sphereIds = implode(',',$sphereIds);
            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_promo_for_you SET bought = 1 WHERE sphere_id in($sphereIds)";
            $conn->query($sql);
            $conn->closeConnection();

        }

    }


    public function getScoreBySphereId($sphereId){

        $result = false;
        if(isset($sphereId) && is_numeric($sphereId)){
            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "SELECT sum(score) as total FROM t_score WHERE sphere_id = :sphere AND is_used = 0";
            $rows  = $conn->fetchRow($sql , array('sphere' => $sphereId));
            $conn->closeConnection();

            $result =  (!empty($rows['total']))? $rows['total'] :  false;

        }

        return $result;

    }


    public function getCouponMedia($media, $instagram , $fase){


        $result = false;
        if(!empty($media) && !empty($instagram) && !empty($fase)){

            $tree = Mage::getModel('tree/tree')->getTreeByInsta($instagram);

            if(!empty($tree['id'])){

                $treeId = $tree['id'];

                $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sql  = "SELECT coupon FROM t_coupon_media WHERE media = :media AND tree_id = :tree ORDER BY fase DESC LIMIT 0,1";
                $rows = $conn->fetchRow($sql , array('media' => $media  , 'tree' => $treeId ));
                $conn->closeConnection();

                $result =  (!empty($rows['coupon']))? $rows['coupon'] :  false;
            }

        }

        return $result;

    }


    public  function dateExpiration($media , $instagram , $fase){

        $coupon  = $this->getCouponMedia($media , $instagram , $fase);
        $result = false ;
        if(!empty($coupon)){
            $result = Seaway_Tree_Model_Cupom::dateExpiration($coupon);
        }
        return $result;
    }


    public function  generateDiscount($tree , $sphereId){


        $result = false;
        if(!empty($tree['customer_id'])){

            $score = $this->getScoreBySphereId($sphereId);
            if(!empty($score)){

                $valueCoupon  = $score;
                if($valueCoupon == 100){
                    $valueCoupon = 85;
                }

                $isCreatedCoupon = $this->verifyDiscountSphere($sphereId,$tree['id']);
                if(empty($isCreatedCoupon)){

                    $code = Seaway_Tree_Model_Cupom::randomCode();
                    $code = Seaway_Tree_Model_Cupom::criarCupom($valueCoupon,$code,'','noexpire',1,1, "Seaway Promo Score" , "Seaway experience Promoscore %".$score);

                    $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');

                    $sql = "INSERT INTO  `t_promo_for_you`(`customer_id`,
                                                   `tree_id`,
                                                   `sphere_id`,
                                                   `coupon`,
                                                   `value`,
                                                   `virtual_value`)
                                             VALUES
                                                (:customer,
                                                 :treeid,
                                                 :sphere,
                                                 :coupon,
                                                 :value,
                                                 :virtual)";

                    $data  = array('customer' => $tree['customer_id'] , 'treeid' => $tree['id']  , 'sphere' => $sphereId  , 'coupon'=>$code , 'value' => $valueCoupon , 'virtual'=> $score);

                    if($conn->query($sql , $data)){
                        $result = $code;
                    }
                    $conn->closeConnection();

                }else{

                    $result = (!empty($isCreatedCoupon['coupon']))? $isCreatedCoupon['coupon'] : false ;
                }
            }

        }


        return $result;

    }


    public function verifyDiscountSphere($sphereId,$treeId){

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql  = "SELECT * FROM t_promo_for_you WHERE sphere_id =  :sphere AND  tree_id  = :tree";

        $data = array('sphere' => $sphereId , 'tree' => $treeId);
        $row = $conn->fetchRow($sql , $data);

        $conn->closeConnection();

        return (!empty($row))? $row : false;
    }


    public function usedSphere($treeId){

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT * FROM t_promo_for_you c
                              INNER JOIN sales_flat_order o
                              ON c.coupon = o.coupon_code AND c.bought = 0 WHERE c.tree_id = :id ";

        $data     = array( 'id' => $treeId );
        $values  =   $conn->fetchAll($sql,$data);

        $sphereIds = array();
        foreach($values as $value){
            $sphereIds[] = $value['sphere_id'];
        }
        $this->markUseSphere($sphereIds);
        $this->markUseDiscount($sphereIds);

        $conn->closeConnection();

    }


    public function createNewSphere($treeId){

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $order = $this->nextOrder($treeId);
        $sql = "INSERT INTO t_sphere(`tree_id` , `order` ) VALUES(:id,:order)";
        $conn->query($sql, array('id'=> $treeId , 'order' => $order));
        $conn->closeConnection();


        return $this->getLastSphere($treeId);

    }


    private function nextOrder($treeId){

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT (s.order + 1) next_order FROM t_sphere s WHERE s.tree_id = :tree ORDER BY s.order DESC LIMIT 0,1";
        $data = array('tree' => $treeId);
        $row = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return (!empty($row['next_order']))? $row['next_order'] : 1 ;


    }


    private function getLastSphere($treeId){

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT id FROM t_sphere WHERE tree_id = :tree ORDER BY id DESC LIMIT 0,1";
        $data = array('tree' => $treeId);
        $row = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return (!empty($row['id']))? $row['id'] : false;
    }


    private function getTotalScoreSphere($sphereId){

        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT sum(score) as total FROM t_sphere s INNER JOIN t_score c ON s.id = c.sphere_id WHERE s.id = :sphere ORDER BY s.created_at DESC LIMIT 0,1";
        $data = array('sphere' => $sphereId);
        $row = $conn->fetchRow($sql,$data);
        $conn->closeConnection();

        return (!empty($row['total']))? $row['total'] : 0;
    }


    public  function getCouponIsValidPromo($coupon){

        try {

            $this->checkStatusCouponPromo($coupon);

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_promo_for_you tc INNER JOIN t_tree t ON tc.tree_id = t.id WHERE tc.coupon=:cod LIMIT 0,1";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);
            return $row;

        }catch (Exception $e){
            //  echo 'getCouponIsValidTree : '.$e->getMessage();
            throw new Exception('getCouponIsValidPromo : '. $e->getMessage() , -3);
        }
    }


    public function checkStatusCouponPromo($couponCode){

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
            throw new Exception('checkStatusCouponPromo : '. $e->getMessage() , -3);
        }

    }


    public function sendPersonally($email , $instagram ){

        $subject = "Cupom Pro surf 30% Off";
        $link    = Mage::getBaseUrl('web').'pessoalmente/'.$instagram;
        $htmlContent  ="<a href='$link' target='_blank'>$link</a>";
        $this->sendMail($email , $subject , $htmlContent);
    }


    public function sendEmail($email , $instagram ){

        $subject = "Cupom Pro surf 30% Off";
        $link    = Mage::getBaseUrl('web').'email/'.$instagram;
        $htmlContent  ="<a href='$link' target='_blank'>$link</a>";
        $this->sendMail($email , $subject , $htmlContent);
    }


    public function sendMail($email , $subject , $htmlContent){



        $includePath = Mage::getBaseDir(). "/lib/swiftmailer/swift_required.php";
        require_once $includePath;



        $from = array('site@seaway.com.br' =>'Seaway');
        $to = array(
            $email  => '',
        );



        $text = "Seaway";
        $html = ' <body style="background-color: #F5F5F5;">

		<div align="center" valign="top" style="padding:20px 0;">
		<table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" style="max-width: 600px; border:1px solid #E0E0E0;">
		<thead>
		<tr style="max-width: height: 80px;
		background: #fafafa;">
		<td valign="top" style=" padding: 25px 0 8px 20px; border-bottom: 1px solid #DFDFDF;"><img src="http://seaway.com.br/skin/frontend/seaway/modern/images/logo-v9.png" style="margin-bottom:10px;" border="0"/></td>
		</tr>
		</thead>

		<td valign="top" style=" padding: 26px;">

        '.$htmlContent.'

		</td>
		</tr>





		<tfoot>

		<tr>
		<td  align="center";  text-align:center;" >



		<p style="font-size:12px; line-height:16px; margin:0 0 2px 0; color: #656565;">
		Atenciosamente,
		</p>

		<p style="font-size:12px; line-height:16px; margin:0 0 20px 0; color: #656565;">
		Equipe Seaway
		</p>

		</td>
		</tr>

		<tr>
		<td bgcolor="#EAEAEA" align="center" style="background:#f9f9f9; border-top:1px solid #E0E0E0; border-bottom:1px solid #E0E0E0;">
		<div style="width: 300px; color: #656565;">sac@seaway.com.br | (81) 3036-8836</div>
		<div style="margin: 15px 0 0 0; color: #656565; text-align: center;"><b>Hor�rio de atendimento:</b></div>
		<div style=" color: #656565; text-align: center;">Dias �teis, das 8:30h �s 12:30h e das 13:30h �s 17:00h</div>
		<div style="margin: 0 0 0 0; color: #656565; text-align: center;">Obs:A cidade de Recife n�o adere ao hor�rio de ver�o</div>
		</tr>

		<tr>
		<td  align="center";  text-align:center; style="padding: 15px 0;" >
		<p style="color: #656565; font-size:11px; margin: 0;">Copyright � 2013 Seaway Confec��es Ltda. Todos os Direitos Reservados.</p>
		<p style="color: #656565; font-size:11px; margin: 0;"> Rua Professor Aur�lio de Castro Cavalcanti, 211, Boa Viagem, Recife - PE - Brasil - Cep: 51.130-280</p>
		<p style="color: #656565; font-size:11px; margin: 0;">CNPJ: 09.026.659/0017-59</p>
		</td>

		</tr>
		</tfoot>

		</table>
		</div>
		</body>';




        $transport = Swift_SmtpTransport::newInstance('smtp.seaway.com.br', 587);
        $transport->setUsername('site@seaway.com.br');
        $transport->setPassword('seaway84');
        $swift = Swift_Mailer::newInstance($transport);

        $message = new Swift_Message($subject);
        $message->setFrom($from);
        $message->setBody($html, 'text/html');
        $message->setTo($to);
        $message->addPart($text, 'text/plain');

        // adiciona um coment�rio no hist�rico do pedido
        if ($swift->send($message, $failures)){
            return true;
        }


    }


    public  function registerSeeAdversing($treeId , $alreadySee = 0){


        $youSee = $this->youSeeAdversing($treeId,  $alreadySee);
        if(!$youSee){

            $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "INSERT INTO `t_promoscore_advertising`(`tree_id`,
                                                        `see`)
                                                    VALUES
                                                    (:tree,
                                                     :see)";

            $data = array('tree'=> $treeId , 'see'=> $alreadySee);
            $conn->query($sql,$data );


        }
    }


    public  function youSeeAdversing($treeId , $alreadySee = 0){

         // 0 nao viu ,  1  ja viu
        $conn   = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT * FROM `t_promoscore_advertising` WHERE tree_id = :id AND see = :see";

        $data = array('id'=> $treeId, 'see' => $alreadySee);
        $rows = $conn->fetchRow($sql , $data );
        // nao viu precisa ver
        return (isset($rows['see']) && $rows['see'] == 0)? true : false;

    }



    public  function setYouSeeAdversing($treeId ){


        $youSee = $this->youSeeAdversing($treeId,  0);
        if($youSee) {

            // 0 nao viu ,  1  ja viu
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE `t_promoscore_advertising` SET see = :see WHERE tree_id = :id ";

            $data = array('see' => 1, 'id' => $treeId);
            $conn->query($sql, $data);
        }
    }
}
