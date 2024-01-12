<?php
class TotalMetrica_Tree_Helper_Categoria extends Mage_Core_Helper_Abstract{

    const SOAPURL = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Categoria";

    public function getCategoria($category_id, $secao_id){

        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCCategoria-PC_Categoria">
                            <x:Header/>
                            <x:Body>
                                <urn3:Pesquisar>
                                    <urn3:Codigo_Secao>' . $secao_id . '</urn3:Codigo_Secao>
                                    <urn3:Codigo_Categoria>' . $category_id . '</urn3:Codigo_Categoria>
                                </urn3:Pesquisar>
                            </x:Body>
                        </x:Envelope>';

        $dadosCategoria = Mage::helper('tree/funcoes')->chamaWebserve(static::SOAPURL, $xml_post_string);
        return $this->trataDadosCategoria($dadosCategoria);

    }

    public function trataDadosCategoria($dadosCategoria){

        $pesquisarresponse = $dadosCategoria['PesquisarResponse'];
        $return = $pesquisarresponse['return'];
        $dados = $return['Dados'];
        return $dados['TCategoria'];

    }
}