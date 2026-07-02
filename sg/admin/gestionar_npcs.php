<?php
/**
 * MyBB 1.8
 *
 * Gestión de NPCs (crear / modificar / eliminar) — tabla mybb_sg_sg_npcs.
 * Se carga por npc_id (PK estable); `codigo` es un slug único editable.
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'gestionar_npcs.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb, $db;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$es_staff = (is_mod($uid) || is_staff($uid));

// NPC cargado para editar (vía GET)
$npc_id_input = intval($mybb->get_input('npc_id'));

// Datos del formulario
$accion_post  = $_POST["accion"];
$npc_id_post  = intval($_POST["npc_id"]);
$codigo       = addslashes(trim($_POST["codigo"]));
$nombre       = addslashes(trim($_POST["nombre"]));
$clan_grupo   = addslashes(trim($_POST["clan_grupo"]));
$afiliacion   = addslashes(trim($_POST["afiliacion"]));
$cargo        = addslashes(trim($_POST["cargo"]));
$frase        = addslashes($_POST["frase"]);
$edad         = addslashes(trim($_POST["edad"]));
$descripcion  = addslashes($_POST["descripcion"]);
$imagen       = addslashes(trim($_POST["imagen"]));
$rango        = addslashes(trim($_POST["rango"]));
$nivel        = intval($_POST["nivel"]);
$fuerza       = intval($_POST["fuerza"]);
$destreza     = intval($_POST["destreza"]);
$inteligencia = intval($_POST["inteligencia"]);
$cchakra      = intval($_POST["cchakra"]);
$mfuerza      = intval($_POST["mfuerza"]);
$mdestreza    = intval($_POST["mdestreza"]);
$minteligencia= intval($_POST["minteligencia"]);
$mcchakra     = intval($_POST["mcchakra"]);
$vida         = intval($_POST["vida"]);
$chakra       = intval($_POST["chakra"]);
$regchakra    = intval($_POST["regchakra"]);
$salud        = intval($_POST["salud"]);
$velocidad    = intval($_POST["velocidad"]);
$tenketsu     = intval($_POST["tenketsu"]);
$sigilo       = intval($_POST["sigilo"]);
$staff        = trim($_POST["staff"]);
$razon        = trim($_POST["razon"]);

$reload_js = "<script>window.location.href = window.location.pathname;</script>";
$log = null;

$set_cols = "
    `codigo`='$codigo', `nombre`='$nombre', `clan_grupo`='$clan_grupo', `afiliacion`='$afiliacion',
    `cargo`='$cargo', `frase`='$frase', `edad`='$edad', `descripcion`='$descripcion', `imagen`='$imagen',
    `rango`='$rango', `nivel`='$nivel',
    `fuerza`='$fuerza', `destreza`='$destreza', `inteligencia`='$inteligencia', `cchakra`='$cchakra',
    `mfuerza`='$mfuerza', `mdestreza`='$mdestreza', `minteligencia`='$minteligencia', `mcchakra`='$mcchakra',
    `vida`='$vida', `chakra`='$chakra', `regchakra`='$regchakra',
    `salud`='$salud', `velocidad`='$velocidad', `tenketsu`='$tenketsu', `sigilo`='$sigilo'
";

// ── Guardar (crear o modificar) ───────────────────────────────
if ($accion_post == 'Guardar' && $codigo && $nombre && $staff && $razon && $es_staff) {
    if ($npc_id_post > 0) {
        $db->query("UPDATE `mybb_sg_sg_npcs` SET $set_cols WHERE `npc_id`='$npc_id_post';");
        $log = "Modificar NPC #$npc_id_post ($nombre / $codigo). rango=$rango, nivel=$nivel, afiliacion=$afiliacion.";
    } else {
        $db->query("
            INSERT INTO `mybb_sg_sg_npcs`
                (`codigo`, `nombre`, `clan_grupo`, `afiliacion`, `cargo`, `frase`, `edad`, `descripcion`, `imagen`, `rango`, `nivel`,
                 `fuerza`, `destreza`, `inteligencia`, `cchakra`, `mfuerza`, `mdestreza`, `minteligencia`, `mcchakra`,
                 `vida`, `chakra`, `regchakra`, `salud`, `velocidad`, `tenketsu`, `sigilo`)
            VALUES
                ('$codigo','$nombre','$clan_grupo','$afiliacion','$cargo','$frase','$edad','$descripcion','$imagen','$rango','$nivel',
                 '$fuerza','$destreza','$inteligencia','$cchakra','$mfuerza','$mdestreza','$minteligencia','$mcchakra',
                 '$vida','$chakra','$regchakra','$salud','$velocidad','$tenketsu','$sigilo');
        ");
        $log = "Nuevo NPC ($nombre / $codigo). rango=$rango, nivel=$nivel, afiliacion=$afiliacion.";
    }
}

// ── Eliminar ──────────────────────────────────────────────────
if ($accion_post == 'Eliminar' && $npc_id_post > 0 && $staff && $razon && $es_staff) {
    $db->query("DELETE FROM `mybb_sg_sg_npcs` WHERE `npc_id`='$npc_id_post';");
    $log = "Eliminar NPC #$npc_id_post ($nombre / $codigo).";
}

// ── Auditoría + recarga ───────────────────────────────────────
if ($log !== null && $es_staff) {
    $log_esc = $db->escape_string($log);
    $db->query("INSERT INTO `mybb_sg_sg_audit_consola_mod` (`staff`, `username`, `razon`, `log`) VALUES ('$staff', '$username', '$razon', '$log_esc');");
    eval('$log_var = $log;');
    eval('$reload_script = $reload_js;');
}

// ── Cargar NPC a editar (o defaults para uno nuevo) ───────────
$npc = null;
if ($npc_id_input > 0) {
    $q = $db->query("SELECT * FROM `mybb_sg_sg_npcs` WHERE npc_id='$npc_id_input'");
    while ($r = $db->fetch_array($q)) { $npc = $r; }
}
if ($npc === null) {
    // Valores por defecto (coinciden con la definición de la tabla)
    $npc = array(
        'npc_id' => '', 'codigo' => '', 'nombre' => '', 'clan_grupo' => '', 'afiliacion' => '',
        'cargo' => '', 'frase' => '', 'edad' => '', 'descripcion' => '', 'imagen' => '', 'rango' => '',
        'nivel' => 1, 'fuerza' => 0, 'destreza' => 0, 'inteligencia' => 0, 'cchakra' => 0,
        'mfuerza' => 1, 'mdestreza' => 1, 'minteligencia' => 1, 'mcchakra' => 1,
        'vida' => 180, 'chakra' => 180, 'regchakra' => 3, 'salud' => 9, 'velocidad' => 9, 'tenketsu' => 9, 'sigilo' => 9,
    );
    $npc_id_input = 0;
}

// ── Opciones del selector de carga (agrupadas por afiliación) ─
$opciones = '';
$q_list = $db->query("SELECT npc_id, codigo, nombre, afiliacion FROM `mybb_sg_sg_npcs` ORDER BY afiliacion, nombre ASC");
$afil_prev = null;
while ($r = $db->fetch_array($q_list)) {
    $afil = trim($r['afiliacion']) !== '' ? $r['afiliacion'] : 'Sin afiliación';
    $afil_esc = htmlspecialchars($afil, ENT_QUOTES);
    if ($afil !== $afil_prev) {
        if ($afil_prev !== null) { $opciones .= "</optgroup>"; }
        $opciones .= "<optgroup label=\"$afil_esc\">";
        $afil_prev = $afil;
    }
    $val   = intval($r['npc_id']);
    $label = htmlspecialchars($r['codigo'] . ' — ' . $r['nombre'], ENT_QUOTES);
    $sel   = ($val === $npc_id_input) ? ' selected' : '';
    $opciones .= "<option value=\"$val\"$sel>$label</option>";
}
if ($afil_prev !== null) { $opciones .= "</optgroup>"; }

if ($es_staff) {
    eval('$nid = $npc_id_input;');
    eval("\$page = \"".$templates->get("staff_gestionar_npcs")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
