<?php

class Seaway_Experience_AppController extends Mage_Core_Controller_Front_Action {


	public $instagram = null;
	public $customerSession = null;

	public function preDispatch(){

		parent::preDispatch();

		$this->customerSession = Mage::getSingleton('customer/session');

		$headerList = getallheaders();
		/* *********** header webview *********** */
		$this->instagram = '';
		if(!empty($headerList['username'])){
			$this->instagram = $headerList['username'];
		}

		//$this->instagram = 'seawayuser';
		if(empty($this->instagram)){
			$this->instagram = Mage::getModel('experience/experience')->getInstaSession();
		}elseif(!empty($instagram)){
			Mage::getModel('experience/experience')->saveInstaSession($this->instagram);
		}



		// se acessar pela web vai para página de error
        $isMobile = Zend_Http_UserAgent_Mobile::match(
			Mage::helper('core/http')->getHttpUserAgent(),
			$_SERVER
		);
		$actionName  = $this->getRequest()->getActionName();
		$isNotError  = (  strcasecmp($actionName,'error') != 0 )? true : false;
		if(!$isMobile  && $isNotError){
			$this->_redirect('experience/friend/error', array('_secure' => true));
			return;
		}


	}

	/*
	 * trazer o instagram (do app) do identify
	 *
	 */
	public function loginAction() {
		$this->loadLayout();
		$this->getLayout()->getBlock('experience')->setData('instagram' , $this->instagram);

		Mage::getModel('track/track')->trackLog( $this->instagram . ' - no_identify_login_app'   , null);

		$this->renderLayout();
	}

	public function freeAction() {
		$this->loadLayout();
		Mage::getModel('track/track')->trackLog();
		$this->renderLayout();
	}
	
	public function inviteAction() {
		$this->loadLayout();
		Mage::getModel('track/track')->trackLog();
		$this->renderLayout();
	}

	public function promoscoreAction() {
		$this->loadLayout();
		Mage::getModel('track/track')->trackLog();
		$this->renderLayout();
	}


	public function loginvalidateparticipantAction() {

		try{

			$data = array();
			$data['status'] = false;

			if(!$this->getRequest()->isPost()){
				throw new Exception('Request is not be valid.' , -2);
			}

			$isLoggedIn = $this->loginCustomer();

			if(!$isLoggedIn){
				throw new Exception('Email and password is invalid.' , -2);
			}

			$customerId  = $this->customerSession->getCustomer()->getEntityId();
			$tree  = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);

			if(empty($tree)){
				throw new Exception('User is not a participant.' , -2);
			}

			if(!empty($this->instagram)){
				//atualizar usuario com novo instagram
				Mage::getModel('tree/tree')->updateInstagram($tree['id'] ,  $this->instagram);
			}

			// caso esteja tudo ok redireciona
			$url  = Mage::getBaseUrl().'experience/user/redirect';

			$data['status'] = true;
			$data['data']= array('instagram' => $this->instagram , 'url' => $url);
			$data['msg'] = 'success';

		}catch(Exception $e){

			if($e->getCode() == -2 || $e->getCode() == -3){
				$message = $e->getMessage();
			}else{
				//$message = 'Internal server error';
				$message = $e->getMessage();
			}

			Mage::log('teste 6' , null , 'instagram_app7.log', true);

			$data['msg'] = $message;
			Mage::log('loginvalidateparticipant => '.$e->getMessage(), null , 'loginvalidate_experience.log' , true);

		}
		header('Content-Type:application/json');
		echo json_encode($data);
		die;

	}

	public function loginvalidatecustomerAction() {

		$return  = array('success'=> false);

		header('Content-Type:application/json');
		echo json_encode($return);
		die;

	}

	public function loginsaveoptionAction() {

		$return = array('success' => true);

		header('Content-Type:application/json');
		echo json_encode($return);
		die;

	}

	public function loginupdateinstagramAction() {

		$return = array('success' => false);

		header('Content-Type:application/json');
		echo json_encode($return);
		die;

	}

	private function loginCustomer(){

		$params  = $this->getRequest()->getParams();

		if(empty($params['login'])){
			throw new Exception('Login and password is invalid.' , -2);
		}
		$login   = $params['login'];
		if(empty($login['username'])){
			throw new Exception('Login is invalid' , -2);
		}

		if(empty($login['password'])){
			throw new Exception('Password is invalid' , -2);
		}


		$this->customerSession->login($login['username'], $login['password']);

		return $this->customerSession->isLoggedIn();

	}

}