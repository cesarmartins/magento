<?php

class TotalMetrica_Experience_Helper_Data extends Mage_Core_Helper_Abstract {

    public function calcularPrazoIndicacao($dataCadastro){

        $hoje = date("Y-m-d");
        $data = $dataCadastro;
        $dif = strtotime($data) - strtotime($hoje);
        $retornoData = ($dif / 86400) + 10;

        $quantidadeDias = ' + ' . $retornoData . ' days';
        $retorno = date('m/d', strtotime($data. $quantidadeDias));

        return $retorno;
    }

    public function calcularPrazoSemIndicacao(){

        $hoje = date("Y-m-d");
        $quantidadeDias = ' + 10 days';
        $retorno = date('m/d', strtotime($hoje. $quantidadeDias));
        return $retorno;

    }
}