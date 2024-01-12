<?php
class Cistecnologia_Importacao_Helper_Estoque extends Mage_Core_Helper_Abstract{

    const SOAPURL = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Estoque";

    public function getEstoque($filial_id, $produto_id){

        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCEstoqueIntf-PC_Estoque">
                                <x:Header/>
                                <x:Body>
                                    <urn3:Pesquisar>
                                        <urn3:Codigo_Filial>' . $filial_id . '</urn3:Codigo_Filial>
                                        <urn3:Codigo_Produto>' . $produto_id . '</urn3:Codigo_Produto>
                                    </urn3:Pesquisar>
                                </x:Body>
                            </x:Envelope>';

        $dadosCategoria = $this->chamaWebserve($xml_post_string);
        return $this->trataDadosCategoria($dadosCategoria);

    }

    public function trataDadosCategoria($dadosCategoria){

        $pesquisarresponse = $dadosCategoria['PesquisarResponse'];
        $return = $pesquisarresponse['return'];
        $dados = $return['Dados'];
        return $dados['TCategoria'];

    }

    public function chamaWebserve($xmlPost){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::SOAPURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Connection: Keep-Alive'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $xml = simplexml_load_string($response);
        $arrayData = Mage::helper('importacao/funcoes')->xmlToArray($xml);

        $envelope = $arrayData['Envelope'];
        $body = $envelope["SOAP-ENV:Body"];

        curl_close($ch);

        return $body;

    }

}