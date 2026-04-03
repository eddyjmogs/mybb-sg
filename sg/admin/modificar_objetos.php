<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'modificar_objetos.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$objeto_id = $mybb->get_input('objeto_id'); 

$objeto_id_old = trim($_POST["objeto_id_old"]);
$objeto_id_post = trim($_POST["objeto_id"]);
$nombre = trim($_POST["nombre"]);
$tipo = trim($_POST["tipo"]);
$categoria = trim($_POST["categoria"]);
$coste = addslashes($_POST["coste"]);
$descripcion = addslashes($_POST["descripcion"]);
$staff = trim($_POST["staff"]);
$razon = trim($_POST["razon"]);

$reload_js = "<script>window.location.href = window.location.pathname;</script>";

if ($objeto_id_post && $nombre && $tipo && $descripcion && $staff && $razon && (is_mod($uid) || is_staff($uid))) {
    $log = "Cambio a objeto ID $objeto_id_old ($nombre). \nLos cambios son: \objeto_id=$objeto_id_post,\nnombre=$nombre,\ntipo=$tipo,\naldea=$aldea,\ncategoria=$categoria,\nsellos=$sellos,\nrango=$rango,\nrequisito=$requisito,\ncoste=$coste,\ndescripcion=$descripcion";

    if ($objeto_id_post != $objeto_id_old) {
        $db->query(" 
            UPDATE `mybb_sg_sg_inventario` SET `objeto_id`='$objeto_id_post' WHERE objeto_id='$objeto_id_old'
        ");
    }

    $db->query(" 
        UPDATE `mybb_sg_sg_objetos` SET `objeto_id`='$$objeto_id_post', `nombre`='$nombre',`tipo`='$tipo',`categoria`='$categoria',`coste`='$coste',`descripcion`='$descripcion' WHERE `objeto_id`=$objeto_id_old;
    ");

    if (is_staff($uid)) {
        $db->query(" 
            INSERT INTO `mybb_sg_sg_audit_consola` (`staff`, `razon`, `log`) VALUES 
            ('$staff', '$razon', '$log');
        ");
    }

    if (is_mod($uid)) {
        $db->query(" 
            INSERT INTO `mybb_sg_sg_audit_consola_mod` (`staff`, `username`, `razon`, `log`) VALUES 
            ('$staff', '$username', '$razon', '$log');
        ");
    }

    eval('$log_var = $log;');
    eval('$reload_script = $reload_js;');
}

if ($objeto_id) {
    $objeto = null;
    $query_objetos = $db->query("
        SELECT * FROM `mybb_sg_sg_objetos` WHERE objeto_id='$objeto_id'
    ");
    while ($q = $db->fetch_array($query_objetos)) {
        $objeto = $q;
    }
}

if (is_mod($uid) || is_staff($uid)) { 
    eval("\$page = \"".$templates->get("staff_modificar_objetos")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
