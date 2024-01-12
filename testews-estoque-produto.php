<?php
ini_set('display_errors', 1);
require 'app/Mage.php';
Mage::app();

try{

    $arrayFilial = array(
        "1" => "Centro",
        "4" => "CD",
        "5" => "GRAVATÁ",
        "6" => "BR",
        "7" => "Limoeiro",
        "8" => "Bezerros",
        "9" => "Escada",
        "10" => "ROD PE-45",
        "11" => "Caruaru - tintas",
        "12" => "Caruaru - pisos",
        "13" => "Jaboatão",
        "14" => "Loja Gravata",
        "15" => "Imbiribeira",
        "16" => "Bezerros",
        );

    foreach ($arrayFilial as $key => $value) {

        echo $value . "<br>________________<br>";

        $soapUrl = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Estoque";

        /*$xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCPrecoIntf-PC_Preco">
        <x:Header/>
        <x:Body>
            <urn3:Pesquisar>
                <urn3:Codigo_Produto>1992</urn3:Codigo_Produto>
            </urn3:Pesquisar>
        </x:Body>
    </x:Envelope>';*/
        $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCEstoqueIntf-PC_Estoque">
                                <x:Header/>
                                <x:Body>
                                    <urn3:Pesquisar>
                                        <urn3:Codigo_Filial>' . $key . '</urn3:Codigo_Filial>
                                        <urn3:Codigo_Produto>11973</urn3:Codigo_Produto>
                                    </urn3:Pesquisar>
                                </x:Body>
                            </x:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $soapUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Connection: Keep-Alive'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $xml = simplexml_load_string($response);
        $arrayData = xmlToArray($xml);
        echo "<pre>";
        var_dump($arrayData);

        $envelope = $arrayData['Envelope'];
        $body = $envelope["SOAP-ENV:Body"];
        $pesquisarresponse = $body['PesquisarResponse'];
        $return = $pesquisarresponse['return'];
        $dados = $return['Dados'];
        $tpreco = $dados['TPreco'];

        var_dump($tpreco);

        curl_close($ch);

    }

} catch (SoapFault $e) {
    var_dump($e);
}catch (Exception $e) {
    var_dump($e);
}

function xmlToArray($xml, $options = array()) {
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


die('chegou aqui 123');

?>