<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 25/01/2017
 * Time: 14:32
 */
class Seaway_Tree_Model_Exhibition{

    public $modelTree = null;
    public $resource  = null;
    public $nodes = array();






    public function __construct(){

        $this->modelTree = new Seaway_Tree_Model_Tree();
        $this->resource = Mage::getSingleton('core/resource')->getConnection('core_write');

    }


    // import data
    public function updateTable(){

        $indexes = $this->modelTree->getChildrensFristLine();
        $all = array();

        $sqlTot = "";
        $i = 1;
        foreach($indexes as $v){
            $id = 0;
            $id = $v['id'];
            $all = $this->modelTree->getAlltreeForId($id);
            //sleep(1);

            $sql = "";
            $sql = "INSERT t_tree_exhibition(tree_id , nome , estado , parent_id , qtd_buy , qtd_friend , qtd_son , qtd_total ) VALUES ";
            $v = "";
            foreach($all as $val){

                $qtdBuys = 0;
                $qtdFriends =  0;
                $qtdSons =  0;

                $qtdBuys = (int)$val['qtd_buys'];
                $qtdFriends =  (int)$val['qtd_friends'];
                $qtdSons =  (int)$val['qtd_sons'];

                $sql.=  "$v(".$val['id'].",'".$val['nome']."','".$val['estado']."',".$val['parent_id'].",".$qtdBuys.",".$qtdFriends.",".$qtdSons.",".$val['qtd_total'].")";
                $v = ",";
            }

            $sqlTot.=$sql.";";

            $i++;
         }

        echo $sqlTot;
        die('terminou');

        //

        //
    }


    public function updateSons(){

        $sql = "SELECT tree_id FROM t_tree_exhibition ";
        $nodes =  $this->resource->fetchAll($sql);

        $sqlUpdate = "";
        foreach($nodes as $node){
            $id = null;
            $id = $node['tree_id'];
            if(is_numeric($id)) {
                $ids = $this->modelTree->getIdsFormatedTree($id);
                array_shift($ids);
                $qtdChilds  = count($ids);
                $ids = ',' . implode(',', $ids) . ',';

                if ($ids != ',,') {
                    $sqlUpdate .= "UPDATE t_tree_exhibition SET sons_id = '$ids' , qtd_child = '$qtdChilds' WHERE tree_id = " . $node['tree_id'] . ";";
                }

            }


        }


        echo $sqlUpdate;
        die('terminou');

    }



    public function getData($pag = 1 , $limit = 100 , $orders = array() ){

        $return = array();
        $offset = 0;
        if($pag > 1){
            $offset = ($pag-1) * $limit;
        }
        try{

            $search = "";
            $ordersSql = "";
            $v = "";
            foreach($orders as  $order ){

                $order['name'] = 't.'.$order['name'];
                if($order['name'] == 't.nome') {
                    $order['name'] = 't.nome';
                }
                if($order['name'] == 't.buy') {
                    $order['name'] =  't.qtd_buy';
                }
                if($order['name'] == 't.friend'){
                    $order['name'] = 't.qtd_friend';
                }
                if($order['name'] == 't.child'){
                    $order['name'] = 't.qtd_child';
                }

                if($order['name'] == 't.parent'){
                    $order['name'] = 'qtd_parent';
                }


                $ordersSql.= $v.$order['name'].' '.$order['value'];

                $v = ",";
            }

            if(!empty($ordersSql)){
                $ordersSql = " ORDER BY ".$ordersSql;
            }

            $primeiroNivel = "(SELECT count(id) FROM t_tree WHERE parent_id = t.tree_id  ) as qtd_parent";

            $sql = "SELECT t.id ,
                            t.nome ,
                            t.qtd_buy ,
                            t.qtd_friend ,
                            t.qtd_child,
                            $primeiroNivel

                      FROM t_tree_exhibition t $ordersSql limit $offset,$limit ";




            $sqlTotal = "SELECT CEILING(count(*)/$limit) as total_pag ,count(*) as total FROM t_tree_exhibition t WHERE 1 = 1  $ordersSql ";



            $valores = $this->resource->fetchAll($sql);
            $total =   $this->resource->fetchRow($sqlTotal);

            $return = array('total' => $total["total"] ,'total_pag' => $total["total_pag"] , 'valores' => $valores);

        }catch(Exception $e){
            throw new Exception('getChildrens :' . $e->getMessage(), -3);
        }


        return $return;

    }





    //
    public function updateQtdBuy($treeId){

        $sql = "UPDATE t_tree_exhibition SET qtd_buy = (qtd_buy + 1 ) , qtd_total = (qtd_total + 1) WHERE id = $treeId ";
        $this->resource->query($sql);

    }


    public function updateQtdSon($treeId){

        $sql = "UPDATE t_tree_exhibition SET qtd_son = (qtd_son + 1) , qtd_total = (qtd_total + 1) WHERE sons_id like '%,$treeId,%'";
        $this->resource->query($sql);


    }


