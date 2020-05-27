<?php

class TotalMetrica_ProcurarProdutos_Block_Autocomplete extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    public function getSuggestData()
    {
        Mage::log(var_export('chegou aqui -> getSuggestData', true),null,'listaprodutospesquisados.log',true);

        $suggestCollection = Mage::getModel('procurarprodutos/query')->getSuggestCollection();
        return $suggestCollection;
    }
}
?>