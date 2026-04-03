<?php

function does_ficha_exist($uid) {
    global $db;
    $ficha = select_one_query_with_id('mybb_sg_sg_fichas', 'fid', $uid);
    $moderada = $ficha['moderated']!= 'no_moderacion';

    return $ficha != null && $moderada;
}

// Sirve para ficha y tienda
function select_one_query_with_id($table_name, $id_name, $id) {
    global $db;

    $obj = null;

    $query = $db->query("
        SELECT * FROM $table_name WHERE $id_name='$id'
    ");

    while ($q = $db->fetch_array($query)) {
        $obj = $q;
    }

    return $obj;
}

function get_obj_from_query($query) {
    global $db;
    
    $obj = null;
    while ($q = $db->fetch_array($query)) {
        $obj = $q;
    }
    return $obj;
}

function calculate_vida($str, $res) {
    return ($str * 3) + ($res * 4);
}

function calculate_chakra($str, $res, $spd, $agi, $dex, $pres, $inte, $ctrl) {
    return round(($str * 1) + ($res * 0.5) + ($spd * 2) + ($agi * 0.5) + ($dex * 2) + ($pres * 2) + ($inte * 2) + ($ctrl * 2.5));
}

function calculate_vida2($fuerza, $destreza, $cchakra, $inteligencia, $salud, $velocidad, $tenketsu, $sigilo) {
    return ($fuerza * 2) + ($destreza * 1) + ($cchakra * 1) + ($inteligencia * 2) + ($salud * 10) + ($velocidad * 0) + ($tenketsu * 5) + ($sigilo * 5);
}

function calculate_chakra2($fuerza, $destreza, $cchakra, $inteligencia, $salud, $velocidad, $tenketsu, $sigilo) {
    return ($fuerza * 1) + ($destreza * 2) + ($cchakra * 2) + ($inteligencia * 1) + ($salud * 0) + ($velocidad * 10) + ($tenketsu * 5) + ($sigilo * 5);
}

function calculate_reg_chakra($str, $res, $spd, $agi, $dex, $pres, $inte, $ctrl) {
    return round(((($str + $res + $spd + $agi + $dex + $pres + $inte + $ctrl) * 2)) / 40) + 1;
}

// function select_queries_with_id($table_name, $field, $value) {
//     global $db;

//     $obj = null;

//     $query = $db->query("
//         SELECT * FROM $table_name WHERE $field='$value'
//     ");

//     $values = array();
    
//     while ($q = $db->fetch_array($query)) {
//         $array_push($values, $q);
//     }

//     return $values;
// }

function is_staff($uid) {
    return ($uid == '196' || $uid == '2' || $uid == '320' || $uid == '155' || $uid == '181' || $uid == '129' || $uid == '178' || $uid == '239');
}

function is_peti_mod($uid) {
    return is_user($uid);
}

function is_mod($uid) {
    return (is_user($uid));
}

function is_user($uid) {
    // Muki: 239
    // Aiko: 129
    // Izuku: 181
    // Killua: 178
    // Ryu/Kano: 217/192
    // Freeia: 239, 251
    // Centyman: 241
    // Senshi 158 Kiseki 347
    // Izanami 204
    // Musacus 234
    // Toji Juri Gojo  - 155 302 306
    // Yagami 383
    // Akami 355 416
    // Namida y Karai - 
    // Kaito - 356

    // Kyoshiro 521
    // Hades 477
    return ($uid == '129' || 
       
        
        $uid == '206' || $uid == '158' || $uid == '347' ||
        $uid == '181' || $uid == '178' || $g_uid == '204' || $g_uid == '312' || $g_uid == '342' ||
        $uid == '239' || $uid == '251' || $uid == '241' || $uid == '341' || $uid == '204' ||
        $uid == '155' || $uid == '302' || $uid == '306' || $uid == '383' || $uid == '355' || $uid == '320' || $uid == '335' || $uid == '312' || 
        // Akami
        $uid == '355' || $uid == '416' ||
        // Taco
        $uid == '318' || $uid == '372' || $uid == '449' || 
        // Taco
        $uid == '356' || $uid == '521' || $uid == '477' ||
        $uid == '299' || $uid == '574' || $uid == '561' || $uid == '486' || $uid == '477'
    );
}

function convertObjectEffects($efectoJsonStr) {

    $efecto_json = json_decode($efectoJsonStr);

    $efectos_str = "";
    $efectos_arr = array("golpe", "clavar", "cortar", "0 a 2 metros", "2 a 4 metros");
    
    foreach ($efectos_arr as $efectoNombre) {
        $efe = $efecto_json->{$efectoNombre};
        if ($efe) {
            $base = $efe->base;
            $efectoCaps = ucfirst($efectoNombre);
            $efectoCaps = "<strong>$efectoCaps</strong>";
            $efectos_str .= "- $efectoCaps: ($base Base)";
            
            $fuerza = $efe->fuerza;
            $destreza = $efe->destreza;
            $texto = $efe->texto;
            if ($fuerza) {
                $efectos_str .= " + ($fuerza * Fuerza)";
            }
            if ($destreza) {
                $efectos_str .= " + ($destreza * Destreza)";
            }
            
            $efectos_str .= "<br>";
            
            if ($texto) {
                $efectos_str .= "<br>Extra: $texto<br>";
            }
    
        }
    }

    return $efectos_str;
}

function convertObjectEffectsUsuario($efectoJsonStr, $destrezaUsuario, $fuerzaUsuario) {
    $efecto_json = json_decode($efectoJsonStr);

    $efectos_str = "";
    $efectos_arr = array("golpe", "clavar", "cortar");
    
    foreach ($efectos_arr as $efectoNombre) {
        $efe = $efecto_json->{$efectoNombre};
        if ($efe) {
            $base = $efe->base;
            $efectoCaps = ucfirst($efectoNombre);
            $efectoCaps = "<strong>- $efectoCaps</strong>";
            $efectos_str .= "$efectoCaps: ";

            $totalVida = floatval($base);
            
            $fuerza = $efe->fuerza;
            $destreza = $efe->destreza;
            $texto = $efe->texto;
            if ($fuerza) {
                $totalVida += (floatval($fuerza) * floatval($fuerzaUsuario));
            }
            if ($destreza) {
                $totalVida += (floatval($destreza) * floatval($destrezaUsuario));
            }
            
            $efectos_str .= $totalVida;
            
            if ($texto) {
                $efectos_str .= "<br><strong>Extra</strong>: $texto";
            }
            $efectos_str .= "<br>";
        }
    }

    return $efectos_str;
}

function convertObjectEffectsTecnica($efectoJsonStr, $destrezaUsuario, $fuerzaUsuario) {
    $efecto_json = json_decode($efectoJsonStr);

    $efectos_str = "";
    $efectos_arr = array("golpe", "clavar", "cortar", "0 a 2 metros", "2 a 4 metros");
    // $efectos_arr = array("golpe", "clavar", "cortar");
    
    foreach ($efectos_arr as $efectoNombre) {
        $efe = $efecto_json->{$efectoNombre};
        if ($efe) {
            $base = $efe->base;
            $efectoCaps = ucfirst($efectoNombre);
            $efectoCaps = "$efectoCaps";
            $efectos_str .= "$efectoCaps: ";

            $totalVida = floatval($base);
            
            $fuerza = $efe->fuerza;
            $destreza = $efe->destreza;
            $texto = $efe->texto;
            if ($fuerza) {
                $totalVida += (floatval($fuerza) * floatval($fuerzaUsuario));
            }
            if ($destreza) {
                $totalVida += (floatval($destreza) * floatval($destrezaUsuario));
            }
            
            $efectos_str .= $totalVida . " de vida | ";
            
            if ($texto) {
                $efectos_str .= "<strong>Extra</strong>: $texto | ";
            }
        }

    }

    return substr($efectos_str, 0, -3) . ".";
}


// function select_one_query_with_id('mybb_sg_sg_fichas', 'fid', $uid);