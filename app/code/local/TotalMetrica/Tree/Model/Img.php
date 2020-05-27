<?php
class Seaway_Tree_Model_Img{

    private  $tree        = null;

    private  $dir         = null;

    private  $pathBg      = null;

    private  $pathItem    = null;

    private  $font        = null;

    private  $fontSize    = null;

    private  $newDir      = null;

    private  $sourceBg    = null;

    private  $sourceItem  = null;

    private  $sourceImageResized = null;

    private  $resizedValueWidthItem = null;

    private  $resizedValueHeightItem = null;

    private  $posItemX  = null ;

    private  $posItemY  = null ;

    private  $name      = null;




    public function getLastItemFreePaymentUser(){

        $customerId  = $this->tree['customer_id'];


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


    private function getImgFatherBySku($sku){

        $img = "";
        if(!empty($sku)){


            $product = Mage::getModel('catalog/product')->loadByAttribute( 'sku' , $sku);
            $color   = $product->getAttributeText('color');

            if(!empty($product->getEntityId())){

                list($productParentId)  =  Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild( $product->getEntityId() );
                $productFather  = Mage::getModel('catalog/product')->load($productParentId);

                $mediaGallery   = $productFather->getData('media_gallery');


                if(!empty($mediaGallery['images'])){

                    $lastsPosition = array_slice($mediaGallery['images'] , -2);
                    foreach($lastsPosition as $last){
                        if(!empty($last['label_default']) && !empty($last['file'])){
                            if(strcasecmp($last['label_default'] ,$color ) == 0){
                                $img = $last['file'];
                            }
                        }
                    }


                }



            }

        }

        return $img;
    }



    public function __construct($params = array()){
        if(!empty($params['tree_id'])){
            $this->tree = Mage::getModel('tree/tree')->getTreeById($params['tree_id']);
        }
    }

    public function configIni(){

        $this->dir  = Mage::getBaseDir('skin').'/adminhtml/default/default/images/experience';
        $this->pathBg   = $this->dir .'/new-base.jpg';

        $imgBoardshort  = $this->getLastItemFreePaymentUser();

        if(empty($imgBoardshort)){

            throw new Exception('Img is not exists.' , -3 );

        }

        $this->pathItem =  Mage::getBaseUrl('media') . 'catalog/product'. $imgBoardshort;

        $this->fontSize =  41;
        $this->resizedValueWidthItem = 370;
        $this->font     =  Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-semibold.ttf";


        $this->newDir   = $this->dir.'/';

        $this->posItemX = 620;
        $this->posItemY = 426;
        //$this->posItemY = 366;


        return $this;

    }

    public function setSourceBg(){
        $this->sourceBg = imagecreatefromjpeg($this->pathBg);
        return $this;
    }

    public function setSourceItem(){

        $this->sourceItem = imagecreatefrompng($this->pathItem);
        $black = imagecolorallocate($this->sourceItem , 0, 0, 0);
        imagecolortransparent($this->sourceItem , $black);
        return $this;
    }

    public function resizeItem(){

        $originalWidth  = imagesx($this->sourceItem);

        $originalHeight = imagesy($this->sourceItem);



        $this->resizedValueHeightItem = (int)(($this->resizedValueWidthItem * $originalHeight)/$originalWidth);

        $this->sourceImageResized = imagecreatetruecolor( $this->resizedValueWidthItem, $this->resizedValueHeightItem );

        imagesavealpha($this->sourceImageResized, true);
        $pngTransparent = imagecolorallocatealpha($this->sourceImageResized, 0, 0, 0, 127);
        imagefill($this->sourceImageResized, 0, 0, $pngTransparent);

        imagecopyresampled( $this->sourceImageResized, $this->sourceItem, 0, 0, 0, 0, $this->resizedValueWidthItem, $this->resizedValueHeightItem, $originalWidth, $originalHeight );

        return $this;
    }

    public function mergeItemBg(){

        imagecopyresampled( $this->sourceBg , $this->sourceImageResized , $this->posItemX, $this->posItemY , 0, 0, $this->resizedValueWidthItem, $this->resizedValueHeightItem, $this->resizedValueWidthItem, $this->resizedValueHeightItem );

        return $this;
    }

    public function addText(){

        $customerId  = $this->tree['customer_id'];
        $customer = Mage::getModel('customer/customer')->load($customerId);

        $firstName = substr($customer->getFirstname() , 0 , 1);
        $lastName  = $customer->getLastname();

        $name = strtoupper($firstName).". ".strtoupper($lastName);
        $textBox = imagettfbbox($this->fontSize,0,$this->font,$name);

        $textWidth = abs(abs($textBox[4]) - abs($textBox[0]));
        $imageWidth = imagesx($this->sourceBg);

        $x = (int)($imageWidth/2) - (int)($textWidth/2);

        $dourado = imagecolorallocate($this->sourceBg, 208, 203, 135);
        imagettftext($this->sourceBg, $this->fontSize, 0 , $x , 88, $dourado, $this->font ,$name);

        $this->name = $name;

        return $this;
    }

    public function saveImg(){


        $name = str_replace(array('.' , ' ') , array('' , '') , $this->name) .'_ex.png';
        $pathNewFile  = $this->newDir.$name;
        imagejpeg($this->sourceBg, $pathNewFile , 100);


        $url  = Mage::getBaseUrl('skin').'/adminhtml/default/default/images/experience/'.$name;


        imagedestroy($this->sourceBg);
        imagedestroy($this->sourceItem);
        imagedestroy($this->sourceImageResized);

        return $url;

    }



    public function generate(){


        return $this->configIni()->setSourceBg()->setSourceItem()->resizeItem()->mergeItemBg()->addText()->saveImg();


    }
















}