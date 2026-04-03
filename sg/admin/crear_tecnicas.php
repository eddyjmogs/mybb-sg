<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'crear_tecnicas.lphp');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$user_accion = $mybb->get_input('accion'); 
$tecnica_id = $mybb->get_input('tecnica_id'); 

$tecnica_id_post = $_POST["tecnica_id"];
$accion_post = $_POST["accion"];

$nombre = trim($_POST["nombre"]);
$tid = trim($_POST["tid"]);
$arbol = trim($_POST["arbol"]);
$rama = trim($_POST["rama"]);
$tipo = trim($_POST["tipo"]);
$aldea = trim($_POST["aldea"]);
$categoria = $_POST["categoria"];
$sellos = $_POST["sellos"];
$rango = trim($_POST["rango"]);
$puntuacion = trim($_POST["puntuacion"]);
$requisito = $_POST["requisito"];
$coste = addslashes($_POST["coste"]);
$efecto = addslashes($_POST["efecto"]);
$rango = addslashes($_POST["rango"]);
$descripcion = addslashes($_POST["descripcion"]);
// $balance = trim($_POST["balance"]);
// $notas_balance = addslashes($_POST["notas_balance"]);

// $staff = $_POST["staff"];
// $razon = $_POST["razon"];
$staff = true;
$razon = true;

$reload_js = "<script>window.location.href = window.location.pathname;</script>";

if ($accion_post == 'Agregar' && $nombre && $tid && $staff && (is_mod($uid) || is_staff($uid))) {
    $log = "Nueva técnica creada de técnica ID $tid ($nombre). \nLa nueva técnica posee: \ntid=$tid,\nnombre=$nombre,\ntipo=$tipo,\naldea=$aldea,\ncategoria=$categoria,\nsellos=$sellos,\nrango=$rango,\nrequisito=$requisito,\ncoste=$coste,\nefecto=$efecto\ndescripcion=$descripcion";

    $db->query(" 
        INSERT INTO `mybb_sg_sg_tecnicas` (`tid`, `nombre`, `arbol`, `rama`, `tipo`, `aldea`, `categoria`, `sellos`, `rango`, `coste`, `efecto`, `requisito`, `descripcion`) VALUES 
        ('$tid','$nombre','$arbol', '$rama', '$tipo','$aldea','$categoria','$sellos','$rango','$coste','$efecto','$requisito','$descripcion');
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

if ($accion_post == 'Remover' && $tecnica_id_post && $nombre && $staff && $razon && (is_mod($uid) || is_staff($uid))) {
    $log = "Remover técnica ID $tecnica_id_post ($nombre). Nuevo ID de la técnica: BORR$tecnica_id_post. Tipo: borrada.";
    
    $db->query(" 
        UPDATE `mybb_sg_sg_tecnicas` SET `tid`='BORR$tecnica_id_post', `tipo`='borrada' WHERE `tid`='$tecnica_id_post';
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

if ($user_accion && $tecnica_id) {
    $query_tecnicas = $db->query("
        SELECT * FROM `mybb_sg_sg_tecnicas` WHERE tid='$tecnica_id'
    ");
    while ($t = $db->fetch_array($query_tecnicas)) {
        eval('$tecnica = $t;');
    }
}

if (is_mod($uid) || is_staff($uid)) { 
    eval('$accion = $user_accion;');
    eval('$tid = $tecnica_id;');
    eval("\$page = \"".$templates->get("staff_crear_tecnicas")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
