<?php

class Seaway_Experience_FriendController extends Mage_Core_Controller_Front_Action {

	private $params = null;
	public $instagram = null;

	CONST CURRENT_STEP_PAGE = 2;

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

		$fullActionName = $this->getFullActionName();
		if($fullActionName != 'experience_friend_error') {
			
			if (empty($this->instagram)) {
				throw new Exception('Instagram is Null.', -2);
			}

			$participant = array();
			$currentStep = 0;
			if (!empty($this->instagram)) {
				$participant = Mage::getModel('experience/experience')->instaVerifyCountry($this->instagram);
				$currentStep = (!empty($participant['values']['current_step'])) ? $participant['values']['current_step'] : 0;
			}

			/*$thisProgramIsRight = Mage::getModel('experience/experience')->thisProgramIsRight($currentStep, self::CURRENT_STEP_PAGE);
            if(!$thisProgramIsRight){
                $url = Mage::getBaseUrl()."experience/user/redirect";
                $this->redirectPreDispatch($url);
            }*/

			// @TODO: verificar este fluxo
			$customerSession = Mage::getSingleton('customer/session');
			if ($customerSession->isLoggedIn()) {

				if (empty($participant['values'])) {
					Mage::getSingleton('customer/session')->logout();
					$this->_redirect('/', array('_secure' => true));
					return;
				}

			} else {

				if (!empty($participant['values']['customer_id'])) {

					$customer = Mage::getModel('customer/customer')->load($participant['values']['customer_id']);
					$customerSession->setCustomerAsLoggedIn($customer);
					$this->_redirect('experience/friend', array('_secure' => true));
					return;

				} else {
					Mage::getSingleton('customer/session')->logout();
					$this->_redirect('/', array('_secure' => true));
					return;
				}

			}
		}

    }


	public function indexAction() {

		//http://devus.seaway/experience/friend/?ig=@cesar.gringo
		$this->loadLayout();

		$customerId = NULL;
		$username = null;
		$customer = $this->getCustomerLoggedIn();
		if(!empty($customer)){
			$customerId = $customer->getEntityId();
		}

		$cid = $this->getRequest()->getParam('cid');

		Mage::log('cid:' . $cid , null , 'experienceindex.log',true);
		$retornoTree = null;
		if(empty($cid)){
			if(!empty($this->params["username"])){
				Mage::log("username " . $this->params["username"] , null , 'experienceindex.log',true);
				$retornoTree = Mage::getModel("tree/tree")->getTreeByInsta($this->params["username"]);
				$username = $this->params["username"];
			}elseif(!empty($this->instagram)){
				Mage::log("instagram " . $this->instagram , null , 'experienceindex.log',true);
				$retornoTree = Mage::getModel("tree/tree")->getTreeByInsta($this->instagram);
				$username = $this->instagram;
			}
			if(!empty($retornoTree["customer_id"])){
				$cid = $retornoTree["customer_id"];
				Mage::getSingleton('customer/session')->setData('firstname', $retornoTree["nome"]);
				Mage::getSingleton('customer/session')->setData('retornotree', $retornoTree);
				Mage::getSingleton('customer/session')->setData('instagram', $username);
				Mage::getSingleton('customer/session')->setData('cid', $cid);
			}
		}



		$tree = array();
    	try{

			// deadline set first access on app
			Mage::getModel('tree/deadline')->setFirstAccess();
			Mage::getModel('tree/deadline')->setInvitedDeadline();


			$tree = Mage::getModel('tree/app')->getTreeList($cid);
			Mage::getModel('customer/session')->setData('mostrarSucessMessage', false);
			// $tree['sellwinboardshort'] = Mage::getModel('tree/app')->getCouponAppInfo($customerId);
			// $tree['sellwinboardshort']['progressbar'] = Mage::getModel('tree/app')->getProgressBar($customerId);
			//Mage::getModel('experience/experience')->setFirstAccess(self::CURRENT_STEP_PAGE);

			Mage::getModel('track/track')->trackLog( $this->instagram . ' - friend_list'   , null);

    	}catch (Exception $e){

			if($e->getCode() == -3){
				
			}

			echo $e->getMessage();
			die;
    	}

		$this->getLayout()->getBlock('experience')->setTree($tree)->setConstumer($retornoTree);
		Mage::getModel('track/track')->trackLog(null, $customerId);
		$this->renderLayout();

	}




	public function postponepromocodeforyouAction(){

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

			$customerId = $customer->getEntityId();
			$link = Mage::getModel('tree/app')->setPostponePromoCode($customerId);

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


	/**
	 * Function received POST message params below :
	 *
	 * @params email , flag(discount) , position
	 *
	 *
	 */





	private function getCustomerLoggedIn(){
		$objCustomerLogin = Mage::getSingleton('customer/session');
		$customer = NULL;
		if($objCustomerLogin->isLoggedIn()){
			$customer = $objCustomerLogin->getCustomer();
		}
		return $customer;
	}



	public function savecontactchannelAction(){


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

			$message  = $this->getRequest()->getParam('message');
			if(empty($message)){
				throw new Exception('Message is invalid.' , -2);
			}


			$customerId = $customer->getEntityId();
			Mage::getModel('tree/app')->saveMessageContact( $customerId ,$message );

			$data['status'] = true;
			$data['msg'] = 'sucesso';

		}catch(Exception $e){
			$message  = "Error, please try again.";
			if($e->getCode()  == -2 || $e->getCode()  == -3 ){
				$message  = $e->getMessage();
			}
			$data['msg'] = $message;
		}


		header('Content-Type:application/json');
		echo json_encode($data);
		die;





	}


	public function generatesellwinAction(){
		try {

			$data = array();
			$data['status'] = false;

			if(!$this->getRequest()->getPost()){
				throw new Exception('Request invalid.' , -2);
			}

			$customer  = $this->getCustomerLoggedIn();
			if(empty($customer->getData())){
				throw new Exception('Customer is not logged in.' , -2);
			}

			$qtyCouponUsed = $this->getRequest()->getParam('qtycouponused');
			if (empty($qtyCouponUsed)) {
				throw new Exception('quantity coupon used is not valid.', -2);
			}

			$customerId = $customer->getEntityId();
			$coupon = Mage::getModel('tree/app')->generateSellWin($customerId , $qtyCouponUsed);


			$data['status'] = true;
			$data['msg']	= 'sucesso';
			$data['link'] = $coupon;
		}catch (Exception $e){

			$message  = $e->getMessage();
			if($e->getCode() == -2 && $e->getCode() == -3 ){
				$message  = 'Error , please try again';
			}

			$data['msg'] = $message;
			Mage::log($e->getMessage() , null , 'savemenuAction.log' , true);

		}


		header('Content-Type:application/json');
		echo json_encode($data);
		die;


	}


 //



	public function checkinstagramtreeAction(){
		try {

			$data = array();
			$data['status'] = false;

			if(!$this->getRequest()->getPost()){
				throw new Exception('Request invalid.' , -2);
			}

			$customer  = $this->getCustomerLoggedIn();
			if(empty($customer->getData())){
				throw new Exception('Customer is not logged in.' , -2);
			}

			$instagram  = $this->getRequest()->getParam('instagram');
			if (empty($instagram)) {
				throw new Exception('Instagram is not exist', -2);
			}

			$coupon= Mage::getModel('tree/app')->instagramIsExist($instagram );

			$data['status'] = $coupon;
			$data['msg']	= 'sucesso';
		}catch (Exception $e){

			$message  = $e->getMessage();
			if($e->getCode() == -2 && $e->getCode() == -3 ){
				$message  = 'Error , please try again';
			}

			$data['msg'] = $message;
			Mage::log($e->getMessage() , null , 'checkinstagramtreeAction.log' , true);

		}


		header('Content-Type:application/json');
		echo json_encode($data);
		die;


	}



	public function saveindicatesAction(){

		try{
			$data = array();
			$data['status'] = false;

			if(!$this->getRequest()->getPost()){
				throw new Exception('Request invalid.' , -2);
			}

			$customer  = $this->getCustomerLoggedIn();
			if(empty($customer->getData())){
				throw new Exception('Customer is not logged in.' , -2);
			}


			$instagram = $this->getRequest()->getParam('instagram');
			if (empty($instagram)) {
				throw new Exception('Param instagram is invalid.', -2);
			}


			$customerId = $customer->getEntityId();
			$tree = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);

			if(empty($tree))
				throw new Exception('User not found in tree' , -2);

			$parentId  = $tree['id'];
			$result = Mage::getModel('tree/app')->saveChildrens($instagram,$parentId ,  0  , true);

			$data['data'] = $result;
			$data['status'] = true;
			$data['msg']	= 'sucesso';

		}catch (Exception $e){

			$message  = $e->getMessage();
			if($e->getCode() == -2 && $e->getCode() == -3 ){
				$message  = 'Error , please try again';
			}

			$data['msg'] = $message;
			Mage::log($e->getMessage() , null , 'childrenfriendsAction.log' , true);

		}

		//{"status":true,"data":[{"input":"#friend-00","instagram":"cesar.gringo.filho","result":true}],"msg":"sucesso"}
		//		$data['data']   = array("input" => "#friend-03", "instagram" => "cesar.gringo.filho", "result" => true);
		//		$data['status'] = true;
		//		$data['msg']	= 'sucesso';
		//		$data = (object) $data;

		header('Content-Type:application/json');
		echo json_encode($data);
		die;
	}

	public function sendmessageAction(){

		$msn = trim($this->getRequest()->getPost('msn'));

		if(!empty($msn)){

			$nome  = Mage::getSingleton('customer/session')->getData('firstname');
			$insta = Mage::getSingleton('customer/session')->getData('instagram');

			$retorno = $this->sendEmailContato($nome, $insta, $msn);

			header('Content-Type:application/json');
			echo json_encode($retorno);
			die;

		}

	}

	public function sendEmailContato($nome, $insta, $mensagem) {

		$retorno = "";
		$html="<p style=\"color: #444444; font-size: 18px; font-family: Helvetica; text-align: left; margin: 0 0 50px 0; line-height: 25px;\">
                <b>Nome:</b> $nome <br/>
                <b>Instagram:</b> $insta <br/>
                <b>Mensagem:</b> $mensagem </p>";

		$mail = Mage::getModel('core/email')
			->setToName($insta)
			->setToEmail('sac@seaway.com.br')
			->setFromEmail('sac@seaway.surf')
			->setFromName('Seaway Experience Program')
			->setBody($html)
			->setSubject('Seaway Experience Program - Seaway.surf')
			->setType('html');

		try {
			$mail->send();
			$retorno["status"] = true;
			$retorno["msg"] = 'sucesso';
			//$this->_redirect('experience/friend/');

		} catch (Exception $error) {

			$retorno["status"] = false;
			$retorno["msg"] = $error;
			//$this->_redirect('experience/friend/');
			
		}
		return $retorno;
	}

	public function savemenuAction(){

		try {

			$data = array();
			$data['status'] = false;

			if(!$this->getRequest()->getPost()){
				throw new Exception('Request invalid.' , -2);
			}

			$customer  = $this->getCustomerLoggedIn();
			if(empty($customer->getData())){
				throw new Exception('Customer is not logged in.' , -2);
			}

			$menu  = $this->getRequest()->getParam('menu');
			if (empty($menu)) {
				$menu  = 0;
				//throw new Exception('Menu is not valid.', -2);
			}


			$customerId = $customer->getEntityId();
			Mage::getModel('tree/app')->saveMenuApp($customerId , $menu);


			$data['status'] = true;
			$data['msg']	= 'sucesso';

		}catch (Exception $e){

			$message  = $e->getMessage();
			if($e->getCode() == -2 && $e->getCode() == -3 ){
				$message  = 'Error , please try again';
			}

			$data['msg'] = $message;
			Mage::log($e->getMessage() , null , 'savemenuAction.log' , true);

		}


		header('Content-Type:application/json');
		echo json_encode($data);
		die;

	}




	public function chooseparticipantAction() {


		try{

			$result = array(
				'status' => false,
				'msg'	 => ''
			);

			if(!$this->getRequest()->isPost()){
				throw new Exception("Request is not valid.", -1);
			}

				// instagram of child
			$id 	 = $this->getRequest()->getParam('id');
			$checked = $this->getRequest()->getParam('checked');

			$checked = boolval($checked);

			if( !isset($id)|| empty($id)){
				throw new Exception("id is empty.", -1);
			}


			Mage::getModel('tree/app')->saveChoose($id  , $checked );
			$result['status'] = true;
			$result['msg']  = "success";


		}catch (Exception $e){
			$result['msg'] = $e->getMessage();
		}

		header('Content-Type:application/json');
		echo json_encode($result);
		die;
	}




	
	public function contactedparticipantAction() {

		if($this->getRequest()->isPost()){
        	
        	$result = array(
	            'status' => false
	        );

            $id = $this->getRequest()->getParam('id');


            if(empty($id)){
            	throw new Exception("id is empty.", -1);
            }

            try{
    			$tree = Mage::getModel('tree/app')->saveContacted($id);
    			$result['status'] = true;
	    	}catch (Exception $e){
				if($e->getCode() == -3){
					echo $e->getMessage();
				}    		
	    	}

	        header('Content-Type:application/json');
	        echo json_encode($result);
	        die;

        }

	}

	public function savemessageparticipantAction() {


		if($this->getRequest()->isPost()){
        	
        	$result = array(
	            'status' => false
	        );

            $id = $this->getRequest()->getParam('id');
            $message = $this->getRequest()->getParam('message');

            if(empty($id)){
            	throw new Exception("id is empty.", -1);
            }

            try{
    			$tree = Mage::getModel('tree/app')->saveMessage($id, $message);
    			$result['status'] = true;
	    	}catch (Exception $e){
				if($e->getCode() == -3){
					echo $e->getMessage();
				}    		
	    	}

	        header('Content-Type:application/json');
	        echo json_encode($result);
	        die;

        }


	}







	public function errorAction(){
		$this->loadLayout();
		$this->renderLayout();
	}


	public function downloadAction(){
		require_once Mage::getBaseDir('lib').'/Util/Util.php';

		$id = "@jazielmatoso_insta_ind.jpg";
		$id = $this->getRequest()->getParam('id');
		$dir = Mage::getBaseDir('skin')."/adminhtml/default/default/images/experience_insta/".$id;
		try{
			Util_Util::downloadFileMobile($dir);
		}catch (Exception $e){
			echo $e;

		}
		die;
	}


	public function redirectPreDispatch($url){
		header("Location: $url");
		exit;
	}

}