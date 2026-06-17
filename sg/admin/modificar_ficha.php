<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'modificar_ficha.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$user_fid = $mybb->get_input('fid');

$es_staff = (is_mod($uid) || is_staff($uid));

$log = "";
$reload_script = '';
$reload_js = "<script>window.location.href = window.location.pathname;</script>";

/* Guardar cambios (un solo UPDATE con todos los campos) */
if ($mybb->request_method === 'post' && $es_staff) {

    $ficha_id = (int) $_POST['ficha_id'];
    $staff    = addslashes($_POST['staff']);
    $razon    = addslashes($_POST['razon']);

    // Campos de texto
    $nombre   = addslashes($_POST['nombre']);
    $apodo    = addslashes($_POST['apodo']);
    $villa    = addslashes($_POST['villa']);
    $clan     = addslashes($_POST['clan']);
    $sexo     = addslashes($_POST['sexo']);
    $notas    = addslashes($_POST['notas']);
    $extra    = addslashes($_POST['extra']);
    $frase    = addslashes($_POST['frase']);
    $virtudes = addslashes($_POST['virtudes']);
    $defectos = addslashes($_POST['defectos']);

    // Campos numéricos
    $ryos               = (int) $_POST['ryos'];
    $reputacion         = (int) $_POST['reputacion'];
    $edad               = (int) $_POST['edad'];
    $temporada_nacimiento = (int) $_POST['temporada_nacimiento'];
    $vida               = (int) $_POST['vida'];
    $chakra             = (int) $_POST['chakra'];
    $regchakra          = (int) $_POST['regchakra'];
    $peso               = (int) $_POST['peso'];
    $altura             = (int) $_POST['altura'];
    $madara             = (int) $_POST['madara'];
    $tobi               = (int) $_POST['tobi'];
    $rin                = (int) $_POST['rin'];
    $fuerza             = (int) $_POST['fuerza'];
    $destreza           = (int) $_POST['destreza'];
    $cchakra            = (int) $_POST['cchakra'];
    $inteligencia       = (int) $_POST['inteligencia'];
    $mfuerza            = (int) $_POST['mfuerza'];
    $mdestreza          = (int) $_POST['mdestreza'];
    $mcchakra           = (int) $_POST['mcchakra'];
    $minteligencia      = (int) $_POST['minteligencia'];
    $salud              = (int) $_POST['salud'];
    $velocidad          = (int) $_POST['velocidad'];
    $tenketsu           = (int) $_POST['tenketsu'];
    $sigilo             = (int) $_POST['sigilo'];
    $puntos_estadistica = (int) $_POST['puntos_estadistica'];
    $nivel              = (int) $_POST['nivel'];

    if ($ficha_id && $staff && $razon) {

        $db->query("
            UPDATE `mybb_sg_sg_fichas` SET
                ryos='$ryos', reputacion='$reputacion', nombre='$nombre', apodo='$apodo',
                edad='$edad', temporada_nacimiento='$temporada_nacimiento',
                villa='$villa', clan='$clan', vida='$vida', chakra='$chakra', regchakra='$regchakra',
                notas='$notas', extra='$extra', frase='$frase', virtudes='$virtudes', defectos='$defectos',
                sexo='$sexo', peso='$peso', altura='$altura', madara='$madara', tobi='$tobi', rin='$rin',
                fuerza='$fuerza', destreza='$destreza', cchakra='$cchakra', inteligencia='$inteligencia',
                mfuerza='$mfuerza', mdestreza='$mdestreza', mcchakra='$mcchakra', minteligencia='$minteligencia',
                salud='$salud', velocidad='$velocidad', tenketsu='$tenketsu', sigilo='$sigilo',
                puntos_estadistica='$puntos_estadistica', nivel='$nivel'
            WHERE `fid`='$ficha_id'
        ");

        // Sincronizar el grupo de usuario según la villa (solo si mapea a un grupo conocido)
        $villa_usergroup = array(
            '1' => '9',   // Konoha
            '3' => '8',   // Kiri
            '4' => '14',  // Iwa
            '5' => '15',  // Kumo
            '6' => '13',  // Renegado
            '7' => '12'   // Sin Aldea
        );
        if (isset($villa_usergroup[$villa])) {
            $nuevo_grupo = $villa_usergroup[$villa];
            $db->query("UPDATE `mybb_sg_users` SET usergroup='$nuevo_grupo' WHERE `uid`='$ficha_id'");
        }

        $log = "Edición completa de la ficha UID $ficha_id ($nombre).";

        $db->query("
            INSERT INTO `mybb_sg_sg_audit_consola_mod` (`staff`, `username`, `razon`, `log`) VALUES
            ('$staff', '$username', '$razon', '$log')
        ");

        eval('$reload_script = $reload_js;');
    }
}

/* Render */
if ($es_staff) {
    $ficha = null;

    if ($user_fid) {
        $query_ficha = $db->query("SELECT * FROM mybb_sg_sg_fichas WHERE fid='$user_fid'");
        while ($f = $db->fetch_array($query_ficha)) {
            $ficha = $f;
        }
    }

    eval('$fid = $user_fid;');
    eval("\$page = \"".$templates->get("staff_modificar_ficha")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
