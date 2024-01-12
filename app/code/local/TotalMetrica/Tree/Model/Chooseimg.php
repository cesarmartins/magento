<?php
class Seaway_Tree_Model_Chooseimg{




    CONST FONT_PATH 	 = DS."adminhtml".DS."default".DS."default".DS."css".DS."font".DS;
    CONST SAVE_IMAGE_DIR = "frontend".DS."seaway".DS."iphone".DS."images".DS."experience".DS."upload".DS."invite".DS.'generate'.DS;
    CONST IMAGE_PATH 	 = DS."frontend".DS."seaway".DS."iphone".DS."images".DS."experience".DS."upload".DS."invite".DS.'temp'.DS;


    // save img previsiouly
    CONST SAVE_IMAGE_PREVISIOULY_DIR = "frontend".DS."seaway".DS."iphone".DS."images".DS."experience".DS."generated".DS;



    public function createTempImg($instagram,$filename , $image , $text  ,$mount, $fontSize ){

        list($type, $image) = explode(';', $image);
        list($type, $image)  = explode(',', $image);

        $image = base64_decode($image);
        $imageName = $instagram.$filename.'.png';

        $path = Mage::getBaseDir('skin').self::IMAGE_PATH.$imageName;
        $value = file_put_contents($path, $image);

        $data = 'done';
        // monta a imagem
        if(is_int($value)){
            if($mount === "true"){
                $data = $this->generateMerge($imageName , $text, $fontSize);
            }
        }
        return $data;

    }



