<?php
class TotalMetrica_Tree_Helper_Clientes extends Mage_Core_Helper_Abstract{

    const SOAPURL = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Cliente";

    public function carregarClientes($codigo){

        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCClienteintf-PC_Cliente">
                            <x:Header/>
                            <x:Body>
                                <urn3:Pesquisar>
                                    <urn3:Codigo_Cliente>' . $codigo . '</urn3:Codigo_Cliente>
                                    <urn3:Somente_Ativos>true</urn3:Somente_Ativos>
                                </urn3:Pesquisar>
                            </x:Body>
                        </x:Envelope>';

        $dados = Mage::helper('tree/funcoes')->chamaWebserve(static::SOAPURL, $xml_post_string);
        return $this->trataDados($dados);

    }

    public function trataDados($dados){

        $pesquisarresponse = $dados['PesquisarResponse'];
        $return = $pesquisarresponse['return'];
        $dados = $return['Dados'];
        return $dados['TCliente'];

    }
}