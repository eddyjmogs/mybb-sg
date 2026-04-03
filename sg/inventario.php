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
define('THIS_SCRIPT', 'inventario.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

$uid = $mybb->get_input('uid'); 

if (!$uid) {
    $uid = $mybb->user['uid'];
}

$objetos_html = '';

$query_inventario = $db->query("
    SELECT * FROM `mybb_sg_sg_objetos` 
    INNER JOIN `mybb_sg_sg_inventario` 
    ON `mybb_sg_sg_objetos`.`objeto_id`=`mybb_sg_sg_inventario`.`objeto_id` 
    WHERE `mybb_sg_sg_inventario`.`uid`='$uid'
");

$objetos = array();
$objetos_array = array();

while ($q = $db->fetch_array($query_inventario)) { 
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

    $categoriaAnterior = $categoria;
    $tipoAnterior = $tipo;
    $tooltip = "<span class='tooltiptext'>Descripcion: <br>$descripcion<br><br>Efecto: <br>$efecto<br><br>Imagen: <img src='../.$imagen' /></span>";

    $objetos_html .= "<span><div class='tooltip'><a href='#'>$nombre</a>$tooltip</div> - Cantidad Maxima: $cantidadMaxima. Coste: $coste.</span><br>";
}

// while ($q = $db->fetch_array($query_inventario)) {
//     $id = $q['id'];
//     $objeto_id = $q['objeto_id'];
//     $nombre = $q['nombre'];
//     $tipo = $q['tipo'];
//     $categoria = $q['categoria'];
//     $coste = $q['coste'];
//     $descripcion = $q['descripcion'];
//     $efecto = $q['efecto'];

//     $objetos_html .= "ID: $id - Objeto ID: $objeto_id - Nombre: $nombre - Tipo: $tipo - Categoría: $categoria - Coste: $coste<br><strong>Descripción</strong>: $descripcion<br><strong>Efecto</strong>: $efecto<br><br>";
// }

eval("\$page = \"".$templates->get("sg_inventario")."\";");
output_page($page);

