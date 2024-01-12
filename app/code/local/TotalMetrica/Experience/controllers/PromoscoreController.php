<?php

class Seaway_Experience_PromoscoreController extends Mage_Core_Controller_Front_Action {

	private $params = null;
	public $instagram = null;
	public $tree = null;
	public $treeId  =null;
	public $fase = null;
	CONST CURRENT_STEP_PAGE = 3;

	public function preDispatch(){
        parent::preDispatch();

        $headerList = getallheaders();

        foreach($headerList as $key=>$value){
            $this->params[$key] = trim($value);
        }
		
        /* *********** header webview *********** */
		$this->instagram = null;
		$ig = $this->getRequest()->getParam('ig');
        if(!empty($ig)){
        	$this->instagram = $ig;
		}
		if(!empty($this->params['username'])){
			$this->instagram = $this->params['username'];
		}


		if(empty($this->instagram)){
			$this->instagram = Mage::getModel('experience/experience')->getInstaSession();
		}elseif(!empty($this->instagram)){
			Mage::getModel('experience/experience')->saveInstaSession($this->instagram);
		}

		if(empty($this->instagram)){
            throw new Exception('Instagram is Null.' , -2);
        }

        $isMobile = Zend_Http_UserAgent_Mobile::match(
			Mage::helper('core/http')->getHttpUserAgent(),
			$_SERVER
		);



		//$isMobile = true;

		$actionName  = $this->getRequest()->getActionName();
		$isNotError  = (  strcasecmp($actionName,'error') != 0 )? true : false;
		if(!$isMobile  && $isNotError){
			$this->_redirect('experience/friend/error', array('_secure' => true));
			return;
		}
		
		
		$participant = array();
		$currentStep = 0;


		if(!empty($this->instagram)){
	        $participant = Mage::getModel('experience/experience')->instaVerifyCountry($this->instagram);
			$currentStep =  (!empty($participant['values']['current_step']))? $participant['values']['current_step'] : 0 ;
		}



		$customerSession = Mage::getSingleton('customer/session');
		if ( $customerSession->isLoggedIn() ) {

			if(empty($participant['values'])){
				
				Mage::getSingleton('customer/session')->logout();
				$this->_redirect('/', array('_secure' => true));
				return;
			}

		}else{

			if(!empty( $participant['values']['customer_id'] )){

				$urlCurrent =  Mage::helper('core/url')->getCurrentUrl();

				$customer  = Mage::getModel('customer/customer')->load($participant['values']['customer_id']);
				$customerSession->setCustomerAsLoggedIn($customer);

				$this->_redirectUrl($urlCurrent, array('_secure' => true));
				return;

	        }else{
				Mage::getSingleton('customer/session')->logout();
				$this->_redirect('/', array('_secure' => true));
				return;
	        }

		}



		if(empty($this->tree)){
			$this->tree = Mage::getModel('tree/tree')->getTreeByInsta($this->instagram);
			$this->treeId = (!empty($this->tree['id']))? $this->tree['id'] : false;


		}

		$this->_init();



		// if ($isLoggedIn) {

		// 	$objCustomerLogin = Mage::getSingleton('customer/session');
		// 	$customer = $objCustomerLogin->getCustomer();
		// 	$customerId = $customer->getEntityId();

		// 	if (!Mage::getModel('tree/tree')->isParticipantByCustomerId($customerId)) {
		// 		Mage::getSingleton('customer/session')->logout();
		// 		$this->_redirect('experience/login', array('_secure' => true));
		// 		return;
		// 	}

		// 	//descomentar
		// 	Mage::getModel('tree/app')->verifyCouponApp($customerId );
		// 	Mage::getModel('tree/app')->updateSell($customerId);
		// 	//cupons promo code for you
		// 	Mage::getModel('tree/app')->customerBought($customerId);


		// }


    }



	// load promoscore table informations
	public function _init(){

		if(!empty($this->tree['id'])){

			Mage::getModel('experience/promoscore')->createInitialScore($this->treeId);
			Mage::getModel('experience/promoscore')->generateAllCouponsForMedia($this->tree);
			Mage::getModel('experience/promoscore')->fillsCouponMediaUsed($this->treeId);
			Mage::getModel('experience/promoscore')->fillsScore($this->treeId);

			$this->fase =  Mage::getModel('experience/promoscore')->returnLastPhase($this->treeId);

		}


	}

