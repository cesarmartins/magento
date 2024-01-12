<?php

class Cistecnologia_Importacao_Model_Produtos {

    //public function insertProdutoSimples($linha, $produtoConfiguravel, $linhaProdtudos ,$key){
    public function insertProdutoSimples($produtos){

        //$caminho = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product';

        $sku = $produtos["codigo_produto"];
        $_store = Mage::app()->getStore();
        $_attribute_set = "4";

        $_type = 'simple';
        $_category = $this->getCategories($produtos["codigo_categoria"], $produtos["codigo_secao"]);
        $description = $produtos["descricao"];
        $name = $produtos["descricao"];
        $short_description = $produtos["descricao"];
        $qty = $this->getQuantidade($produtos["codigo_categoria"], $produtos["codigo_secao"]);
        $price = $this->getPreco($produtos["codigo_categoria"], $produtos["codigo_secao"]);

        $product = Mage::getModel('catalog/product');

        try{

            $product
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setWebsiteIds(array(Mage::app()->getStore()->getId()))
                ->setAttributeSetId($_attribute_set) //Grupo de atributos
                ->setTypeId($_type) //product type
                ->setSku($sku) //SKU
                ->setName($name) //product name
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                ->setPrice($price) //price in form 11.22
                ->setDescription($description)
                ->setShortDescription($short_description);

                $product->setStockData(array(
                        'qty' => $qty //qty
                    )
                )
                    ->setCategoryIds($_category); //assign product to categories
                $product->save();

                //Produto Configuravel
                $configProduct = $product;
                $configProduct->setCategoryIds($_category); //assign product to categories
                $configProduct->getTypeInstance()->setUsedProductAttributeIds(array(155)); //attribute ID of attribute 'color' in my store
                $configurableAttributesData = $configProduct->getTypeInstance()->getConfigurableAttributesAsArray();

                $configProduct->setCanSaveConfigurableAttributes(true);
                $configProduct->setConfigurableAttributesData($configurableAttributesData);

                $configProduct->save();

            //Pegar o ultimo ID cadastrado
            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSort('created_at', 'desc');
            $collection->getSelect()->limit(1);

            $latestItemId = $collection->getLastItem()->getId();
            return $latestItemId;

        }catch(Exception $e){
            throw new Exception($e->getMessage());
            Mage::log($e->getMessage());
            return false;
        }
    }


    public function getCategories($category_id, $secao_id){

        try{

            $category = Mage::getResourceModel('catalog/category_collection')
                ->load($category_id);

            $cadastrar = true;
            foreach ($category as $cat){
                if(!is_null($cat->getName())){
                    $cadastrar = false;
                }
            }

            if($cadastrar){

                $retorno = Mage::helper("importacao/categoria")->getCategoria($category_id, $secao_id);

                $category = Mage::getModel('catalog/category');
                $category->setName(uc_words($retorno["descricao"]));
                $category->setIsActive(1);
                $category->setIsAnchor(1); //for active anchor
                $category->setStoreId(Mage::app()->getStore()->getId());
                $parentCategory = Mage::getModel('catalog/category')->load(2);
                $category->setPath($parentCategory->getPath());
                $category->save();

                $category = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('name', uc_words($retorno["descricao"]))
                    ->getFirstItem(); // The parent category
            }
            return $category->getId();
        }catch (Exception $e){
            throw new Exception($e->getMessage() , -3);
        }
    }


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