
<?php
class TotalMetrica_Tree_Adminhtml_TreeController extends Mage_Adminhtml_Controller_Action {

   const PATH_FILE_IMG_EXPERIENCE = '/adminhtml/default/default/images/experience';

    public $atributo = "";

	public function indexAction(){

		$this->loadLayout();
        $tree = new TotalMetrica_Tree_Model_Tree();
        $uploader = new Mage_Uploader_Block_Single();
        $uploader->getUploaderConfig()->setTarget("tree/adminhtml_tree/saveFuture");
        //$uploader->_template = "tree/index.html";
		//$tree->atualizarTree();
		$childrens = $tree->getChildrens();
		//$this->getLayout()->getAllBlocks()

		$this->getLayout()->getBlock('treeindex')->setData("uploader", $uploader);
		$this->renderLayout();

	}

    public function saveimportacaoAction() {

	    die('chegou aqui');

        $teste = $_POST;

        $file = $_FILES;

        $cesar = $teste . $file;

    }

	public function saveFutureAction(){

        //if(isset($_FILES['arquivo']['name']) && $_FILES['arquivo']['name'] != '') {
            try {

                    $produtos = $this->insertProdutoSimples($_POST);

                    $valores['sucess'] = true;
                    $valores['produtos'] = $produtos;

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('postcode')->__('Item was successfully saved')
                );

            } catch (Exception $e) {
                $valores['sucess'] = false;
                $valores['msn'] = $e->getMessage();
            }
        //}

