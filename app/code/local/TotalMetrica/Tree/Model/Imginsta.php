<?php
class Seaway_Tree_Model_Imginsta{

    private  $tree        = null;

    private  $dir         = null;

    private  $pathBg      = null;

    private  $font        = null;

    private  $fontSize    = null;

    private  $newDir      = null;

    private  $sourceBg    = null;

    private  $name      = null;


    public function __construct($params = array()){
        if(!empty($params['tree_id'])){
            $this->tree = Mage::getModel('tree/tree')->getTreeById($params['tree_id']);
        }
    }

    public function configIni(){

        $this->dir  = Mage::getBaseDir('skin').'/adminhtml/default/default/images/experience';
        $this->pathBg   = $this->dir .'/base-name.jpg';

        $this->fontSize =  50;
        $this->font     =  Mage::getBaseDir('skin')."/adminhtml/default/default/css/font/montserrat-semibold.ttf";
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
        $sql = "select *   from t_tree WHERE id = :parent";
        $valores = $resource->fetchRow($sql, $bind);
        return $valores;
    }



    public function addText(){

        $tree = $this->getTreeByParent($this->tree['parent_id']);

        $instagram  = $tree['instagram'];



        if(strpos($instagram , '@') === FALSE ) {

            $instagram = '@'.$instagram;
        }

        $textBox = imagettfbbox($this->fontSize,0,$this->font,$instagram);

        $textWidth = abs(abs($textBox[4]) - abs($textBox[0]));
        $imageWidth = imagesx($this->sourceBg);

        $x = (int)($imageWidth/2) - (int)($textWidth/2);

        $dourado = imagecolorallocate($this->sourceBg, 0, 16, 122);
        imagettftext($this->sourceBg, $this->fontSize, 0 , $x , 172, $dourado, $this->font ,$instagram);


        $this->name = $instagram;

        return $this;
    }

    public function saveImg(){


        $name = $this->name .'_insta.png';
        $pathNewFile  = $this->newDir.$name;
        imagejpeg($this->sourceBg, $pathNewFile , 100);


        $url  = Mage::getBaseUrl('skin').'adminhtml/default/default/images/experience/'.$name;


        imagedestroy($this->sourceBg);


        return $url;

    }



    public function generate(){


        return $this->configIni()->setSourceBg()->addText()->saveImg();


    }
















}