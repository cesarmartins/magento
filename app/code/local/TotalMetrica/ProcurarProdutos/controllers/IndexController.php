<?php

class TotalMetrica_ProcurarProdutos_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $query = Mage::helper('catalogsearch')->getQuery();
        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
                else {
                    $query->prepare();
                }
            }

            Mage::helper('catalogsearch')->checkNotes();

            $this->loadLayout();
            $this->getLayout()->getBlock('procurarprodutos')->setData('produtoProcurado',$query->getQueryText());
            //$this->_initLayoutMessages('catalog/session');
            //$this->_initLayoutMessages('checkout/session');

            $this->renderLayout();

            if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->save();
            }
        }
        else {
            $this->_redirectReferer();
        }
	}
}