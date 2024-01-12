<?php
class CisTecnologia_ListaFavoritos_Model_Favoritos extends Mage_Core_Model_Abstract
{

    public function __construct()
    {
       $this->resource = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function pegarListaProdutoFavoritos($id)
    {
        $id = key($id);
        $sql = "select * from cis_lista_favoritos_produtos where lista_favoritos_id = " . $id;
        $fetchAll = $this->resource->fetchAll($sql);
        return $fetchAll;
    }

    public function insertDescricaoListaFavoritos($nome, $userID, $alterar){

        if($alterar == "false"){
            $sql = "INSERT cis_lista_favoritos (lista_favoritos_user_id,lista_favoritos_nome,lista_favoritos_data, lista_favoritos_ativo)";
            $sql .= "VALUES(:lista_favoritos_user_id,:lista_favoritos_nome, now(),1)";

            $data = array(
                "lista_favoritos_user_id" => $userID,
                "lista_favoritos_nome" => $nome
            );
            $retorno = $this->resource->query($sql, $data);

        }else{
            $retorno = $this->alterarListaFavoritos($nome, $alterar);
        }
        return $retorno;
    }

    public function inserirProdutoFavoritos($userProdutos, $lista){

        $userID = key($userProdutos);
        $produtoId = $userProdutos[$userID];

        $sqlListaFavoritos = "select * from cis_lista_favoritos 
                                where lista_favoritos_user_id = :user 
                                AND lista_favoritos_id = :lista";
        $fetchAll = $this->resource->fetchAll($sqlListaFavoritos , array('user' => $userID, 'lista' => $lista));

        $metodo = "inserir";
        $ativo = 1;
        foreach ($fetchAll as $item){

            $sql = "select * from cis_lista_favoritos_produtos 
                        where lista_favoritos_id = " . $item["lista_favoritos_id"] . " 
                        AND product_id = " . $produtoId;
            $encontrado = $this->resource->fetchAll($sql);

            if(count($encontrado) >= 1){
                $metodo = "delete";
                $ativo = 0;
                $sql = "DELETE FROM cis_lista_favoritos_produtos WHERE (`idcis_lista_favoritos_produtos` =" . $encontrado[0]["idcis_lista_favoritos_produtos"] . ");";
                $this->resource->query($sql);
            }else{
                $sql = "INSERT cis_lista_favoritos_produtos (lista_favoritos_id, product_id, data_insert)";
                $sql .= "VALUES(:lista_favoritos_id,:product_id, now())";

                $data = array(
                    "lista_favoritos_id" => $item["lista_favoritos_id"],
                    "product_id" => $produtoId
                );
                $this->resource->query($sql, $data);

            }


        }
        $retorno = array("metodo" => $metodo, "ativo" => $ativo);
        return $retorno;
    }

    public function checkFavorite($_product, $user)
    {
        $sql = "SELECT * FROM cis_lista_favoritos f
                inner join cis_lista_favoritos_produtos p 
                    ON f.lista_favoritos_id = p.lista_favoritos_id
                WHERE f.lista_favoritos_user_id = " . $user . "
                AND p.product_id = " . $_product->getId();
        $retorno = $this->resource->fetchAll($sql);
        return (count($retorno) >= 1)? true : false;
    }

    public function getListaFavoritosCollection($userId)
    {
        $sqlListaFavoritos = "select * from cis_lista_favoritos where lista_favoritos_user_id = :user";
        $fetchAll = $this->resource->fetchAll($sqlListaFavoritos , array('user' => $userId));
        return $fetchAll;
    }

    public function getListaFavoritos($id, $userId)
    {
        $sqlListaFavoritos = "select * from cis_lista_favoritos 
                              where lista_favoritos_id = :id
                                    AND lista_favoritos_user_id = :user";
        $fetchAll = $this->resource->fetchAll($sqlListaFavoritos , array('id' => $id, 'user' => $userId));
        return $fetchAll;
    }

    public function getListaFavoritosProdutosCollection($listaId)
    {
        $sqlListaFavoritos = "select * from cis_lista_favoritos_produtos 
                              where lista_favoritos_id = :listaId";
        $fetchAll = $this->resource->fetchAll($sqlListaFavoritos , array('listaId' => $listaId));
        return $fetchAll;
    }

    public function removerListaFavoritos($listaId)
    {
        $sqlListaFavoritos = "delete from cis_lista_favoritos 
                              where lista_favoritos_id = :listaId";
        $fetchAll = $this->resource->query($sqlListaFavoritos , array('listaId' => $listaId));
        return $fetchAll;
    }
    public function alterarListaFavoritos($nome, $listaId)
    {
        $sqlListaFavoritos = "UPDATE cis_lista_favoritos
                                SET lista_favoritos_nome = :nome
                                WHERE lista_favoritos_id = :listaId";
        $fetchAll = $this->resource->query($sqlListaFavoritos , array('nome' => $nome, 'listaId' => $listaId));
        return $fetchAll;
    }
}