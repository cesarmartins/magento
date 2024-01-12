<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 11/10/2017
 * Time: 11:51
 */

class Seaway_Experience_AwardsController extends Mage_Core_Controller_Front_Action {



    private $params = null;

    public function preDispatch()
    {
        parent::preDispatch();

        $id = null;
        // SIMULATE INSTAGRAM
        // header('username: rcdrigc');
        // header('token: 4565445654654654654');
        // $id = 'instagram';


        /*$headerList = getallheaders();
        if(!empty($headerList['username'])){

            $this->params = array(
                'username' => $headerList['username']

            );

        }*/

    }

    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }


    public function uploadAction(){

        try{

            $result = array();
            $data['status'] = false;

            if(!$this->getRequest()->isPost()){
                throw new Exception('Request invalid ', -2);
            }
            /* if(empty($this->params['username'])){
                throw new Exception('Username require.', -2);
            }*/
            $description = $this->getRequest()->getParam('msg');
            // if(empty($description)){
            //     throw new Exception('Request description invalid ', -2);
            // }

            $paramsLog = var_export($this->getRequest()->getParams(),true);
            $files     = var_export($_FILES,true);
            Mage::log($paramsLog , null , 'awards_upload_params.log' , true);
            Mage::log($files     , null , 'awards_upload_params.log' , true);

            $instagram   = $this->getRequest()->getParam('username');
            if(empty($instagram)){
                throw new Exception('Request username invalid ', -2);
            }




            if(!empty($_FILES['attachment']['name'])) {

                if (empty($_FILES['attachment']['tmp_name'])) {
                    throw new Exception('Attachment tmp_name invalid ', -2);
                }
                $tempFile = $_FILES['attachment']['tmp_name'];

                if (empty($_FILES['attachment']['name'])) {
                    throw new Exception('Attachment name invalid ', -2);
                }
                $nameFile = $_FILES['attachment']['name'];

                $type        = $this->getRequest()->getParam('type');
                $type        = 1 ;
                if(empty($type)){
                    throw new Exception('Request type invalid ', -2);
                }

                list($linkImg, $id) = Mage::getModel('experience/experience')->uploadMedia($instagram, $tempFile, $nameFile, $type, $description);

                if (!empty($linkImg) && !empty($id)) {
                    $result[] = array("statusFile" => "ok", "resultFile" => $linkImg, 'upload_id' => $id);
                }
            }

            $msg  = $this->getRequest()->getParam('msg');
            if(!empty($msg)){
               $resultMessage =  Mage::getModel('experience/experience')->saveMessageApp($instagram , $msg);
               if($resultMessage){
                   $result[] = array( "statusMsg" => "ok", "resultMsg" => "RECEBIDO: ".$msg);
               }else{
                   $result[] = array( "statusMsg" => "fail", "resultMsg" => "");
               }

            }else{
                 $result[] = array( "statusMsg" => "fail", "resultMsg" => "");
            }




        }catch (Exception $e){


            $result[] = array( "statusFile" => "fail", "resultFile" => "");
            if($e->getCode() == -2 ||  $e->getCode() == -3 ){
                //$data['msg']    = $e->getMessage();
            }else{
                // $data['msg']    = 'Internal server error.';
                //$result[] = array( "statusFile" => "fail", "resultFile" => "");
            }
            Mage::log($e->getMessage() , null , 'awards_upload_error.log' , true);
            $exportResult  = var_export( $result ,true);
            Mage::log('catch : '.$exportResult , null , 'awards_upload_result.log' , true);
            header('Content-Type:application/json');
            http_response_code(401);
            $final = array("result" => $result);
            echo json_encode($final);
            die;


        }

        $exportResult  = var_export( $result ,true);
        Mage::log('final : '. $exportResult , null , 'awards_upload_result.log' , true);

        header('Content-Type:application/json');
        $final = array("result" => $result);
        echo json_encode($final);
        die;

    }



}