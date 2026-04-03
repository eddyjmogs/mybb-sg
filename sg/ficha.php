<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'ficha.php');
require_once "./../global.php";
require "./../inc/config.php";
require_once "./functions/sg_functions.php";

global $templates, $mybb;

$uid = $mybb->get_input('uid'); 
$action = $mybb->get_input('action');
$module = $mybb->get_input('module'); 
$s_uid = $mybb->user['uid'];

$cambiar_avatar1 = addslashes($_POST["cambiar_avatar1"]);
$cambiar_avatar2 = addslashes($_POST["cambiar_avatar2"]);

if ($cambiar_avatar1 != '') {
    $db->query(" UPDATE `mybb_sg_users` SET `avatar`='$cambiar_avatar1' WHERE `uid`='$s_uid'; ");
}

if ($cambiar_avatar2 != '') {
    $db->query(" UPDATE `mybb_sg_sg_fichas` SET `banner`='$cambiar_avatar2' WHERE `fid`='$s_uid'; ");
}

$is_owner = $mybb->user['uid'] == $mybb->get_input('uid');

$ficha_existe = false;
$moderated = false;
eval('$action = $action;');
eval('$module = $module;');
eval('$userid = $uid;');

$query_ficha = $db->query("
    SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
");



while ($f = $db->fetch_array($query_ficha)) {
    $moderated = $f['moderated'] != 'no_moderacion';
    $ficha_existe = true;
}

if ($ficha_existe == true && ($moderated == true || (is_mod($s_uid) || is_staff($s_uid)))) {
    $query_usuario = $db->query("
        SELECT * FROM mybb_sg_users WHERE uid='$uid'
    ");

    while ($u = $db->fetch_array($query_usuario)) {
        $avatar = $u['avatar'];
        if (substr($u['avatar'], 0, 18) === './uploads/avatars/') {
            $avatar = './.' . $u['avatar'];
        }

        eval('$user_avatar = $avatar;');
        eval('$usuario = $u;');
        $puntos_rol = intval(floor($usuario['newpoints']));
    }

    $query_ficha = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");

    while ($f = $db->fetch_array($query_ficha)) {


        if ($f['moderated'] == 'no_moderacion') {
            $aprobada = false;
        } else {
            $aprobada = true;
        }

        $villa = $f['villa'];
        $clan = $f['clan'];
        $el1 = ucfirst($f['elemento1']);
        $el2 = ucfirst($f['elemento2']);
        $el3 = ucfirst($f['elemento3']);
        $el4 = ucfirst($f['elemento4']);
        $el5 = ucfirst($f['elemento5']);
        $ma1 = $f['maestria'];
        $ma2 = $f['maestria_secundaria'];
        $in1 = $f['invocacion'];
        $in2 = $f['invocacion_secundaria'];
        eval('$elemento1 = $el1;');
        eval('$elemento2 = $el2;');
        eval('$elemento3 = $el3;');
        eval('$elemento4 = $el4;');
        eval('$elemento5 = $el5;');
        eval('$maestria1 = $ma1;');
        eval('$maestria2 = $ma2;');
        eval('$invo1 = $in1;');
        eval('$invo2 = $in2;');
        $pasivaSlot = $f['pasiva_slot'];
        $kosei1 = $f['kosei1'];
        $kosei2 = $f['kosei2'];

        $query_villa = $db->query("
            SELECT * FROM mybb_sg_sg_villas WHERE vid='$villa'
        ");
    
        $query_clan = $db->query("
            SELECT * FROM mybb_sg_sg_clanes WHERE cid='$clan'
        ");
    
        // clanes
        while ($c = $db->fetch_array($query_clan)) {
            $nombreClan = ucwords($c['nombreClan']);
            eval('$nClan = $nombreClan;');
            eval('$clan = $c;');
        }
    
        while ($v = $db->fetch_array($query_villa)) {
            $villa_color = '';
            $villa_var_color = '';
            $sombra1 = '';
            $sombra2 = '';

            if ($villa == 1) { 
                $villa_color = 'fhko'; 
                $villa_var_color = '--konoha-group-color'; 
                $sombra1 = '#3e5d1d';
                $sombra2 = '#2f4616';
            }
               
            if ($villa == 3) { 
                $villa_color = 'fhki';
                $villa_var_color = '--kiri-group-color';  
                $sombra1 = '#527ca7';
                $sombra2 = '#3e5d7d';
            }
            // if ($villa == 4) { 
            //     $villa_color = 'fhiw'; 
            //     $villa_var_color = '--iwa-group-color';  
            //     $sombra1 = '#FFEAC6';
            //     $sombra2 = '#DAB067';
            // }
            if ($villa == 4) { 
                $villa_color = 'fhiw'; 
                $villa_var_color = '--iwa-group-color';  
                $sombra1 = '#6e370f';
                $sombra2 = '#52290b';
            }
            if ($villa == 5) { 
                $villa_color = 'fhku'; 
                $villa_var_color = '--kumo-group-color';
                $sombra1 = '#a48a26';
                $sombra2 = '#7b681c';  
            }
            if ($villa == 6) { 
                $villa_color = 'fhre'; 
                $villa_var_color = '--nsa-group-color';  
                $sombra1 = '#9c0c0c';
                $sombra2 = '#750909';
            }
            if ($villa == 7) { 
                $villa_color = 'fhnsa'; 
                $villa_var_color = '--renegados-group-color'; 
                $sombra1 = '#69394f';
                $sombra2 = '#4f2b3b'; 
            }

            eval('$villaVarColor = $villa_var_color;');
            eval('$villaColor = $villa_color;');
            eval('$villa = $v;');
        }
    
        $agSum = 0;
        $ckSum = 0;
        $agNivSum = 1;
        $ckNivSum = 1;
    
        if ($f['espe'] == 'NIN' || $f['espe'] == 'GEN') {
            $ckSum = $f['inte'] + $f['ctrl'];
            // $ckNivSum = $f['nivel'];
            $ckNivSum = 2;
        }
        
        if ($f['espe'] == 'TAI') {
            $ckSum = $f['res'] + $f['agi'];
            // $agNivSum = $f['nivel'];
            $ckNivSum = 2;
        }
        
        eval('$ficha = $f;');
        // $v = $f['str'] * 3 + $f['res'] * 4;
        // $c = ($f['pres'] * 2) + ($f['inte'] * 2) + ($f['ctrl'] * 3) + $ckSum;
        // $a = round(($f['agi'] * 1/2) + (($f['str'] + $f['spd']) * 3/2) + ($f['res'] * 2) + ($f['dex'] * 9/4)) + $agSum;
        // $reg_a = round((($f['str'] + $f['str'] + $f['spd'] + $f['agi']) / 20) + 1 + $agNivSum);
        // $reg_c = round((($f['pres'] + $f['inte'] + $f['ctrl']) / 20) + 1 + $ckNivSum);

        $v = $f['str'] * 3 + $f['res'] * 4;
        $c = round(($f['str'] * 1) + ($f['res'] * 0.5) + ($f['spd'] * 2) + ($f['agi'] * 0.5) + ($f['dex'] * 2) + ($f['pres'] * 2) + ($f['inte'] * 2) + ($f['ctrl'] * 2.5));
        $a = 0;  
        $reg_a = 0;
        $reg_c = round(((($f['str'] + $f['res'] + $f['spd'] + $f['agi'] + $f['dex'] + $f['pres'] + $f['inte'] + $f['ctrl']) * 2)) / 40) + 1;
        $suma_stats_var = $f['str'] + $f['res'] + $f['spd'] + $f['agi'] + $f['dex'] + $f['pres'] + $f['inte'] + $f['ctrl'];

        $historia_var = nl2br($ficha['historia']);
        $apariencia_var = nl2br($ficha['apariencia']);
        $personalidad_var = nl2br($ficha['personalidad']);
        $extra_var = nl2br($ficha['extra']);
        $frase_var = nl2br($ficha['frase']);
        $limite_nivel = $ficha['limite_nivel'];
        $nivel = $ficha['nivel'];
        $puntos_estadistica = intval($ficha['puntos_estadistica']);
        $mejoras = intval($ficha['mejoras']);

        if ($puntos_rol >= 9800 && $nivel == '19') {
            $nivel = 20;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 8700 && $nivel == '18') {
            $nivel = 19;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 7700 && $nivel == '17') {
            $nivel = 18;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 6800 && $nivel == '16') {
            $nivel = 17;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 6000 && $nivel == '15') {
            $nivel = 16;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 5250 && $nivel == '14') {
            $nivel = 15;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 4550 && $nivel == '13') {
            $nivel = 14;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");

        } else if ($puntos_rol >= 3900 && $nivel == '12') {
            $nivel = 13;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 3300 && $nivel == '11') {
            $nivel = 12;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 2750 && $nivel == '10') {
            $nivel = 11;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 2250 && $nivel == '9') {
            $nivel = 10;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 1800 && $nivel == '8') {
            $nivel = 9;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 1400 && $nivel == '7') {
            $nivel = 8;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 1050 && $nivel == '6') {
            $nivel = 7;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 750 && $nivel == '5') {
            $nivel = 6;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 500 && $nivel == '4') {
            $nivel = 5;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 300 && $nivel == '3') {
            $nivel = 4;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 150 && $nivel == '2') {
            $nivel = 3;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query("  UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        } else if ($puntos_rol >= 50 && $nivel == '1') {
            $nivel = 2;
            $puntos_estadistica += 15; $mejoras += 1;
            $db->query(" UPDATE `mybb_sg_sg_fichas` SET `nivel`='$nivel',`puntos_estadistica`='$puntos_estadistica',`mejoras`='$mejoras',`mejoras`='$mejoras' WHERE `fid`='$uid'; ");
        }


        
        eval('$vida = $v;');
        eval('$aguante = $a;');
        eval('$chakra = $c;');
        eval('$regA = $reg_a;');
        eval('$regC = $reg_c;');
        eval('$historia = $historia_var;');
        eval('$apariencia = $apariencia_var;');
        eval('$personalidad = $personalidad_var;');
        eval('$extra = $extra_var;');
        eval('$frase = $frase_var;');
        eval('$nivel_limite = $limite_nivel;');
        eval('$suma_stats = $suma_stats_var;');
    }
    eval("\$fichaleft = \"".$templates->get("sg_fichaleft")."\";");
    eval("\$ficharight = \"".$templates->get("sg_ficharight")."\";");

    // zona privada, solo para admins
    if ($s_uid == $uid || is_staff($s_uid) || is_peti_mod($s_uid)) {
        $query_tec_aprendidas = $db->query("
            SELECT * FROM `mybb_sg_sg_tecnicas` 
            INNER JOIN `mybb_sg_sg_tec_aprendidas` 
            ON `mybb_sg_sg_tecnicas`.`tid`=`mybb_sg_sg_tec_aprendidas`.`tid` 
            WHERE `mybb_sg_sg_tec_aprendidas`.`uid`='$uid'
        ");

        $tec_aprendidas = array();
        
        while ($tec_aprendida = $db->fetch_array($query_tec_aprendidas)) {
            $tec_aprendida['descripcion'] = nl2br($tec_aprendida['descripcion']);
            $key = strtolower($tec_aprendida['tipo']) . '_' . strtolower($tec_aprendida['aldea']);

            if (!$tec_aprendidas[$key]) {
                $tec_aprendidas[$key] = array();
            }
            array_push($tec_aprendidas[$key], $tec_aprendida);
        }
        $tec_aprendidas_json = json_encode($tec_aprendidas);
        
        // create variables
        eval('$tec_aprendidas = "'.addslashes($tec_aprendidas_json).'";');
        eval("\$fichaprivate_script = \"".$templates->get("sg_fichaprivate_script")."\";");
        eval("\$fichaprivate = \"".$templates->get("sg_fichaprivate")."\";");
    }

    eval("\$page = \"".$templates->get("sg_ficha")."\";");
    output_page($page);

} else if ($ficha_existe == false && $mybb->user['uid'] != 0 && $mybb->user['uid'] == $uid) {
    $uid = $db->query("
    SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");

    // clanes
    $query_clanes = $db->query("
        SELECT * FROM mybb_sg_sg_clanes WHERE activo='1'
    ");
    $clanes = array();
    while ($clan = $db->fetch_array($query_clanes)) {
        unset($clan['descripcion']);
        array_push($clanes, $clan);
    }
    $clanes_json = json_encode($clanes);

    // villas
    $query_villas = $db->query("
        SELECT * FROM mybb_sg_sg_villas WHERE activa='1'
    ");
    $villas = array();
    while ($villa = $db->fetch_array($query_villas)) {
        array_push($villas, $villa);
    }
    $villas_json = json_encode($villas);


    $sinClanKonoha = 1;
    $sinClanKiri = 1;
    $sinClanIwa = 1;
    $sinClanKumo = 1;
    $sinClanSinAldea = 1;

    function getVillasSinClan($villaId) {
        $last_two_weeks = time() - (14 * 24 * 3600);

        return "
            SELECT villa, nombreClan, count(nombreClan) as numeroPjs FROM (SELECT DISTINCT fichas.villa, clanes.nombreClan, p.username FROM mybb_sg_posts as p 
            INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
            INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
            INNER JOIN mybb_sg_sg_fichas as fichas ON p.uid = fichas.fid 
            INNER JOIN mybb_sg_sg_clanes as clanes ON clanes.cid = fichas.clan 
            WHERE p.dateline > '$last_two_weeks'
            AND villa=$villaId AND nombreClan='Sin Clan'                              
            AND f.parentlist LIKE '37,%') t
            GROUP BY nombreClan
            ORDER BY villa, nombreClan;
        ";


    }

    $querySinClanKonoha = $db->query(getVillasSinClan(1));
    $querySinClanKiri = $db->query(getVillasSinClan(3));
    // $querySinClanIwa = $db->query(getVillasSinClan(4)); 
    // $querySinClanKumo = $db->query(getVillasSinClan(5)); 
    // $querySinClanSinAldea = $db->query(getVillasSinClan(7)); 

    while ($q = $db->fetch_array($querySinClanKonoha)) {  if (intval($q['numeroPjs']) >= 2) {   $sinClanKonoha = 0;   } }
    while ($q = $db->fetch_array($querySinClanKiri)) {  if (intval($q['numeroPjs']) >= 2) {   $sinClanKiri = 0;   } }
    // while ($q = $db->fetch_array($querySinClanIwa)) {  if (intval($q['numeroPjs']) >= 2) {   $sinClanIwa = 0;   } }
    // while ($q = $db->fetch_array($querySinClanKumo)) {  if (intval($q['numeroPjs']) >= 2) {   $sinClanKumo = 0;   } }
    // while ($q = $db->fetch_array($querySinClanSinAldea)) {  if (intval($q['numeroPjs']) >= 2) {   $sinClanSinAldea = 0;   } }

    // create variables
    eval('$clanes = "'.addslashes($clanes_json).'";');
    eval('$villas = "'.addslashes($villas_json).'";');
    eval('$nueva_ficha_script = "'.$templates->get('sg_nueva_ficha_script').'";');

    eval("\$page = \"".$templates->get("sg_nueva_ficha")."\";");
    output_page($page);
} else if ($ficha_existe == true && $moderated == false && $mybb->user['uid'] == $uid && $mybb->user['uid'] != 0) {
    eval("\$page = \"".$templates->get("sg_ficha_en_moderacion")."\";");
    output_page($page); 
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}
// eval("\$page = \"".$templates->get("ficha")."\";");
// output_page($page);



