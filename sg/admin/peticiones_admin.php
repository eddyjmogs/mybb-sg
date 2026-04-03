<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'fichas_en_cola.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$accion = $mybb->get_input('accion');
$peti_id = $mybb->get_input('peti_id');

$reload_js = "<script>window.location.href = window.location.pathname;</script>";

if ($accion == 'resolver' && $peti_id) {

    $db->query(" 
        UPDATE `mybb_sg_sg_peticiones` SET `resuelto`=1, `mod_uid`='$uid', `mod_nombre`='$username' WHERE id='$peti_id'
    ");

    eval('$reload_script = $reload_js;');
} else if ($action == 'borrar' && $peti_id) {
    $db->query("
        DELETE FROM mybb_sg_sg_peticiones WHERE uid='$peti_id';
    ");
    eval('$reload_script = $reload_js;');
}

if (is_mod($uid) || is_staff($uid) || is_user($uid)) { 
    $peticiones_li = "";

    // $borrar_a = "$url_page?accion=borrar&peti_id=$pid";

    function print_peticion($nombre_categoria, $categoria, $uid) {
        global $db;
        $query_peticion = $db->query("
            SELECT * FROM mybb_sg_sg_peticiones
            WHERE resuelto=0 AND categoria='$categoria'
        ");

        $peticiones_li = "";
        while ($q = $db->fetch_array($query_peticion)) {
            $pid = $q['id'];
            $u_uid = $q['uid'];
            $categoria = $q['categoria'];
            $resumen = $q['resumen'];
            $descripcion =  nl2br($q['descripcion']);
            $url = $q['url'];
            $url = "<a href='$url' target='_blank'>$url</a>";
            $nombre = $q['nombre'];
            $nombre = "<a href='/sg/ficha.php?uid=$u_uid' target='_blank'>$nombre</a>";
            $fecha = $q['tiempo'];
            
            $peticiones_li .= "<li>";
            $peticiones_li .= "[$nombre - $u_uid] <br> <strong>Categoría</strong>: $categoria <br> <strong>Resumen</strong>: $resumen <br> <strong>Descripción</strong>: $descripcion <br> <strong>URL</strong>: $url <br> <strong>Fecha</strong>: $fecha <br>";
            
            if (is_mod($uid) || is_staff($uid)) {
                $url_page = "/sg/admin/peticiones_admin.php";
                $resolver_a = "$url_page?accion=resolver&peti_id=$pid";
                $peticiones_li .= "<span><a href='$resolver_a' >Resolver</a></span>";
            }
            // $peticiones_li .= "<span><a href='$borrar_a' target='_blank'>Borrar</a></span>";
            $peticiones_li .= "</li><br>";
        
        }

        if ($peticiones_li != "") {
            return "<h5>$nombre_categoria</h5>" . $peticiones_li;
        } else {
            return "";
        }
    }


    $peticiones_li .= print_peticion('Moderación de Ficha', 'ficha', $uid);
    $peticiones_li .= print_peticion('Moderación de Temas', 'tema', $uid);
    $peticiones_li .= print_peticion('Moderación de Combate', 'combate', $uid);
    $peticiones_li .= print_peticion('Moderación de Técnica', 'tecnica', $uid);
    $peticiones_li .= print_peticion('Otras Moderaciones', 'otros', $uid);

    eval("\$page = \"".$templates->get("staff_peticiones_admin")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
