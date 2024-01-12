<?php


class CisTecnologia_ListaFavoritos_MinhaListaController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {

        $this->loadLayout();

        $id = $this->getRequest()->getParams();

        $body = $this->getLayout()->getBlock('listafavoritos/listagemadicionados');

        $this->renderLayout();

    }
    public function listagemAction()
    {
        $this->loadLayout();

        $id = $this->getRequest()->getParams();

        $getListaCollection = Mage::getModel("cistecnologia_listafavoritos/favoritos")->pegarListaProdutoFavoritos($id);
        $this->getLayout()->getBlock('listafavoritos')->setData('collection', $getListaCollection);

        $this->renderLayout();

    }

}