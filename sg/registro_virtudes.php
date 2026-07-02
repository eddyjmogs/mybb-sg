<?php
/**
 * MyBB 1.8
 *
 * Registro de Virtudes y Defectos.
 * Dos columnas: virtudes (izquierda) y defectos (derecha), cada una scrolleable.
 * Comparten la tabla mybb_sg_sg_virtudes; se distinguen por el signo de `puntos`.
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'registro_virtudes.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

// ── Construye una tarjeta ─────────────────────────────────────
function sg_vt_card($r)
{
    $puntos    = intval($r['puntos']);
    $es_virtud = $puntos >= 0;

    $nombre   = htmlspecialchars($r['nombre'], ENT_QUOTES);
    $vid      = htmlspecialchars($r['virtud_id'], ENT_QUOTES);
    $desc     = nl2br(htmlspecialchars($r['descripcion'], ENT_QUOTES));
    $data_nm  = htmlspecialchars(strtolower($r['nombre']), ENT_QUOTES);

    $abs        = abs($puntos);
    $costo_lbl  = ($es_virtud ? '+' : '−') . $abs;
    $costo_cls  = $es_virtud ? 'sg-vt-cost--v' : 'sg-vt-cost--d';

    $excl = intval($r['exclusivo']) === 1
        ? "<span class=\"sg-vt-badge sg-vt-badge--excl\">Exclusivo</span>"
        : '';

    $icon = $es_virtud
        ? "<span class=\"sg-vt-icon sg-vt-icon--v\" title=\"Virtud\"><svg viewBox=\"0 0 12 12\" fill=\"currentColor\"><path d=\"M6 1.5 11 10.5 1 10.5Z\"/></svg></span>"
        : "<span class=\"sg-vt-icon sg-vt-icon--d\" title=\"Defecto\"><svg viewBox=\"0 0 12 12\" fill=\"currentColor\"><path d=\"M6 10.5 1 1.5 11 1.5Z\"/></svg></span>";

    return "<article class=\"sg-vt-item\" data-name=\"$data_nm\">"
        . "<div class=\"sg-vt-head\">"
        . $icon
        . "<h3 class=\"sg-vt-name\">$nombre</h3>"
        . "<span class=\"sg-vt-cost $costo_cls\">$costo_lbl</span>"
        . "</div>"
        . "<div class=\"sg-vt-meta\"><span class=\"sg-vt-badge\">$vid</span>$excl</div>"
        . "<p class=\"sg-vt-desc\">$desc</p>"
        . "</article>";
}

// ── Consulta ──────────────────────────────────────────────────
$virtudes_html = '';
$defectos_html = '';
$n_virtudes = 0;
$n_defectos = 0;

$query = $db->query("
    SELECT virtud_id, nombre, puntos, exclusivo, descripcion
    FROM mybb_sg_sg_virtudes
    ORDER BY nombre ASC
");

while ($r = $db->fetch_array($query)) {
    if (intval($r['puntos']) >= 0) {
        $virtudes_html .= sg_vt_card($r);
        $n_virtudes++;
    } else {
        $defectos_html .= sg_vt_card($r);
        $n_defectos++;
    }
}

if ($n_virtudes === 0) {
    $virtudes_html = "<div class=\"sg-vt-empty\">No hay virtudes registradas.</div>";
}
if ($n_defectos === 0) {
    $defectos_html = "<div class=\"sg-vt-empty\">No hay defectos registrados.</div>";
}

eval("\$page = \"".$templates->get("sg_registro_virtudes")."\";");
output_page($page);
