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
define('THIS_SCRIPT', 'nueva_ficha.php');
require_once "./../global.php";
require "./../inc/config.php";
global $templates, $mybb;

$name = addslashes($_POST["name"]);
$alias = addslashes($_POST["alias"]);
$age = $_POST["age"];
$season = $_POST["season"];
$villa = $_POST["villa"];
$clan = $_POST["clan"];
$elemento = $_POST["elemento"];
$peso = $_POST["peso"];
$altura = $_POST["altura"];
$sexo = $_POST["sexo"];
$phi = addslashes($_POST["phi"]);
$psi = addslashes($_POST["psi"]);
$history = addslashes($_POST["history"]);
$extra = addslashes($_POST["extra"]);
$frase = addslashes($_POST["frase"]);
$fisico_de_pj = addslashes($_POST["fisico_de_pj"]);
$como_nos_conociste = addslashes($_POST["como_nos_conociste"]);
$submit = $_POST["submit"];
$uid = $_POST["uid"];
$fichaurl = $_POST["fichaurl"];

if ($name && $age && $season && $villa && $clan && $elemento && $phi && $psi && $history && $submit && $uid == $mybb->user['uid']) {

    $slots = $clan == '1001' ? '8' : '6';

    $db->query(" 
    INSERT INTO `mybb_sg_sg_fichas` (`fid`, `puntos_habilidad`, `peso`, `altura`, `sexo`, `ryos`, `nombre`, `apodo`, `rango`, `edad`, `temporada_nacimiento`, `villa`, `clan`, `slots`, `espe`, `reputacion`, `espe_estilo`, `maestria`, `maestria_secundaria`, `elemento1`, `elemento2`, `elemento3`, `apariencia`, `personalidad`, `historia`, `moderated`, `invocacion`, `invocacion_secundaria`, `notas`, `extra`, `frase`, `fisico_de_pj`, `como_nos_conociste`) VALUES 
    ('". $uid ."', 10, 0, '".$name."', '".$alias."','genin',".$age.",".$season.",'".$villa."','".$clan."',$slots,'',0,'','','','".$elemento."','','','".$phi."','".$psi."','".$history."','no_moderacion','','','', '".$extra."', '$frase', '$fisico_de_pj', '$como_nos_conociste');
    ");
    eval("\$page = \"".$templates->get("sg_nueva_ficha_creada")."\";");
    output_page($page);
} else  {
    eval("\$page = \"".$templates->get("sg_ficha_no_creada")."\";");
    output_page($page);
}



