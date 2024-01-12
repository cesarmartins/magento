<?php


class CisTecnologia_ListaFavoritos_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction(){

        $this->loadLayout();

        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $redirectpath = Mage::getUrl(' ', array('_direct' => 'listafavoritos/index/inicio'));
            $this->_redirectUrl($redirectpath);
        }
        $this->renderLayout();

    }

    public function inicioAction(){

        $this->loadLayout();

        $customerSession = Mage::getSingleton('customer/session');
        $listaColletion = array();
        $listaProdutosColletion = array();

        if ($customerSession->isLoggedIn()) {
            $listaColletion = Mage::getModel("cistecnologia_listafavoritos/favoritos")->getListaFavoritosCollection($customerSession->getId());
            if(count($listaColletion) >= 1){
                foreach ($listaColletion as $key => $lista){
                    $listaProdutosColletion[$lista["lista_favoritos_id"]]["favoritos"] = $lista;
                    $listaProdutosColletion[$lista["lista_favoritos_id"]]["produtos"][] = Mage::getModel("cistecnologia_listafavoritos/favoritos")->getListaFavoritosProdutosCollection($lista["lista_favoritos_id"]);
                }
            }
        }
        $this->getLayout()->getBlock('head')->setTitle("Minha Lista");
        $this->getLayout()->getBlock('listafavoritos')->setData("collection", $listaProdutosColletion);
        $this->renderLayout();

    }

}