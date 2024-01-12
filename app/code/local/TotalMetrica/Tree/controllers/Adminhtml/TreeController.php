
<?php
class TotalMetrica_Tree_Adminhtml_TreeController extends Mage_Adminhtml_Controller_Action {

   const PATH_FILE_IMG_EXPERIENCE = '/adminhtml/default/default/images/experience';

    public $atributo = "";

	public function indexAction(){

		$this->loadLayout();
        //$retorno = Mage::getModel("tree/api")->dadosTesteImplantacao();
        //$retorno = Mage::helper("tree/data")->carregarProdutos(60);

        $retorno = Mage::helper("tree/data")->cadastrarProduto('13595');

        Mage::log(var_export($retorno, true), null, 'importacao_tree.log', true);

        echo "<pre>";
        var_dump($retorno);

        foreach ($retorno["Dados"]["TProduto"] as $key => $value){

            var_dump($value);

        }
        //Mage::getModel("tree/produtos")->insertProdutoSimples($retorno);


        $this->renderLayout();

	}

	public function clientesAction(){

        //for ($i=1; $i <= 100; $i++){
	    //  echo $i . "<br>";
        echo "<pre>";
	      $retorno = Mage::helper("tree/clientes")->carregarClientes(10);
        var_dump($retorno);

	      $teste = Mage::getModel('tree/clientes')->insertClientes($retorno);


	      var_dump($teste);

	    //  if($i == 20){
        //      die('asd');
        //  }

        //}


    }

    public function estoqueAction(){

        $retorno = Mage::helper("tree/clientes")->carregarClientes(10);

    }




}