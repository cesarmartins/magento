<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 14/11/2017
 * Time: 18:35
 */

class Seaway_Experience_FreeboardshortforfriendsController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch(){

    }

    public function selogarAction(){

        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function indexAction(){

        Mage::getModel('experience/experience')->insertUserIp();
        Mage::getModel('customer/session')->logout();

        $customerId = null;
        $cid = null;
        $this->instagram = $this->getRequest()->getParam('id');

        if(!empty($this->instagram)){

            Mage::log("instagram " . $this->instagram , null , 'experienceindex.log',true);
            $retornoTree = Mage::getModel("tree/tree")->getTreeByInsta($this->instagram);

            Mage::log("$retornoTree " . var_export($retornoTree, true) , null , 'experienceindex.log',true);

            if(!empty($retornoTree["customer_id"])){

                $customerSession = Mage::getSingleton('customer/session');

                $customer = Mage::getModel('customer/customer')->load($retornoTree["customer_id"]);
                $customerSession->setCustomerAsLoggedIn($customer);

                if(!empty($this->getCustomerLoggedIn())){
                    $customerId = $customer->getEntityId();
                }
            }

            if($retornoTree){
                $username = $this->instagram;

                if(!empty($retornoTree["customer_id"])){
                    $cid = $retornoTree["customer_id"];
                    Mage::getSingleton('customer/session')->setData('firstname', $retornoTree["nome"]);
                    Mage::getSingleton('customer/session')->setData('retornotree', $retornoTree);
                    Mage::getSingleton('customer/session')->setData('instagram', $username);
                    Mage::getSingleton('customer/session')->setData('cid', $cid);
                }else{
                    $this->_redirect('/');
                }
            }else{
                $this->_redirect('/');
            }
        }

        try{

            if(!empty($retornoTree["customer_id"])) {
                $this->loadLayout();
                $tree = array();
                // deadline set first access on app
                Mage::getModel('tree/deadline')->setFirstAccess();
                Mage::getModel('tree/deadline')->setInvitedDeadline();

                $tree = Mage::getModel('tree/app')->getTreeList($cid);

                Mage::getModel('customer/session')->setData('mostrarSucessMessage', false);

                Mage::getModel('track/track')->trackLog($this->instagram . ' - friend_list', null);

                $this->getLayout()->getBlock('experience')->setTree($tree)->setConstumer($retornoTree);
                Mage::getModel('track/track')->trackLog(null, $customerId);
                $this->renderLayout();
            }else{
                $this->_redirect('/');
            }

        }catch (Exception $e){

            if($e->getCode() == -3){
            }
            echo $e->getMessage();
            die;
        }

    }

    private function getCustomerLoggedIn(){
        $objCustomerLogin = Mage::getSingleton('customer/session');
        $customer = NULL;
        if($objCustomerLogin->isLoggedIn()){
            $customer = $objCustomerLogin->getCustomer();
        }
        return $customer;
    }

}