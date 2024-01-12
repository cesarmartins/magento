<?php

class Seaway_Tree_Model_Messageapp extends Mage_Core_Model_Abstract{


    public function getAllMessages($dateStart  , $dateLast){

        $_conn = Mage::getSingleton('core/resource')->getConnection('read');
        $sql   = "SELECT * FROM t_message_app  WHERE DATE(created_at) >= :start and DATE(created_at) <= :last ORDER BY created_at DESC ";
        $data  = array("start" => $dateStart , "last" => $dateLast);
        $result  = $_conn->fetchAll($sql , $data);
        $_conn->closeConnection();
        return $result;

    }


    public function getAllMessagesInstagram($instagram , $noRead  = false)
    {
        $_conn = Mage::getSingleton('core/resource')->getConnection('read');

        $sqlNoRead  ="";
        if($noRead == true){
            $sqlNoRead  =" AND message_read = 0";
        }

        $sql = "SELECT *, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as date_formated FROM t_message_app  WHERE REPLACE(instagram , '@' , '' )= REPLACE(:insta, '@' , '' ) $sqlNoRead ORDER BY created_at ASC ";
        $data = array("insta" => $instagram);
        $result = $_conn->fetchAll($sql, $data);
        $_conn->closeConnection();
        return $result;

    }


    public function getMessagesChat($instagram){

        $resultsChat    = array();
        $messagesApp = array();
        if(!empty($instagram)){

            $messagesApp  = $this->getAllMessagesInstagram($instagram);
            $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            echo $sql   = "select * from t_chat_messages where replace(instagram_invited , '@' , '') = replace(:instagram , '@' , '')";
            $data  =  array("instagram" => $instagram );
            $resultsChat  = $_conn->fetchAll($sql , $data);

            $messages   = array();
            foreach($messagesApp as $messageApp){
                $messages[strtotime($messageApp['created_at'])] = array('id' => $messageApp['id']   ,'date' =>  $messageApp['created_at'] , 'msg' => $messageApp['msg'] , 'user' => $messageApp['instagram']);
            }
            foreach($resultsChat  as  $chat){
                $timestamp = "";
                $timestamp = strtotime($chat['created_at']);
                if(isset($messages[$timestamp])){
                    do{
                        $isGo  = false;
                        $timestamp += 1;
                        if(!isset($messages[$timestamp])){
                            $isGo = true;
                        }
                    }while(!$isGo);
                }
                $messages[$timestamp] =  array( 'id' => $chat['id']   , 'date' =>  $chat['created_at'] , 'msg' => utf8_encode($chat['msg']) , 'user' => 'seaway');
            }

            ksort($messages);

        }

        return $messages;

    }


    public function saveMessagesChat($instagram , $type , $msg){




        if(!empty($instagram) && !empty($type) && !empty($msg)  ) {


            $tree  = Mage::getModel('tree/tree')->getTreeByInsta($instagram);
            $treeId = null;
            if(!empty($tree)){
                $treeId = $tree['id'];
            }
            $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql = "INSERT INTO t_chat_messages(type , tree_id , instagram_invited , msg)
                VALUES (:type , :tree , :insta , :msg)";
            $data = array('type' => $type, 'tree' => $treeId, 'insta' => $instagram, 'msg' => $msg);
            $conn->query($sql, $data);
            $conn->closeConnection();
        }


    }


    public function  getMessagesChatNotRead($instagram){

        $_conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql   = "select * , DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as date_formated from t_chat_messages where instagram_invited = :instagram AND message_read = 0 ORDER BY id ASC";
        $data  =  array("instagram" => $instagram );
        $resultsChat  = $_conn->fetchAll($sql , $data);
        return $resultsChat;

    }


    public function saveMessages($instagram , $msg){

        if(!empty($instagram) && !empty($msg)  ) {
           $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
           $sql = "INSERT INTO t_message_app( msg , instagram , area)
                VALUES (:msg , :insta ,:area)";
           $data = array('msg' => $msg, 'insta' => $instagram  , 'area' => 'chat' );
           $conn->query($sql, $data);
           $conn->closeConnection();
        }


    }


    public function lastMessageSetVisualized($instagram){

        if(strpos($instagram ,'@' === false )){
            $instagram = '@'.$instagram;
        }
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE t_chat_messages SET message_read = 1 WHERE `instagram_invited` = :insta";
        $data  =  array( ":insta"  => $instagram);
        $resource->query($sql,$data);

    }



    public function lastMessageSetVisualizedAdmin($instagram){

        if(!(strpos($instagram ,'@' === false ))){
            $instagram = str_replace('@' , '' , $instagram );
        }
        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE t_message_app SET message_read = 1 WHERE `instagram` = :insta";
        $data  =  array( ":insta"  => $instagram);
        $resource->query($sql,$data);

    }




    public function setTypingChat($instagram){

        if(strpos($instagram ,'@' === false )){
            $instagram = '@'.$instagram;
        }
        $dir = Mage::getBaseDir('var').DS.'chat'.DS.$instagram.'.txt';
        if(!file_exists($dir)){
            $handle = fopen($dir ,'a+');
            fclose($handle);
        }
    }

    public function removeTypingChat($instagram){

        if(strpos($instagram ,'@' === false )){
            $instagram = '@'.$instagram;
        }
        $dir = Mage::getBaseDir('var').DS.'chat'.DS.$instagram.'.txt';
        if(!file_exists($dir)){
          unlink($dir);
        }
    }



}
