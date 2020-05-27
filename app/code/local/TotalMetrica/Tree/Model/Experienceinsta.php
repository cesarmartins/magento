<?php
class Seaway_Tree_Model_Experienceinsta{

    private  $tree        = null;

    private  $dir         = null;

    private  $pathBg      = null;

    private  $font        = null;




    private  $fontSizeTitle   = null;

    private  $fontSizeText    = null;

    private  $fontSizeProg     = null;

    private  $fontSizeE     = null;

    private  $newDir      = null;

    private  $sourceBg    = null;

    private  $name      = null;

    private  $fontText  = null;

    private  $fontTitleE  = null;

    private  $fontTitleProgram  = null;



    public function __construct($params = array()){
        if(!empty($params['tree_id'])){
            $this->tree = Mage::getModel('tree/tree')->getTreeById($params['tree_id']);

        }
    }

    public function configIni(){



        $this->dir  = Mage::getBaseDir('skin').'/adminhtml/default/default/images/experience_insta';
      //  $this->pathBg   = $this->dir .'/bg.jpg';
         $this->pathBg   = $this->dir .'/base.jpg';

        $this->fontSizeTitle =  27;
        $this->fontSizeText = 25;
        $this->fontSizeE  = 28;
        $this->fontSizeProg  = 45;

        $this->font     =  Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-regular.ttf";
        $this->fontText =  Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-bolditalic.ttf";
        $this->fontTitleE = Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-bold.ttf";
        $this->fontTitleProgram = Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/jupiter.ttf";

        $this->newDir   = $this->dir.'/';

        return $this;

    }

    public function setSourceBg(){
        $this->sourceBg = imagecreatefromjpeg($this->pathBg);
        return $this;
    }



    public function getTreeByParent($treeId){

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $bind =  array('parent' => $treeId);
        $sql = "SELECT * FROM t_tree WHERE id = :parent";
        $valores = $resource->fetchRow($sql, $bind);
        return $valores;
    }



    public function addText(){

        $tree = $this->getTreeByParent($this->tree['parent_id']);

        $instagramParent  = $tree['instagram'];
        $instagram  = $this->tree['instagram'];

        if(strpos($instagram , '@') === FALSE ) {

            $instagram = '@'.$instagram;
        }

        if(strpos($instagramParent , '@') === FALSE ) {

            $instagramParent = '@'.$instagramParent;
        }



        $azul = imagecolorallocate($this->sourceBg, 61, 165 , 207);
        imagettftext($this->sourceBg, $this->fontSizeText + 5 , 0 , 158  , 105, $azul, $this->font,$instagramParent);
        imagettftext($this->sourceBg, $this->fontSizeText, 0 , 248  , 176, $azul, $this->font,$instagram);


        $url  = 'seaway.surf/free/'.$instagram;
        $textBox = imagettfbbox($this->fontSizeText - 5,0,$this->font,$url);

        $textWidth = abs(abs($textBox[4]) - abs($textBox[0]));
        $imageWidth = imagesx($this->sourceBg);

        $x = (int)($imageWidth/2) - (int)($textWidth/2);
        imagettftext($this->sourceBg, $this->fontSizeText - 5, 0 , $x  , 530, $azul, $this->font ,$url);




        //$instagramParent = "wertyasasdayyy";

      /*  if(strpos($instagramParent , '@') === FALSE ) {

            $instagramParent = '@'.$instagramParent;
        }


        if(strlen($instagramParent) > 19 ){
            $instagramParent = substr($instagramParent , 0 , 19);
        }*/



       /* $textBoxInstaParent = imagettfbbox($this->fontSizeTitle,0,$this->font,$instagramParent);
        $textWidthInstaParent = abs(abs($textBoxInstaParent[4]) - abs($textBoxInstaParent[0]));

        $textBoxE = imagettfbbox($this->fontSizeE,0,$this->fontTitleE,'&');
        $textWidthE = abs(abs($textBoxE[4]) - abs($textBoxE[0]));

        $textBoxProd = imagettfbbox($this->fontSizeProg,0,$this->fontTitleProgram,'Prog');
        $textWidthProd = abs(abs($textBoxProd[4]) - abs($textBoxProd[0]));*/


        //$spaceLeft  = 57;
        /*$spaceLeft  = 57;
        $spaceRight = 54;

        $textTotalWidth = $textWidthInstaParent+$textWidthE+$textWidthProd + $spaceLeft + $spaceRight;

        $imageWidth = imagesx($this->sourceBg);


        $xInstaParent =  0;
        $xSpaceLeft   =  0;
        $xE           =  0;
        $xSpaceRight  =  0;
        $xProg        =  0;

        $margin = 20;

        if($textTotalWidth < ($imageWidth - 20) ){

            $xInstaParent = (int)($imageWidth/2) - (int)($textTotalWidth/2);
            $xSpaceLeft   =  $xInstaParent + $textWidthInstaParent  + $spaceLeft;
            $xE           =  $xSpaceLeft ;
            $xSpaceRight  =  $xSpaceLeft  + $textWidthE +$spaceRight;
            $xProg        =  $xSpaceRight;

        }else if($textTotalWidth >= ($imageWidth - 20)){

            $diff =  $textTotalWidth - ($imageWidth - 20);
            $diff = (int)($diff / 2);

            $margin += $diff - $margin;

            $xInstaParent = (int)($imageWidth/2) - (int)($textTotalWidth/2) + $margin ;
            $xSpaceLeft   =  $xInstaParent + $textWidthInstaParent  + $spaceLeft - $margin;
            $xE           =  $xSpaceLeft ;
            $xSpaceRight  =  $xSpaceLeft  + $textWidthE +$spaceRight - $margin;
            $xProg        =  $xSpaceRight;


        }else{
            die('c');
        }

       // $xTitle = 60;
        $yTitle = 50;
        $branco = imagecolorallocate($this->sourceBg, 255, 255, 255);

        imagettftext($this->sourceBg, $this->fontSizeTitle, 0 , $xInstaParent , $yTitle, $branco, $this->font ,$instagramParent);
        imagettftext($this->sourceBg, $this->fontSizeE, 0 , $xE , $yTitle, $branco, $this->fontTitleE ,'&');
        imagettftext($this->sourceBg, $this->fontSizeProg, 0 , $xProg , $yTitle, $branco, $this->fontTitleProgram ,'Prog');

        $xText = 22;
        $yText = 300;
        imagettftext($this->sourceBg, $this->fontSizeText, 0 , $xText , $yText, $branco, $this->fontText ,$instagram);*/


        $this->name = $instagram;

        return $this;
    }

    public function saveImg(){

        try{
            $name = $this->name .'_insta_ind.jpg';
            $pathNewFile  = $this->newDir.$name;
            imagejpeg($this->sourceBg, $pathNewFile , 100);

            $url  = Mage::getBaseUrl('skin').'adminhtml/default/default/images/experience_insta/'.$name;

            imagedestroy($this->sourceBg);

            return $url;


        }catch (Exception $e){

            echo $e->getMessage();

            die;

        }





    }



    public function generate(){


        return $this->configIni()->setSourceBg()->addText()->saveImg();


    }
















}