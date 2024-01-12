<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 07/02/2017
 * Time: 16:55
 */

class Cistecnologia_Importacao_RedirectController extends Mage_Core_Controller_Front_Action{


    public function indexAction(){


        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $param = preg_replace('@(.*)?(/_/)(.*)?(/)?@' ,'$3',$currentUrl );
        $slug  = "";

        

        if($param != ""){
           if(strpos('/',$param ) === FALSE  ){
                $slug  = $param;
            }else{
                $slug = current(explode('/' , $param));
            }



            if($slug != ""){

                $tree  = new Seaway_Tree_Model_Tree();
                $node = $tree->verifySlugAtleta($slug);

                $code = (!empty($node['cod_gerado_1']))? $node['cod_gerado_1'] :  $node['cod_gerado_2'];

                if(!empty($code)){

                    $this->_redirect("superheatcomp?c=$code");


                }else{

                    $this->_redirect("/opss");
                }


            }else{

                $this->_redirect("/opss");

            }

        }else{

            $this->_redirect("/opss");

        }


    }



    public function testeAction(){

        $list = Mage::getModel('tree/app')->getTreeList();


        echo '<pre>';
        var_dump($list);

        die('The end');
    }

}