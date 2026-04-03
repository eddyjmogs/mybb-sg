<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'censo.php');
require_once "./../global.php";
require "./../inc/config.php";

global $templates, $mybb, $db;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];

$categoria = addslashes($_POST["categoria"]);
$resumen = addslashes($_POST["resumen"]);
$descripcion = addslashes($_POST["descripcion"]);
$url = addslashes($_POST["url"]);

if ($categoria && $resumen) {
    $mensaje_redireccion = "¡Tu petición ha sido enviada exitosamente y la estaremos revisando! Muchas gracias crack, nunca cambies.";

    $db->query(" 
        INSERT INTO `mybb_sg_sg_peticiones` (`uid`, `nombre`, `categoria`, `resumen`, `descripcion`, `url`) VALUES ('$uid','$username','$categoria','$resumen','$descripcion','$url')
    ");

    eval("\$page = \"".$templates->get("sg_redireccion")."\";");
    output_page($page);
} else {
    
    if ($uid == '0') {
        $mensaje_redireccion = "Tienes que estar logueado para enviar peticiones administrativas, crack.";
        eval("\$page = \"".$templates->get("sg_redireccion")."\";");
        output_page($page);
    } else {
        $peticiones_txt = "";
        $pet_counter = 1;
        $query_peticion = $db->query("
            SELECT * FROM mybb_sg_sg_peticiones
            WHERE uid='$uid'
            AND resuelto=0
        ");
    
        while ($q = $db->fetch_array($query_peticion)) {
            $peticiones_txt .= $pet_counter . '. ' . $q['resumen'] . '<br>' . $q['descripcion'] . '<br><br>';
            $pet_counter += 1;
        }
    
        eval("\$page = \"".$templates->get("sg_peticiones")."\";");
        output_page($page);
    }


}
