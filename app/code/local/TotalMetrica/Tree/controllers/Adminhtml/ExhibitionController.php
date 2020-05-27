<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 21/02/2017
 * Time: 17:14
 */
class Seaway_Tree_Adminhtml_ExhibitionController extends Mage_Adminhtml_Controller_Action
{

    public function tableApresentationAction()
    {
       $this->loadLayout();
       $this->renderLayout();
    }


    public function paginationAction(){



        $childrens = array();
        if($this->getRequest()->isPost()){
            $pag = $this->getRequest()->getParam('pag');
            $limit = $this->getRequest()->getParam('limit');
            $orders = $this->getRequest()->getParam('orders');

            if(empty($orders)){
                $orders = array();
            }

            $orders = json_decode($orders , true);
            //, $orders

            $ex = new Seaway_Tree_Model_Exhibition();
            $childrens = $ex->getData($pag , $limit , $orders);

        }

        header('Content-Type:application/json');
        echo json_encode($childrens, true);
        die;



    }



}