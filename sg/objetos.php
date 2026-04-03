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
define('THIS_SCRIPT', 'armas.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

$uid = $mybb->user['uid'];
$s_uid = $mybb->user['uid'];

$query_objetos = $db->query(" SELECT * FROM `mybb_sg_sg_objetos` WHERE exclusivo='0' ORDER BY categoria, tipo, nombre ");
$objetos = array();
$objetos_array = array();

while ($q = $db->fetch_array($query_objetos)) { 
    $objeto_id = $q['objeto_id'];
    $key = "$objeto_id";
    if (!$objetos[$key]) { $objetos[$key] = array(); }
    array_push($objetos[$key], $q);
    array_push($objetos_array, $objeto_id);
}

$objetos_array_json = json_encode($objetos_array);
$objetos_json = json_encode($objetos);

$objetos_html = "";
$categoriaAnterior = ""; 
$tipoAnterior = "";

$objetos_html = "";
$categoriaAnterior = ""; 
$tipoAnterior = "";

foreach ($objetos_array as $obj_key) {
    $obj_map = $objetos[$obj_key][0];
    $nombre = $obj_map['nombre'];
    $cantidadMaxima = $obj_map['cantidadMaxima'];
    $coste = $obj_map['coste'];
    $categoria = $obj_map['categoria'];
    $tipo = $obj_map['tipo'];
    $descripcion = $obj_map['descripcion'];
    $imagen = $obj_map['imagen'];
    $efecto = $obj_map['efecto'];
    
    if ($categoria != $categoriaAnterior) {
        $objetos_html .= "<br><h2 style='margin: 0;'>$categoria</h2>";
    }
    if ($tipo != $tipoAnterior) {
        $objetos_html .= "<br><h4 style='margin: 0;'>$tipo</h4>";
    }
    $efectos_str = convertObjectEffects($efecto);

    $categoriaAnterior = $categoria;
    $tipoAnterior = $tipo;

    $danos = '';

    if ($efectos_str) {
        $danos = "<br><strong>Daños</strong>: <br>$efectos_str<br><strong>Código</strong>: [arma=$obj_key]<br>";
    }

    $tooltip = "<span class='tooltiptext'><strong>Descripción</strong>: <br>$descripcion<br>$danos<br><strong>Imagen</strong>: <img src='../.$imagen' /><br><br></span>";
    $objetos_html .= "<span><div class='tooltip'><a href='#'>$nombre</a>$tooltip</div> - Cantidad Maxima: $cantidadMaxima. Coste: $coste ryos.</span><br>";
}

eval("\$page = \"".$templates->get("sg_objetos")."\";");
output_page($page);

