<?php


class CisTecnologia_ListaFavoritos_AjaxController extends Mage_Core_Controller_Front_Action
{

    public function installmentsAction(){

        $cesar = null;
        //die('cesar');

    }

    public function cadastrarListaFavoritosAction()
    {

        if ($this->getRequest()->isPost()) {

            $name = $this->getRequest()->getPost('name');
            $user = $this->getRequest()->getPost('user');
            $alterar = $this->getRequest()->getPost("alterar");

            $model = Mage::getModel("cistecnologia_listafavoritos/favoritos")
                        ->insertDescricaoListaFavoritos($name, $user, $alterar);

            $retorno = array(
                        "sucess" => true,
                        "msg"    => ($alterar == "false")? "Lista cadastrada com sucesso!" : "Lista alterada com sucesso!"
                    );

            header('Content-Type:application/json');
            echo json_encode($retorno, true);

        }
    }

    public function cadastrarProdutosFavoritosAction()
    {

        if ($this->getRequest()->isPost()) {
            $produtcId = $this->getRequest()->getPost("produtcId");
            $userId = $this->getRequest()->getPost("userId");
            $listaSelecionada = $this->getRequest()->getPost("listaSelecionada");
            $userProdutos = array($userId => $produtcId);

            $retorno = Mage::getModel("cistecnologia_listafavoritos/favoritos")->
                            inserirProdutoFavoritos($userProdutos, $listaSelecionada);
            header('Content-Type:application/json');
            echo json_encode($retorno, true);

        }
    }

    public function salvarQtdProdutosAction(){

        if ($this->getRequest()->isPost()) {

            $qtd = $this->getRequest()->getPost('produtcQtd');
            Mage::getSingleton('core/session')->setQtdProdutos($qtd);

            $retorno = array("retorno" => true, "msg" => "Quatidade salva com sucesso!", "qtd" => $qtd);
            header('Content-Type:application/json');
            echo json_encode($retorno, true);
        }
    }

    public function adicionarProdutosNoCarrinhoAction(){

        if ($this->getRequest()->isPost()) {

            $listaId = $this->getRequest()->getPost("id");

            $retorno = Mage::getModel("cistecnologia_listafavoritos/favoritos")->getListaFavoritosProdutosCollection($listaId);

            foreach ($retorno as $item){

                $_product = Mage::getModel('catalog/product')->load($item["product_id"]);
                $params = array(
                    'product' => $item["product_id"],
                    'qty' => 1
                );

                $cart = Mage::getModel('checkout/cart');
                $cart->init();
                $cart->addProduct($_product, $params);
                $cart->save();

                $quote = Mage::getModel('checkout/session')->getQuote();
                $quote->collectTotals()->save();

            }

            $retorno = array("retorno" => true, "msg" => "Quatidade salva com sucesso!");
            header('Content-Type:application/json');
            echo json_encode($retorno, true);
            //$redirectpath = Mage::getUrl(' ', array('_direct' => 'checkout/cart/'));
            //$this->_redirectUrl($redirectpath);

        }
    }

    public function deleteListaFavoritosAction(){

        if ($this->getRequest()->isPost()) {

            $listaId = $this->getRequest()->getPost("id");

            $retorno = Mage::getModel("cistecnologia_listafavoritos/favoritos")
                ->removerListaFavoritos($listaId);

            $retorno = array("retorno" => true, "msg" => "Lista removida com sucesso!");
            header('Content-Type:application/json');
            echo json_encode($retorno, true);
        }
    }
    public function getListaFavoritosAction(){

        if ($this->getRequest()->isPost()) {

            $listaId = $this->getRequest()->getPost("id");
            $userId = $this->getRequest()->getPost("user");

            $retornoDados = Mage::getModel("cistecnologia_listafavoritos/favoritos")
                ->getListaFavoritos($listaId, $userId);

            $retorno = array("retorno" => true, "dados" => $retornoDados);
            header('Content-Type:application/json');
            echo json_encode($retorno, true);
        }
    }


}