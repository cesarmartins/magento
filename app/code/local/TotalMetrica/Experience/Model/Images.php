<?php
class TotalMetrica_Experience_Model_Images
{

    private  $tree              = null;

    private  $dir               = null;
    private  $newDirSeaway      = null;
    private  $newDirParticipant = null;

    private  $dirDirect               = null;
    private  $newDirSeawayDirect      = null;
    private  $newDirParticipantDirect = null;

    private  $tempDir           = null;
    private  $pathBg            = null;

    private  $font              = null;
    private  $fontBold          = null;
    private  $fontSize          = null;

    private $sysBoardshorts      = array();
    private $userBoardshorts     = array();
    private $colors              = array();
    private $urlParticipant      = null;

    private $imageTemplate229x403  = null;
    private $imageTemplate600x197  = null;

    private $imageTemplate229x403Save  = null;
    private $imageTemplate600x197Save  = null;

    private $imageTemplate600x403Direct = null ;
    private $imageTemplate229x403Direct = null ;

    private $imageTemplate600x403DirectSave = null ;
    private $imageTemplate229x403DirectSave = null ;



    private $instagram = null;
    private $insta = null;
    private $text = null;

    private $seawayTemplates  = array();
    private $currentDir = null;

    public function __construct($params = array()){

        if(!empty($params['tree_id'])){
            $this->tree = Mage::getModel('tree/tree')->getTreeById($params['tree_id']);
        }
        if(!empty($params['current_dir'])){
            $this->currentDir = $params['current_dir'];
        }else{
            $this->currentDir =  'instagramdirect';
        }
    }


    public function configIni(){

        $this->dir               = Mage::getBaseDir('skin').'/frontend/seaway/iphone/images/experience/promoscore/instagrampost/';
        $this->tempDir           = Mage::getBaseDir('skin').'/frontend/seaway/iphone/images/experience/promoscore/temp/';

        $this->fontSize          = 18.5;
        $this->font              = Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-regular.ttf";
        $this->fontBold          = Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-bold.ttf";
        $this->newDirSeaway      = $this->dir.'generated/seaway/600x600/';
        $this->newDirParticipant = $this->dir.'generated/participant/';
        $this->urlParticipant    = 'frontend/seaway/iphone/images/experience/promoscore/instagrampost/generated/participant/';


        $this->imageTemplate600x197  = $this->dir . 'images-template/600x197/';
        $this->imageTemplate229x403  = $this->dir . 'images-template/229x403/';

        $this->imageTemplate600x197Save  = $this->dir . 'generated/seaway/600x197/';
        $this->imageTemplate229x403Save  = $this->dir . 'generated/seaway/229x403/';

        $this->instagram = $this->tree['instagram'];
        $this->insta = str_replace('@', '' , $this->instagram);
        $this->text = "({$this->insta})+n Seaway, o Boardshort Equipamento (#oMaisLevedoMundo)+22207b\n(#SeawaymeuBoardshortFavorito)+22207b";





        $this->dirDirect                   = Mage::getBaseDir('skin').'/frontend/seaway/iphone/images/experience/promoscore/'.$this->currentDir.'/';
        $this->newDirSeawayDirect          = $this->dirDirect.'generated/seaway/';
        $this->newDirParticipantDirect     = $this->dirDirect.'generated/participant/';
        $this->urlParticipantDirect        = "frontend/seaway/iphone/images/experience/promoscore/{$this->currentDir}/generated/participant/";

        $this->imageTemplate600x403Direct  = $this->dirDirect . 'images-template/600x403/';
        $this->imageTemplate229x403Direct  = $this->dirDirect . 'images-template/229x403/';

        $this->imageTemplate600x403DirectSave  = $this->dirDirect . 'generated/seaway/600x403/';
        $this->imageTemplate229x403DirectSave  = $this->dirDirect . 'generated/seaway/229x403/';




        $this->colors             = $this->getOptionColors();
        $this->sysBoardshorts     = $this->selectBoardshort();
        $this->userBoardshorts    = $this->getUserBoardshort();


        return $this;

    }

    public function generateAllTemplatesDirect(){
        $this->templateTopOneDirect();
        $this->templateTopTwoDirect();
        $this->templateTopTreeDirect();

    }

    public function templateTopTreeDirect(){


        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];
        $nameImage 		= "$instagram-template-seaway-3-result.jpg";

        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->imageTemplate600x403DirectSave.$nameImage;

