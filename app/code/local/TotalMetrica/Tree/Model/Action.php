<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 31/07/2017
 * Time: 17:05
 */
class Seaway_Tree_Model_Action {




    public function register($data){

        try{

            if(empty($data['parent']['name'])){

                throw new Exception( 'Param invalid.', -3);
            }

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

            foreach($data['type'] as  $type){


                 foreach($data['parent']['name'] as  $key => $nameAction){


                     $sqlAction = "INSERT INTO t_action(name,tipo,tipo_acao) VALUES (:name,:tipo,:t_acao);";
                     $dataAction = array('name' => $nameAction , 'tipo' => $type , 't_acao' => 'p' );
                     $resource->query($sqlAction,$dataAction);

                     $lastInsertId = false;
                     $lastInsertId = $resource->lastInsertId();

                     if(!empty($data['childrens'][$key]) && is_numeric($lastInsertId) && !empty($lastInsertId)){

                         foreach($data['childrens'][$key]  as $nameObservation){

                             $sqlObs = "INSERT INTO t_action(name,tipo,tipo_acao,proxima_acao) VALUES (:name,:tipo,:t_acao,:p_acao);";
                             $dataObs = array('name' => $nameObservation , 'tipo' => $type , 't_acao' => 'e', 'p_acao' => $lastInsertId );
                             $resource->query($sqlObs,$dataObs);

                         }

                     }


                 }

             }



        }catch (Exception $e){

            throw new Exception($e->getMessage() , -3);

        }


    }


    public function getObservation($tipo , $mainActionId){

       return  $this->getActions($tipo , $mainActionId ,  "e"  );

    }


    public function getActions($tipo  ,  $mainActionId = false , $tipoAcao = "p"){


        $sqlMainAction =  " ";
        if(is_numeric($mainActionId)){
            $sqlMainAction =  " and proxima_acao = $mainActionId ";
        }

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT * FROM t_action where tipo = :tipo  and tipo_acao = :tipo_acao $sqlMainAction order by ordem asc ";
        $dataObs = array('tipo' => $tipo , 'tipo_acao' =>  $tipoAcao);
        return $resource->fetchAll($sql,$dataObs);


    }


    public function saveSortable($actions, $type){

        $actions  = explode('-',$actions);
        $values   =  $actions;
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "";
        $order = 1;
        $lastValue = end($actions);
        current($values);
        foreach($actions as $key =>  $action){
            $nextAction  = 'null';
            if($lastValue != $action){
               $nextAction  = next($values);
            }
            $sql = " UPDATE t_action SET ordem = $order , proxima_acao = $nextAction WHERE tipo = $type  AND id = $action ;";
            $query = $resource->query($sql);
            if($query){
                $this->updateSortable($action , $nextAction);
            }
            $order++;
        }

    }


    private function updateSortable($action , $nextAction ){

        $futureActionName = 'null';
        $futureActionId   = 'null';
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        if($nextAction != 'null'){

            $sqlFutureAction = "SELECT * FROM t_action WHERE id  = $nextAction ;";
            $futureAction  = $resource->fetchRow($sqlFutureAction);

            $futureActionId   = $futureAction['id'];
            $futureActionName = "'".$futureAction['name']."-".$futureActionId."'";

        }

        $sqlTree = "SELECT id FROM t_tree WHERE last_action_id  = $action ;";
        $treeActions = $resource->fetchAll($sqlTree);

        if(!empty($treeActions)){
            $sqlUpdate = "";
            foreach($treeActions as $treeAction){
                $sqlUpdate .= "UPDATE t_tree SET    future_action_id = $futureActionId  , future_action = $futureActionName WHERE id = ".$treeAction['id'].";";

            }
            $resource->query($sqlUpdate);
        }

    }











}