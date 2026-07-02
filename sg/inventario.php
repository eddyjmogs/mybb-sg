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

$uid = intval($mybb->get_input('uid'));
if (!$uid) {
    $uid = intval($mybb->user['uid']);
}

$default_img = '/images/sg/objeto_default.png';

// Dueño del inventario
$owner_nombre = '';
$query_owner = $db->query("SELECT nombre FROM mybb_sg_sg_fichas WHERE fid='$uid'");
while ($o = $db->fetch_array($query_owner)) {
    $owner_nombre = $o['nombre'];
}
if (trim($owner_nombre) === '') {
    $owner_nombre = "Usuario #$uid";
}
$owner_nombre = htmlspecialchars($owner_nombre, ENT_QUOTES);

// Inventario (ordenado por tipo y nombre)
$query_inventario = $db->query("
    SELECT i.cantidad AS cantidad, o.*
    FROM `mybb_sg_sg_inventario` i
    INNER JOIN `mybb_sg_sg_objetos` o ON o.objeto_id = i.objeto_id
    WHERE i.uid='$uid'
    ORDER BY o.tipo, o.nombre
");

$objetos_html = '';
$tipoAnterior = null;
$total = 0;
$unidades = 0;

while ($q = $db->fetch_array($query_inventario)) {
    $total++;
    $cant = intval($q['cantidad']);
    $unidades += $cant;

    $oid       = htmlspecialchars($q['objeto_id'], ENT_QUOTES);
    $nombre    = htmlspecialchars($q['nombre'], ENT_QUOTES);
    $tipo      = trim($q['tipo']) !== '' ? $q['tipo'] : 'Otros';
    $tipo_esc  = htmlspecialchars($tipo, ENT_QUOTES);
    $tamano    = htmlspecialchars($q['tamano'], ENT_QUOTES);
    $desc      = nl2br(htmlspecialchars($q['descripcion'], ENT_QUOTES));
    $efecto_items = '';
    foreach (array($q['efecto1'], $q['efecto2'], $q['efecto3']) as $ef) {
        if (trim($ef) !== '') {
            $efecto_items .= "<div class=\"sg-item-effect\"><span class=\"sg-item-eff-label\">Efecto</span> " . nl2br(htmlspecialchars($ef, ENT_QUOTES)) . "</div>";
        }
    }
    $img       = trim($q['imagen']) !== '' ? htmlspecialchars($q['imagen'], ENT_QUOTES) : $default_img;
    $data_name = htmlspecialchars(strtolower($q['nombre']), ENT_QUOTES);
    $data_tipo = htmlspecialchars(strtolower($tipo), ENT_QUOTES);

    // Nuevo grupo por tipo
    if ($tipo !== $tipoAnterior) {
        if ($tipoAnterior !== null) {
            $objetos_html .= "</div></section>";
        }
        $objetos_html .= "<section class=\"sg-inv-group\"><h2 class=\"sg-inv-group-title\">$tipo_esc</h2><div class=\"sg-inv-grid\">";
        $tipoAnterior = $tipo;
    }

    $badges = "<span class=\"sg-item-badge\">$tipo_esc</span>";
    if ($tamano !== '') {
        $badges .= "<span class=\"sg-item-badge sg-item-badge--soft\">$tamano</span>";
    }

    $desc_html   = trim($q['descripcion']) !== '' ? "<p class=\"sg-item-desc\">$desc</p>" : '';
    $efecto_html = $efecto_items;

    $objetos_html .= "<article class=\"sg-item\" data-name=\"$data_name\" data-tipo=\"$data_tipo\">"
        . "<div class=\"sg-item-media\">"
        . "<img class=\"sg-item-img\" src=\"$img\" alt=\"$nombre\" loading=\"lazy\" onerror=\"sgImgFallback(this)\">"
        . "<span class=\"sg-item-qty\">&times;$cant</span>"
        . "</div>"
        . "<div class=\"sg-item-body\">"
        . "<h3 class=\"sg-item-name\">$nombre</h3>"
        . "<div class=\"sg-item-badges\">$badges</div>"
        . $desc_html
        . $efecto_html
        . "</div>"
        . "</article>";
}
if ($tipoAnterior !== null) {
    $objetos_html .= "</div></section>";
}
if ($total === 0) {
    $objetos_html = "<div class=\"sg-inv-empty\">Este inventario está vacío.</div>";
}

eval("\$page = \"".$templates->get("sg_inventario")."\";");
output_page($page);
