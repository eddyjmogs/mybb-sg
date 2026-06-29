<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 * Tienda: catálogo comprable con validación de compra en el servidor.
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'tienda.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

$uid = intval($mybb->user['uid']);
$default_img = '/images/sg/objeto_default.png';

$accion        = $_POST["accion"];
$objeto_post   = trim($_POST["objeto"]);
$cantidad_post = intval($_POST["cantidad"]);

$compra_ok = '';
$compra_error = '';

if (!does_ficha_exist($uid)) {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
    exit;
}

$ficha = select_one_query_with_id('mybb_sg_sg_fichas', 'fid', $uid);
$ryos = intval($ficha['ryos']);

// ── Compra (validada y serializada en el servidor) ────────────
if ($accion === 'comprar' && $objeto_post !== '' && $uid > 0) {
    $objeto_esc = $db->escape_string($objeto_post);
    $lock_name  = "sg_tienda_compra_$uid";

    // Lock por usuario: serializa compras concurrentes (evita doble-compra).
    // GET_LOCK funciona en MyISAM e InnoDB (las tablas usan motores mixtos).
    $got_lock = 0;
    $rl = $db->query("SELECT GET_LOCK('$lock_name', 5) AS l");
    while ($r = $db->fetch_array($rl)) {
        $got_lock = intval($r['l']);
    }

    if ($got_lock !== 1) {
        $compra_error = "No se pudo procesar la compra en este momento. Intenta de nuevo.";
    } else {
        // Todo lo que sigue corre dentro del lock con datos FRESCOS.
        $obj = null;
        $qo = $db->query("SELECT * FROM `mybb_sg_sg_objetos` WHERE objeto_id='$objeto_esc' AND en_tienda='1'");
        while ($o = $db->fetch_array($qo)) {
            $obj = $o;
        }

        if (!$obj) {
            $compra_error = "Ese objeto no está disponible en la tienda.";
        } else {
            // Saldo de ryos fresco (no el leído antes del lock)
            $ryos_actual = 0;
            $qr = $db->query("SELECT ryos FROM `mybb_sg_sg_fichas` WHERE fid='$uid'");
            while ($r = $db->fetch_array($qr)) {
                $ryos_actual = intval($r['ryos']);
            }

            $onombre = htmlspecialchars($obj['nombre'], ENT_QUOTES);
            $coste   = intval($obj['coste']);
            $maxq    = intval($obj['cantidadMaxima']);
            $n       = $cantidad_post > 0 ? $cantidad_post : 1;

            $actual = 0;
            $has = false;
            $qi = $db->query("SELECT cantidad FROM `mybb_sg_sg_inventario` WHERE uid='$uid' AND objeto_id='$objeto_esc'");
            while ($i = $db->fetch_array($qi)) {
                $has = true;
                $actual = intval($i['cantidad']);
            }

            $espacio = $maxq - $actual;

            if ($espacio <= 0) {
                $compra_error = "Ya tienes el máximo de \"$onombre\".";
            } else if ($n > $espacio) {
                $compra_error = "Solo puedes comprar $espacio más de \"$onombre\".";
            } else {
                $totalCoste = $coste * $n;
                if ($ryos_actual < $totalCoste) {
                    $compra_error = "Ryos insuficientes: necesitas " . number_format($totalCoste, 0, ',', '.') . " y tienes " . number_format($ryos_actual, 0, ',', '.') . ".";
                } else {
                    if ($has) {
                        $nueva = $actual + $n;
                        $db->query("UPDATE `mybb_sg_sg_inventario` SET `cantidad`='$nueva' WHERE uid='$uid' AND objeto_id='$objeto_esc'");
                    } else {
                        $db->query("INSERT INTO `mybb_sg_sg_inventario` (`objeto_id`, `uid`, `cantidad`) VALUES ('$objeto_esc', '$uid', '$n')");
                    }

                    $ryos_actual = $ryos_actual - $totalCoste;
                    $db->query("UPDATE `mybb_sg_sg_fichas` SET ryos='$ryos_actual' WHERE `fid`='$uid'");

                    $compra_ok = "Compraste $n × \"$onombre\" por " . number_format($totalCoste, 0, ',', '.') . " ryos.";
                }
            }

            // Refleja el saldo (actualizado o sin cambios) para el render posterior
            $ryos = $ryos_actual;
        }

        $db->query("SELECT RELEASE_LOCK('$lock_name')");
    }
}

// ── Inventario actual del usuario ─────────────────────────────
$inv = array();
$qinv = $db->query("SELECT objeto_id, cantidad FROM `mybb_sg_sg_inventario` WHERE uid='$uid'");
while ($r = $db->fetch_array($qinv)) {
    $inv[$r['objeto_id']] = intval($r['cantidad']);
}