    public function generateMerge($imageName , $text , $fontSize){

        $skin			= Mage::getBaseDir('skin');
        $fontPath 		= $skin.self::FONT_PATH;
        $imageSavePath  = $skin.DS.self::SAVE_IMAGE_PREVISIOULY_DIR.'participant'.DS;
        $imgPath 		= $skin.self::IMAGE_PATH;

        $font 			= $fontPath . "montserrat-regular.ttf";
        $fontBold 			= $fontPath . "montserrat-bold.ttf";



        try{
            switch($fontSize){
                case 16 : $fontSize = 24;break;
                case 18 : $fontSize = 28;break;
                default: $fontSize = 22;break;
            }

            $data = array();
            $data['status'] = false;

            list($instagram , $str , $template , $position) = explode('-', $imageName);

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();
            $time	 = time();
            $nameImage 		= "$instagram-template-$template-result_$time.jpg";

            $imgCenter = "";
            $imgLeft   = "";
            $imgRight  = "";

            if($template == '1') {

                $imgCenter  	= $imgPath."$instagram-$str-$template-center.png";
                if(file_exists($imgCenter)) {
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->textoCentralizado($text ,  20 , 457 ,  $fontSize , $font, $fontBold, 570, true)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);
                }

            }else if($template == '2') {

                $imgLeft  		= $imgPath."$instagram-$str-$template-left.png";
                $imgRight 		= $imgPath."$instagram-$str-$template-right.png";

                if(file_exists($imgLeft) && file_exists($imgRight)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->textoCentralizado($text ,  20 , 457 ,  $fontSize , $font, $fontBold, 570, true)
                        ->marca($imgLeft,'topo','esquerda')
                        ->marca($imgRight,'topo','direita')
                        ->grava($imageSavePath.$nameImage);
                }

            }


            if(!empty($imgCenter) && file_exists($imgCenter)){
                unlink($imgCenter);
            }

            if(!empty($imgLeft) && file_exists($imgLeft)){
                unlink($imgLeft);
            }

            if(!empty($imgRight) && file_exists($imgRight)){
                unlink($imgRight);
            }


            $url = Mage::getBaseUrl('skin').self::SAVE_IMAGE_PREVISIOULY_DIR.'participant'.DS.$nameImage;
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




    public function generatePreviously($instagram ){

        $result  = array();
        $baseImgs =  array('foto_produto_7.jpg' ,'foto_produto_6.jpg' ,'foto_produto_5.jpg' ,  'foto_produto_4.jpg',  'foto_produto_3.jpg',  'foto_produto_2.jpg' ,  'foto_produto_1.jpg');

        $skin  = Mage::getBaseDir('skin');
        $imageSavePath  = $skin.DS.self::SAVE_IMAGE_PREVISIOULY_DIR.'seaway'.DS;
        $imageBasePath  = $skin.DS.self::SAVE_IMAGE_PREVISIOULY_DIR.'template'.DS;

        $fontSize = 23.5;
        $fontPath 		= $skin.self::FONT_PATH;
        $font 			= $fontPath . "montserrat-regular.ttf";
        $fontBold 		= $fontPath . "montserrat-bold.ttf";


        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        $canvas = Canvas::Instance();
        $text = "(Seaway)+n (Experience)+n\n and ($instagram)+n invite you\nto choose (for)+n (free)+n (01)+n (Boardshort.)+n\nUse the link to get yours";
        $url = Mage::getBaseUrl('skin').self::SAVE_IMAGE_PREVISIOULY_DIR.'seaway'.DS;
        foreach($baseImgs as $baseImg){
            if( file_exists($imageSavePath . "$instagram-img-$baseImg")){

                $result[] = $url . "$instagram-img-$baseImg";
                continue;
            }
            $imgFullBase = "";
            $imgFullBase = $imageBasePath.$baseImg;
            $canvas
              ->hexa('#fff')
              ->novaImagem(600, 600)
              ->hexa('#000')
              // ->texto($text, 20, 460, $fontSize, $font)
              ->textoCentralizado($text ,  20 , 457 ,  $fontSize , $font, $fontBold, 570, true)
              ->marca($imgFullBase, 'topo', 'esquerda')
              ->grava($imageSavePath . "$instagram-img-$baseImg");

            $result[] = $url . "$instagram-img-$baseImg";

        }



        return $result;

    }






   /* public function generateMerge($imageName , $text , $fontSize){

        $skin			= Mage::getBaseDir('skin');
        $fontPath 		= $skin.self::FONT_PATH;
        $imageSavePath  = $skin.DS.self::SAVE_IMAGE_DIR;
        $imgPath 		= $skin.self::IMAGE_PATH;

        $font 			= $fontPath . "montserrat-bolditalic.ttf";


        try{
            switch($fontSize){
                case 16 : $fontSize = 24;break;
                case 18 : $fontSize = 28;break;
                default: $fontSize = 24;break;
            }


            $data = array();
            $data['status'] = false;


            list($instagram , $str , $template , $position) = explode('-', $imageName);

            require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
            $canvas = Canvas::Instance();
            $time	 = time();
            $nameImage 		= "$instagram-template-$template-result_$time.jpg";

            $imgCenter = "";
            $imgLeft   = "";
            $imgRight  = "";



            if($template == '1') {

                $imgCenter  	= $imgPath."$instagram-$str-$template-center.png";
                if(file_exists($imgCenter)) {
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->texto($text, 20, 460, $fontSize, $font)
                        ->marca($imgCenter, 'topo', 'esquerda')
                        ->grava($imageSavePath . $nameImage);
                }

            }else if($template == '2') {

                $imgLeft  		= $imgPath."$instagram-$str-$template-left.png";
                $imgRight 		= $imgPath."$instagram-$str-$template-right.png";

                if(file_exists($imgLeft) && file_exists($imgRight)){
                    $canvas
                        ->hexa('#fff')
                        ->novaImagem(600, 600)
                        ->hexa('#000')
                        ->texto($text, 20 , 460 , $fontSize , $font )
                        ->marca($imgLeft,'topo','esquerda')
                        ->marca($imgRight,'topo','direita')
                        ->grava($imageSavePath.$nameImage);
                }

            }


            if(!empty($imgCenter) && file_exists($imgCenter)){
                unlink($imgCenter);
            }

            if(!empty($imgLeft) && file_exists($imgLeft)){
                unlink($imgLeft);
            }

            if(!empty($imgRight) && file_exists($imgRight)){
                unlink($imgRight);
            }


            $url = Mage::getBaseUrl('skin').self::SAVE_IMAGE_DIR.$nameImage;
            $data['status'] = true;
            $data['msg'] 	= 'sucesso';
            $data['data'] 	= $url;


        }catch(Exception $e){
            Mage::log($e->getMessage() , null , 'log_upload_seaway_generateMerge_app.log' ,true );
            $data['msg'] 	= 'Internal Server Error.';
            $data['data'] 	= '';
        }

        return $data;

    }*/



}