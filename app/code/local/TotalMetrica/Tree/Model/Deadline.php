<?php

class Seaway_Tree_Model_Deadline{


    private $customerId = null;
    private $treeId  = null ;
    private $tree    = null;



    public function __construct(){
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $this->customerId = $customer->getEntityId();
            $getTree = $this->getTree( $this->customerId);
            if(!empty($getTree['id'])){
                $this->tree    = $getTree;
                $this->treeId  = $getTree['id'];
            }
        }
    }



//$tree =  $this->getTreeByEmail($email);
    public function verifyDateApp($tree){
        $result = false;
        if($tree['ex_first_time_access'] == 1 || ($tree['ex_first_time_access'] == 0 && $tree['is_date_valid_app'] == 1)){
            $result = true;
        }
        return $result;
    }


    public function getTreeValue(){
        return $this->tree;
    }



    public function verifyDateInvitedApp($tree){
        $result = true;
        if($tree['ex_completed'] == 1 || $tree['is_date_valid_invited_app'] == 1 ){
            $result = false;
        }
        return $result;
    }


    public function getTreeByEmail($email){


        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        if($email){

            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer  = $customer->loadByEmail($email);
            $customerId = $customer->getEntityId();

        }

        $query = "SELECT * ,
                      if((DATEDIFF(ex_link_app , NOW())) < 0 , 0  , 1  ) as  is_date_valid_app  ,
                      DATE_FORMAT(ex_link_app , '%m/%d/%Y') as date_app

                      FROM t_tree WHERE customer_id = :id AND (status = 0 or status = 1) ";

        $data = array('id' => $customerId);

        $valores = $resource->fetchRow($query,$data);
        return $valores;

    }



    public function getTree($customerId = null ){


        if(empty($customerId)){
            $customerId = $this->customerId;
        }

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $newSQL = "SELECT *  ,
                      if((DATEDIFF(ex_invited , NOW())) < 0 , 0  , 1  ) as  is_date_valid_invited_app  ,
                      DATE_FORMAT(ex_invited , '%m/%d/%Y') as date_invited_app

                                                    FROM  t_tree  WHERE customer_id  = :id";
        $data = array( 'id' => $customerId );
        return $resource->fetchRow($newSQL, $data);
    }



    // chama na hora que usuario admin ir em tree -> clicar em um participante e clicar no botao Criar Data Expiração Link
    public function createDateLinkApp($treeId = null){

        if(empty($treeId)){
            $treeId = $this->treeId;
        }


        $deadline = date('Y-m-d', strtotime('+6 days'));
        $deadlineReturn = date('d/m/Y', strtotime($deadline));

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE t_tree SET ex_link_app = :ex WHERE id  = :id";
        $data = array( 'ex' => $deadline , 'id' => $treeId );
        $resource->query($sql, $data);


        return $deadlineReturn;
    }




    public function createNewLinkApp($treeId){



        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $while  = true;
        do{

            $cod  = mt_rand(100000 ,999999 );
            $sqlHash = "SELECT count(*) as total FROM  t_tree WHERE hash_new_link = :cod ";
            $dataHash = array( 'cod' => $cod );
            $rowTotal  = $resource->fetchRow($sqlHash, $dataHash);

            if(empty($rowTotal['total'])){

                $while  = false;

            }

        }while($while);

        $sql = "UPDATE t_tree SET hash_new_link = :hash WHERE id  = :id";
        $data = array( 'hash' => $cod , 'id' => $treeId );
        $resource->query($sql, $data);


        return $cod;
    }

    // chama na hora que entrar no app
    public function setFirstAccess(){


        if(!empty($this->treeId) && !empty($this->tree) && empty($this->tree['ex_first_time_access'])) {

            $treeId = $this->treeId;
            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_tree SET ex_first_time_access = :first WHERE id  = :id";
            $data = array( 'first' => 1 , 'id' => $treeId );
            $resource->query($sql, $data);

        }

    }

  // chama na hora que entrar no app
    public function setInvitedDeadline(){


        if(!empty($this->treeId) && !empty($this->tree) && empty($this->tree['ex_invited'])) {

            $treeId = $this->treeId;
            $deadline = date('Y-m-d', strtotime('+10 days'));
            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE t_tree SET ex_invited = :ex WHERE id  = :id";
            $data = array('ex' => $deadline, 'id' => $treeId);
            $resource->query($sql, $data);

        }

    }



    // chama na hora de confirmar a pessoa
    public function setCompleted($treeId = null){


        if(empty($treeId)){
            $treeId = $this->treeId;
        }

        // verificar a qtd de pessoas que foram indicadas 5 no total e marcadas o limite de pessoas
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $nodeLimit = "(u.qtd  - (SELECT count(s.id) FROM t_tree s WHERE s.parent_id = u.id AND s.status_app IN(4) )) as limit_people_confirmed";
        $peopleInvitedQtd = "(SELECT count(t.id) FROM t_tree t WHERE t.parent_id = u.id AND t.new_list_app > 0) as qtd_people_invited";

        $newSQL = "SELECT u.*  ,$nodeLimit,$peopleInvitedQtd FROM  t_tree u WHERE u.id  = :id";
        $newData = array('id' => $treeId);
        $row = $resource->fetchRow($newSQL, $newData);



        if ($row['limit_people_confirmed'] == 0 && $row['qtd_people_invited'] >= 5) {

            $sql = "UPDATE t_tree SET   ex_completed = :ex WHERE id  = :id";
            $data = array( 'ex' => 1 , 'id' => $treeId );
            $resource->query($sql, $data);

        }

    }



    public function qtdDaysToExpireList($treeId = null){

        if(empty($treeId)){
            $treeId = $this->treeId;
        }


        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT ex_completed , ex_invited  FROM t_tree WHERE id  = :id";
        $data = array('id' => $treeId );
        $rows = $resource->fetchRow($sql, $data);

        $return  =  0 ;
        if((isset($rows['ex_completed']) &&  $rows['ex_completed'] == 0) && !empty($rows['ex_invited'])){

            $data1 = date('Y-m-d');
            $data2 = $rows['ex_invited'];

            $dia1 = strtotime( $data1 );
            $dia2 = strtotime( $data2 );

            if( ($dia2 - $dia1) > 0 ){
                $return = ( $dia2 - $dia1 ) / 86400;
            }else if( ($dia2 - $dia1) <=  0  ){
                $return = false;
            }

        }

        return $return;

    }








}