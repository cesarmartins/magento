<?php

/**
 * Overriding Customer account controller
 */
require_once Mage::getModuleDir('controllers', 'Mage_Customer') . DS . 'AccountController.php';

class CesarMartins_Customer_AccountController extends Mage_Customer_AccountController {

    /*public function ajaxAction() {  //if you want to create a custom method in customer controller
        echo 'Ajax cction is working!!';
    }*/

    public function loginPostAction() {

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();
        $message = '';

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            if(!empty($login['listafavoritos'])){
                $this->_login($login['username'], $login['password']);
                //if ($session->getCustomer()->getIsJustConfirmed()) {
                    //$this->_welcomeCustomer($session->getCustomer(), true);

                $listaFavoritos = Mage::getModel("cistecnologia_listafavoritos/favoritos")->getListaFavoritosCollection($session->getCustomerId());
                $dataRetorno =  array("logado" => true, "userid" => $session->getCustomerId(), "lista_select" => $listaFavoritos);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($dataRetorno));

                return $dataRetorno;
                    /*header('Content-Type:application/json');
                    echo json_encode($dataRetorno);*/
                //}
            }
            elseif (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    protected function _login($username, $password)
    {
        $session = $this->_getSession();
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->authenticate($username, $password)) {
            $session->setCustomerAsLoggedIn($customer);
            return true;
        }
        return false;
    }

    protected function _loginPostRedirect()
    {
        //veio do carrinho
        $tavaLa = Mage::getSingleton('core/session')->getTavaNoCarrinho();
        $teste = $this->_getRefererUrl();
        //if($tavaLa){
        //$requestUri = Mage::getSingleton('core/session')->getData('visitor_data')['request_uri'];
        //}
        $session = Mage::getSingleton('customer/session');
        if($tavaLa) {
            $newRoute = Mage::getUrl(' ', array('_direct' => 'checkout/onepage/'));
            $session->setAfterAuthUrl($newRoute);
        } else {
            $newRoute = $this->_getHelper('customer')->getDashboardUrl();
            $session->setAfterAuthUrl($newRoute);
        }
        //tira a origem do carrinho
        Mage::getSingleton('core/session')->setTavaNoCarrinho(false);
        $session->setBeforeAuthUrl($newRoute);

        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

    public function logoutAction()
    {
        $session = $this->_getSession();
        $session->logout()->renewSession();

        if (Mage::getStoreConfigFlag(Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)) {
            $session->setBeforeAuthUrl(Mage::getBaseUrl());
        } else {
            $session->setBeforeAuthUrl($this->_getRefererUrl());
        }
        Mage::getSingleton('core/session')->setTavaNoCarrinho(false);
        $this->_redirect('*/*/logoutSuccess');
    }

    public function customerLogin($observer)
    {
        $cesar = "teste";
    }

    public function createPostAction()
    {
        $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));

        if (!$this->_validateFormKey()) {
            $this->_redirectError($errUrl);
            return;
        }

        /** @var Mage_Customer_Model_Session $session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->_redirectError($errUrl);
            return;
        }

        $params = $this->getRequest()->getParams();

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $firstname = $params["firstname"];
        $lastname = $params["lastname"];
        $email = $params["email"];
        $password = $params["password"];
        $taxvat = $params["taxvat"];
        $telephone = $params["telephone"];
        $fax = $params["fax"];
        $postcode = $params["postcode"];
        $street = $params["street"];


        $city = $params["city"];
        $region_id = $params["region_id"];
        if(empty($params["dob"])){
            $dob = $params["day"] . "/" . $params["month"] . "/" . $params["year"];
        }else{
            $dob = $params["dob"];
        }

        try {
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($email)
                ->setPassword($password)
                ->setTaxvat($taxvat)
                ->setDob($dob);
            $customer->save();

            $address = Mage::getModel("customer/address");
            $address->setCustomerId($customer->getId())
                ->setFirstname($customer->getFirstname())
                ->setMiddleName($customer->getMiddlename())
                ->setLastname($customer->getLastname())
                ->setTelephone($telephone)
                ->setFax($fax)
                ->setPostcode($postcode)
                ->setStreet($street)
                ->setCity($city)
                ->setRegionId($region_id)
                ->setCountryId('BR')
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            $address->save();

            $this->_dispatchRegisterSuccess($customer);
            $this->_successProcessRegistration($customer);

        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
            } else {
                $message = $this->_escapeHtml($e->getMessage());
            }
            $session->addError($message);
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            $session->addException($e, $this->__('Cannot save the customer.'));
        }

    }

    protected function _getHelper($path)
    {
        return Mage::helper($path);
    }

}