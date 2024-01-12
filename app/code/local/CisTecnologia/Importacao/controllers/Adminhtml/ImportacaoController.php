
<?php
class CisTecnologia_Calculadora_Adminhtml_CalculadoraController extends Mage_Adminhtml_Controller_Action {

	public function indexAction(){

	    $this->loadLayout();

	    $importacao = new Cistecnologia_Importacao_Model_Api();
	    Mage::getModel("importacao/api")->dadosTesteImplantacao();
        //$importacao->doCall();

        //$tree = new TotalMetrica_Tree_Model_Tree();
        //$uploader = new Mage_Uploader_Block_Single();
        //$uploader->getUploaderConfig()->setTarget("tree/adminhtml_tree/saveFuture");
        //$uploader->_template = "tree/index.html";
		//$tree->atualizarTree();
		//$childrens = $tree->getChildrens();
		//$this->getLayout()->getAllBlocks()

		//$this->getLayout()->getBlock('treeindex')->setData("uploader", $uploader);
		$this->renderLayout();

	}

    public function saveimportacaoAction() {

    }

	public function saveFutureAction(){

	}

}