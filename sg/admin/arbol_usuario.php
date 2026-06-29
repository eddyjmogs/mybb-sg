<?php
/**
 * MyBB 1.8
 *
 * Devuelve el ESTADO del Dojo de un usuario, derivado de tec_aprendidas +
 * catálogo cacheado (ver docs/arboles_instruciones.txt). Útil para depurar.
 *
 * Uso:
 *   /sg/admin/arbol_usuario.php?uid=725
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'arbol_usuario.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $db, $mybb;

$staff_uid = (int) $mybb->user['uid'];
if (!(is_mod($staff_uid) || is_staff($staff_uid))) {
    die('Sin permisos.');
}

header('Content-Type: application/json; charset=utf-8');

$uid = (int) $mybb->get_input('uid');
if (!$uid) {
    echo json_encode(array('error' => 'Falta el parámetro uid. Ej: ?uid=725'), JSON_UNESCAPED_UNICODE);
    exit;
}

$estado = sg_dojo_estado($db, $uid);
if ($estado === null) {
    echo json_encode(array('error' => "No existe ficha para uid $uid."), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
