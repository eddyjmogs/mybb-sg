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

$armas = '';

$query_armas = $db->query("
    SELECT * FROM mybb_sg_sg_objetos
");

$s_uid = $mybb->user['uid'];

while ($q = $db->fetch_array($query_armas)) {
    $id = $q['id'];
    $nombre = $q['nombre'];
    $tipo = $q['tipo'];
    $categoria = $q['categoria'];
    $coste = $q['coste'];
    $descripcion = $q['descripcion'];
    $efecto = $q['efecto'];

    $armas .= "ID: $id - Nombre: $nombre - Tipo: $tipo - Categoría: $categoria - Coste: $coste<br><strong>Descripción</strong>: $descripcion<br><strong>Efecto</strong>: $efecto<br><br>";
}

eval("\$page = \"".$templates->get("sg_objetos")."\";");
output_page($page);

