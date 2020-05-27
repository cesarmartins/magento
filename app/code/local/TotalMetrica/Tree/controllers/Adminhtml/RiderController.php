<?php
class Seaway_Opinioes_Adminhtml_RiderController extends Mage_Adminhtml_Controller_Action {

     public function  indexAction(){
         $this->loadLayout();
         $this->renderLayout();
     }


    public function salvarAction(){

        $msg = array('msg' => 'nao Cadastrado' );
        /*if($this->getRequest()->isPost()){
            $nome= $this->getRequest()->getParam('nome');
            if(!empty($nome)) {
                $model = new Seaway_Opinioes_Model_Rider();
                $isCreate = $model->salvar($nome);
                if($isCreate){
                    $msg = array('msg' => 'Cadastro realizado com sucesso!' );
                }
            }
        }*/
        header('Content-Type:application/json');
        echo json_encode($msg, true);
        die;

    }

}