        if(!file_exists($imageSavePath)){

            list($boardshort1 , $boardshort2 ) = $this->getBoardshort(2);

            $pathBoardshort1 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort1;
            $pathBoardshort2 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort2;

            $this->pathBg = $this->imageTemplate600x403Direct.'foto-03-v1.jpg';

            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 403)
                ->marca($this->pathBg,'topo', 'esquerda')
                ->marca($pathBoardshort1, 33  ,  55  , 100 ,  275 , null)
                ->marca($pathBoardshort2, 290  , 55  , 100 ,  275 , null)
                ->grava($imageSavePath);
        }

    }

    public function templateTopTwoDirect(){


        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];
        $nameImage 		= "$instagram-template-seaway-2-result.jpg";

        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->imageTemplate600x403DirectSave.$nameImage;

        if(!file_exists($imageSavePath)){

            list($boardshort1) = $this->getBoardshort(1);

            $pathBoardshort1 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort1;


            $this->pathBg = $this->imageTemplate600x403Direct.'foto-02-v1.jpg';

            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 403)
                ->marca($this->pathBg,'topo', 'esquerda')
                ->marca($pathBoardshort1, 411, 194, 100, 150, null)
                ->grava($imageSavePath);
            }

    }

    public function templateTopOneDirect(){


        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];
        $nameImage 		= "$instagram-template-1-result.jpg";

        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->imageTemplate229x403DirectSave.$nameImage;

        if(!file_exists($imageSavePath)){

            list($boardshort1 ) = $this->getBoardshort(1);

            $pathBoardshort1 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort1;

            $this->pathBg = $this->imageTemplate229x403Direct.'foto-01-v1.jpg';

            $canvas
                ->hexa('#fff')
                ->novaImagem(229, 403)
                ->marca($this->pathBg,'topo', 'esquerda')
                ->marca($pathBoardshort1, 35 , 194, 100, 150, null)
                ->grava($imageSavePath);
        }

    }

    public function generateAllTemplates(){

        $this->templateOneGenerate();
        $this->templateTwoGenerate();
        $this->templateTreeGenerate();
        $this->templateFourGenerate();
        $this->generateAllFooterImgs();

    }

    public function templateOneGenerate(){

        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];
        $nameImage 	= "$instagram-template-seaway-1-result.jpg";
        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->newDirSeaway.$nameImage;

        if(!file_exists($imageSavePath)) {

            $text = $this->text;

            $this->pathBg = $this->dir . 'images-template/600x600/foto-01-v1.jpg';


            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 600)
                ->marca($this->pathBg, 'topo', 'esquerda')
                ->hexa('#000')
                ->textoModificado($text, 13, 522, $this->fontSize, $this->font, $this->fontBold, 570, false)
                ->grava($imageSavePath);

        }

        $this->seawayTemplates[] = $nameImage;

    }

    public function templateTwoGenerate(){

        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];


        $nameImage 		= "$instagram-template-seaway-2-result.jpg";
        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->newDirSeaway.$nameImage;

        if(!file_exists($imageSavePath)) {

            $text = $this->text;

            list($boardshort) = $this->getBoardshort(1);


         /*   var_dump($boardshort);
            die;*/

            $pathBoardshort = Mage::getBaseUrl('media') . 'catalog/product' . $boardshort;

            $this->pathBg = $this->dir . 'images-template/600x600/foto-02-v1.jpg';




            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 600)
                ->marca($this->pathBg, 'topo', 'esquerda')
                ->hexa('#000')
                ->textoModificado($text, 13, 522, $this->fontSize, $this->font, $this->fontBold, 570, false)
                ->marca($pathBoardshort, 411, 194, 100, 150, null)
                ->grava($imageSavePath);
        }

    }

    public function templateTreeGenerate(){

        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();


        $instagram  = $this->tree['instagram'];
        $nameImage 		= "$instagram-template-seaway-3-result.jpg";

        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->newDirSeaway.$nameImage;

        if(!file_exists($imageSavePath)){


            $text = $this->text;


           
            list($boardshort1 , $boardshort2 ) = $this->getBoardshort(2);

            $pathBoardshort1 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort1;
            $pathBoardshort2 =  Mage::getBaseUrl('media') . 'catalog/product'. $boardshort2;

            $this->pathBg = $this->dir . 'images-template/600x600/foto-03-v1.jpg';

            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 600)
                ->marca($this->pathBg,'topo', 'esquerda')
                ->hexa('#000')
                ->textoModificado($text , 13 , 522 , $this->fontSize , $this->font, $this->fontBold, 570, false)
                ->marca($pathBoardshort1, 33  ,  55  , 100 ,  275 , null)
                ->marca($pathBoardshort2, 290  , 55  , 100 ,  275 , null)
                ->grava($imageSavePath);


        }


    }

    public function templateFourGenerate(){

        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();

        $instagram  = $this->tree['instagram'];
        $nameImage 		= "$instagram-template-seaway-4-result.jpg";
        $instagram = str_replace('@','', $instagram);

        $imageSavePath = $this->newDirSeaway.$nameImage;

        if(!file_exists($imageSavePath)) {

            list($boardshort1) = $this->getBoardshort(1);

            $pathBoardshort1 = Mage::getBaseUrl('media') . 'catalog/product' . $boardshort1;
            $this->pathBg = $this->dir . 'images-template/600x600/foto-04-v1.jpg';

            $canvas
                ->novaImagem(600, 600)
                ->marca($this->pathBg, 'topo', 'esquerda')
                ->marca($pathBoardshort1, 400, 214, 100, 200, null)
                ->grava($imageSavePath);
        }
    }

    public function getBoardshort($qty = 1){

        $result = array();

        if($qty == 1){
            if(!empty($this->userBoardshorts)){
                $result = $this->userBoardshorts;
            }else{
                reset($this->sysBoardshorts);
                $result[] = current($this->sysBoardshorts);
            }
        }

        if($qty > 1 ){
            $result = $this->sysBoardshorts;
        }


        return $result;
    }

    public function getUserBoardshort(){

        $customerId  = $this->tree['customer_id'];
        $result = array();
        if(!empty($customerId)){

            $img = $this->getLastItemFreePaymentUser($customerId);
            if(!empty($img)){
                $result[] = $img;
            }


        }
       return $result;

    }

    public function selectBoardshort(){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT sku FROM t_select_boardshorts ";
        $rows = $_conn->fetchAll($sql);
        $_conn->closeConnection();

        $imgs = array();
        foreach($rows as $row ){

            $imgs[] = $this->getImgFatherBySku($row['sku']);
        }



        return $imgs;
    }

    public function getLastItemFreePaymentUser($customerId){


        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->getSelect()->join(
            array('p' => $orders->getResource()->getTable('sales/order_payment')),
            'p.parent_id = main_table.entity_id',
            array()
        );
        //
        $orders->addFieldToFilter('method','freepayment')
            ->addFieldToFilter('status', array('complete' , 'processing'))
            ->addAttributeToFilter('customer_id' , array('eq' => $customerId))
            ->setOrder('created_at', 'desc')
            ->setPageSize(1)
            ->setCurPage(1);


        $sku = "";

        foreach($orders as $order){
            $obj = null ;
            $obj  = Mage::getModel('sales/order')->load($order->getData('entity_id'));
            $itens  = array();
            $itens  = $obj->getAllItems();
            foreach($itens as $item){
                $sku =  $item->getData('sku');
            }
        }


        $img =  $this->getImgFatherBySku($sku);


        return $img;


    }

    public function getImgFatherBySku($sku, $teste = false){

        $img = "";
        if(!empty($sku)){


            $product = Mage::getModel('catalog/product')->loadByAttribute( 'sku' , $sku);

            $color   = $product->getAttributeText('color');



            if(!empty($product->getEntityId())){

                 $skuParent  =  substr($sku , 0 , 4);
                 $productFather  = Mage::getModel('catalog/product')->loadByAttribute( 'sku' , $skuParent);

                 $productFather->load('media_gallery');
                 $mediaGallery = $productFather->getMediaGalleryImages();



                 if(!empty($mediaGallery)) {
                     $images  =  $mediaGallery->getItems();

                     $lastsPosition = array_slice($images, -2);

                     foreach ($lastsPosition as $last) {
                        $lastData = $last->getData();
                         if (!empty($lastData['value_id']) && !empty($lastData['file'])) {

                             if (strcasecmp($lastData['label'], $color) == 0 ) {
                                 $img = $lastData['file'];
                                 break;
                             }

                         }

                     }
                 }
            }

        }

        return $img;
    }

    public function verifyColor($valueId  , $color){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql  = "SELECT * FROM catalog_product_entity_media_gallery WHERE value_id = :id" ;
        $data = array('id'=>$valueId);
        $rows = $_conn->fetchRow($sql,$data);
        $_conn->closeConnection();




        $optIdColor = $rows['option_color_id'];
        $result = false;

        if(empty($this->colors)){
            $this->colors = $this->getOptionColors();
        }


        if(!empty($this->colors[$optIdColor])){
            if(strcasecmp($this->colors[$optIdColor] , $color) == 0){
                $result = true;
            }
        }
        return $result;
    }

    public function getOptionColors(){


        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT v.option_id, v.value FROM `eav_attribute_option` o
                                inner join `eav_attribute_option_value` v
                                ON o.option_id = v.option_id AND v.store_id = 0
                                where o.attribute_id = 92 ";
        $rows = $_conn->fetchAll($sql);
        $_conn->closeConnection();
        $result = array();
        foreach($rows  as $row){
            $result[$row['option_id']] = $row['value'];
        }

        return $result;


    }

    public function createTempImg($filename , $image ,$mount){

        list($type, $image) = explode(';', $image);
        list($type, $image)  = explode(',', $image);

        $instagram = $this->tree['instagram'];

        $image = base64_decode($image);
        $imageName = $instagram.$filename.'.png';
        $path = $this->tempDir.$imageName;
        $value = file_put_contents($path, $image);

        $data = $path;
        // monta a imagem
        if(is_int($value)){
            if($mount === "true"){
                $data = $this->generateMerge($imageName);
            }
        }
        return $data;

    }

    public function generateMerge($imageName){
        try{


            $imageSavePath= $this->newDirParticipant;

            $data = array();
            $data['status'] = false;

            $instagram = $this->tree['instagram'];

            list( $insta , $str , $template , $position) = explode('-', $imageName);

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();
            $time	 = time();
            $nameImage 		= "$instagram-template-$template-result_$time.jpg";

            $this->pathBgText = $this->imageTemplate600x197Save . "$instagram-template-1.jpg";

            //\600x197
            $imgCenter = "";
            $imgLeft   = "";
            $imgRight  = "";

            if($template == '1') {

                $imgCenter  	= $this->tempDir."$instagram-$str-$template-center.png";
                if(file_exists($imgCenter)) {
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);

                   unlink($imgCenter);

                }

            }else if($template == '2') {


                $this->pathBgText =  $this->imageTemplate600x197Save."$instagram-template-2.jpg";
                $imgCenter  	= $this->tempDir."$instagram-$str-$template-center.png";


                list($boardshort) = $this->getBoardshort(1);

                $pathBoardshort = Mage::getBaseUrl('media') . 'catalog/product' . $boardshort;


                if(file_exists($imgCenter)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);

                    unlink($imgCenter);
                }


            }else if($template == '3'){

                $imgLeft  		= $this->tempDir."$instagram-$str-$template-left.png";
                $imgRight 		= $this->imageTemplate229x403Save."$instagram-template-1.jpg";

                if(file_exists($imgLeft) && file_exists($imgRight)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->marca($imgLeft,'topo','esquerda')
                        ->marca($imgRight,'topo','direita')
                        ->grava($imageSavePath.$nameImage);

                    unlink($imgLeft);
                }


            }

            $url = Mage::getBaseUrl('skin'). $this->urlParticipant.$nameImage;
            $data['status'] = true;
            $data['msg'] 	= 'sucesso';
            $data['data'] 	= $url;


        }catch(Exception $e){
            Mage::log($e->getMessage() , null , 'log_upload_seaway_generateMerge_app.log' ,true );
            $data['msg'] 	= 'Internal Server Error.';
            $data['data'] 	= '';
        }

        return $data;

    }

    public function generateAllFooterImgs(){
        //// -- template 1
        $this->footerImg1();
        //// -- template 2
        $this->footerImg2();
        //// -- template 3
        $this->footerImg3();

    }

    public function  footerImg1(){

        $nameImage 		= "{$this->instagram}-template-1.jpg";
        $imageTemplate600x197One  = $this->imageTemplate600x197.'foto-01-v1.jpg';


        if(!file_exists($this->imageTemplate600x197Save.$nameImage)) {

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();

            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 197)
                ->hexa('#000')
                ->marca($imageTemplate600x197One, 'topo', 'esquerda')
                ->textoModificado($this->text, 13, 121, $this->fontSize, $this->font, $this->fontBold, 570, false)
                ->grava($this->imageTemplate600x197Save . $nameImage);
        }


    }

    public function  footerImg2(){

        $nameImage 		= "{$this->instagram}-template-2.jpg";
        $imageTemplate600x197Two  = $this->imageTemplate600x197.'foto-02-v1.jpg';

        if(!file_exists($this->imageTemplate600x197Save.$nameImage)) {
            list($boardshort) = $this->getBoardshort(1);
            $pathBoardshort = Mage::getBaseUrl('media') . 'catalog/product' . $boardshort;

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();

            $canvas
                ->hexa('#fff')
                ->novaImagem(600, 197)
                ->hexa('#000')
                ->marca($imageTemplate600x197Two, 'topo', 'esquerda')
                ->marca($pathBoardshort, 424, 22, 100, 150, null)
                ->grava($this->imageTemplate600x197Save . $nameImage);
        }


    }

    public function  footerImg3(){


        $nameImage 		= "{$this->instagram}-template-1.jpg";
        $imageTemplate229x403One  = $this->imageTemplate229x403.'foto-01-v1.jpg';
        if(!file_exists($this->imageTemplate229x403Save.$nameImage)) {

            list($boardshort) = $this->getBoardshort(1);
            $pathBoardshort = Mage::getBaseUrl('media') . 'catalog/product' . $boardshort;

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();

            $canvas
                ->hexa('#fff')
                ->novaImagem(229, 403)
                ->marca($imageTemplate229x403One, 'topo', 'esquerda')
                ->marca($pathBoardshort, 30, 184, 100, 160, null)
                ->grava($this->imageTemplate229x403Save . $nameImage);

        }
    }

    public function createTempImgDirect($filename , $image ,$mount , $text , $fontType){

        list($type, $image) = explode(';', $image);
        list($type, $image)  = explode(',', $image);

        $instagram = $this->tree['instagram'];

        $image = base64_decode($image);
        $imageName = $instagram.$filename.'.png';
        $path = $this->tempDir.$imageName;
        $value = file_put_contents($path, $image);

        $data = $path;
        // monta a imagem
        if(is_int($value)){
            if($mount === "true"){
                $data = $this->generateMergeDirect($imageName , $text, $fontType);
            }
        }
        return $data;

    }

    public function generateMergeDirect($imageName ,$text , $fontType ){
        try{


            $imageSavePath= $this->newDirParticipantDirect;

            $data = array();
            $data['status'] = false;

            $instagram = $this->tree['instagram'];

            list( $insta , $str , $template , $position) = explode('-', $imageName);

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();
            $time	 = time();
            $nameImage 		= "$instagram-template-$template-result_$time.jpg";

            $this->pathBgText = $this->dirDirect . "images-template/600x197/foto-01-v1.jpg";

            //\600x197
            $imgCenter = "";
            $imgLeft   = "";
            $imgRight  = "";

            $font = $this->font;
            if(strcasecmp($fontType , 'bold' ) == 0){
                $font = $this->fontBold;
            }

            if($template == '1') {

                $imgCenter  	= $this->tempDir."$instagram-$str-$template-center.png";
                if(file_exists($imgCenter)) {
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        //->texto( $text , 10 , 245 ,$this->fontSize  , $this->font  )
                        ->textoModificado($text ,  30 , 445 ,  $this->fontSize , $font, $this->fontBold, 520 , false , 7)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);

                    unlink($imgCenter);

                }

            }else if($template == '2') {

                $imgLeft  		= $this->tempDir."$instagram-$str-$template-left.png";
                $imgRight 		= $this->imageTemplate229x403DirectSave . "$instagram-template-1-result.jpg";

                if(file_exists($imgLeft) && file_exists($imgRight)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->textoModificado($text ,  30 , 445 ,  $this->fontSize , $font, $this->fontBold, 520 , false , 7)
                        ->marca($imgLeft,'topo','esquerda')
                        ->marca($imgRight,'topo','direita')
                        ->grava($imageSavePath.$nameImage);

                   // unlink($imgLeft);
                }



            }else if($template == '3'){

                $imgCenter = $this->dirDirect. "images-template/600x403/foto-01-v1.jpg";
                if(file_exists($imgCenter)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->textoModificado($text ,  30 , 445 ,  $this->fontSize , $font, $this->fontBold, 520 , false , 7)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);


                }



            }else if($template == '4'){



                $imgCenter = $this->imageTemplate600x403DirectSave. "$instagram-template-seaway-2-result.jpg";
                if(file_exists($imgCenter)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->textoModificado($text ,  30 , 445 ,  $this->fontSize , $font, $this->fontBold, 520 , false , 7)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);


                }



            }else if($template == '5'){

                $imgCenter = $this->imageTemplate600x403DirectSave. "$instagram-template-seaway-3-result.jpg";
                if(file_exists($imgCenter)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->marca($this->pathBgText, 'baixo', 'esquerda')
                        ->textoModificado($text ,  30 , 445 ,  $this->fontSize , $font, $this->fontBold, 520 , false , 7)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);

                    //unlink($imgCenter);
                }



            }

            $url = Mage::getBaseUrl('skin'). $this->urlParticipantDirect.$nameImage;
            $data['status'] = true;
            $data['msg'] 	= 'sucesso';
            $data['data'] 	= $url;


        }catch(Exception $e){
            Mage::log($e->getMessage() , null , 'log_upload_seaway_generateMerge_app.log' ,true );
            $data['msg'] 	= 'Internal Server Error.';
            $data['data'] 	= '';
        }

        return $data;

    }



}