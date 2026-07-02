<?php
/**
 * MyBB 1.8
 *
 * Bingo Book: listado público de NPCs con filtro por nombre/afiliación.
 * Al hacer clic en un NPC lleva a su ficha (sg/npc.php?codigo=...).
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'npcs.php');

global $templates, $mybb, $db;

require_once "./../global.php";
require_once "./functions/sg_functions.php";

$default_img = '/images/sg/objeto_default.png';

$query = $db->query("SELECT * FROM `mybb_sg_sg_npcs` ORDER BY `nombre` ASC");

$cards = '';
$total = 0;
$afiliaciones = array(); // afil (display) => color, para los chips de filtro

while ($n = $db->fetch_array($query)) {
    $total++;

    $cod    = htmlspecialchars($n['codigo'], ENT_QUOTES);
    $nom    = htmlspecialchars($n['nombre'], ENT_QUOTES);
    $afil   = trim($n['afiliacion']) !== '' ? $n['afiliacion'] : 'Otros';
    $afil_e = htmlspecialchars($afil, ENT_QUOTES);
    $afil_l = htmlspecialchars(strtolower($afil), ENT_QUOTES);
    $color  = sg_npc_afiliacion_color($afil);
    $clan   = htmlspecialchars($n['clan_grupo'], ENT_QUOTES);
    $cargo  = htmlspecialchars($n['cargo'], ENT_QUOTES);
    $rango  = htmlspecialchars($n['rango'], ENT_QUOTES);
    $nivel  = intval($n['nivel']);
    $img    = trim($n['imagen']) !== '' ? htmlspecialchars($n['imagen'], ENT_QUOTES) : $default_img;

    $afiliaciones[$afil] = $color;

    $data_name = htmlspecialchars(strtolower($n['nombre'] . ' ' . $n['clan_grupo'] . ' ' . $n['cargo']), ENT_QUOTES);

    // Meta: clan · cargo (solo lo que exista)
    $meta_parts = array();
    if ($clan !== '')  { $meta_parts[] = $clan; }
    if ($cargo !== '') { $meta_parts[] = $cargo; }
    $meta = implode(' · ', $meta_parts);

    $rank_bits = '';
    if ($rango !== '') { $rank_bits .= "<span class=\"sg-bb-rango\">$rango</span>"; }
    $rank_bits .= "<span class=\"sg-bb-nivel\">Nv $nivel</span>";

    $cards .= "<a class=\"sg-bb-card\" href=\"/sg/npc.php?codigo=$cod\" style=\"--accent: $color;\" data-name=\"$data_name\" data-afil=\"$afil_l\">"
        . "<div class=\"sg-bb-media\">"
        . "<img class=\"sg-bb-img\" src=\"$img\" alt=\"$nom\" loading=\"lazy\" onerror=\"sgImgFallback(this)\">"
        . "<span class=\"sg-bb-afil\">$afil_e</span>"
        . "</div>"
        . "<div class=\"sg-bb-body\">"
        . "<h3 class=\"sg-bb-name\">$nom</h3>"
        . ($meta !== '' ? "<div class=\"sg-bb-meta\">$meta</div>" : "")
        . "<div class=\"sg-bb-rank\">$rank_bits</div>"
        . "</div>"
        . "</a>";
}

// Chips de filtro por afiliación
ksort($afiliaciones);
$chips = "<button type=\"button\" class=\"sg-bb-chip is-active\" data-afil=\"\" onclick=\"sgBBFilterAfil(this)\">Todas</button>";
foreach ($afiliaciones as $afil => $color) {
    $afil_e = htmlspecialchars($afil, ENT_QUOTES);
    $afil_l = htmlspecialchars(strtolower($afil), ENT_QUOTES);
    $chips .= "<button type=\"button\" class=\"sg-bb-chip\" data-afil=\"$afil_l\" style=\"--accent: $color;\" onclick=\"sgBBFilterAfil(this)\">$afil_e</button>";
}

if ($total === 0) {
    $cards = "<div class=\"sg-bb-empty\">No hay NPCs registrados por ahora.</div>";
}

eval("\$page = \"".$templates->get("sg_npcs")."\";");
output_page($page);
