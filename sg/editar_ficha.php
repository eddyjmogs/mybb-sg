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

$ficha_existe = false;
$moderated = false;
eval('$action = $action;');
eval('$module = $module;');

$query_ficha = $db->query("
    SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
");

while ($f = $db->fetch_array($query_ficha)) {
    $moderated = $f['moderated'] != 'no_moderacion';
    $ficha_existe = true;
}

if ($ficha_existe == true && $moderated == true && ($uid == $mybb->user['uid'] || $mybb->user['uid'] == '2')) {
    $query_usuario = $db->query("
        SELECT * FROM mybb_sg_users WHERE uid='$uid'
    ");

    $query_ficha2 = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");

    while ($f = $db->fetch_array($query_ficha2)) {
        $agSum = 0;
        $ckSum = 0;
        $agNivSum = 0;
        $ckNivSum = 1;
        $espe = $f['espe'];
        $limite_nivel = $f['limite_nivel'];

        if ($espe == 'NIN' || $espe == 'GEN') {
            $ckSum = $f['inte'] + $f['ctrl'];
            // $ckNivSum = $f['nivel'];
            $ckNivSum = 2;
        }
        
        if ($espe == 'TAI') {
            $ckSum = $f['res'] + $f['agi'];
            // $agNivSum = $f['nivel'];
            $ckNivSum = 2;
        }
        eval('$ficha = $f;');

        // $v = $f['str'] * 3 + $f['res'] * 4;
        // $c = round(($f['str'] * 1) + ($f['res'] * 0.5) + ($f['spd'] * 2) + ($f['agi'] * 0.5) + ($f['dex'] * 2) + ($f['pres'] * 2) + ($f['inte'] * 2) + ($f['ctrl'] * 2.5));
        $v = calculate_vida($f['str'], $f['res']);
        $c = calculate_chakra($f['str'], $f['res'], $f['spd'], $f['agi'], $f['dex'], $f['pres'], $f['inte'], $f['ctrl']);
        $reg_c = calculate_reg_chakra($f['str'], $f['res'], $f['spd'], $f['agi'], $f['dex'], $f['pres'], $f['inte'], $f['ctrl']);
        $a = 0;  
        $reg_a = 0;
        // $reg_c = round(((($f['str'] + $f['res'] + $f['spd'] + $f['agi'] + $f['dex'] + $f['pres'] + $f['inte'] + $f['ctrl']) * 2)) / 40) + 1;

        eval('$vida = $v;');
        eval('$aguante = $a;');
        eval('$chakra = $c;');
        eval('$regA = $reg_a;');
        eval('$regC = $reg_c;');
        eval('$especialidad = $espe;');
        eval('$nivel_limite = $limite_nivel;');
    }
    eval('$editar_ficha_script = "'.$templates->get('sg_editar_stats_script').'";');
    eval("\$page = \"".$templates->get("sg_editar_ficha")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}
