<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 14/11/2017
 * Time: 18:35
 */

class Seaway_Experience_TestController extends Mage_Core_Controller_Front_Action
{

    public function generatelinkAction(){

       $instagram = $this->getRequest()->getParam('instagram');

        if(!empty($instagram)){

            $tree = Mage::getModel('tree/tree')->getTreeByInsta($instagram);

            if(!empty($tree['id'])){

                $redirectLocation = Mage::getModel('tree/app')->generateLink($tree['instagram'], $tree['parent_name'], $tree['id']);

                echo $redirectLocation;
                die;

            }

        }
    }


    public function _indexAction()
    {


      /*  $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT * FROM t_tree ";
        $valores = $resource->fetchAll($sql);

        foreach($valores as $val){
            Mage::getModel('tree/app')->updateHistoryTree($val['id']);
        }*/


        require_once Mage::getBaseDir('lib') . DS . "canvas" . DS . "canvas.php";
        // qual fonte q ira utilizar

        $fontPath = Mage::getBaseDir('skin') . DS . "adminhtml". DS . "default" . DS . "default" . DS . "css" . DS . "font" . DS;
        $imageSavePath  = Mage::getBaseDir('skin') .DS."frontend".DS."seaway".DS."iphone".DS."images".DS."experience".DS."generate".DS;
        $imgPath = Mage::getBaseDir('skin').DS."frontend".DS."seaway".DS."iphone".DS."images".DS."experience".DS."upload".DS."friend".DS;

        $nameImage = "testCanvas.jpg";

        $font     = $fontPath."montserrat-bolditalic.ttf";
        $imgLeft  = $imgPath. "rcdrigc-template-2-left.png";
        $imgRight = $imgPath. "rcdrigc-template-2-right.png";


        $text = "";


        //16 * 2 = 32 -- +8 24
        //18.5 * 2 = 37 32 --


       // $fontSize1 = 24;


        $fontSize = 32;


        $xSeleted  = "";
        $ySelected = "";

        $width = 0;
        $height = 0;



        $text  = "#teste";
        //$text  = "#teste chupetinha teste chupeta na caveira";

        //$text  = "Esse  Boardshort  � o mais leve do mundo!!  #boardshortseaway #boardshortseaway #boardshortseaway";


        $canvas = Canvas::Instance();
        $canvas
            ->hexa('#fff')
            ->novaImagem(600, 600)
            ->hexa('#000')
            ->texto($text,  20 , 460 , $fontSize , $font )
            ->marca($imgLeft,'topo','esquerda')
            ->marca($imgRight,'topo','direita')
            ->grava($imageSavePath.$nameImage);


    }

    public function getipAction(){
        $allLinks = Mage::getModel('track/track')->getAllLinks();

        echo "<pre>";
        var_dump($allLinks);

    }

    public function sendemailcontatoAction() {

        $email = "cesar.martins@gmail.com";
        $insta = "@cesar.gringo";
        $mensagem = "teste de email";

        $html="<p style=\"color: #444444; font-size: 18px; font-family: Helvetica; text-align: left; margin: 0 0 50px 0; line-height: 25px;\">
                <b>E-mail:</b> $email <br/>
                <b>Instagram:</b> $insta <br/>
                <b>Mensagem:</b> $mensagem </p>";

            $mail = Mage::getModel('core/email')
            ->setToName('César')
            ->setToEmail('site@seaway.com.br')
            ->setFromEmail('sac@seaway.surf')
            ->setFromName('Seaway Experience Program')
            ->setBody($html)
            ->setSubject('Seaway Experience Program - Seaway.surf')
            ->setType('html');

        try {
            $mail->send();
        } catch (Exception $error) {
            //Mage::log($error->getMessage(), null, 'auto_order_emails.log');
            echo $error->getMessage();
            //continue;
        }
    }

}