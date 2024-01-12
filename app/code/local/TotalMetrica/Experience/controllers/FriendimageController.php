<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 14/11/2017
 * Time: 16:29
 */
class Seaway_Experience_FriendimageController extends Mage_Core_Controller_Front_Action{




	public function indexAction(){
		$instagram = $this->getRequest()->getParam('ig');
		//$instagramFather = $this->getRequest()->getParam('igfather');
        $this->loadLayout();
		$results = array();
		$list 	 = array();
		if( !empty($instagram) ){
			$results = Mage::getModel('tree/chooseimg')->generatePreviously($instagram);
			$list 	 = Mage::getModel('tree/tree')->getListSeawayChildsByInstagram($instagram);
		}

		$this->getLayout()->getBlock('experience')->setData('seawayimages', $results)->setData('list', $list);
	    $this->renderLayout();
    }



	public function createimageparticipantAction(){


		if($this->getRequest()->getPost()){

			$image = $_POST['image'];
			$filename = $_POST['filename'];
			$instagram = $_POST['instagram'];
			//$instagramFather = $_POST['instagramfather'];
			//$text = $_POST['text'];
			$mount = $_POST['mount'];
			$fontSize = 23.5;

			$text = "(Seaway)+n (Experience)+n\n and ($instagram)+n invite you\nto choose (for)+n (free)+n (01)+n (Boardshort.)+n\nUse the link to get yours";
			//$text = "($instagram)+n and (Seaway)+n (Experience)+n,invite you\nto choose (for)+n (free)+n (01)+n (Boardshort.)+n\nUse the link to get yours";
			$result = Mage::getModel('tree/chooseimg')->createTempImg($instagram,$filename , $image , $text  ,$mount , $fontSize );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

		}


	}


}