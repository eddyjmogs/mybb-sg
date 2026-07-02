<?php
/**
 * MyBB 1.8
 *
 * Gestión de Virtudes y Defectos (crear / modificar / eliminar).
 * Comparten la tabla mybb_sg_sg_virtudes; el signo de `puntos` define el tipo
 * (>= 0 virtud, < 0 defecto).
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'gestionar_virtudes.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb, $db;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$es_staff = (is_mod($uid) || is_staff($uid));

// Virtud cargada para editar (vía GET)
$virtud_id_input = trim($mybb->get_input('virtud_id'));

// Datos del formulario
$accion_post   = $_POST["accion"];
$virtud_id_old = trim($_POST["virtud_id_old"]);
$virtud_id_post= trim($_POST["virtud_id_post"]);
$nombre        = trim($_POST["nombre"]);
$puntos        = intval($_POST["puntos"]);
$exclusivo     = intval($_POST["exclusivo"]);
$descripcion   = addslashes($_POST["descripcion"]);
$staff         = trim($_POST["staff"]);
$razon         = trim($_POST["razon"]);

$reload_js = "<script>window.location.href = window.location.pathname;</script>";
$log = null;

// ── Guardar (crear o modificar) ───────────────────────────────
if ($accion_post == 'Guardar' && $virtud_id_post && $nombre && $descripcion && $staff && $razon && $es_staff) {
    $lookup_id = $virtud_id_old !== '' ? $virtud_id_old : $virtud_id_post;

    $existe = null;
    $query_existe = $db->query("SELECT virtud_id FROM `mybb_sg_sg_virtudes` WHERE virtud_id='$lookup_id'");
    while ($e = $db->fetch_array($query_existe)) {
        $existe = $e;
    }

    if ($existe) {
        // Modificar: si cambió el ID, actualiza también las asignaciones a usuarios
        if ($virtud_id_post != $virtud_id_old && $virtud_id_old !== '') {
            $db->query("UPDATE `mybb_sg_sg_virtudes_usuarios` SET `virtud_id`='$virtud_id_post' WHERE virtud_id='$virtud_id_old'");
        }

        $db->query("
            UPDATE `mybb_sg_sg_virtudes` SET
                `virtud_id`='$virtud_id_post', `nombre`='$nombre', `puntos`='$puntos',
                `exclusivo`='$exclusivo', `descripcion`='$descripcion'
            WHERE `virtud_id`='$lookup_id';
        ");

        $log = "Modificar virtud/defecto ID $lookup_id -> $virtud_id_post ($nombre).\npuntos=$puntos,\nexclusivo=$exclusivo,\ndescripcion=$descripcion";
    } else {
        // Crear
        $db->query("
            INSERT INTO `mybb_sg_sg_virtudes` (`virtud_id`, `nombre`, `puntos`, `exclusivo`, `descripcion`) VALUES
            ('$virtud_id_post','$nombre','$puntos','$exclusivo','$descripcion');
        ");

        $log = "Nueva virtud/defecto ID $virtud_id_post ($nombre).\npuntos=$puntos,\nexclusivo=$exclusivo,\ndescripcion=$descripcion";
    }
}

// ── Eliminar ──────────────────────────────────────────────────
if ($accion_post == 'Eliminar' && $virtud_id_post && $staff && $razon && $es_staff) {
    $db->query("DELETE FROM `mybb_sg_sg_virtudes` WHERE `virtud_id`='$virtud_id_post';");
    $log = "Eliminar virtud/defecto ID $virtud_id_post ($nombre).";
}

// ── Auditoría + recarga (común a Guardar/Eliminar) ────────────
if ($log !== null && $es_staff) {
    $db->query("INSERT INTO `mybb_sg_sg_audit_consola_mod` (`staff`, `username`, `razon`, `log`) VALUES ('$staff', '$username', '$razon', '$log');");

    eval('$log_var = $log;');
    eval('$reload_script = $reload_js;');
}

// ── Cargar virtud a editar ────────────────────────────────────
$virtud = null;
if ($virtud_id_input) {
    $query_v = $db->query("SELECT * FROM `mybb_sg_sg_virtudes` WHERE virtud_id='$virtud_id_input'");
    while ($q = $db->fetch_array($query_v)) {
        $virtud = $q;
    }
}

// ── Opciones del selector de carga (todas, agrupadas) ─────────
$opts_virtudes = '';
$opts_defectos = '';
$query_lista = $db->query("SELECT virtud_id, nombre, puntos FROM `mybb_sg_sg_virtudes` ORDER BY nombre ASC");
while ($l = $db->fetch_array($query_lista)) {
    $oid   = htmlspecialchars($l['virtud_id'], ENT_QUOTES);
    $onom  = htmlspecialchars($l['nombre'], ENT_QUOTES);
    $sel   = ($l['virtud_id'] === $virtud_id_input) ? ' selected' : '';
    $opt   = "<option value=\"$oid\"$sel>$oid — $onom</option>";
    if (intval($l['puntos']) >= 0) {
        $opts_virtudes .= $opt;
    } else {
        $opts_defectos .= $opt;
    }
}

if ($es_staff) {
    eval('$vid = $virtud_id_input;');
    eval("\$page = \"".$templates->get("staff_gestionar_virtudes")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
