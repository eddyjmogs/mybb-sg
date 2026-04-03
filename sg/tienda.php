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
define('THIS_SCRIPT', 'tienda.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

$uid = $mybb->user['uid'];
$accion = $_POST["accion"];
$objeto = $_POST["objeto"];
$ficha = null;

$query_ficha = $db->query("SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'");
while ($q = $db->fetch_array($query_ficha)) { $ficha = $q; }

$ryos = $ficha['ryos'];

$reload_js = "";

if ($accion == 'comprar' && $objeto && $uid != '0') {

    $has_objeto = false;
    $inventario_actual = $db->query("SELECT * FROM mybb_sg_sg_inventario WHERE uid='$uid' AND objeto_id='$objeto'");
    $query_objetos = $db->query(" SELECT * FROM `mybb_sg_sg_objetos` WHERE objeto_id='$objeto' ");
    $coste = '0';

    while ($q = $db->fetch_array($inventario_actual)) {
        $has_objeto = true;
        $cantidad = $q['cantidad'];
        // $coste = $q['coste'];
    }           

    while ($q = $db->fetch_array($query_objetos)) { 
        $coste = $q['coste'];
    }

    if ($has_objeto) {
        $nueva_cantidad = intval($cantidad) + 1;
        $db->query(" 
            UPDATE `mybb_sg_sg_inventario` SET `cantidad`='$nueva_cantidad' WHERE objeto_id='$objeto' AND uid='$uid'
        ");
    } else {
        $db->query(" 
            INSERT INTO `mybb_sg_sg_inventario` (`objeto_id`, `uid`, `cantidad`) VALUES 
            ('$objeto', '$uid', '1');
        ");
    }
    
    $ryosNuevos = intval($ryos) - intval($coste); 
    echo($ryosNuevos);

    $db->query(" 
        UPDATE `mybb_sg_sg_fichas` SET ryos='$ryosNuevos' WHERE `fid`='$uid';
    ");

    $reload_js = "<script>window.location.href = window.location.origin + window.location.pathname;</script>";
}

if (does_ficha_exist($uid)) {

    $ficha = select_one_query_with_id('mybb_sg_sg_fichas', 'fid', $uid);
    
    $rango = $ficha['rango'];
    $rangoNumero = 3;

    if ($rango == 'Jounin') { $rangoNumero = 5; }
    else if ($rango == 'Chuunin') { $rangoNumero = 4; }

    $inventario_actual = $db->query("SELECT * FROM `mybb_sg_sg_inventario` WHERE uid='$uid'");
    $query_objetos = $db->query(" SELECT * FROM `mybb_sg_sg_objetos` WHERE exclusivo='0' AND rango <= $rangoNumero ORDER BY categoria, tipo, nombre");

    $objetos = array();
    $objetos_array = array();
    $inventario = array();

    while ($q = $db->fetch_array($query_objetos)) { 
        $objeto_id = $q['objeto_id'];
        $key = "$objeto_id";
        if (!$objetos[$key]) { $objetos[$key] = array(); }
        array_push($objetos[$key], $q);
        array_push($objetos_array, $objeto_id);
    }

    while ($q = $db->fetch_array($inventario_actual)) {
        $objeto_id = $q['objeto_id'];
        $key = "$objeto_id";

        if (!$inventario[$key]) { $inventario[$key] = array(); }
        array_push($inventario[$key], $q);
    }  

    $objetos_array_json = json_encode($objetos_array);
    $objetos_json = json_encode($objetos);
    $inventario_json = json_encode($inventario);

    $objetos_html = "";
    $categoriaAnterior = ""; 
    $tipoAnterior = "";

    foreach ($objetos_array as $obj_key) {
        $obj_map = $objetos[$obj_key][0];
        $inv_map = $inventario[$obj_key][0];
        $nombre = $obj_map['nombre'];
        $cantidadMaxima = $obj_map['cantidadMaxima'];
        $coste = $obj_map['coste'];
        $categoria = $obj_map['categoria'];
        $tipo = $obj_map['tipo'];
        $descripcion = $obj_map['descripcion'];
        $imagen = $obj_map['imagen'];
        $efecto = $obj_map['efecto'];
        $cantidadActual = $inv_map != null ? $inv_map['cantidad'] : '0';
        $cumpleRequisitos = true;

        if ($cumpleRequisitos) { 

            if ($inventario[$obj_key] && intval($inventario[$obj_key][0]['cantidad']) >= intval($cantidadMaxima)) {
                $comprarButton = "<span>Comprado.</span>";
            } else if ($cantidadActual < $cantidadMaxima && intval($ryos) >= intval($coste)) {
                $comprarButton = "<input class='button' onclick=\"(function(){ $('#accion').val('comprar'); $('#objeto').val('$obj_key'); })();\" type='submit' value='Comprar'>";
            } else {
                $comprarButton = "<span>Faltan ryos.</span>";
            }
        } else {
            $comprarButton = "<span>Faltan requisitos.</span>";
        }
        
        if ($categoria != $categoriaAnterior) {
            $objetos_html .= "<br><h2 style='margin: 0;'>$categoria</h2>";
        }
        if ($tipo != $tipoAnterior) {
            $objetos_html .= "<br><h4 style='margin: 0;'>$tipo</h4>";
        }

        $efectos_str = convertObjectEffectsUsuario($efecto, 50, 25);

        $categoriaAnterior = $categoria;
        $tipoAnterior = $tipo;
        $danos = '';

        if ($efectos_str) {
            $danos = "<br><strong>Daños</strong>: <br>$efectos_str<br><strong>Código</strong>: [arma=$obj_key]<br>";
        }
    
        $tooltip = "<span class='tooltiptext'><strong>Descripción</strong>: <br>$descripcion<br>$danos<br><strong>Imagen</strong>: <img src='../.$imagen' /><br><br></span>";
        
        $objetos_html .= "<span><div class='tooltip'><a href='#'>$nombre</a>$tooltip</div> - Cantidad Maxima: $cantidadMaxima. Cantidad Actual: $cantidadActual. Coste: $coste ryos. || $comprarButton</span><br>";
    }

    eval("\$page = \"".$templates->get("sg_tienda")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}

// <div class="tooltip">Hover over me
//   <span class="tooltiptext">Tooltip text</span>
// </div>

    // select_one_query_with_id('mybb_sg_sg_items', 'eid', $item_id);

// ($categoria-$tipo) - 
/* 
$db->query(" 
    INSERT INTO `mybb_sg_sg_inventario` (`objeto_id`, `uid`) VALUES 
    ('$clean_obj', '$ficha_id');
");

reducir ryos, aumentar cantidad si ya es 1

*/