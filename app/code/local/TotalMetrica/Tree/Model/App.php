<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 28/07/2017
 * Time: 14:01
 */

class Seaway_Tree_Model_App{


    CONST CUSTOMER_SEAWAY = 13;
    CONST COUPON_PROMO_FOR_YOU  = 10;
    CONST APP_VALUE_DISCOUNT  = 76.92;

    public function verifyCouponApp($customerId ){


        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            $sql = "SELECT  a.id as id , rc.code  as code , IF( (now() > mt.to_date) , 0 , 1) as is_valid , mt.times_used , sfo.increment_id
                            FROM
							t_coupon_app a
                            INNER JOIN `salesrule_coupon` AS `rc` ON a.coupon = rc.code
                            INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            LEFT JOIN `sales_flat_order` sfo ON a.coupon = sfo.coupon_code AND ( sfo.status  = 'processing' or  sfo.status  = 'complete')

                            AND rc.is_primary = 1 WHERE a.customer_id  = :id and a.status = 1";

            $data = array('id' => $customerId);
            $values  = $resource->fetchAll($sql ,$data);

            $couponsUsed  ='';
            $couponsExpirados  ='';
            $usedV  = '';
            $usedE  = '';

            foreach ($values as $value ) {
                // coupons used
                if(!is_null($value['increment_id'])){
                    $couponsUsed .= $usedV.$value['id'];
                    $usedV  = ',';

                }
                // coupons expired
                if($value['is_valid'] == 0 && is_null($value['increment_id'])){
                    $couponsExpirados .= $usedE.$value['id'];
                    $usedE  = ',';
                }
            }


            $sqlUpdate = "";
            if(!empty($couponsUsed)){
                $sqlUpdate .= "UPDATE t_coupon_app SET status = 2 WHERE id  in ($couponsUsed);";

            }


            if(!empty($couponsExpirados)){
                $sqlUpdate .= "DELETE FROM  t_coupon_app WHERE id in ($couponsExpirados);";
            }


            if(!empty($sqlUpdate)){
                $resource->query($sqlUpdate);
            }


        }catch (Exception $e){


            //throw new Exception($e->getMessage() , -3);
            Mage::log($e->getMessage() , null , 'ModelAppverifyCouponApp.log' , true);
        }
    }



    public function getProgressBar($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT  credit  as progress  FROM t_sellwin WHERE customer_id  = :id  ";

        $data = array('id' => $customerId );
        $values = $resource->fetchAll($sql, $data);
        return $values;
    }


    public function updateSell($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sql = "SELECT  credit , progress_bar , id  FROM
							t_sellwin
                            WHERE customer_id  = :id ";

        $data = array('id' => $customerId );
        $bars = $resource->fetchAll($sql, $data);

        $totalCreditBar = 0;
        // verificar se tem barra
        $totalCredits = $this->getQtyCuponsUsed($customerId);

        if(empty($bars) && $totalCredits == 0){



            $tree = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);

            $sqlInsertCredit = "INSERT INTO `t_sellwin`
                            (`tree_id`,
                            `customer_id`,
                            `credit`,
                            `progress_bar`)
                            VALUES
                            (:tree,
                            :customer,
                            :credit,
                            :progress);";


            $data = array('tree' => $tree['id'], 'customer' => $customerId, 'credit' => 0,  'progress' => 1);
            $resource->query($sqlInsertCredit, $data);


            // se tem barra cadastrada
        }else{

            $this->deleteAllProgressBar($customerId);

            if(!empty($totalCredits)){

                $valuesRef = range(1, $totalCredits);
                $chunck = array_chunk($valuesRef , 5);

                foreach( $chunck as $k => $v) {
                    $progressBar =  0 ;
                    $progressBar =  $k + 1 ;

                    $creditsForBar = count($v);

                    $sql = "SELECT  count(*) as total, credit  FROM
							t_sellwin
                            WHERE customer_id  = :id and progress_bar = :progress";

                    $data = array('id' => $customerId , 'progress' => $progressBar );
                    $value = $resource->fetchRow($sql, $data);

                    if (empty($value['total'])) {

                        $tree = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);

                        $sqlInsertCredit = "INSERT INTO `t_sellwin`
                            (`tree_id`,
                            `customer_id`,
                            `credit`,
                            `progress_bar`)
                            VALUES
                            (:tree,
                            :customer,
                            :credit,
                            :progress);";
                        $data = array('tree' => $tree['id'], 'customer' => $customerId, 'credit' => $creditsForBar,  'progress' => $progressBar);

                        $resource->query($sqlInsertCredit, $data);

                    }

                }

            }else{

                $tree = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);

                $sqlInsertCredit = "INSERT INTO `t_sellwin`
                            (`tree_id`,
                            `customer_id`,
                            `credit`,
                            `progress_bar`)
                            VALUES
                            (:tree,
                            :customer,
                            :credit,
                            :progress);";


                $data = array('tree' => $tree['id'], 'customer' => $customerId, 'credit' => 0,  'progress' => 1);
                $resource->query($sqlInsertCredit, $data);

            }

        }

    }


    public function deleteAllProgressBar($customerId ){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sqlDeleteCredit = "DELETE  FROM `t_sellwin` WHERE customer_id = :customer";
        $data = array('customer' => $customerId);
        $resource->query($sqlDeleteCredit, $data);


    }


    public function generateSellWin($customerId , $qtyCouponUsed){


        try{
            $tree =  Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
            $discountVirtual  = $qtyCouponUsed * 20;

            $discountReal = 0 ;
            switch($discountVirtual){
              //  case 80  : $discountReal = 80 ;break;
                case 100 : $discountReal = self::APP_VALUE_DISCOUNT ;break;
                default  : $discountReal = $discountVirtual;break;

            }

            $code = $this->createCouponNameApp($tree['instagram'],'swin');
            $coupon = Seaway_Tree_Model_Cupom::criarCupom($discountReal, $code);

            if(!empty($coupon)){

                $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sqlCouponBar = "INSERT INTO `t_coupon_bar`(`coupon`,`value`,`customer_id`,`tree_id`) VALUES (:coupon,:value,:customer,:tree)";
                $dataBar = array('coupon'=>  $coupon , 'value' => $discountVirtual  , 'customer'=>  $customerId , 'tree' => $tree['id']);
                $resource->query($sqlCouponBar , $dataBar);


                $sqlCouponUp = "SELECT  * FROM `t_coupon_app` WHERE customer_id = :customer AND status = 2 AND status_used = 0 LIMIT 0,$qtyCouponUsed";
                $data = array('customer'=>  $customerId );
                $all = $resource->fetchAll($sqlCouponUp , $data);


                $updated = "";
                $v= "";
                $i = 1;
                foreach( $all as $dest){

                    $updated .=  $v .$dest['id'];
                    $v = ',';

                    if($i == $qtyCouponUsed)
                        break;

                    $i++;
                }

                if(!empty($updated )){

                    $sqlUp  = "UPDATE t_coupon_app SET  status_used = 1 WHERE id  in ($updated)";
                    $resource->query($sqlUp );

                    $this->deleteAllProgressBar($customerId);

                }


            }


             $link = "";

            if($discountVirtual != 100 ){
                $link = Mage::getBaseUrl().'ds?c='.$coupon;
            }else{
                    $link = Mage::getBaseUrl().'fp?c='.$coupon;
            }

        //



            return $link;

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
            Mage::log($e->getMessage() , null , 'generateSellWin.log' , true);

        }


    }



    public function getQtyCuponsUsed($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sqlCredits = "SELECT  count(id) as credits FROM t_coupon_app WHERE status = 2 AND customer_id = :customer AND status_used = 0";
        $data = array('customer'=>$customerId);
        $rowCredits = $resource->fetchRow($sqlCredits,$data);

        return  (!empty($rowCredits['credits']))? $rowCredits['credits'] : 0 ;

    }

    public function createCouponMedia( $customerId ,  $treeId , $instagram ,  $media , $fase = 1){


        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            if(empty($treeId)){
                throw new Exception("User not tree." , -3 );
            }

            $isCouponExists =  $this->isCouponCodeExistsForMedia($media , $treeId , $fase);
            if(!$isCouponExists){
                $discount = 30;

                $validDate = 'noexpire';
                // usuarios por cupom -> ilimitado
                $usesPerCoupon  = null;
                $customerPerCoupon = null;

                $prefixo  = "";
                switch($media){
                    case 'sms'               :  $prefixo  = "sms-"; break;
                    case 'email'             :  $prefixo  = "email-"; break;
                    case 'personally'        :  $prefixo  = "person-";break;
                    case 'facebook'          :  $prefixo  = "face-";break;
                    case 'whatsapp'          :  $prefixo  = "whats-";break;
                    case 'instagram-direct'  :  $prefixo  = "instad-";break;
                    case 'instagram-post'    :  $prefixo  = "instap-";break;
                    case 'instagram-post-bio':  $prefixo  = "";break;

                }

                $instagram = $prefixo.$instagram;
                $code = $this->createCouponNameInsta( $instagram , "" );
                $code = Seaway_Tree_Model_Cupom::criarCupom($discount , $code,'60',null , $usesPerCoupon , $customerPerCoupon );
                $dateExpire = Seaway_Tree_Model_Cupom::dateExpiration($code , 'en');


                $sqlInsert = "INSERT INTO t_coupon_media(customer_id , tree_id , coupon , `value` , media , fase , expire_date) VALUES(:customer , :tree , :coupon , :value ,:media , :fase , :expire)";
                $data = array('customer' => $customerId , 'tree' => $treeId , 'coupon' => $code , 'value' => $discount , 'media' =>$media  , 'fase' => $fase , 'expire' => $dateExpire);
                if(!$resource->query($sqlInsert ,$data)){
                    throw new Exception("Error inserting coupon." , -3 );
                }
            }
            //ds?c=
            /* $name  = $row['nome'];
             $link  = Mage::getBaseUrl().$prefixo.'/'.$code;*/
            //$resource->closeConnection();
            //$this->sendmail($email , $name  , $link);
            //  return $link;

        }catch(Exception $e){

            $resource->closeConnection();
            $message = $e->getMessage();
            throw new Exception($message , -3 );

        }

    }

    public function isCouponCodeExistsForMedia($media , $treeId , $fase ){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT count(id) as total FROM t_coupon_media WHERE media = :media AND  tree_id = :id AND fase = :fase";
        $data = array('media'=>$media , 'id' => $treeId , 'fase' => $fase );
        $verify = $resource->fetchRow($sql , $data);
        $resource->closeConnection();

        return (isset($verify) &&  $verify['total'] > 0)? true : false;
    }


    private function sendmail( $email , $name  , $link  ){

        $emailTemplate = Mage::getModel('core/email_template');
        $emailTemplate->loadByCode('s_c_i sell win');

        $emailTemplateVariables = array(
            'name' => $name,
            'link' => $link
        );
        $name = '';
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $mail = Mage::getModel('core/email')
            ->setToName($name)
            ->setToEmail($email)
            ->setFromEmail(Mage::getStoreConfig('trans_email/ident_sales/email'))
            ->setFromName(Mage::getStoreConfig('trans_email/ident_sales/name'))
            ->setBody($processedTemplate)
            ->setSubject($emailTemplate->getTemplateSubject())
            ->setType('html');

        $return  = false;
        try {
            if($mail->send()){
                $return  = true;
            }
        }catch (Exception $error) {
            throw new Exception($error->getMessage() , -2);
        }

        return $return;

    }

    public function createCouponApp( $customerId, $media){


        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "SELECT * FROM t_tree WHERE customer_id = :id order by id asc limit 0,1";
            $data = array('id' => $customerId);
            $row = $resource->fetchRow($sql ,$data);

            if(empty($row["id"])){
                throw new Exception("User not tree." , -3 );
            }

            $isCouponExists =  $this->isCouponCodeExistsForMedia($media , $row['id'] );
            if($isCouponExists){
                throw new Exception("The coupon for a media has already been generated." , -3 );
            }

            $discount = 30;

            $nome = $row['nome'];

            $validDate = '2017-12-31';
            // usuarios por cupom -> ilimitado
            $usesPerCoupon  = null;
            $code = $this->createCouponNameApp( $nome , "" );

            $code = Seaway_Tree_Model_Cupom::criarCupom($discount,$code,'16',$validDate , $usesPerCoupon);

            $sqlInsert = "INSERT INTO t_coupon_app(customer_id , tree_id , coupon , `value` , media) VALUES(:customer , :tree , :coupon , :value ,:media)";
            $data = array('customer' => $customerId , 'tree' => $row['id'] , 'coupon' => $code , 'value' => $discount , 'media' =>$media );
            if(!$resource->query($sqlInsert ,$data)){
                throw new Exception("Error inserting coupon." , -3 );
            }

            $prefixo  = "";
            switch($media){
                case 'twitter' :   $prefixo  = "pt";break;
                case 'instagram' : $prefixo  = "pi";break;
                case 'facebook' :  $prefixo  = "pf";break;
                case 'sms' :       $prefixo  = "ps";break;
                case 'email' :     $prefixo  = "pe";break;
            }
            //ds?c=
            $name  = $row['nome'];
            $link  = Mage::getBaseUrl().$prefixo.'/'.$code;

            $resource->closeConnection();
            //$this->sendmail($email , $name  , $link);
            return $link;

        }catch(Exception $e){

            $resource->closeConnection();
            $message = $e->getMessage();
            throw new Exception($message , -3 );

        }





    }





    public function getCouponAppInfo( $customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT  email, DATE_FORMAT(created_at , '%m/%d/%Y') as `date` , status,position FROM t_coupon_app WHERE customer_id = :id  ORDER BY position ASC";
        $data = array('id' => $customerId);
        $row = $resource->fetchAll($sql ,$data);

        $result = array();
        foreach($row as $pos){
            $result[$pos['position']] = $pos;
        }

        return $result;
    }


    public function verifyDiscountParticipant($treeId , $discount , $limit ){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT COUNT(id) as total FROM t_coupon_app WHERE tree_id = :tree AND value = :discount";
        $data = array( 'tree' => $treeId  , 'discount' => $discount );
        $row = $resource->fetchRow($sql ,$data);

        return (isset($row['total']) && ( $limit > $row['total']))? true : false;

    }





    public function instagramIsExist($insta ){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT count(*) as total FROM t_tree t WHERE REPLACE(trim(t.instagram), '@', '') = REPLACE(trim(:insta), '@', '') ";
        $data = array('insta' => $insta);
        $count = $resource->fetchRow($sql, $data);

        return (!empty($count['total']) && $count['total'] > 0) ? false : true;

    }





    public function getListFriendIndicated($cid = null){

        $rows = array();
        $objCustomerLogin = Mage::getSingleton('customer/session');
        if($objCustomerLogin->isLoggedIn()) {
            $customer = $objCustomerLogin->getCustomer();
            $customerId = $customer->getEntityId();
            if(!empty($cid) && self::CUSTOMER_SEAWAY == $customerId)
                $customerId = $cid;

            $treeUser = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
            if(!empty($treeUser['id'])){

                $parentId = $treeUser['id'];
                $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sql = "SELECT  instagram, cretated_at FROM t_tree WHERE parent_id = :id AND  status_app  is not null AND instagram_list > 0 AND new_list_app = 1  ORDER BY id ASC";
                $data = array('id' => $parentId);
                $rows = $resource->fetchAll($sql, $data);

            }

        }

        return $rows ;
    }



    public  function createCouponNameApp( $nome , $type = "" ){



        $firstName  = trim($nome);
        $firstName  = explode(' ', $firstName);
        $firstName  = current($firstName);

        $i  = 1;
        do{

            $isCupomValid = true;
            $code = $type.$firstName.$i;

            $isEmpty = Seaway_Tree_Model_Cupom::getSalesRuleCouponApp($code);

            if(empty($isEmpty)){
                $isCupomValid = false;
            }

            $i++;
        }while($isCupomValid);


        return $code;


    }


    public  function createCouponNameInsta( $instagram , $type = "" ){


        $i  = 1;
        do{

            $isCupomValid = true;
            $number  = $i;

            $code = $type.$instagram.$number;

            $isEmpty = $this->getSalesRuleCouponApp($code);

            if(empty($isEmpty)){
                $isCupomValid = false;
            }

            $i++;
        }while($isCupomValid);


        return $code;

    }


    public  function getSalesRuleCouponApp($code){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT * FROM salesrule_coupon  WHERE code like '$code' LIMIT 0,1";
        $row = $_conn->fetchRow($sql);

        return $row;

    }


    public function getTreeList($cid = null){

        /* fun��o ir� retornar lista com filhos de um pai da arvore
         * pegar customer id da sessao , usuario logado ok
         * pegar todos filhos da arvore desse usuario   ok
         * criar coluna status_app no banco
         * coluna status_app com os status (available, unavailable, choose)
         */

        $objCustomerLogin = Mage::getSingleton('customer/session');
        if($objCustomerLogin->isLoggedIn()){

            $customer = $objCustomerLogin->getCustomer();
            $customerId = $customer->getEntityId();

            if($customerId == self::CUSTOMER_SEAWAY && is_numeric($cid)){
                $customerId = $cid;
            }

            $nodeTree = $this->getNodeTree($customerId);

            if(empty($nodeTree))
                throw new Exception('Node not exist.',-3);


            $list = $this->getChildrensParent($nodeTree['id'] );


            $tree = array();
            $tree['list']		            = $list;
            $tree['suggested_children']		= $this->getListFriendIndicated($customerId);
            $tree['indicated_list']	   		= $this->getListFriendConfirmed($customerId);
            $tree['indicated_blocked_list']	= $this->getListFriendBlocked($customerId);


            $qtdIndicate  =   count($tree['suggested_children']);

            $valuesTree = Mage::getModel('tree/deadline')->getTreeValue();

            $isValidInvitedApp = Mage::getModel('tree/deadline')->verifyDateInvitedApp($valuesTree);
            $tree['expirate_date_page'] 	= $valuesTree['date_invited_app'];
            $tree['is_expirate_date_page']	= $isValidInvitedApp;
            $tree['qty_indicate'] 			= abs($valuesTree['qty_indication'] - $qtdIndicate);

           // $firstStep  = 0;
           // if(!empty($valuesTree['ex_completed'])){
                //$firstStep  =  $valuesTree['ex_completed'];
            //}

            //$tree['finish_first_step'] =  $firstStep;

            $tree['instagram_father'] =  $nodeTree['instagram'];



            return $tree;

        }

    }






    private function getChildrensParent($id ){

        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            $sql = "SELECT id,nome,status_app,obs_app,instagram,instagram_list as type ,status_app_action  FROM t_tree WHERE parent_id = :id AND  status_app  is not null AND instagram_list > 0 and new_list_app = 0 ORDER BY status_app DESC , cretated_at DESC";
            $data = array( 'id' => $id );
            $return = $resource->fetchAll($sql,$data);

            $result = array();
            foreach  ( $return as $value ) {
                $value['name']   =  $value['instagram'];
                $value['status'] =  $this->getStatusApp($value['status_app']);
                $value['message']=  (is_null($value['obs_app']))? "" : $value['obs_app'];
                $value['history']=  json_decode($value['status_app_action'], true);
                $value['img'] = Mage::getBaseUrl('skin').'adminhtml/default/default/images/experience_insta/'. $value['instagram'].'_insta_ind.jpg';


                unset($value['status_app_action']);
                //unset($value['status_app']);
                unset($value['obs_app']);
                unset($value['nome']);
                $result[] = $value;
            }

            return $result;

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }

    }



    public function setLimitWhenApproved($id){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sqlAproved = "SELECT parent_id FROM t_tree WHERE id = :id";
        $data  = array('id' => $id);
        $rows  = $resource->fetchRow($sqlAproved, $data);

        if(!empty($rows['parent_id']) ){

            $parentId = $rows['parent_id'];
            $sql = "UPDATE t_tree SET qtd =  (qtd+1) WHERE id= $parentId ";
            $resource->query($sql);

            $sql = "SELECT customer_id FROM  t_tree WHERE  id =  $parentId ";
            $values  = $resource->fetchRow($sql);

            if(!empty($values['customer_id'])){
                $this->saveChoose($id , true , $values['customer_id'] );
            }
        }

    }


    public function getListFriendConfirmed($cid = null){


        $objCustomerLogin = Mage::getSingleton('customer/session');
        $result = array();
        if($objCustomerLogin->isLoggedIn()) {
            $customer = $objCustomerLogin->getCustomer();
            $customerId = $customer->getEntityId();
            if(!empty($cid) && self::CUSTOMER_SEAWAY == $customerId)
                $customerId = $cid;


            $treeUser = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
            if(!empty($treeUser['id'])){

                $parentId = $treeUser['id'];
                $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sql = "SELECT  id,nome,status_app,obs_app,instagram,instagram_list as type ,status_app_action  FROM t_tree WHERE parent_id = :id AND  status_app  is not null AND instagram_list > 0 AND new_list_app = 2  ORDER BY id ASC";
                $data = array('id' => $parentId);
                $row = $resource->fetchAll($sql, $data);


                foreach  ( $row as $value ) {
                    $value['name']   =  $value['instagram'];
                    $value['status'] =  $this->getStatusApp($value['status_app']);
                    $value['message']=  (is_null($value['obs_app']))? "" : $value['obs_app'];
                    $value['history']=  json_decode($value['status_app_action'], true);
                    $value['img'] = Mage::getBaseUrl('skin').'adminhtml/default/default/images/experience_insta/'. $value['instagram'].'_insta_ind.jpg';



                    unset($value['status_app_action']);
                    //unset($value['status_app']);
                    unset($value['obs_app']);
                    unset($value['nome']);
                    $result[] = $value;
                }

            }

        }

        return $result;
    }




    public function saveMessageContact($customerId , $message){

        try{


            $treeUser = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
            if(empty($treeUser)){
                throw new Exception('User not exist in Tree');
            }


            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "INSERT INTO t_contact(tree_id ,  msg ) VALUES (:id , :msg)";
            $data = array('id' => $treeUser['id'] , 'msg' => $message);
            $resource->query($sql,$data );

         }catch (Exception $e){

            Mage::log($e->getMessage() , null , 'ContactApp.log' , true);

        }


        return true;

    }


    public function getListFriendBlocked($cid){


        $objCustomerLogin = Mage::getSingleton('customer/session');
        $result = array();
        if($objCustomerLogin->isLoggedIn()) {
            $customer = $objCustomerLogin->getCustomer();
            $customerId = $customer->getEntityId();

            if(!empty($cid) && self::CUSTOMER_SEAWAY == $customerId)
                $customerId = $cid;

            $treeUser = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
            if(!empty($treeUser['id'])){

                $parentId = $treeUser['id'];
                $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                //$sql = "SELECT  id,nome,status_app,obs_app,instagram,instagram_list as type ,status_app_action  FROM t_tree WHERE parent_id = :id AND  status_app  is not null AND instagram_list > 0 AND new_list_app = 1 AND instagram_status = 1  ORDER BY id ASC";
                $sql = "SELECT  id,nome,status_app,obs_app,instagram,instagram_list as type ,status_app_action  FROM t_tree WHERE parent_id = :id AND  status_app  is not null AND instagram_list > 0 AND new_list_app = 3  ORDER BY id ASC";
                $data = array('id' => $parentId);
                $row = $resource->fetchAll($sql, $data);


                foreach  ( $row as $value ) {

                    $value['name']   =  $value['instagram'];
                    $value['status'] =  $this->getStatusApp($value['status_app']);
                    $value['message']=  (is_null($value['obs_app']))? "" : $value['obs_app'];
                    $value['history']=  json_decode($value['status_app_action'], true);



                    unset($value['status_app_action']);
                    //unset($value['status_app']);
                    unset($value['obs_app']);
                    unset($value['nome']);
                    $result[] = $value;
                }

            }

        }

        return $result;
    }



    private function getNodeTree($id){

        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            //$nodeLimit = "(u.qtd  - (SELECT count(s.id) FROM t_tree s WHERE s.parent_id = u.id AND s.status_app IN(2,4) )) as node_limit";
            //$nodeLimitSys = " (u.qtd  - (SELECT count(s.id) FROM t_tree s WHERE s.parent_id = u.id AND s.status_app IN(4) ))  as node_limit_sys";

            $sql = "SELECT u.* FROM t_tree u WHERE u.customer_id = :id";
            $data = array( 'id' => $id );
            $return = $resource->fetchRow($sql,$data);

            return $return;

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }
    }



    public function saveMessage($id , $msg){

        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            $sql = "UPDATE t_tree SET obs_app = :mesg WHERE id  = :id ";
            $data = array( 'id' => $id  , 'mesg' => $msg);
            $resource->query($sql,$data);

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }
    }

    public function saveCopyInviteOrIndicateLink($instagram, $target){

        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            $column = '';

            if(strcasecmp($target, 'invite') == 0){
                $column = 'copy_invite_link';
            }

            if(strcasecmp($target, 'indicate') == 0){
                $column = 'copy_indicate_link';
            }

            if(empty($column)){
                throw new Exception("column is empty.", -1);
            }

            $sql = "UPDATE t_tree SET $column = :copy WHERE instagram  = :instagram ";
            $data = array( 'instagram' => $instagram, 'copy' => true);
            $resource->query($sql,$data);

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }
    }

    private function getStatusApp($status){

        $result = null;
        switch($status) {
            case 1 : $result = 'available';break;
            case 2 : $result = 'choose'; break;
            case 3 : $result = 'unavailable' ; break;
            case 4 : $result = 'contacted' ; break;
        }
        return $result;
    }


    private function setAvailableUnavailableChilds($id , $setAvaliable = true){

        try{
            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');


            $whereSearch = "status_app  = 3" ;
            $setStatusApp = "status_app  = 1";

            if(!$setAvaliable){
                $whereSearch = "status_app  = 1" ;
                $setStatusApp = "status_app  = 3";
            }

            $sqlParent =  "SELECT s.id FROM t_tree s WHERE s.parent_id = :id AND $whereSearch AND (new_list_app = 2 OR  new_list_app = 0 OR new_list_app is null ) ";
            $data = array('id' => $id);
            $valores = $resource->fetchAll($sqlParent,$data);
            if(!empty($valores)){
                $ids  =  "";
                $v = "";
                foreach($valores as $valor){
                    $ids .= $v.$valor['id'];
                    $v = ",";
                }


                if(!empty($ids) ){

                    $sql = "UPDATE  t_tree  SET $setStatusApp   WHERE  id IN ($ids)  ";
                    $data = array('id' => $id);
                    $resource->query($sql, $data);
                }

            }

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }


    }



    private function setListB($id){

        try{
            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');


            $sqlParent =  "SELECT s.id FROM t_tree s WHERE s.parent_id = :id AND  status_app  = 3";
            $data = array('id' => $id);
            $valores = $resource->fetchAll($sqlParent,$data);
            if(!empty($valores)){
                $ids  =  "";
                $v = "";
                foreach($valores as $valor){
                    $ids .= $v.$valor['id'];
                    $v = ",";
                }


                if(!empty($ids) ){

                    $sql = "UPDATE  t_tree  SET  status = 2 WHERE  id IN ($ids)  ";
                    $data = array('id' => $id);
                    $resource->query($sql, $data);
                }

            }

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }


    }


    public function setStatusFollowingSeaway($id){

        $status = "FOLLOWING SEAWAY EXP.";
        $this->setStatusAppAction( $status ,$id );

    }


    public function setStatusNotFollowedSeaway($id){

        $status = "IS NOT FOLLOWING @SEAWAYEXPERIENCE.USA YET.";
        $this->setStatusAppAction( $status ,$id );

    }



    public function setStatusLinkSend($id){

        $status = "LINK<br/>SENT";
        $this->setStatusAppAction( $status ,$id );

    }



    public function setStatusMessageRead($id){

        $status = "MESSAGE<br/>WAS READ";
        $this->setStatusAppAction( $status ,$id );

    }


    public function setStatusChosen($id){

        $status = "CHOSEN";
        $this->setStatusAppAction( $status ,$id );

    }

    public function deleteStatusChosen($id){

        $status = "CHOSEN";
        $this->setStatusAppAction( $status , $id , false , true);
    }



    public function setStatusChoose($id){

        $status = "CHOOSE THE<br/>BOARDSHORT";
        $this->setStatusAppAction( $status ,$id );

    }


    public function setStatusWon($id){

        $status = "WON THE<br/>BOARDSHORT";
        $this->setStatusAppAction( $status ,$id );

    }


    public function statusAppIsExists( $status , $treeId ){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sqlExists = "SELECT count(id) as total FROM t_history_app WHERE status = :status AND tree_id = :tree ";
        $dataExists = array('status'=>$status , 'tree'=>$treeId );
        $fetchExistis = $resource->fetchRow($sqlExists,$dataExists);

        return  (!empty($fetchExistis['total']) && $fetchExistis['total'] > 0 ) ? true : false;

    }

    public function getTreeIdByCustomer($id){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $dataCustomer = array('customer' => $id);
        $sqlGetId = "SELECT id FROM  t_tree WHERE  customer_id = :customer";
        $fetchCustomer = $resource->fetchRow($sqlGetId,$dataCustomer);
        $treeId = false;
        if(!empty($fetchCustomer)){
            $treeId = $fetchCustomer['id'];
        }

        return $treeId;

    }


    public function getHistoryApp($id){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $data = array('tree' => $id);
        $sql  = "SELECT status , CONCAT( LPAD(MONTH(created_at), 2 , '0') , '/' , LPAD(DAY(created_at) , 2 , '0') , '/' , SUBSTRING(YEAR(created_at) , 3 , 2))  as `date` FROM  t_history_app WHERE tree_id = :tree";
        $all = $resource->fetchAll($sql,$data);


        return $all;

    }


    public function updateHistoryTree($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $history = $this->getHistoryApp($treeId);




        $json  = json_encode($history);
        $sql = "UPDATE t_tree SET status_app_action = :status WHERE  id = :tree";
        $data = array('status' => $json , 'tree' => $treeId);

        $resource->query($sql,$data);

     }



    private function setStatusAppAction( $status , $id , $customer = false, $delete = false ){


        try{

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $treeId = $id;
            if($customer){
                $treeId = $this->getTreeIdByCustomer($id);
            }

            $isExists  = $this->statusAppIsExists($status,$treeId);
            $data = array('tree'=>$treeId ,'status'=>$status);


            if(!$delete){

                if(!$isExists){

                    $sql  = "INSERT INTO t_history_app( status , tree_id ) VALUES ( :status , :tree)";
                    $resource->query($sql,$data);

                }else{

                    $sql  = "UPDATE  t_history_app SET  status = :status  WHERE tree_id = :tree ";
                    $resource->query($sql,$data);

                }


            }else if($isExists && $delete){

                $sql  = "DELETE FROM t_history_app WHERE status =  :status AND tree_id = :tree ";
                $resource->query($sql,$data);

            }


            $this->updateHistoryTree($treeId);


        }catch(Exception $e){
            echo $e->getMessage();
            throw new Exception($e->getMessage() , -3);
        }

    }



    public function setWinBs($treeId){
        try {


            if(!is_numeric($treeId)){
                throw new Exception('TreeId is not a number.' , -3);
            }

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_tree SET win_bs = :wbs WHERE id = :tree ";
            $data = array('wbs' => 1 , 'tree' => $treeId);

            $resource->query($sql , $data);


        }catch(Exception $e){
            throw new Exception( 'setWinBs - '.$e->getMessage() , -3);
        }

    }



    public function changeCurrentStep($currentStep , $treeId){
        try {

            if(!is_numeric($currentStep)) {
                throw new Exception('Value not is a number.' , -3);
            }

            $valuesAlowed = range(1,4);
            if(!in_array($currentStep , $valuesAlowed)) {
                throw new Exception('Value isn\'t alowed.' , -3);
            }

            if(!is_numeric($treeId)){
                throw new Exception('TreeId is not a number.' , -3);
            }

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_tree SET current_step = :step WHERE id = :tree ";
            $data = array('step' => $currentStep , 'tree' => $treeId);

            if(!$resource->query($sql , $data)){
                throw new Exception('Error update current step.' , -4);
            }

            //$this->updateMenu($currentStep , $treeId);

        }catch(Exception $e){
            throw new Exception( 'changeCurrentStep - '.$e->getMessage() , -3);
        }

    }


    /*
   * Função que salva a informação de que imagem para convidar
   * 1 -  img salva foi seaway quem gerou
   * 2 -  img salva foi ele quem editou e gerou
    */
    public function saveImageInvite($instagram , $saveas){

        try{

            if(empty($instagram)){
                throw new Exception('Instagram  is false!' , -2);
            }

            $tree  = Mage::getModel('tree/tree')->getTreeByInsta($instagram);
            if(empty($tree['id'])){
                throw new Exception('Instagram  is false!' , -2);
            }
            $id = $tree['id'];

            if(empty($saveas)){
                throw new Exception('Save as  is empty!' , -2);
            }


            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE  t_tree  SET saveas = :as   WHERE  id = :id";
            $data = array( 'as' => $saveas , 'id' => $id  );
            $resource->query($sql, $data);

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }


    }






    public function saveChoose($id , $marked = true , $customerId = false){

        try{

            $objCustomerLogin = Mage::getSingleton('customer/session');
            if($objCustomerLogin->isLoggedIn() && !$customerId) {
                $customer = $objCustomerLogin->getCustomer();
                $customerId = $customer->getEntityId();
            }


            if(!$customerId){
                throw new Exception('Customer id is false!' , -2);
            }


            $treeNode = $this->getNodeTree($customerId);

            if (empty($treeNode))
                throw new Exception('Node not exist.', -3);

            /*if($treeNode['win_bs'] == 1){
                throw new Exception('User already win boardshort, It can not be unmarked.' , -3);
            }*/

            $isCustomerGift =  $this->isCustomerGift($id);
            if($isCustomerGift){
                throw new Exception('User already win boardshort, It can not be unmarked.' , -3);
            }

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            $sqlStatus      = "status_app = 1";
            $currentStep    = 4;

            if ($marked) {
                $sqlStatus      = "status_app = 2";
                $currentStep    = 1;
            }

            $sql = "UPDATE  t_tree  SET $sqlStatus   WHERE  id = :id";

            $data = array('id' => $id);
            $value = $resource->query($sql, $data);
            $this->changeCurrentStep( $currentStep , $id);
            $acaoId = Mage::getModel('tree/tree')->getActionIdByStatusApp($id ,'chosen');

            try {
                if ($acaoId && $marked) {

                    Mage::getModel('tree/tree')->cadastrarAcao($id, $acaoId);

                    //$qtd = $treeNode['node_limit'];
                    //if($qtd == 1 && $value ){
                       // $this->setAvailableUnavailableChilds($treeNode['id'],false);
                    //}


                }else if($acaoId && !$marked){

                    $treeActionId = Mage::getModel('tree/tree')->getTreeActionId($id, $acaoId);
                    if($treeActionId){
                        Mage::getModel('tree/tree')->removerAcao($treeActionId);
                        $this->deleteStatusChosen($id);
                        //$this->setAvailableUnavailableChilds($treeNode['id']);
                    }
                }
            }catch(Exception $e){
                throw new Exception($e->getMessage() , -3);
            }

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }


    }


    public function isFreepaymentParticipant(){

        $couponSession = Mage::getModel('customer/session')->getData('coupon');
        $returnValue = false;
        if(!empty($couponSession)){
            if(!empty($couponSession['discount']) && $couponSession['discount'] == 100){
                $returnValue = true;
            }
        }

        return $returnValue;
    }





    public function saveContacted($id){

        try{

            $objCustomerLogin = Mage::getSingleton('customer/session');
            if($objCustomerLogin->isLoggedIn()) {

                $customer = $objCustomerLogin->getCustomer();
                $customerId = $customer->getEntityId();
                $treeNode = $this->getNodeTree($customerId);

                if(empty($treeNode))
                    throw new Exception('Node not exist.',-3);

                //$qtd =  $treeNode['node_limit_sys'];


               /* if( $qtd <= 0 ){
                    throw new Exception('Limit is over.' , -3);
                }*/

                $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

                $sql = "UPDATE  t_tree  SET status_app  = 4 WHERE  id = :id";
                $data = array('id' => $id);
                $value = $resource->query($sql, $data);

                /*$type =  Mage::getModel('tree/tree')->identifyType($id);
                $acaoId = "";
                switch($type){
                    case '0': $acaoId =  110 ; break;
                    case '1': $acaoId =  111 ; break;
                    case '2': $acaoId =  112 ; break;
                }*/

                $acaoId = Mage::getModel('tree/tree')->getActionIdByStatusSys($id , 'confirm');
                if($acaoId){
                    try{
                        Mage::getModel('tree/tree')->cadastrarAcao($id,$acaoId);
                    }catch(Exception $e){
                        echo $e->getMessage();
                    }

                }

                if($qtd == 1 && $value ){

                    $this->setListB($treeNode['id']);
                }


                if($value){


                    Mage::getModel('tree/deadline')->setCompleted($treeNode['id']);
                }



            }

        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }


    }




    public  function getCouponIsValidFriendApp($coupon){

        try {
            Mage::getModel('tree/tree')->checkStatusCoupon($coupon);

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_coupon_app  WHERE coupon=:cod";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);

            return $row;
        }catch (Exception $e){
            throw new Exception('getCouponIsValidFriendApp : '. $e->getMessage() , -3);
        }
    }


    public  function getCouponIsValidSellWinApp($coupon, $freepayment = false){

        try {
            Mage::getModel('tree/tree')->checkStatusCoupon($coupon);

            $sqlValue = "AND value <> 100";
            if($freepayment){
                $sqlValue = "";
            }

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_coupon_bar  WHERE coupon=:cod  $sqlValue ";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);

            return $row;
        }catch (Exception $e){
            throw new Exception('getCouponIsValidSellWinApp : '. $e->getMessage() , -3);
        }
    }


    public  function getCouponIsValidPromoCodeForYou($coupon){

        try {
            Mage::getModel('tree/tree')->checkStatusCoupon($coupon);

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT * FROM t_promo_for_you  WHERE coupon = :cod AND bought = 0  AND expired = 0 ";
            $data = array('cod' => $coupon);
            $row = $_conn->fetchRow($sql, $data);

            return $row;
        }catch (Exception $e){
            throw new Exception('getCouponIsValidPromoCodeForYou : '. $e->getMessage() , -3);
        }
    }



    public  function getUserForCupomSellWinApp($cupom  ){
        try{
            if(empty($cupom) && is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);

            $conn = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sql = "SELECT * FROM  t_tree t INNER JOIN t_coupon_bar c ON t.id = c.tree_id WHERE c.coupon = '$cupom'";
            $values = $conn->fetchRow($sql);

            return $values;

        }catch(Exception $e){
            throw new Exception('getUserForCupom :' . $e->getMessage(), -3);
        }
    }




    public  function getUserForCupomPromoForYou($cupom){
        try{
            if(empty($cupom) && is_string($cupom))
                throw new Exception( 'erro ao passar dados' , -3);




            $conn = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sql = "SELECT * FROM  t_tree t INNER JOIN t_promo_for_you c ON t.id = c.tree_id WHERE c.coupon = '$cupom'";
            $values = $conn->fetchRow($sql);

            return $values;

        }catch(Exception $e){
            throw new Exception('getUserForCupom :' . $e->getMessage(), -3);
        }
    }

   /* public function getTreeParentIdByCustomer($customerId){


        try {

            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "SELECT parent_id FROM t_tree  WHERE customer_id =:id ";
            $data = array('id' => $customerId);
            $row = $_conn->fetchRow($sql, $data);

            return $row;
        }catch (Exception $e){
            throw new Exception('getCouponIsValidSellWinApp : '. $e->getMessage() , -3);
        }

    }*/



    public function saveChildrens($childrens,$parentId,$qtd = 0 , $returnSituation = false ){
        try{

            $sql = "INSERT INTO t_tree(parent_id,parent_name,grandfather_id,nome,slug,telefone,instagram , instagram_status,instagram_list , new_list_app,situation ) VALUES ";
            $sql .=  "(:parentid , :parentname , :grandfatherid ,:nome,:slug,:telefone,:instagram, :instagramstatus ,:instagramlist ,:newlistapp, :situation)";




            if(empty($childrens)) {
                throw new Exception('saveChildrens : Passagem de parametros incorreta.' , -3);
            }

            $resource = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sqlGetParent = "select j.nome , j.parent_id from t_tree j where j.id = '$parentId'";
            $getParent = $resource->fetchRow($sqlGetParent);

            $slugs = array();
            $valuesResult = array();
            foreach ($childrens as $key => $childValue) {

                $slug = "";

                if(empty($childValue)){
                    continue;
                }

                $child  = $childValue;
                if($returnSituation && !empty($childValue['instagram'])){
                    $child = $childValue['instagram'];
                }

                $child = trim($child);
                $slug = Mage::getModel('tree/tree')->removeAcentos($child, '-');
                $slugs[] = $slug;

                $telefone = '(99)9999999';
                $atleta = 0;

                $instagram = '';
                $instagram = trim($child);
                if(substr($instagram , 0 , 1) != '@'){
                    $instagram = '@'.$instagram;
                }

                $isExistsInsta  = Mage::getModel('tree/tree')->isExistsInsta($instagram);
                if($isExistsInsta){
                    $childValue['result'] = false;
                    $valuesResult[$key] = $childValue;
                    continue;
                   // $instagram = $instagram.'_jaExistente';
                   // $child = $child.'_jaExistente';
                }

                // possui instagram ?
                $instagramStatus = 2;
                $instagramList = 2;

                $dadosSql  = array();
                $dadosSql  = array('parentid'  => $parentId,
                                   'parentname'=> $getParent['nome'],
                                   'grandfatherid'=> $getParent['parent_id'],
                                   'nome'=>$child ,
                                   'slug' => $slug,
                                   'telefone'=>$telefone ,
                                   'instagram' => $instagram,
                                   'instagramstatus'=> $instagramStatus,
                                   'instagramlist'=> $instagramList,
                                   'newlistapp'=>  '1',
                                   'situation' => '0');


                if($resource->query($sql , $dadosSql)){

                    $childValue['result'] = true;
                    $sqlInsertId  =  "SELECT max(id) as last_id  FROM t_tree ";
                    $lastRow = $resource->fetchRow($sqlInsertId);
                    if(!empty($lastRow)){
//                        $acaoId = Mage::getModel('tree/tree')->getActionIdByStatusSys($lastRow['last_id'] , 'suggest');
//                        if($acaoId){
//                            Mage::getModel('tree/tree')->cadastrarAcao($lastRow['last_id'],$acaoId);
//                        }
                        Mage::getModel('tree/tree')->triggerUpdadeIndicarAmigos($lastRow['last_id']);
                        Mage::log(var_export('Antes current step', true) , null , 'cesar.log' , true);
                        Mage::getModel('tree/app')->changeCurrentStep(1 , $lastRow['last_id']);

                    }

                }


                $valuesResult[$key] = $childValue;
            }



            return $valuesResult;


        }catch(Exception $e){

            throw new Exception('Erro ao cadastrar filho:'.$e->getMessage(), -3);
        }
    }


    public function customerBought($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT  a.id as id , rc.code  as code , IF( (now() > mt.to_date) , 0 , 1) as is_valid , mt.times_used , sfo.increment_id
                            FROM
							t_promo_for_you a
                            INNER JOIN `salesrule_coupon` AS `rc` ON a.coupon = rc.code
                            INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            LEFT JOIN `sales_flat_order` sfo ON a.coupon = sfo.coupon_code AND ( sfo.status = 'pending' or sfo.status  = 'processing' or  sfo.status  = 'complete')

                            AND rc.is_primary = 1 WHERE a.customer_id  = :customer and a.bought = 0";

        $data = array('customer' => $customerId);
        $isUpdatedCoupons = $resource->fetchAll($sql , $data);

        $couponsUsed = '';
        $usedV = '';

        $couponsExpirados = '';
        $usedE = '';

        foreach($isUpdatedCoupons  as $update){

            // coupons used
            if(!is_null($update['increment_id'])){
                $couponsUsed .= $usedV.$update['id'];
                $usedV  = ',';

            }
            // coupons expired
            if($update['is_valid'] == 0 && is_null($update['increment_id'])){
                $couponsExpirados .= $usedE.$update['id'];
                $usedE  = ',';
            }
        }

        $sqlUpdate = "";
        if(!empty($couponsUsed)){
            $sqlUpdate .= "UPDATE t_promo_for_you SET bought = 1 WHERE id  in ($couponsUsed);";

        }

        if(!empty($couponsExpirados)){
            $sqlUpdate .= "UPDATE  t_promo_for_you SET expired = 1  WHERE id in ($couponsExpirados);";
        }

        if(!empty($sqlUpdate)){

            $resource->query($sqlUpdate);


        }



    }








    public function getLastCouponPromoForYou($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql= "SELECT * , DATE_FORMAT(  date_expire , '%m/%d/%Y') as expiredate FROM  t_promo_for_you WHERE customer_id =:customer  ORDER BY id DESC LIMIT 0,1";
        $lastRow= $resource->fetchRow($sql ,array('customer' => $customerId));

        return $lastRow;

    }




    public function getLastCouponValidPromoForYou($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql= "SELECT * , DATE_FORMAT(  date_expire , '%m/%d/%Y') as expiredate FROM  t_promo_for_you WHERE customer_id =:customer  AND expired = 0 AND bought = 0 ORDER BY id DESC LIMIT 0,1";
        $lastRow= $resource->fetchRow($sql ,array('customer' => $customerId));

        return $lastRow;

    }

    // promo code for you 10 por cento validos ate o ultimo dia de dezembro 2017
    public function savePromoCodeForYou($customerId , $first = false){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $code = $this->generateCouponNamePromoForYou() ;

        //$tomorrow = date('Y-m-d', strtotime("+15 days"));
        $tomorrow = $dateExpiration = '2017-12-31';

        $discount  = self::COUPON_PROMO_FOR_YOU;
        if($first){
            $discount  =  30;
        }

        $coupon = Seaway_Tree_Model_Cupom::criarCupom($discount, $code ,'16',$tomorrow);

        $tree =  Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
        if(!empty($coupon) && !empty($tree['id'])){
            $sql  = "INSERT INTO t_promo_for_you(tree_id , customer_id , coupon , value ,date_expire) VALUES (:treeid , :customer , :coupon , :value , :expire);";
            $data = array('treeid'=> $tree['id'] , 'customer'=>$customerId , 'coupon' => $coupon , 'value' => $discount , 'expire' => $tomorrow);
            $resource->query($sql , $data);
        }

    }




    public  function generateCouponNamePromoForYou(){

        $i  = 1;
        do{

            $isCupomValid = true;
            $code = Seaway_Tree_Model_Cupom::getCode(8);
            $isEmpty = Seaway_Tree_Model_Cupom::getSalesRuleCouponApp($code);
            if(empty($isEmpty)){
                $isCupomValid = false;
            }
            $i++;
        }while($isCupomValid);

        return $code;

    }



    public function getCouponPromoForYou(){

        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        $getLastCoupon =array();
        if($isLoggedIn){
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getEntityId();
            $getLastCoupon = $this->getLastCouponPromoForYou($customerId);
            if(empty($getLastCoupon)){
                $this->savePromoCodeForYou($customerId , true);
                $getLastCoupon = $this->getLastCouponPromoForYou($customerId);
            }


        }

        return $getLastCoupon;


    }



    public function setPostponePromoCode($customerId){


        $getLastCoupon = $this->getLastCouponPromoForYou($customerId);
        if(!empty($getLastCoupon)){
            if($getLastCoupon['marked'] == 0 && $getLastCoupon['bought'] == 0){
                $couponCode = $getLastCoupon['coupon'];
                $couponObj = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                $ruleObj = Mage::getModel('salesrule/rule')->load($couponObj->getRuleId());

                $postponeDate  = date('Y-m-d', strtotime("+30 days"));

                $ruleObj->setData('to_date',$postponeDate);
                if($ruleObj->save()){

                    $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $sql  = "UPDATE t_promo_for_you SET date_expire = :dateexp  , expired  = :exp , marked = :mark  WHERE id = :id ";
                    $data = array('dateexp'=> $postponeDate , 'exp' => 0 , 'mark' => 1 , 'id' => $getLastCoupon['id']);
                    $resource->query($sql , $data);

                }

            }
        }

    }


    public function isCustomerBought($customerId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT  count(sfo.entity_id) as total
                            FROM
							t_promo_for_you a
                            INNER JOIN `salesrule_coupon` AS `rc` ON a.coupon = rc.code
                            INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            INNER JOIN `sales_flat_order` sfo ON a.coupon = sfo.coupon_code AND ( sfo.status = 'pending' or sfo.status  = 'processing' or  sfo.status  = 'complete')

                            AND rc.is_primary = 1 WHERE a.customer_id  = :customer and a.bought = 0";

        $data = array('customer' => $customerId);
        $isCustomerBought = $resource->fetchRow($sql , $data);


        $clientIsBought = false;
        if(!empty($isCustomerBought['total']) && $isCustomerBought['total'] > 0){
            $clientIsBought = true;
        }


        return $clientIsBought;


    }


    public function isCustomerGiftApp($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        //sfo.status = 'pending' or processing
        $sql = "SELECT  count(sfo.entity_id) as total
                            FROM
							t_tree_coupon a
                            INNER JOIN `salesrule_coupon` AS `rc` ON a.coupon = rc.code
                            INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            INNER JOIN `sales_flat_order` sfo ON a.coupon = sfo.coupon_code AND (  sfo.status  = 'complete' or sfo.status = 'delivered')
        AND rc.is_primary = 1 WHERE a.tree_id  = :tree";

        $data = array('tree' => $treeId);
        $isCustomerGift = $resource->fetchRow($sql , $data);


        $clientIsGift = false;
        if(!empty($isCustomerGift['total']) && $isCustomerGift['total'] > 0){
            $clientIsGift = true;
        }

        return $clientIsGift;

    }



    public function isCustomerGift($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        //sfo.status = 'pending' or processing
        $sql = "SELECT  count(sfo.entity_id) as total
                            FROM
							t_tree_coupon a
                            INNER JOIN `salesrule_coupon` AS `rc` ON a.coupon = rc.code
                            INNER JOIN `salesrule` AS `mt` ON mt.rule_id = rc.rule_id
                            INNER JOIN `sales_flat_order` sfo ON a.coupon = sfo.coupon_code AND (sfo.status  = 'processing' or  sfo.status  = 'pending' or   sfo.status  = 'complete' or sfo.status = 'delivered')
        AND rc.is_primary = 1 WHERE a.tree_id  = :tree";

        $data = array('tree' => $treeId);
        $isCustomerGift = $resource->fetchRow($sql , $data);


        $clientIsGift = false;
        if(!empty($isCustomerGift['total']) && $isCustomerGift['total'] > 0){
            $clientIsGift = true;
        }

        return $clientIsGift;

    }





    public function returnLastCoupon($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');

        $sql = "SELECT coupon FROM t_tree_coupon  WHERE tree_id  = :tree ORDER BY id DESC LIMIT 0,1 ";
        $data = array('tree' => $treeId);
        $dataValues = $resource->fetchRow($sql , $data);

        $result  = "";
        if(!empty($dataValues['coupon'])){
            $result = Mage::getBaseUrl('web')."ex?c=".$dataValues['coupon'];
        }
        return $result;
    }

    public function generateLink( $instagram  , $parentName ,  $treeId ){

        if(empty($parentName) )
            throw new Exception("param is invalid parentName." , -3);

        if(empty($treeId) ||  !is_numeric($treeId))
            throw new Exception("param is invalid treeId." , -3);

        if(empty($instagram) )
            throw new Exception("param is invalid instagram." , -3);

        $link  = $this->generateCouponForWin( $instagram  , $parentName ,  $treeId );


        return $link.'&wv=1';
    }



    private function generateCouponForWin(  $instagram  , $parentName ,  $treeId){
        $i = 0;
        $treeModel = Mage::getModel('tree/tree');
        do{

            $hasGenerate = false;

            $code  = Seaway_Tree_Model_Cupom::createCouponName("" , $parentName , $instagram , $treeId);
            $cupom = Seaway_Tree_Model_Cupom::criarCupom(self::APP_VALUE_DISCOUNT , $code);

            if(!empty($cupom)){
                $retorno = $treeModel->isValid($cupom, $treeId);
                if($retorno){
                    $treeModel->addCupom($cupom , $treeId  );
                    $hasGenerate = true;
                }
           }

            if($i == 5)
                break;

            $i++;
        }while( $hasGenerate == false && $i < 3 );


        return Mage::getBaseUrl('web')."ex?c=".$cupom;
    }

    public function generateCouponForWinInstagram(  $instagram  , $parentName ,  $treeId){
        $i = 0;
        $treeModel = Mage::getModel('tree/tree');
        do{

            $hasGenerate = false;

            $code  = Seaway_Tree_Model_Cupom::createCouponName("" , $parentName , $instagram , $treeId);
            $cupom = Seaway_Tree_Model_Cupom::criarCupom(self::APP_VALUE_DISCOUNT , $code);

            if(!empty($cupom)){
                $retorno = $treeModel->isValid($cupom, $treeId);
                if($retorno){
                    $treeModel->addCupom($cupom , $treeId  );
                    $hasGenerate = true;
                }
            }

            if($i == 5)
                break;

            $i++;
        }while( $hasGenerate == false && $i < 3 );


        return $cupom;
    }

    public function getMsgCallbackExpError(){
        $message  = Mage::getModel('customer/session')->getData('experience_callback_error');
        Mage::getModel('customer/session')->unsetData('experience_callback_error');
        return $message;
    }




   public function encrypt($decrypted, $password, $salt='!kQm*fF3pXe1Kbm%9') {
        // Build a 256-bit $key which is a SHA256 hash of $salt and $password.
        $key = hash('SHA256', $salt . $password, true);
        // Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
        srand();
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
        if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
        // Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
        // We're done!
        return $iv_base64 . $encrypted;
    }


    public  function decrypt($encrypted, $password, $salt='!kQm*fF3pXe1Kbm%9') {
        // Build a 256-bit $key which is a SHA256 hash of $salt and $password.
        $key = hash('SHA256', $salt . $password, true);
        // Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
        $iv = base64_decode(substr($encrypted, 0, 22) . '==');
        // Remove $iv from $encrypted.
        $encrypted = substr($encrypted, 22);
        // Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
        // Retrieve $hash which is the last 32 characters of $decrypted.
        $hash = substr($decrypted, -32);
        // Remove the last 32 characters from $decrypted.
        $decrypted = substr($decrypted, 0, -32);
        // Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
        if (md5($decrypted) != $hash) return false;
        // Yay!
        return $decrypted;
    }


    /**
     * Funcao utilizada para mostrar na index do site caso ocorra algum  erro
     * @param $msg string  -  msg de erro a ser salva na sess�o
     */
    public function setSessionAppError($msg){
        Mage::getModel('core/session')->addData('session_app_error' , $msg);
    }

    /**
     *
     * @return retorna a msg de erro para ser exibida na home
     */

    public function getSessionAppError(){
        $msgError  =  Mage::getModel('core/session')->getData('session_app_error');
        Mage::getModel('core/session')->unsetData('session_app_error');
        return $msgError;
    }





}