	public function indexAction() {

		$this->loadLayout();
		$body = $this->getLayout()->getBlock('experience');
		$body->setInstagram($this->instagram);

		if($this->getRequest()->isPost()){
			$paramId = $this->getRequest()->getparam('sphere_id');
			if(!empty($paramId))
				$this->discountButtonCLick($paramId);
		}

		$promoscoreObj  = Mage::getModel('experience/promoscore');
		$promoscoreObj->usedSphere($this->treeId);
		$promoscoreObj->loadScore($this->treeId);

		$score 			  = $promoscoreObj->getScore();
		$players		  = $promoscoreObj->getTreeScore();
		$jsonSpheres	  = $promoscoreObj->getSpheresPlayers();
		$jsonDistribution = $promoscoreObj->qtyDistributionCoupons($this->treeId);
		$isFirstDiscount  = $promoscoreObj->isFirstDiscount();
		$endScoreValue    = $promoscoreObj->getScoreEndValue();
		$isShowVideo	  = $promoscoreObj->youSeeAdversing($this->treeId);

		$promoscoreObj->setYouSeeAdversing($this->treeId);

		Mage::getModel('experience/experience')->setFirstAccess(self::CURRENT_STEP_PAGE);
		Mage::getModel('experience/promoscore')->saveLastAccess($this->treeId);
		//Mage::getModel('experience/promoscore')->sendPersonally($email , $this->instagram );



		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('personally' , $this->instagram , $this->fase);
		$body->setData('valid' , $dateExpiration);
		$body->setData('showvideo' , $isShowVideo);
		$body->setData('fase' , $this->fase);
		$body->setData('score' ,  $score);
		$body->setData('players' ,$players);
		$body->setData('isfirstdiscount' ,$isFirstDiscount);
		$body->setData('jsonspheres' , $jsonSpheres);
		$body->setData('jsondistributioncoupons' , $jsonDistribution);
		$body->setData('endscorevalue' , $endScoreValue);


		$this->renderLayout();


	}


	/**
	 * pega informações arvore
	 *
	 *
	 *
	 */

	private function discountButtonCLick($sphereId){
		$promoscoreObj  = Mage::getModel('experience/promoscore');
		$code  = $promoscoreObj->generateDiscount($this->tree , $sphereId);
		$url =  Mage::getBaseUrl('web').'foryou?c='.$code;

		$this->_redirectUrl($url);
	}



