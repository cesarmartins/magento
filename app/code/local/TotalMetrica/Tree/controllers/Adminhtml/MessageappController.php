<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 01/02/2018
 * Time: 11:27
 */

class Seaway_Tree_Adminhtml_MessageappController extends Mage_Adminhtml_Controller_Action
{

    CONST SALT = 'd968cfe1a7f9';
    CONST PASS = 'Seaway84*';


    public function indexAction(){

        $this->loadLayout();
        $allValues = array();

        $dateStart = date('Y-m-d');
        $dateLast  = date('Y-m-d');

        if($this->getRequest()->isPost()){

            $dateStartSend = $this->getRequest()->getParam('date_start');
            $dateLastSend = $this->getRequest()->getParam('date_last');
            if($dateStartSend && $dateLastSend){
                $dateStart = $this->getRequest()->getParam('date_start');
                $dateLast  = $this->getRequest()->getParam('date_last');
            }
        }

        $allValues = Mage::getModel('tree/messageapp')->getAllMessages($dateStart  , $dateLast);

        //$tContact  = Mage::getModel('messageapp/message')->getAllMessagesTcontact($dateStart  , $dateLast);
        $this->getLayout()->getBlock('messageapp')
            ->setData('allvalues'  ,$allValues)
            //   ->setData('allcontact' , $tContact)
            ->setData('start'  ,$dateStart )
            ->setData('last'   ,$dateLast );
        $this->renderLayout();

    }



    public function chatAction(){

        $this->loadLayout();

        try{
            $insta        = $this->getRequest()->getParam('insta');
            $type         = $this->getRequest()->getParam('type');

            if(empty($insta)){
                throw new Exception('Instagram is null.' , -1);
            }

            if(empty($type)){
                throw new Exception('Type is null.' , -1);
            }

            $values  = Mage::getModel('tree/messageapp')->getMessagesChat($insta , $type);

            Mage::getModel('tree/messageapp')->lastMessageSetVisualizedAdmin($insta);

            $this->getLayout()->getBlock('messageappchat')
                ->setData('messages' , $values)
                ->setData('instagram' , $insta)
                ->setData('type' , $type);

            $this->renderLayout();

        }catch (Exception $e){
            $arrayErros = array(-1,-2,-3);
            $codeError  = $e->getCode();

            if(in_array($codeError , $arrayErros)){

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }else{

                Mage::getSingleton('adminhtml/session')->addError('An error occurred, look log messages.');
                Mage::log($e->getMessage() , null , 'error_messages_chat.log' , true);
            }

            $url  = Mage::getModel('adminhtml/url')->getUrl("messageapp/adminhtml_index/index/");
            $this->_redirectUrl($url);

        }


    }


    public function saveMsgChatAction(){

        try{

            $data = array();
            $data['msg'] = "";
            $data['status'] = false;

            if(!$this->getRequest()->isPost()){
                throw new Exception('request invalid.' , -1);
            }

            $msg  = $this->getRequest()->getParam('msg');
            if(empty($msg)){
                throw new Exception('Msg is null.' , -1);
            }

            $insta = $this->getRequest()->getParam('insta');
            if(empty($insta)){
                throw new Exception('Insta is null.' , -1);
            }

            $type = $this->getRequest()->getParam('type');
            if(empty($type)){
                throw new Exception('Type is null.' , -1);
            }

            $sendPush = $this->getRequest()->getParam('sendPush');
            if(!isset($sendPush)){
                throw new Exception('param sendPush invalid.' , -1);
            }




            Mage::getModel('messageapp/message')->saveMessagesChat($insta , $type , $msg);
            if(strpos($insta , '@') === false){
                $insta  = '@'.$insta;
            }

            if($sendPush == 1){
                $reference  = "message";
                require_once Mage::getBaseDir('lib').DS.'Util'.DS.'Util.php';
                $instagramEncripty = Util_Util::encrypt($insta, self::PASS , self::SALT);
                $instagramEncripty = urlencode($instagramEncripty);
                $url  	 	=  Mage::getBaseUrl('web')."messageapp/index/splash/?ikey=$instagramEncripty";
                $typePush 	=  10;
                Mage::getModel('tree/push')->send( $insta , $reference , $url , $typePush ,  "1" , $msg);
            }


            $data['status'] = true;
            $data['msg']    = 'success';

        }catch (Exception $e){
            $arrayErros = array(-1,-2,-3);
            $codeError  = $e->getCode();
            if(in_array($codeError , $arrayErros)){
                $data['msg']    = $e->getMessage();
            }else{
                $data['msg']    = $e->getMessage();
                // $data['msg']    = 'An error occurred, look log messages.';
                Mage::log($e->getMessage() , null , 'error_save_messages_chat.log' , true);
            }
        }

        header('content-type:application/json');
        echo json_encode($data);
        exit();


    }



    public function getmsgchatAction()
    {

        try {

            $data = array();
            $data['msg'] = "";
            $data['status'] = false;

            if (!$this->getRequest()->isPost()) {
                throw new Exception('request invalid.', -1);
            }

            $insta = $this->getRequest()->getParam('insta');
            if (empty($insta)) {
                throw new Exception('Insta is null.', -1);
            }

            $messagesNotRead =  Mage::getModel('messageapp/message')->getAllMessagesInstagram($insta, true);

            $data['status'] = true;
            $data['messages'] = $messagesNotRead;
            $data['msg'] = 'success';

        } catch (Exception $e) {
            $arrayErros = array(-1, -2, -3);
            $codeError = $e->getCode();
            if (in_array($codeError, $arrayErros)) {
                $data['msg'] = $e->getMessage();
            } else {
                $data['msg'] = 'An error occurred, please contact a Seaway.';
                Mage::log($e->getMessage(), null, 'error_save_messages_chat.log', true);
            }
        }

        header('content-type:application/json');
        echo json_encode($data);
        exit();


    }
}