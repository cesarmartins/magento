<?php
class Cistecnologia_Importacao_Helper_Data extends Mage_Core_Helper_Abstract{

    const SOAPURL = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Produto";

    public function carregarProdutos($qtd){

        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCProdutoIntf-PC_Produto">
                            <x:Header/>
                            <x:Body>
                                <urn3:PesquisarPaginacao>
                                    <urn3:De>1</urn3:De>
                                    <urn3:Ate>' . $qtd . '</urn3:Ate>
                                </urn3:PesquisarPaginacao>
                            </x:Body>
                        </x:Envelope>';

        //$produtos = $this->chamaWebserve($xml_post_string);

        $array = Mage::getModel("importacao/api")->dadosTesteImplantacao();

        Mage::getModel("importacao/produtos")->insertProdutoSimples($array);

    }

    public function cadastrarProdutos($produtos){

        $pesquisarresponse = $produtos['PesquisarResponse'];
        $return = $pesquisarresponse['return'];
        $dados = $return['Dados'];
        $tpreco = $dados['TPreco'];

        $product = $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $linha[0]);
        if(empty($product)){

        }


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
        $arrayData = xmlToArray($xml);

        $envelope = $arrayData['Envelope'];
        $body = $envelope["SOAP-ENV:Body"];

        curl_close($ch);

        return $body;

    }

    public function xmlToArray($xml, $options = array()) {
        $defaults = array(
            'namespaceSeparator' => ':',
            'attributePrefix' => '@',
            'alwaysArray' => array(),
            'autoArray' => true,
            'textContent' => '$',
            'autoText' => true,
            'keySearch' => false,
            'keyReplace' => false
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null;

        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                $childArray = xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        // Retorna o nó como array
        return array(
            $xml->getName() => $propertiesArray
        );
    }


}