	public function personallyAction() {

		$this->loadLayout();

		if($this->getRequest()->isPost()){

			try{
				$result = array('status' =>false ,  'msg' => '');

				$email = $this->getRequest()->getParam('email');
				Mage::getModel('experience/promoscore')->sendPersonally($email , $this->instagram );

				$result['status'] = true ;
				$result['msg'] = 'sucesso';

			}catch (Exception $e){
				$result['msg'] = $e->getMessage();
			}
			header('Content-type:application/json');
			echo json_encode($result);
			exit;

		}

		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('personally' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/personally'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function instagrampostAction() {



		$this->loadLayout();
		Mage::getModel('experience/images' , array('tree_id' => $this->treeId ))->configIni()->generateAllTemplates();
		$body = $this->getLayout()->getBlock('experience');

		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('instagram-post' , $this->instagram , $this->fase);
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/insta'.$this->fase.'/'.$this->instagram,
			'url_promo' => 'seaway.surf/promo1/',
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function instagramdirectAction() {

		$this->loadLayout();
		Mage::getModel('experience/images' , array('tree_id' => $this->treeId ))->configIni()->generateAllTemplatesDirect();

		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('instagram-direct' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/direct'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function smsAction() {

		$this->loadLayout();
		Mage::getModel('experience/images' , array('tree_id' => $this->treeId , 'current_dir' => 'sms' ))->configIni()->generateAllTemplatesDirect();

		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('sms' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/sms'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function emailAction() {

		$this->loadLayout();

		if($this->getRequest()->isPost()){

			try{
				$result = array('status' =>false ,  'msg' => '');
				$email = $this->getRequest()->param('email');
				Mage::getModel('experience/promoscore')->sendEmail($email , $this->instagram );

				$result['status'] = true ;
				$result['msg'] = 'sucesso';

			}catch (Exception $e){
				$result['msg'] = $e->getMessage();
			}
			header('Content-type:application/json');
			echo  json_encode($result);
			exit;

		}

		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('email' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/email'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function facebookAction() {

		$this->loadLayout();

		Mage::getModel('experience/images' , array('tree_id' => $this->treeId , 'current_dir' => 'facebook' ))->configIni()->generateAllTemplatesDirect();


		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('facebook' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/facebook'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	public function whatsappAction() {

		$this->loadLayout();

		Mage::getModel('experience/images' , array('tree_id' => $this->treeId , 'current_dir' => 'whatsapp' ))->configIni()->generateAllTemplatesDirect();


		$dateExpiration  = Mage::getModel('experience/promoscore')->dateExpiration('whatsapp' , $this->instagram , $this->fase);
		$body = $this->getLayout()->getBlock('experience');
		$data = array(
			'instagram' => $this->instagram,
			'url' => 'seaway.surf/whatsapp'.$this->fase.'/'.$this->instagram,
			'valid' => $dateExpiration,
		);
		$body->setData($data);

		$this->renderLayout();
		
	}

	private function getCustomerLoggedIn(){
		$objCustomerLogin = Mage::getSingleton('customer/session');
		$customer = NULL;
		if($objCustomerLogin->isLoggedIn()){
			$customer = $objCustomerLogin->getCustomer();
		}
		return $customer;
	}

	public function generateCouponMediaAction(){

		try{

			$data = array();
			$data['status'] = false;

			$customer  = $this->getCustomerLoggedIn();
			if(empty($customer->getData())){
				throw new Exception('Customer is not logged in.' , -2);
			}

			if(!$this->getRequest()->getPost()){
				throw new Exception('Request invalid.' , -2);
			}
			$media  = $this->getRequest()->getParam('media');
			if(empty($media)){
				throw new Exception('Media is invalid.' , -2);
			}

			$customerId = $customer->getEntityId();
			// desconto de 30 por cento para o amigo
			$link = Mage::getModel('tree/app')->createCouponApp( $customerId ,$media);

			$data['status'] = true;
			$data['msg'] = 'sucesso';
			$data['code'] = $link;


		}catch(Exception $e){
			//$message  = "Error, please try again.";
			$message = $e->getMessage();
			Mage::log($message , null , 'createLinkFriend.log',true);
			if($e->getCode()  == -2 || $e->getCode()  == -3 ){
				$message  = $e->getMessage();
			}
			$data['msg'] = $message;
		}


		header('Content-Type:application/json');
		echo json_encode($data);
		die;
	}

	public function redirectPreDispatch($url){
		header("Location: $url");
		exit;
	}

	public function createimageinstagrampostAction(){


		if($this->getRequest()->getPost()){

			$image 	   = $this->getRequest()->getParam('image');
			$filename  = $this->getRequest()->getParam('filename');
			$mount	   = $this->getRequest()->getParam('mount');


			$result = Mage::getModel('experience/images', array('tree_id' => $this->treeId))
				->configIni()->
				createTempImg( $filename ,$image , $mount  );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

		}


	}

	public function createimageinstagramdirectAction(){


		if($this->getRequest()->getPost()){

			$image 	   = $this->getRequest()->getParam('image');
			$filename  = $this->getRequest()->getParam('filename');
			$mount	   = $this->getRequest()->getParam('mount');
			$text	   = $this->getRequest()->getParam('text');
			$fontType  = $this->getRequest()->getParam('fontWeight');




			$result = Mage::getModel('experience/images', array('tree_id' => $this->treeId))
				->configIni()->
				createTempImgDirect( $filename ,$image , $mount  , $text, $fontType );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

		}


	}

	public function createimagesmsAction(){


		if($this->getRequest()->getPost()){

			$image 	   = $this->getRequest()->getParam('image');
			$filename  = $this->getRequest()->getParam('filename');
			$mount	   = $this->getRequest()->getParam('mount');
			$text	   = $this->getRequest()->getParam('text');
			$fontType  = $this->getRequest()->getParam('fontWeight');

			$result = Mage::getModel('experience/images', array('tree_id' => $this->treeId , 'current_dir'=> 'sms'))
				->configIni()->
				createTempImgDirect( $filename ,$image , $mount  , $text, $fontType );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

	}

 }



	public function createimagefacebookAction(){


		if($this->getRequest()->getPost()){

			$image 	   = $this->getRequest()->getParam('image');
			$filename  = $this->getRequest()->getParam('filename');
			$mount	   = $this->getRequest()->getParam('mount');
			$text	   = $this->getRequest()->getParam('text');
			$fontType  = $this->getRequest()->getParam('fontWeight');




			$result = Mage::getModel('experience/images', array('tree_id' => $this->treeId , 'current_dir'=> 'facebook'))
				->configIni()->
				createTempImgDirect( $filename ,$image , $mount  , $text, $fontType );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

		}

	}





	public function createimagewhatsappAction(){


		if($this->getRequest()->getPost()){

			$image 	   = $this->getRequest()->getParam('image');
			$filename  = $this->getRequest()->getParam('filename');
			$mount	   = $this->getRequest()->getParam('mount');
			$text	   = $this->getRequest()->getParam('text');
			$fontType  = $this->getRequest()->getParam('fontWeight');




			$result = Mage::getModel('experience/images', array('tree_id' => $this->treeId , 'current_dir'=> 'whatsapp'))
				->configIni()->
				createTempImgDirect( $filename ,$image , $mount  , $text, $fontType );
			if(is_array($result)){
				header('Content-type:application/json');
				echo  json_encode($result);
				exit;

			}

			echo $result;

		}

	}




}