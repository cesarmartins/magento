<?php
class TotalMetrica_Tree_Helper_Preco extends Mage_Core_Helper_Abstract{

    const SOAPURL = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Preco";

    public function getPreco($codigo_produto){

        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCPrecoIntf-PC_Preco">
                            <x:Header/>
                            <x:Body>
                                <urn3:Pesquisar>
                                    <urn3:Codigo_Produto>' . $codigo_produto . '</urn3:Codigo_Produto>
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
        $preco = $dados['TPreco'];
        return $preco['preco_venda'];

    }
}