// ── Catálogo a la venta ───────────────────────────────────────
$query_objetos = $db->query("
    SELECT * FROM `mybb_sg_sg_objetos`
    WHERE en_tienda='1'
    ORDER BY tipo, nombre
");

$objetos_html = '';
$tipoAnterior = null;
$total = 0;

while ($q = $db->fetch_array($query_objetos)) {
    $total++;

    $oid_raw   = $q['objeto_id'];
    $oid       = htmlspecialchars($oid_raw, ENT_QUOTES);
    $nombre    = htmlspecialchars($q['nombre'], ENT_QUOTES);
    $tipo      = trim($q['tipo']) !== '' ? $q['tipo'] : 'Otros';
    $tipo_esc  = htmlspecialchars($tipo, ENT_QUOTES);
    $tamano    = htmlspecialchars($q['tamano'], ENT_QUOTES);
    $desc      = nl2br(htmlspecialchars($q['descripcion'], ENT_QUOTES));
    $efecto    = nl2br(htmlspecialchars($q['efecto'], ENT_QUOTES));
    $coste     = intval($q['coste']);
    $maxq      = intval($q['cantidadMaxima']);
    $img       = trim($q['imagen']) !== '' ? htmlspecialchars($q['imagen'], ENT_QUOTES) : $default_img;
    $data_name = htmlspecialchars(strtolower($q['nombre']), ENT_QUOTES);
    $data_tipo = htmlspecialchars(strtolower($tipo), ENT_QUOTES);

    $coste_label = ($coste >= 99999) ? '—' : number_format($coste, 0, ',', '.') . ' ryos';

    $actual  = isset($inv[$oid_raw]) ? $inv[$oid_raw] : 0;
    $espacio = $maxq - $actual;
    $afford  = $coste > 0 ? intdiv($ryos, $coste) : $espacio;

    // Control de compra
    if ($espacio <= 0) {
        $buy = "<div class=\"sg-buy-status sg-buy-status--max\">Máximo alcanzado</div>";
    } else if ($afford <= 0) {
        $buy = "<div class=\"sg-buy-status sg-buy-status--no\">Ryos insuficientes</div>";
    } else {
        $maxbuy = min($espacio, $afford);
        $buy = "<form method=\"post\" action=\"/sg/tienda.php\" class=\"sg-buy\">"
            . "<input type=\"hidden\" name=\"accion\" value=\"comprar\">"
            . "<input type=\"hidden\" name=\"objeto\" value=\"$oid\">"
            . "<input class=\"sg-buy-qty\" type=\"number\" name=\"cantidad\" min=\"1\" max=\"$maxbuy\" value=\"1\" title=\"Cantidad (máx. $maxbuy)\">"
            . "<button class=\"sg-btn\" type=\"submit\">Comprar</button>"
            . "</form>";
    }

    // Nuevo grupo por tipo
    if ($tipo !== $tipoAnterior) {
        if ($tipoAnterior !== null) {
            $objetos_html .= "</div></section>";
        }
        $objetos_html .= "<section class=\"sg-cat-group\"><h2 class=\"sg-cat-group-title\">$tipo_esc</h2><div class=\"sg-cat-grid\">";
        $tipoAnterior = $tipo;
    }

    $badges = "<span class=\"sg-item-badge\">$tipo_esc</span>";
    if ($tamano !== '') {
        $badges .= "<span class=\"sg-item-badge sg-item-badge--soft\">$tamano</span>";
    }

    $desc_html   = trim($q['descripcion']) !== '' ? "<p class=\"sg-item-desc\">$desc</p>" : '';
    $efecto_html = trim($q['efecto']) !== '' ? "<div class=\"sg-item-effect\"><span class=\"sg-item-eff-label\">Efecto</span> $efecto</div>" : '';

    $objetos_html .= "<article class=\"sg-item\" data-name=\"$data_name\" data-tipo=\"$data_tipo\">"
        . "<div class=\"sg-item-media\">"
        . "<img class=\"sg-item-img\" src=\"$img\" alt=\"$nombre\" loading=\"lazy\" onerror=\"sgImgFallback(this)\">"
        . "<span class=\"sg-item-cost\">$coste_label</span>"
        . "</div>"
        . "<div class=\"sg-item-body\">"
        . "<h3 class=\"sg-item-name\">$nombre</h3>"
        . "<div class=\"sg-item-badges\">$badges</div>"
        . $desc_html
        . $efecto_html
        . "<div class=\"sg-item-meta\">Tienes <strong>$actual</strong> / $maxq</div>"
        . $buy
        . "</div>"
        . "</article>";
}
if ($tipoAnterior !== null) {
    $objetos_html .= "</div></section>";
}
if ($total === 0) {
    $objetos_html = "<div class=\"sg-cat-empty\">No hay objetos a la venta por ahora.</div>";
}

$ryos_label = number_format($ryos, 0, ',', '.');

eval("\$page = \"".$templates->get("sg_tienda")."\";");
output_page($page);