    public function updateQtdFriend($treeId){

        $sql = "UPDATE t_tree_exhibition SET qtd_friend = (qtd_friend + 1) , qtd_total = (qtd_total + 1) WHERE id = $treeId ";
        $this->resource->query($sql);


    }


    public function createExhibition($parentId , $slugs){

        $sql = "SELECT id,nome FROM t_tree WHERE slug in ($slugs) ";
        $childs =  $this->resource->fetchAll($sql);


        $sqlInsert = "INSERT INTO t_tree_exhibition(nome,tree_id,parent_id) VALUES ";
        $v = "";
        $idsChild = "";
        $qtdChild = 0;
        foreach($childs as $child){
            $sqlInsert.="$v('".$child['nome']."',".$child['id'].",".$parentId.")";
            $v = ",";
            $idsChild .= $child['id'].$v;
            $qtdChild++;
        }

        // atualizar todos os avôs do sistema
        $sqlGrand = "UPDATE t_tree_exhibition SET sons_id = CONCAT(sons_id ,'$idsChild'), qtd_child = (qtd_child + $qtdChild)  WHERE sons_id like '%,$parentId,%'";
        $idsChild = ','.$idsChild;
        // atualizar o pai
        $sqlParent = "UPDATE t_tree_exhibition SET sons_id = CONCAT(SUBSTRING(IF(sons_id is null , '' , sons_id ), 1, CHAR_LENGTH(IF(sons_id is null , '' , sons_id )) - 1) ,'$idsChild') , qtd_child = (qtd_child + $qtdChild) WHERE tree_id = $parentId ";


        $this->resource->query($sqlInsert);

        $this->resource->query($sqlGrand);

        $this->resource->query($sqlParent);


    }


    public function  getEstado($nome){


        $estados = array("AC"=>"acre", "AL"=>"alagoas", "AM"=>"amazonas", "AP"=>"amapa","BA"=>"bahia","CE"=>"ceara","DF"=>"distrito-federal","ES"=>"espirito-santo","GO"=>"goias","MA"=>"maranhao","MT"=>"mato-grosso","MS"=>"mato-grosso-do-sul","MG"=>"minas-gerais","PA"=>"para","PB"=>"paraiba","PR"=>"parana","PE"=>"pernambuco","PI"=>"piaui","RJ"=>"rio-de-janeiro","RN"=>"rio-grande-do-norte","RO"=>"rondonia","RS"=>"rio-grande-do-sul","RR"=>"roraima","SC"=>"santa-catarina","SE"=>"sergipe","SP"=>"sao-paulo","TO"=>"tocantins") ;
        $sigla = "";
        if(!empty($nome)) {
            $nome = Mage::getModel('checkout/cart')->removeAcentos($nome , '-');
            foreach ($estados as $k => $n) {
                if ($n == trim($nome)) {
                    $sigla = $k;
                }
            }
        }
        return $sigla;
    }


    public function getTree($id){

        $sql = "SELECT  nome , tree_id as id , parent_id , estado , qtd_buy , qtd_friend , qtd_son , qtd_total  FROM t_tree_exhibition WHERE parent_id = $id ORDER BY qtd_total DESC";
        $nodes = $this->resource->fetchAll($sql);
        $values  = array();

        foreach ($nodes as $node ) {

            $node['estado']  = $this->getEstado($node['estado']);
            $values[] = $node;
            $resp = array();
            $resp = $this->getTree($node['id']);
            if(!empty($resp))
                $values = array_merge($values, $resp);
        }

        return $values;
    }


    public function getNode($id){

        $sql = "SELECT  nome , tree_id as id , estado ,parent_id , qtd_buy , qtd_friend , qtd_son , qtd_total  FROM t_tree_exhibition WHERE tree_id = $id ";
        $nodes = $this->resource->fetchAll($sql);
        $result = array();
        foreach($nodes as $node){
            $node['estado']  = $this->getEstado($node['estado']);
            $result[] = $node;
        }
        return $result;
    }


    public function showTree($id  = 3){
        $this->nodes = array();

        $val = $this->getTree($id);
        $father = $this->getNode($id);
        $val = array_merge($father , $val );

        return $val;
    }


    public function getChildrensFristLine(   $order = 'buy' , $id  = 2 ){

        try{

            $sqlOrder = " qtd_total DESC ";
            if($order != 'buy' ){
                $sqlOrder = " qtd_child DESC ";
            }

            //and (status = 1 or (cod_gerado_1 <> '' or cod_gerado_2 <> '' or codigo_ref <> '' ))
            $sql = "SELECT tree_id as id,nome  FROM t_tree_exhibition WHERE parent_id = $id  ORDER BY $sqlOrder ";
            $return = $this->resource->fetchAll($sql);

            /* foreach ($return as $value) {
                 //$value['id']
             }*/


            return $return;



        }catch(Exception $e){
            throw new Exception($e->getMessage() , -3);
        }

    }

    




}