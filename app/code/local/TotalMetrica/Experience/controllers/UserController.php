<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 29/09/2017
 * Time: 15:28
 */

class Seaway_Experience_UserController extends Mage_Core_Controller_Front_Action {


    // 1- procura em qual base o usuario est�
    // Mage::getModel('experience/experience')->searchUserInSeaway($id, $params);

    // 2- usuario encontrado?

    // 2.1 n�o -> usuario n�o existe na base (pelo IP) ou usuario n�o est� no programa e esta no magento (ja sabe o pais dele)
    // 2.1.1 -> identifica e redireciona para o site do pais de origem

    // 2.2 sim -> usuario est� no programa

    // 2.2.1 - j� aceitou push?
    // 2.2.1.1 n�o -> permanece na tela do push para aceitar
    // 2.2.1.2 sim -> identifica em qual fase do programa o usuario est� e redireciona



    private $params = null;

    /*
     * params login, country, username, token
     *
     *
     *
     */

    CONST SITE_URL_BR    = "https://seaway.com.br/";
   // CONST SITE_URL_BR    = "http://devbr.seaway/";
    CONST SALT = 'd968cfe1a7f9';
    CONST PASS = 'Seaway84*';


    public $customerSession = null;

    public function preDispatch(){
        parent::preDispatch();
        $headerList = getallheaders();
        //$instagram = 'seawayuser';
        //$headerList['username'] = 'marcelo_mota66';
        //$headerList['username'] = 'diegokabbaz';
        foreach($headerList as $key=>$value){
            $this->params[$key] = trim($value);
        }

        Mage::log($headerList, null , 'log_country.log', true);


        if(!empty($this->params['country'])){

            $country = $this->params['country'];
            if(!strpos($country , '-') === FALSE){
                $country = explode('-',$country);
                $this->params['country'] = strtoupper(end($country));
            }
            
        }

        /* *********** header webview *********** */
        $instagram = '';
        if(!empty($this->params['username'])){
            $instagram = $this->params['username'];
        }

        if(empty($instagram)){
            $this->params['username'] = Mage::getModel('experience/experience')->getInstaSession();
        }elseif(!empty($instagram)){
            Mage::getModel('experience/experience')->saveInstaSession($this->params['username']);
        }

        /* if(!empty($headerList['username']) && $paramLogin === false ){
             $this->programFlow();
         }*/

        // se o usuario estiver logado
      /*  $this->customerSession = Mage::getSingleton('customer/session');
        if($this->customerSession->isLoggedIn()){
            $this->flowCustomerLoggedIn();
        }*/





    }


    public function redirectAction(){

        $this->programFlow();
    }


    private function verifyIfBoolean($headerListLogin){
        $paramLogin = null;
        if(strcasecmp($headerListLogin , 'false') == 0){
            $paramLogin = false;
        }else if(strcasecmp($headerListLogin , 'true') == 0){
            $paramLogin = true;
        }
        return $paramLogin;
    }




    /* username - instagram do usuario
     * country  - pais informado pelo app
     * token    - token instagram
     *
     * login true consulta de renato
     * login false site
     *   - redirecionamento
     *   -
     *
     *
     */

    public function identifyAction(){

        try{
            $data = array();
            $data['status'] = false;
            $data['data']   = array();

            if(empty($this->params['username'])){
                throw new Exception('Instagram is Null.' , -2);

            }

            if(empty($this->params['country'])){
                throw new Exception('Country is Null.' , -2);

            }

            if(empty($this->params['token'])){
                throw new Exception('Token is Null.' , -2);

            }

            $instagram  = $this->params['username'];
            $response  = Mage::getModel('experience/experience')->instaVerify($instagram, $this->params['country']);


            if(empty($response)){
                throw new Exception('User not exist in the program.'  , -2);
            }


            Mage::getModel('track/track')->trackLog( $instagram . ' - login_app'   , null);



            $data['status'] = true;
            $data['data']   = $response;
            $data['message']    = 'success';

        }catch(Exception $e){

            if($e->getCode() == -2 || $e->getCode() == -3){
                $data['message'] = $e->getMessage();
            }else{
                $data['message'] = $e->getMessage();
               // $data['message'] = 'Internal server error.';
            }

            Mage::log($e->getMessage() , null , 'rest_user_identify.log' , true);
        }

        header('Content-Type:application/json');
        echo json_encode($data);
        die;

    }



    /*
     *  Dois m�todos:
     *
     *    - setar quando logar o app saber que o usuario esta logado
     *
     *    - quando o usuario for redirecionado ele saber quem � o usuario
     *      comparar na arvore e setar qual etapa ele est� caso seja a
     *      primeira vez que esta acessando:
     *
     *             - 1 ganhar bermuda
     *             - 2 escolher/indicar amigos
     *             - 3 promo score
     *
     *
     *     - verificar criptografia, chave, etc.
     */



