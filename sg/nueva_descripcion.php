`<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'nueva_descripcion.php');
require_once "./../global.php";
require "./../inc/config.php";
global $templates, $mybb;

$alias = addslashes($_POST["alias"]);
$phi = addslashes($_POST["phi"]);
$psi = addslashes($_POST["psi"]);
$faccion = addslashes($_POST["faccion"]);
$history = addslashes($_POST["history"]);
$extra = addslashes($_POST["extra"]);
$frase = addslashes($_POST["frase"]);
$fisico_de_pj = addslashes($_POST["fisico_de_pj"]);
$banner = addslashes($_POST["banner"]);
$submit = $_POST["submit"];
$uid = $_POST["uid"];
$fichaurl = $_POST["fichaurl"];

if ($phi && $psi && $history && $submit && ($uid == $mybb->user['uid'] || $mybb->user['uid'] == 2)) {

    $db->query(" 
        INSERT INTO `mybb_sg_sg_audit_descripcion` (`fid`, `apariencia`, `personalidad`, `historia`, `extra`, `frase`, `apodo`, `fisico_de_pj`) VALUES 
        ('".$uid."', '".$phi."', '".$psi."', '".$history."', '".$extra."', '".$frase."', '".$alias."', '$fisico_de_pj');
    ");

    $db->query(" 
        UPDATE `mybb_sg_sg_fichas` SET `apariencia`='".$phi."',`personalidad`='".$psi."',`historia`='".$history."',`extra`='".$extra."',`frase`='".$frase."',`apodo`='$alias',`fisico_de_pj`='$fisico_de_pj',`faccion`='$faccion',`banner`='$banner' WHERE `fid`='".$uid."';
    ");

    eval("\$page = \"".$templates->get("sg_ficha_editada")."\";");
    output_page($page);
} else  {
    eval("\$page = \"".$templates->get("sg_ficha_no_creada")."\";");
    output_page($page);
}