        $this->_redirect('*/*/');
        return;

/*		header('Content-Type:application/json');
		echo json_encode($valores, true);
		die;*/


	}

    public function getCsvData($file){
        $csvObject = new Varien_File_Csv();
        try {
            return $csvObject->getData($file);
        } catch (Exception $e) {
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
            return false;
        }

    }

    public function lerarquivocsvAction(){

        $arquivo = Mage::getSingleton("core/session")->getData('arquivo');

        $csv = $this->getCsvData($arquivo);

        $this->insertProducts($csv);

    }

    public function insertProducts($csv){

	    $contador = 0;
	    $configuravel = "";
        $arrayProdutos = array();
        foreach ($csv as $lines => $linha) {

            if($linha[0] == "SKU" || $linha[0] == "sku") {



                //continue;
            }else{

                if($linha[4] == 1){
                    $tipo = $linha[3];
                    if($tipo == 'configurable'){
                        $arrayProdutos[$contador][] = $linha;
                    }else{
                        $arrayProdutos[$contador][] = $linha;
                    }
                    $contador++;
                }else{
                    $arrayProdutos[$contador][] = $linha;
                    $contador++;
                }
            }
        }
        $produtoConfiguravel = '';
        foreach ($arrayProdutos as $rows => $linhaProdtudos) {

            $produtoSimples = array();
            foreach ($linhaProdtudos as $lines => $linha) {

                if($linha[4] == 0){
                    $simples = true;
                    $produtoSimplesId = $this->insertProdutoSimples($linha, $produtoConfiguravel);
                    echo $produtoSimplesId . "-" . $linha[18] . " - OK<br>";
                } else {
                    $simples = false;
                    $tipo = $linha[3];

                    if ($tipo == 'configurable') {
                        $produtoConfiguravel = $this->insertProdutoConfiguravel($linha);
                        echo $linha[18] . " - OK<br>";
                    } else {
                        $produtoSimplesId = $this->insertProdutoSimples($linha, $produtoConfiguravel);
                        $produtoSimples[$produtoSimplesId] = array( //['920'] = id of a simple product associated with this configurable
                            '0' => array(
                                'label' => '', //attribute label
                                'attribute_id' => '92', //attribute ID of attribute 'color' in my store
                                'value_index' => '', //value of 'Green' index of the attribute 'color'
                                'is_percent' => '0', //fixed/percent price for this option
                                'pricing_value' => '' //value for the pricing
                            )
                        );
                        echo $linha[18] . " - OK<br>";
                    }
                }
            }

            if(!$simples){

                $configProduct = Mage::getModel('catalog/product')->load($produtoConfiguravel);
                //$configProduct->getTypeInstance()->setUsedProductAttributeIds(array(92)); //attribute ID of attribute 'color' in my store
                //$configurableAttributesData = $configProduct->getTypeInstance()->getConfigurableAttributesAsArray();

                //$configProduct->setCanSaveConfigurableAttributes(true);
                //$configProduct->setConfigurableAttributesData($configurableAttributesData);

                /*$configurableProductsData = array();
                $configurableProductsData['920'] = array( //['920'] = id of a simple product associated with this configurable
                    '0' => array(
                        'label' => 'Green', //attribute label
                        'attribute_id' => '92', //attribute ID of attribute 'color' in my store
                        'value_index' => '24', //value of 'Green' index of the attribute 'color'
                        'is_percent' => '0', //fixed/percent price for this option
                        'pricing_value' => '21' //value for the pricing
                    )
                );*/
                $configProduct->setConfigurableProductsData($produtoSimples);
                $configProduct->save();
            }

        }
        return $arrayProdutos;
    }

    public function insertProdutoConfiguravel($linha){

        $sku        = $linha[0];
        $descrico   = $linha[1];
        $preco      = $linha[5];

        $product = Mage::getModel('catalog/product');

        try{
            $product
                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                ->setAttributeSetId(12) //ID of a attribute set named 'default'
                ->setTypeId('configurable')
                //->setTypeId('simple') //product type
                ->setCreatedAt(strtotime('now')) //product creation time
//    ->setUpdatedAt(strtotime('now')) //product update time

                ->setSku($sku) //SKU
                ->setName($descrico) //product name
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(0) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                ->setManufacturer(28) //manufacturer id

                ->setPrice($preco) //price in form 11.22

                ->setMetaTitle('')
                ->setMetaKeyword('')
                ->setMetaDescription('')

                ->setDescription($descrico)
                ->setShortDescription($descrico)

                ->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'is_in_stock' => 1, //Stock Availability
                    )
                );

            $configProduct = $product;
            $configProduct->setCategoryIds(array(3, 10)); //assign product to categories
            $configProduct->getTypeInstance()->setUsedProductAttributeIds(array(149)); //attribute ID of attribute 'color' in my store
            $configurableAttributesData = $configProduct->getTypeInstance()->getConfigurableAttributesAsArray();

            $configProduct->setCanSaveConfigurableAttributes(true);
            $configProduct->setConfigurableAttributesData($configurableAttributesData);

            $configProduct->save();

            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSort('created_at', 'desc');
            $collection->getSelect()->limit(1);

            $latestItemId = $collection->getLastItem()->getId();

            return $latestItemId;

        }catch(Exception $e){
            Mage::log($e->getMessage());
        }

    }
    public function getAtributo(){
	    return $this->atributo;
    }
    public function setAtributo($attId){
        $this->atributo = $attId;
    }

    public function getCategories($nomeCategoria){

        $categorias = explode("/", $nomeCategoria);
	    $retorno = array();
        foreach($categorias as $categoryName){

            $category = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name', $categoryName)
                ->getFirstItem(); // The parent category
            array_push($retorno, $category->getId());

        }
	    return $retorno;
    }

    function getOptions($linha, $tipos){

        $retorno = array();
	    foreach ($tipos as $tipo){
            if(!empty($linha[$tipo])) {
                $array = array(
                    array(
                        'title' => $linha[$tipo],
                        'price' => 0,
                        'price_type' => 'fixed',
                        'sort_order' => '1'
                    )
                );
            }
            $retorno = array_merge($retorno, $array);
        }
        return $retorno;
    }

    public function getDescricao($linha){

	    $html = "<p>";

	    if($linha["entrada_opc1"]){

            $html .= "<span style=\"font-size: large;\"><strong>Entrada</strong></span></p>
                        <ul>
                            <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 1:</strong>&nbsp;" . $linha["entrada_opc1"] . ".</span></li>
                            <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 2:</strong>&nbsp;" . $linha["entrada_opc1"] . ".</span></li>
                        </ul><p></p>";

        }
        if($linha["principal_opc1"]){

            $html .= "<p><span style=\"font-size: large;\"><strong>Prato Principal</strong></span></p>
                        <ul>
                        <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 1:</strong>" . $linha["principal_opc1"] . ".</span></li>
                        <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 2:</strong>" . $linha["principal_opc2"] . ".</span></li>
                        </ul>
                        <p></p>";
        }
        if($linha["sobremesa_opc1"]){

            $html .= "<p><span style=\"font-size: large;\"><strong>Prato Principal</strong></span></p>
                        <ul>
                        <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 1:</strong>" . $linha["sobremesa_opc1"] . ".</span></li>
                        <li><span style=\"font-size: medium;\"><strong>Op&ccedil;&atilde;o 2:</strong>" . $linha["sobremesa_opc1"] . ".</span></li>
                        </ul>
                        <p></p>";
        }
        $html .= "</p>";
        return $html;
    }

    public function getSku(){

	    $obj = Mage::getModel('catalog/product');
        $generateSku = 'mrwid' . date("dmhs");
        if(!$obj->getIdBySku($generateSku)){
            $sku = $generateSku;
        }else{
            $this->getSku();
        }
        return $sku;
    }

    public function insertProdutoSimples($linha){

	    try {
            $linha = $linha;

            if(!empty($linha["entrada_opc1"])){

                $option = array(
                    'title' => 'Entrada',
                    'type' => 'radio', // could be drop_down ,checkbox , multiple
                    'is_require' => 1,
                    'sort_order' => 1,
                    'values' => $this->getOptions($linha, array("entrada_opc1", "entrada_opc2"))
                );

            }
            if(!empty($linha["principal_opc1"])){

                $optionPrincipal = array(
                    'title' => 'Principal',
                    'type' => 'radio', // could be drop_down ,checkbox , multiple
                    'is_require' => 1,
                    'sort_order' => 2,
                    'values' => $this->getOptions($linha, array("principal_opc1", "principal_opc2"))
                );
            }
            if(!empty($linha["sobremesa_opc1"])){

                $optionSobremesa = array(
                    'title' => 'Sobremesa',
                    'type' => 'radio', // could be drop_down ,checkbox , multiple
                    'is_require' => 1,
                    'sort_order' => 3,
                    'values' => $this->getOptions($linha, array("sobremesa_opc1", "sobremesa_opc2"))
                );
            }

            if(!empty($linha["doacao"])){

                $optionDoacao = array(
                    'title' => 'Doação Delivery Solidário',
                    'type' => 'checkbox', // could be drop_down ,checkbox , multiple
                    'is_require' => 1,
                    'sort_order' => 4,
                    'values' => $array = array(
                        array(
                            'title' => 'Doação Delivery Solidário',
                            'price' => '10.00',
                            'price_type' => 'fixed',
                            'sort_order' => '1'
                        )
                    )
                );
            }

            $description = $this->getDescricao($linha);
            $sku = $this->getSku();

            $created_at = strtotime('now');
            $product = Mage::getModel('catalog/product');

            $product
                ->setAttributeSetId(4) //Grupo de atributos
                ->setTypeId('simple') //product type
                ->setCreatedAt($created_at) //product creation time
                ->setSku($sku) //SKU
                ->setName('MENU RESTAURANT WEEK') //product name
                ->setWeight('0.100')
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(0) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(4) //catalog and search visibility
                ->setPrice('49.90') //price in form 11.22
                ->setDescription($description)
                ->setShortDescription('MENU RESTAURANT WEEK')
                ->setDescricaogoogleshopping('MENU RESTAURANT WEEK - ' . Mage::getBaseUrl())
                ->setUrlKey('menu-restaurant-week-' . date("his"))

                ->setManufacturer() //manufacturer id
                ->setColor()
                ->setSpecialPrice() //special price in form 11.22
                ->setSpecialFromDate() //special price from (MM-DD-YYYY)
                ->setSpecialToDate() //special price to (MM-DD-YYYY)
                ->setMetaTitle()
                ->setMetaKeyword()
                ->setMetaDescription()
                ->setGoogleshopping_exclude('Não')
                ->setEan('03053290')

                ->setStockData(array(
                        'use_config_manage_stock' => 1, //'Use config settings' checkbox
                        'manage_stock'=> 0, //manage stock
                        'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                        'max_sale_qty'=>10, //Maximum Qty Allowed in Shopping Cart
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => 10000 //qty
                    )
                );
            //$product->save();

            $optionInstance = $product->getOptionInstance()->unsetOptions();
            $product->setHasOptions(1);
            $optionInstance->addOption($option);
            $optionInstance->setProduct($product);

            //$optionInstance = $product->getOptionInstance()->unsetOptions();
            $product->setHasOptions(1);
            $optionInstance->addOption($optionPrincipal);
            $optionInstance->setProduct($product);

            //$optionInstance = $product->getOptionInstance()->unsetOptions();
            $product->setHasOptions(1);
            $optionInstance->addOption($optionSobremesa);
            $optionInstance->setProduct($product);

            if($linha["doacao"] == "true"){
                $product->setHasOptions(1);
                $optionInstance->addOption($optionDoacao);
                $optionInstance->setProduct($product);
            }

            $product->save();
            unset($product);

            return true;
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }

    }

}