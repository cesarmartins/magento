


function getState(estado , telefone){


     var uf = "NS";
    if(estado != ""  || telefone != "" ){

        if(estado) {
            estado = estado.toLowerCase();
            estado = estado.replace(' ', '');
            estado = removerAcentos(estado);

           // console.log(estado);
        }



          switch (estado) {
            case 'acre'       :
                uf = 'AC';
                break;
            case 'alagoas' :
                uf = 'AL';
                break;
            case 'amapa'   :
                uf = 'AP';
                break;
            case 'amazonas':
                uf = 'AM';
                break;
            case 'bahia'   :
                uf = 'BA';
                break;
            case 'ceara'   :
                uf = 'CE';
                break;
            case 'distritofederal' :
                uf = 'DF';
                break;
            case 'espiritosanto' :
                uf = 'ES';
                break;
            case 'goias' :
                uf = 'GO';
                break;
            case 'maranhao' :
                uf = 'MA';
                break;
            case 'matogrosso'    :
                uf = 'MT';
                break;
            case 'matogrossodosul':
                uf = 'MS';
                break;
            case 'minasgerais'    :
                uf = 'MG';
                break;
            case 'para'    :
                uf = 'PA';
                break;
            case 'paraiba'    :
                uf = 'PB';
                break;
            case 'parana'    :
                uf = 'PR';
                break;
            case 'pernambuco' :
                uf = 'PE';
                break;
            case 'piaui':
                uf = 'PI';
                break;
            case 'riodejaneiro' :
                uf = 'RJ';
                break;
            case 'riograndedonorte':
                uf = 'RN';
                break;
            case 'riograndedosul':
                uf = 'RS';
                break;
            case 'rondonia'    :
                uf = 'RO';
                break;
            case 'roraima'    :
                uf = 'RR';
                break;
            case 'santacatarina'    :
                uf = 'SC';
                break;
            case 'saopaulo'    :
                uf = 'SP';
                break;
            case 'sergipe' :
                uf = 'SE';
                break;
            case 'tocantins' :
                uf = 'TO';
                break;

            default :

                var ddd = "";

                if (telefone != "" && telefone != undefined ) {

                    telefone = telefone.replace(" " ,"");
                    ddd = telefone.substr(0, 2);
                    //console.log(ddd + " - "+ telefone);
                    uf = getStateForDDD(ddd);

                }

                break;
        }

    }


    return uf;

}

function getStateForDDD(ddd){

    var estados  = new Array();

    estados[11] = "SP";
    estados[12] = "SP";
    estados[13] = "SP";
    estados[14] = "SP";
    estados[15] = "SP";
    estados[16] = "SP";
    estados[17] = "SP";
    estados[18] = "SP";
    estados[19] = "SP";

    estados[21] = "RJ";
    estados[22] = "RJ";
    estados[24] = "RJ";

    estados[27] = "ES";
    estados[28] = "ES";

    estados[31] = "MG";
    estados[32] = "MG";
    estados[33] = "MG";
    estados[34] = "MG";
    estados[35] = "MG";
    estados[37] = "MG";
    estados[38] = "MG";

    estados[41] = "PR";
    estados[42] = "PR";
    estados[43] = "PR";
    estados[44] = "PR";
    estados[45] = "PR";
    estados[46] = "PR";

    estados[47] = "SC";
    estados[48] = "SC";
    estados[49] = "SC";

    estados[51] = "RS";
    estados[53] = "RS";
    estados[54] = "RS";
    estados[55] = "RS";

    estados[61] = "GO ou DF";
    estados[62] = "GO";
    estados[64] = "GO";

    estados[63] = "TO";

    estados[65] = "MT";
    estados[66] = "MT";

    estados[67] = "MS";

    estados[68] = "AC";

    estados[69] = "RO";

    estados[71] = "BA";
    estados[73] = "BA";
    estados[74] = "BA";
    estados[75] = "BA";
    estados[77] = "BA";

    estados[79] = "SE";

    estados[81] = "PE";
    estados[87] = "PE";

    estados[82] = "AL";

    estados[83] = "PB";

    estados[84] = "RN";

    estados[85] = "CE";
    estados[88] = "CE";

    estados[86] = "PI";
    estados[89] = "PI";

    estados[91] = "PA";
    estados[93] = "PA";
    estados[94] = "PA";

    estados[92] = "AM";
    estados[97] = "AM";

    estados[95] = "RR";

    estados[96] = "AP";

    estados[98] = "MA";
    estados[99] = "MA";

    var rs_estado = estados[ddd];

    if(rs_estado != undefined){
        return rs_estado;
    }

    return 'NS';

}

function removerAcentos( newStringComAcento ) {
    var string = newStringComAcento;
    var mapaAcentosHex 	= {
        a : /[\xE0-\xE6]/g,
        e : /[\xE8-\xEB]/g,
        i : /[\xEC-\xEF]/g,
        o : /[\xF2-\xF6]/g,
        u : /[\xF9-\xFC]/g,
        c : /\xE7/g,
        n : /\xF1/g,
        '' : /\s/g
    };

    for ( var letra in mapaAcentosHex ) {
        var expressaoRegular = mapaAcentosHex[letra];
        string = string.replace( expressaoRegular, letra );
    }

    return string;
}