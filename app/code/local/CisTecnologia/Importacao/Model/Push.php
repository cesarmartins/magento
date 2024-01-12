<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 06/11/2017
 * Time: 16:13
 */
class Seaway_Tree_Model_Push{


  CONST APP_ID = '80e48205-34be-4837-ba56-94e374fb9b8e';
  CONST REST_API_KEY = 'Njk2N2IwZWEtMGQ4Yi00NDVjLTg1ZTYtZTU4NjkzNDM0Yjdk';
  CONST URL_API = "https://onesignal.com/api/v1/notifications";



    public  function send( $instagram, $referenceMsg, $url , $type , $action = "1") {

      $appId = self::APP_ID;
      $restApiKey = self::REST_API_KEY;


      $message  = $this->getMessage($referenceMsg);
      if(empty($message['message'])){
         throw new Exception('Reference do not exist' , -3);
      }

      $title  = $message['title'];
      $msg    = $message['message'];

      $instagram = trim($instagram);
      $instagram = strtolower($instagram);
      $instagram = str_replace('@','',$instagram );


      $tags[] = array(
          "key" => 'user_'.$instagram,
          "relation" => "=",
          "value" => "1"
      );

//      $tags[] = array(
//          "key" => 'user_cesar.gringo',
//          "relation" => "=",
//          "value" => "1"
//      );

      $data = array(
          "action" => $action,
          "url" => $url,
          "type"   => (string) $type
      );


      $content = array(
            "en" => $msg
        );

        $title = array(
            "en" => $title
        );
        

        $fields = array(
            'app_id' => $appId,
            'contents' => $content,
            'headings' => $title,
            'tags' => $tags,
            'data' => $data,
            'content_available' => 1
        );


        $fields = json_encode($fields , true);

        var_dump($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL_API );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
            'Authorization: Basic '.$restApiKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($ch);
        curl_close($ch);


        $isReceivedPush = 0;
        $values = json_decode($result, true);
        $resultValue = false;
        if(!empty($values['id'])){
            $isReceivedPush = 1;
            $resultValue =  true;
        }
        if(!$resultValue){

            $pushMessage  = $instagram .' -> '. $result;
            Mage::log( $pushMessage , null , 'push_message.log' , true);

        }

        $this->saveLogPush($message['id'] ,$message['title'],$msg,$instagram ,$type,$url, $isReceivedPush);


        return $resultValue;

    }



    public function getMessage($referenceMsg ){


        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql =  "SELECT * FROM t_push_message WHERE reference  = :refer";
        $data  =  array('refer' => $referenceMsg );
        $message = $resource->fetchRow( $sql , $data);
        return $message;


    }



    public function saveLogPush($messageId ,$messageTitle,$messageText,$instagram ,$menuId,$url, $isReceivedPush = 1){

        try{
            $resource = Mage::getSingleton('core/resource')->getConnection('experienceus_write');


            $sql =  "INSERT INTO `log_push` (`message_id`,
                                         `title`,
                                         `message`,
                                         `instagram`,
                                         `menu_id`,
                                         `url`,
                                         `message_received`)
                                                    VALUES
                                                        (:message ,
                                                         :title ,
                                                         :text ,
                                                         :instagram ,
                                                         :menu ,
                                                         :url ,
                                                         :received)";

            $data  =  array(":message"  => $messageId ,
                ":title"    => $messageTitle,
                ":text"     => $messageText,
                ":instagram"=> $instagram ,
                ":menu"     => $menuId,
                ":url"      => $url,
                ":received" => $isReceivedPush);



            $resource->query($sql,$data);


        }catch (Exception $e){

             Mage::log($e->getMessage() , null , 'error_register_push_log.log',true);
        }




    }









}