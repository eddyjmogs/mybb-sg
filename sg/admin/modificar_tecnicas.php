<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'modificar_tecnicas.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$tecnica_id = $mybb->get_input('tecnica_id'); 

$tecnica_id_post = trim($_POST["tecnica_id"]);
$nombre = trim($_POST["nombre"]);
$tid = trim($_POST["tid"]);
$arbol = trim($_POST["arbol"]);
$rama = trim($_POST["rama"]);
$tipo = trim($_POST["tipo"]);
$aldea = trim($_POST["aldea"]);
$categoria = trim($_POST["categoria"]);
$sellos = trim($_POST["sellos"]);
$rango = trim($_POST["rango"]);
$requisito = trim($_POST["requisito"]);
$coste = addslashes($_POST["coste"]);
$efecto = addslashes($_POST["efecto"]);
$descripcion = addslashes($_POST["descripcion"]);
// $staff = trim($_POST["staff"]);
// $razon = trim($_POST["razon"]);
$staff = true;
$razon = true;

$reload_js = "<script>window.location.href = window.location.pathname;</script>";
$tecnica = null;

if ($tecnica_id) {
    $query_tecnicas = $db->query("
        SELECT * FROM `mybb_sg_sg_tecnicas` WHERE tid='$tecnica_id'
    ");
    while ($t = $db->fetch_array($query_tecnicas)) {
        $tecnica = $t;
    }
}

if ($tecnica_id && $tecnica_id_post && $nombre && $descripcion && $staff && $razon && (is_mod($uid) || is_staff($uid))) {
    $log = "Cambio a técnica ID $tid ($nombre). \nLos cambios son: \ntid=$tid,\nnombre=$nombre,\ntipo=$tipo,\naldea=$aldea,\ncategoria=$categoria,\nsellos=$sellos,\nrango=$rango,\nrequisito=$requisito,\ncoste=$coste,\nefecto=$efecto\ndescripcion=$descripcion";

    // $db->query(" 
    //     INSERT INTO `mybb_sg_sg_tecnicas_version` 
    //     (`tid`, `tid_old`, `version`, `nombre`, `tipo`, `aldea`, `categoria`, `sellos`, `rango`, `puntuacion`, `exclusiva`, `coste`, `efecto`, `requisito`, `descripcion`, `balance`, `notas_balance`)
    //     VALUES ('$tid', '$tid_old', '$version_old', '$nombre_old', '$tipo_old', '$aldea_old', '$categoria_old', '$sellos_old', '$rango_old', '$puntuacion_old', '$exclusiva_old', '$coste_old', '$efecto_old', '$requisito_old', '$descripcion_old', '$balance_old', '$notas_balance_old')
    // ");

    if ($tecnica_id_post != $tid) {
        $db->query(" 
            UPDATE `mybb_sg_sg_tec_aprendidas` SET `tid`='$tid' WHERE tid='$tecnica_id_post'
        ");
    }

    $db->query(" 
        UPDATE `mybb_sg_sg_tecnicas` SET `tid`='$tid',`nombre`='$nombre',`arbol`='$arbol',`rama`='$rama',`tipo`='$tipo',`aldea`='$aldea',`categoria`='$categoria',`sellos`='$sellos',`rango`='$rango',`coste`='$coste',`efecto`='$efecto',`requisito`='$requisito',`descripcion`='$descripcion' WHERE `tid`='$tecnica_id';
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

if (is_mod($uid) || is_staff($uid)) { 
    // eval('$tid = $tecnica_id;');
    eval("\$page = \"".$templates->get("staff_modificar_tecnicas")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
