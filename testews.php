<?php
ini_set('display_errors', 1);
require 'app/Mage.php';
Mage::app();

try {

    $cesar = "César Martins de Albuquerquer";
    $dados = explode(" ", $cesar);
    $ultimoArray = $dados;

    $ultimo = end($ultimoArray);
    $anterior = key($ultimoArray);
    $anterior--;

    if (strlen($dados[$anterior]) == 2) {
        $ultimoNome = $dados[$anterior] . " " . $ultimo;
    } else {
        $ultimoNome = $ultimo;
    }
    $retorno = array("nome" => ucfirst($dados[0]), "sobrenome" => ucwords($ultimoNome));

    $soapUrl = "http://186.233.105.20:8085/PCSIS2699.exe/soap/PC_Departamento";

    for ($i=1; $i <= 30; $i++){

    /*$xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCPrecoIntf-PC_Preco">
    <x:Header/>
    <x:Body>
        <urn3:Pesquisar>
            <urn3:Codigo_Produto>1992</urn3:Codigo_Produto>
        </urn3:Pesquisar>
    </x:Body>
</x:Envelope>';*/

    /*$xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCProdutoIntf-PC_Produto">
    <x:Header/>
    <x:Body>
        <urn3:PesquisarPaginacao>
            <urn3:De>1</urn3:De>
            <urn3:Ate>100</urn3:Ate>
        </urn3:PesquisarPaginacao>
    </x:Body>
</x:Envelope>';

    $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCPrecoIntf-PC_Preco">
    <x:Header/>
    <x:Body>
        <urn3:Pesquisar>
            <urn3:Codigo_Produto>54</urn3:Codigo_Produto>
        </urn3:Pesquisar>
    </x:Body>
</x:Envelope>';

    $xml_post_string = '<x:Envelope xmlns:x="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn3="urn:uPCFilialintf-PC_Filial">
                        <x:Header/>
                        <x:Body>
                            <urn3:Pesquisar>
                                <urn3:Codigo_Filial>' . $i . '</urn3:Codigo_Filial>
                            </urn3:Pesquisar>
                        </x:Body>
                    </x:Envelope>';

    */

    $xml_post_string = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:uPCDepartamentoIntf-PC_Departamento">
                           <soapenv:Header/>
                           <soapenv:Body>
                              <urn:Pesquisar soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                                 <Codigo_Departamento xsi:type="xsd:int">' . $i . '</Codigo_Departamento>
                                 </urn:Pesquisar>
                           </soapenv:Body>
                        </soapenv:Envelope>';


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

        echo "<br>----------<br>";
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