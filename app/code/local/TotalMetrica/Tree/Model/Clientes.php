<?php

class TotalMetrica_Tree_Model_Clientes {

    //public function insertProdutoSimples($linha, $produtoConfiguravel, $linhaProdtudos ,$key){
    public function insertClientes($clientes){

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        $arrayNome = $this->tratarNome($clientes["nome"]);
        $email = isset($clientes["email"])? $clientes["email"] : 'veneza@veneza.com.br';

        $customer = Mage::getModel("customer/customer");
        $customer
            ->setWebsiteId($websiteId)
            ->setStore($store)
            ->setGroupId(1)
            ->setFirstname($arrayNome["nome"])
            ->setLastname($arrayNome["sobrenome"])
            ->setCity()
            ->setEmail($email);

        try{
            $customer->save();
            $this->insertEndereco($customer, $clientes);
            return true;
        }
        catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }

    }

    public function insertEndereco($customer, $clientes){

        $address = Mage::getModel("customer/address");
        $address->setCustomerId($customer->getId())
            ->setFirstname($customer->getFirstname())
            ->setLastname($customer->getLastname())
            ->setPostcode($clientes["cep"])
            ->setCity($clientes["municipio"])
            ->setCountryId("BR")
            ->setRegionId($clientes["uf"])
            ->setVatId($clientes["cpf_cnpj"])
            ->setTelephone($clientes["telefone_celular"])
            ->setFax($clientes["telefone_celular"])
            ->setStreet(array($clientes["endereco"],$clientes["numero"],"",$clientes["bairro"]))
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');

        try{
            $address->save();
        }
        catch (Exception $e) {
            Zend_Debug::dump($e->getMessage());
        }
    }

    public function tratarNome($nome){

        $dados = explode(" ", $nome);
        $ultimoArray = $dados;

        $ultimo = end($ultimoArray);
        $anterior = key($ultimoArray);
        $anterior--;

        if(strlen($dados[$anterior]) == 2){
            $ultimoNome = $dados[$anterior] . " " . $ultimo;
        }else{
            $ultimoNome = $ultimo;
        }
        $retorno = array("nome" => ucfirst($dados[0]), "sobrenome" => ucwords($ultimoNome));
        return $retorno;

    }
}