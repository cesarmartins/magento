<?php
class TotalMetrica_Experience_Model_Experience
{

    private $resource = null;
    private $seawayExperienceDb = null;

    public function __construct()
    {
        //parent::_construct();
        //$this->_init('tree/tree');
        //$this->resource = Mage::getSingleton('core/resource')->getConnection('media_read');
    }


    public function autoLogin($customerId)
    {
        $result = false;
        try {
            Mage::getSingleton('customer/session')->loginById($customerId);
            $result = true;
        } catch (Exception $e) {
            Mage::log('Model Experience/autoLogin: ' . $e->getMessage(), null, 'experience.php', true);
            throw new Exception('AutoLogin has been error ', -3);
        }


        return $result;
    }


    CONST uploadDir = 'frontend/seaway/iphone/images/experience/award';


    public function getUploadDir($country)
    {

        $url = "";
        switch ($country) {
            case 'BR':
                $url = Mage::getBaseDir('skin') . '/' . self::uploadDir . '/br/';
                break;
            case 'US':
                $url = Mage::getBaseDir('skin') . '/' . self::uploadDir . '/us/';
                break;
        }

        if (!empty($dataBase)) {
            throw new Exception('Country does not have DIR Seaway Experience!', -3);
        }
        return $url;


    }


    public function getUploadUrl($country)
    {

        $url = "";
        switch ($country) {
            case 'BR':
                $url = Mage::getBaseUrl('skin') . self::uploadDir . '/br/';
                break;
            case 'US':
                $url = Mage::getBaseUrl('skin') . self::uploadDir . '/us/';
                break;
        }

        if (!empty($dataBase)) {
            throw new Exception('Country does not have DIR Seaway Experience!', -3);
        }
        return $url;


    }


    public function uploadMedia($insta, $path, $name, $type = 1, $description = "teste")
    {

        $valores = $this->instaVerifyCountry($insta);
        // if (empty($valores)) {
        //     throw new Exception('User not exist in the program.', -3);
        // }

        if (!empty($valores['values'])) {

            $treeId = $valores['values']['id'];
            $country = $valores['country'];
            $this->configDbByCountry($country);

            $dir = $this->getUploadDir($country);
            $extension = $this->getExtension($name);

            $namePath = time() . '_' . $treeId . '.' . $extension;
            $imagePath = $dir . $namePath;


            move_uploaded_file($path, $imagePath);

            // $isWrited = file_put_contents($imagePath, base64_decode($path));
            // if ($isWrited === FALSE) {
            //     throw new Exception('File is not be uploaded.', -3);
            // }

            if (!file_exists($imagePath)){
                throw new Exception("Fail", -3);
                //$result[] = array( "statusFile" => "ok", "resultFile" => $actual_link);
            }
            $pathValue = $this->getUploadUrl($country) . $namePath;
            $idValue   = $this->saveUpload($treeId, $pathValue, $namePath, $type, $description);

            return array($pathValue  , $idValue);
        }

    }

    private function getExtension($name)
    {

        $extension = "";

        $values = explode('.', $name);
        $extension = end($values);

        return $extension;
    }

    public function saveUpload($treeId, $path, $name, $type, $desc)
    {


        $conn = Mage::getModel('core/resource')->getConnection($this->getDataBaseExperience());
        $typeValue = 1;
        if ($type == 'video') {
            $typeValue = 2;
        }
        $sql = "INSERT INTO `media_tree`(`tree_id`,
                                            `media_path`,
                                            `media_name`,
                                            `media_type` ,
                                            `description`)
                                            VALUES
                                            (:tree,
                                            :path,
                                            :name,
                                            :type ,
                                            :desc)";


        $data = array('tree' => $treeId,
            'path' => $path,
            'name' => $name,
            'type' => $typeValue, 'desc' => $desc);

        $conn->query($sql, $data);
        $conn->closeConnection();