    private function programFlow(){




        try{

            if(empty($this->params['username'])){
                throw new Exception('Instagram is Null.' , -2);
            }


            $valuesTree = array();
            $instagram = $this->params['username'];
            $valuesTree = Mage::getModel('experience/experience')->instaVerifyCountry($instagram);
            $redirectLocation = Mage::getBaseUrl('web');



            if(empty($valuesTree['values'])){
                $country = $this->params['country'];
                switch($country){
                    case 'US' : $this->redirectPreDispatch($redirectLocation.'loginfromapp/');  break;
                    case 'BR' :

                        require_once Mage::getBaseDir('lib').DS.'Util'.DS.'Util.php';
                        $instagramEncripty = Util_Util::encrypt($instagram, self::PASS , self::SALT);
                        $instagramEncripty = urlencode($instagramEncripty);
                        $urlRedirect  = self::SITE_URL_BR.'loginfromapp/?param='.$instagramEncripty;

                        $this->redirectPreDispatch( $urlRedirect);
                        break;
                }
            }

            $tree       = array();
            $tree = $valuesTree['values'];

            // verificando usuario na arvore
            $currentStep = (int)$tree['current_step'];

            if(empty($currentStep)){
                throw new Exception('Step not identified.' , -2);

            }
            $redirectLocation = "";
            if($valuesTree['country'] == 'US'){
                $redirectLocation = $this->flowChoiceAndPromoScoreUs($tree);
            }elseif($valuesTree['country'] == 'BR'){

                require_once Mage::getBaseDir('lib').DS.'Util'.DS.'Util.php';
                $instagramEncripty = Util_Util::encrypt($instagram, self::PASS , self::SALT);
                $instagramEncripty = urlencode($instagramEncripty);
                $redirectLocation  = self::SITE_URL_BR.'experience/user/redirect/?param='.$instagramEncripty;


            }


        }catch(Exception $e){
            /*Mage::getModel('customer/session')->unsetData('experience_callback_error');
             Mage::getModel('customer/session')->setData('experience_callback_error' , $e->getMessage() );*/
            Mage::log($e->getMessage() , null , 'user_programflow_error.log' , true);
        }


        $this->redirectPreDispatch($redirectLocation);

    }


/*    // se o usuario for do brasil obviamente nao vai se logar
    public function flowCustomerLoggedIn(){



        $customerId = $this->customerSession->getCustomer()->getEntityId();
        $tree = Mage::getModel('tree/tree')->getTreeByCustomerId($customerId);
        $redirectUrl = $this->flowChoiceAndPromoScoreUs($tree , true);
        $this->redirectPreDispatch($redirectUrl);

        exit;
    }*/


    public function flowChoiceAndPromoScoreUs($tree , $isLoggedIn = false){


        $redirectLocation = "";

        // se ele vai ganhar ele nao possui customer_id
        if( !empty($tree['current_step'])){
            $currentStep = $tree['current_step'];
            $redirectLocation = Mage::getBaseUrl('web');
            switch($currentStep) {
                case 1 :
                    // precisa ser escolhido pelo participante
                    try{

                        $treeApp  = Mage::getModel('tree/app');

                        // ja ganhou
                        $isWinBs = $treeApp->isCustomerGiftApp($tree['id']);
                        if($isWinBs){
                            // seta p/ 2 e da um reload na pagina
                            // Mage::getModel('tree/app')->changeCurrentStep(2 ,  $tree['id']);
                            //Mage::getModel('tree/app')->updateMenu(2 , $tree['id']);
                            Mage::getModel('tree/app')->setWinBs($tree['id']);

                            $customerId  = $tree['customer_id'];
                            // ordem do ultimo freepayment do usuario
                            $orderId =  Mage::getModel('experience/experience')->getLastOrderFreepayment($customerId);
                            $values   = Mage::getModel('nfe/invoice')->getOrderAwb($orderId);
                            $awbNumber = (!empty($values['awb_number']))? $values['awb_number'] :  0;
                            //787168259133
                            return 'https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='.$awbNumber;
                          // throw new Exception('User already won boarshort' , -2);
                        }

                        // verifica se ja possui link
                        $isAlreadyHaveLink = $treeApp->returnLastCoupon($tree['id']);
                        if(!empty($isAlreadyHaveLink)){
                            //devolve o link j� gerado
                            $redirectLocation = $isAlreadyHaveLink;
                        }else{
                            // gerar link
                            $redirectLocation = Mage::getModel('tree/app')->generateLink($tree['instagram'], $tree['parent_name'], $tree['id']);
                        }


                    }catch(Exception $e){
                        $msg  = "";
                        if($e->getCode() == -2 || $e->getCode() == -3  ){
                            $msg  = $e->getMessage();
                        }else{
                            $msg  = "Error generating coupon to win, please contact us";
                        }
                        $treeApp->setSessionAppError($msg);
                        Mage::log($e->getMessage() , null , 'user_won_app.log' , true);
                    }

                break;
                case ($currentStep > 1) :
                    if (!$isLoggedIn){
                        Mage::getModel('experience/experience')->autoLogin($tree['customer_id']);
                    }
                    if($currentStep == 2 ){
                        $redirectLocation .= "experience/friend";
                    }else if($currentStep == 3 ) {
                        $redirectLocation .= "experience/promoscore";
                    }else if($currentStep == 4) {
                        $redirectLocation .= "loginfromapp/";
                    }


                break;
            }

        }


        return $redirectLocation;

    }




    public function redirectPreDispatch($url){
        header("Location: $url");
        exit;
    }








}