        return $this->getLastMediaId();


    }



    private function getLastMediaId(){

        $conn = Mage::getModel('core/resource')->getConnection($this->getDataBaseExperience());
        $sql = "SELECT id FROM `media_tree` ORDER BY created_at DESC";
        $lastId  = $conn->fetchRow($sql);
        $conn->closeConnection();
        if(empty($lastId['id']))
            throw new Exception('last insert id not be returned' , -3);

        return $lastId['id'];
    }



    private function getMediaConnection($country)
    {
        if (empty($this->configDatabasesCountry[$country]['connection'])) {
            throw new Exception('Connection not exist.', -3);
        }
        return $this->configDatabasesCountry[$country];
    }


    private $configDatabasesCountry = array(
        'US' => array(
            'connection' => 'read',
            'table' => 't_tree',
            'redirect_url' => 'https://www.seaway.com.br'

        ),
        'BR' => array(
            'connection' => 'seawaybr_read',
            'table' => 't_tree',
            'redirect_url' => 'https://seaway.surf'
        )

    );

    public function instaVerify($instagram, $countryApp = null)
    {
        $valores = $this->instaVerifyCountry($instagram);
        $valuesCountry = array();
        if (!empty($valores['values']) && $valores['values']['current_step'] != 4  ) {

                $nome = trim($valores['values']['nome']);
                if (!(strpos($nome, ' ') === FALSE)) {
                    $nomes = explode(' ', $nome);
                    $nome = current($nomes);
                }

                $valuesCountry['name'] = $nome;
                $valuesCountry['step'] = $valores['values']['current_step'];
                $valuesCountry['menu'] = $this->getMenu($valores['values']['id'], $valores['country'] , $valores['values']['current_step'],$instagram);
                $valuesCountry['text'] = $this->getText($valores['country'], $valores['values']['current_step']);

        } else {

            $step = 4;
            $valuesCountry['name'] = '';
            $valuesCountry['step'] = 4;
            $valuesCountry['menu'] = $this->getMenu(null, $countryApp , $step);
            $valuesCountry['text'] = $this->getText($countryApp, $step);


        }

        return $valuesCountry;
    }


    public function getText($country, $currentStep)
    {

        $conn = Mage::getModel('core/resource')->getConnection($this->seawayExperienceDb);

        $conn->query('SET CHARACTER SET utf8;');
        $sql = "select page , text , size  from text  where fase_tree = :step";
        $data = array('step' => $currentStep);
        $valores = $conn->fetchAll($sql, $data);

        $result = array();
        foreach ($valores as $valor ) {
            $val = "";
            $val = $valor['text'];
            eval("\$val=\"$val\";");
            $valor['text'] = $val;
            $result[]= $valor;
        }
        $conn->closeConnection();
        return $result;
    }


    public function instaVerifyCountry($instagram)
    {


        $valuesCountry = array();
        foreach ($this->configDatabasesCountry as $country => $conf) {
            $conn = null;
            if (!empty($conf['connection']) && $conf['table']) {

                $conn = Mage::getModel('core/resource')->getConnection($conf['connection']);

                $instagram = trim($instagram);
                $sql = "select *  from " . $conf['table'] . " where REPLACE(instagram, '@','') = REPLACE(:insta,'@','')";

                $data = array('insta' => $instagram);
                $valores = $conn->fetchRow($sql, $data);

                $conn->closeConnection();
                $ifExists = (!empty($valores)) ? true : false;

                if ($ifExists) {
                    $valuesCountry['country'] = $country;
                    $valuesCountry['values'] = $valores;
                    break;
                }
            }
        }
        return $valuesCountry;
    }





    public function configDbByCountry($country)
    {
        $database = "";
        switch ($country) {
            case 'BR':
                $database = 'experiencebr_write';
                break;
            case 'US':
                $database = 'experienceus_write';
                break;
        }

        if (!empty($dataBase)) {
            throw new Exception('Country does not have database Seaway Experience!', -3);
        }
        $this->seawayExperienceDb = $database;
    }


    public function getDbSiteByExperienceDb($seawayExperienceDb)
    {
         $database = "";
        switch ($seawayExperienceDb) {
            case 'experiencebr_write':
                $database = 'seawaybr_read';
                break;
            case 'experienceus_write':
                $database = 'read';
                break;
        }

        if (!empty($dataBase)) {
            throw new Exception('Country does not have database Seaway Experience!', -3);
        }
       return  $database;
    }




    public function  getDataBaseExperience()
    {
        return $this->seawayExperienceDb;
    }


    public function createAllMenuUser($treeId , $currentStep )
    {
        $currentStepAlow  = range(0,3);
        if (isset($currentStep) && !is_numeric($currentStep) && !in_array($currentStep ,$currentStepAlow)) {
            throw new Exception('Current step is Empty!', -3);
        }

        if (empty($treeId) && !is_numeric($treeId)) {
            throw new Exception('TreeId is Empty!', -3);
        }

        $connExp = Mage::getSingleton('core/resource')->getConnection($this->getDataBaseExperience());
        $sql = "DELETE FROM menu_tree WHERE tree_id = :tree";
        $data  = array('tree'=> $treeId);
        $connExp->query($sql , $data);

        $menuOfFirstAccess = 1;
        $alreadyLogged = $this->existFirstAccess($treeId , $currentStep);
        if($alreadyLogged) {
            $menuOfFirstAccess = 0;
        }

        $sqlCnf = "SELECT menu_id FROM menu_step WHERE step = :current AND first_access = :first";
        $dataCnf = array('current' => $currentStep, 'first' => $menuOfFirstAccess);

        if($treeId == 3996){
            $log = var_export($dataCnf , true);
            Mage::log($log , null, 'menu_step3.log' , true);
        }


        $settings = $connExp->fetchAll($sqlCnf , $dataCnf);

        $menuIds = array();
        foreach($settings as $set){
            $menuIds[]= $set['menu_id'];
        }
        $menuIds = implode(',' , $menuIds);

        if (empty($menuIds)) {
            throw new Exception('No menu itens for this step!', -3);
        }
        $sqlInsertMenu = "INSERT INTO menu_tree (tree_id , menu_id) SELECT  $treeId , menu.id FROM menu WHERE menu.id IN($menuIds);";
        $connExp->query($sqlInsertMenu);
        $connExp->closeConnection();
    }




    public function saveMessageApp($instagram , $message){

        $value = $this->instaVerifyCountry($instagram);
        $dbCountryConnection = "seawaybr_read";
        if(!empty($value['values']) && !empty($value['country'])){
            $dbCountry = $this->configDatabasesCountry[$value['country']];
            if(!empty($dbCountry['connection'])){
                $dbCountryConnection = $dbCountry['connection'];
            }
        }
        $conn = Mage::getSingleton('core/resource')->getConnection($dbCountryConnection);
        $sql  = "INSERT INTO t_message_app( msg  , instagram , area )";
        $sql .= "VALUES ( :msg , :insta , :area )";

        $data = array('msg'   => $message ,
            'insta' => $instagram,
            'area'  => 'menu_app' );

        if($conn->query($sql , $data)){
            return true;
        }
        return false;

    }


    public function getMenuUser($treeId , $instagram)
    {




        $conn = Mage::getSingleton('core/resource')->getConnection($this->getDataBaseExperience());
        $conn->query('SET CHARACTER SET utf8;');

        $sql = "SELECT  m.id , m.label , m.type_link , m.url , m.icon ,mt.visible as visible_user , m.visible as visible_geral FROM menu m INNER JOIN menu_tree mt ON m.id = mt.menu_id WHERE mt.tree_id  = :id ;";
        $data = array('id' => $treeId);
        if (empty($treeId)) {
            $sql = "SELECT  m.id , m.label , m.type_link , m.url , m.icon , mt.visible as visible_user , m.visible as visible_geral FROM menu m INNER JOIN menu_tree mt ON m.id = mt.menu_id WHERE mt.tree_id  is null;";
        }
        $values = $conn->fetchAll($sql, $data);
        $conn->closeConnection();
        // caso o menu geral esteja desabilitado
        $menuOptions = array();
        foreach ($values as $val) {

            if ($val['visible_geral'] == 2) {
                continue;
            }
            if ($val['visible_user'] == 2) {
                continue;
            }

            if (!(strpos($val['label'], '|') === FALSE)) {

                $parts = explode('|', $val['label']);
                $val['label'] = current($parts);
                $val['sublabel'] = end($parts);
            }


            if(!(strpos($val['url'],'ikey') === FALSE)){

                require_once Mage::getBaseDir('lib').DS.'Util'.DS.'Util.php';
                $instagramEncripty = Util_Util::encrypt($instagram, 'Seaway84*', 'd968cfe1a7f9');
                $instagramEncripty = urlencode($instagramEncripty);
                $val['url'] = $val['url'].$instagramEncripty;
            }


            if(!(strpos($val['url'],'gcode') === FALSE)){

                // fase de indicar e algum amigo ja escolheu
                $bannerUrl = '';
                if($this->isBannerWaitingPromoscore($treeId)){
                     $bannerUrl = '&banner_wait_promoscore=1';
                }
                $val['url'] = $val['url'].$bannerUrl;
            }



            if (!empty($val['icon'])) {
                $val['icon'] = Mage::getBaseUrl('skin') . $val['icon'];
            }


            unset($val['visible_user']);
            unset($val['visible_geral']);
            $menuOptions[] = $val;
        }


        return $menuOptions;
    }


    public function isBannerWaitingPromoscore($treeId = null){


        $result  = false;
        if(!empty($treeId)){

            $DBsite = $this->getDbSiteByExperienceDb($this->getDataBaseExperience());
            $conn = Mage::getSingleton('core/resource')->getConnection($DBsite);
            $sql = "SELECT IF(a.trigger_app = 'banner' , 1 , 0) as is_banner FROM t_tree t INNER JOIN t_action a ON t.last_action_id  = a.id  WHERE t.id = :tree and t.current_step = 2";
            $data = array('tree' => $treeId);
            $values  = $conn->fetchRow($sql, $data);
            $conn->closeConnection();
            $result  = (!empty($values['is_banner']))? true : false;
        }

        return $result ;
    }


    public function getMenu($treeId, $country , $currentStep, $instagram)
    {
        $this->configDbByCountry($country);
        //$menuAll = $this->getMenuUser($treeId);
        if (!empty($treeId)) {
            $this->createAllMenuUser($treeId , $currentStep);
            $menuAll = $this->getMenuUser($treeId, $instagram);
        }else{
            $menuAll = $this->getMenuUser(null);
        }
        return $menuAll;
    }


    public function verifyDatabases($param)
    {
        //$instagram
    }


    public function saveInstaSession($instagram)
    {
        if (!empty($instagram)) {
            Mage::getModel('core/session')->setData('instagram', $instagram);
        }
    }

    public function getInstaSession()
    {
        return Mage::getModel('core/session')->getData('instagram');
    }


    public function existFirstAccess($treeId , $currentStep){

        $conn = Mage::getModel('core/resource')->getConnection('core_write');

        $sql = "SELECT COUNT(id) as total FROM t_first_access WHERE tree_id = :tree AND current_step = :step";
        $data = array('tree' => $treeId , 'step' => $currentStep);
        $values  = $conn->fetchRow($sql, $data);
        $conn->closeConnection();

        $isFirstAcess = (!empty($values['total']))? true : false ;
        return $isFirstAcess;

    }

    public function setFirstAccess($currentStep){
        $tree = $this->getTree();
        if(!empty($tree)){
            $this->createFirstAccess($tree,$currentStep);
        }

    }


    public function createFirstAccess($tree , $currentStep ){

        $isFirstAcess =    $this->existFirstAccess($tree['id'] , $currentStep);
        if(!$isFirstAcess){
            $conn = Mage::getModel('core/resource')->getConnection('core_write');
            $sqlInsert = "INSERT INTO t_first_access(tree_id , current_step) VALUES(:tree, :step)";
            $data = array('tree'=>  $tree['id'] , 'step' => $currentStep );
            $conn->query($sqlInsert, $data);
        }

    }




    public function getTree(){

        $result = array();
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getEntityId();

            $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
            $newSQL = "SELECT *  FROM  t_tree  WHERE customer_id  = :id";
            $data = array('id' => $customerId);
            $result =  $resource->fetchRow($newSQL, $data);
        }
        return $result;

    }



    public function thisProgramIsRight($currentStepUser , $currentStepPage){

        $isRight = true;
        if($currentStepUser != $currentStepPage){
            $isRight = false;
        }
        return $isRight;

    }


    public function getLastOrderFreepayment($customerId){

       $sql= "SELECT `main_table`.entity_id
                                          FROM `sales_flat_order` AS `main_table`
                                          INNER JOIN `sales_flat_order_payment` AS `a`
                                          ON main_table.entity_id = a.parent_id AND a.method = 'freepayment'
                                          WHERE  (main_table.customer_id = :customer)
                                                AND (((`status` = 'complete')
                                                OR (`status` = 'delivered')))
                                                                    ORDER BY main_table.entity_id DESC LIMIT 1";

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $data = array('customer' => $customerId);
        $result =  $resource->fetchRow($sql, $data);

        return (!empty($result['entity_id']))?  $result['entity_id'] : false;

    }

    public function insertUserIp(){

        $userIp = $_SERVER["REMOTE_ADDR"];
        $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        Mage::log("remote_addr" . $userIp . "url" . $url , null , 'experience_insert_user_ip.log',true);

        $mobile = FALSE;
		$user_agents = array("iPhone","iPad","Android","webOS","BlackBerry","iPod","Symbian","IsGeneric");

		foreach($user_agents as $user_agent){
            if (strpos($_SERVER['HTTP_USER_AGENT'], $user_agent) !== FALSE) {
                $mobile = TRUE;
                $modelo = $user_agent;
                break;
            }
        }

        if ($mobile){
            $mobile = "m";
        }else{
            $mobile = "d";
        }

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sqlInsert = "INSERT log_visitor_online (visitor_type, remote_addr, first_visit_at, last_url)
                   VALUES(:visitor_type, :remote_addr, now(), :last_url)";
        $data = array('visitor_type'=> $mobile , 'remote_addr' => $userIp, 'last_url' => $url);
        $resource->query($sqlInsert, $data);

    }
    
}