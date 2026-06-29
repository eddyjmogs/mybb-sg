<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'nueva_ficha.php');
require_once "./../global.php";
require "./../inc/config.php";
require_once "./functions/sg_functions.php";
global $templates, $mybb;

$name               = addslashes($_POST['name']);
$alias              = addslashes($_POST['alias']);
$age                = (int) $_POST['age'];
$season             = (int) $_POST['season'];
$villa              = addslashes($_POST['villa']);
$clan               = addslashes($_POST['clan']);
$peso               = (int) $_POST['peso'];
$altura             = (int) $_POST['altura'];
$sexo               = addslashes($_POST['sexo']);
$virtudes           = addslashes($_POST['virtudes']);
$defectos           = addslashes($_POST['defectos']);
$phi                = addslashes($_POST['phi']);
$psi                = addslashes($_POST['psi']);
$history            = addslashes($_POST['history']);
$extra              = addslashes($_POST['extra']);
$frase              = addslashes($_POST['frase']);
$fisico_de_pj       = addslashes($_POST['fisico_de_pj']);
$como_nos_conociste = addslashes($_POST['como_nos_conociste']);
$submit             = $_POST['submit'];
$uid                = (int) $_POST['uid'];


if ($name && $age && $season && $villa && $clan && $phi && $psi && $history && $submit && $uid == $mybb->user['uid']) {

    $db->query("
        INSERT INTO `mybb_sg_sg_fichas`
            (`fid`, `peso`, `altura`, `sexo`, `nombre`, `apodo`, `edad`, `temporada_nacimiento`, `virtudes`, `defectos`, `villa`, `clan`, `apariencia`, `personalidad`, `historia`, `moderated`, `extra`, `frase`, `fisico_de_pj`, `como_nos_conociste`)
        VALUES
            ('$uid', '$peso', '$altura', '$sexo', '$name', '$alias', '$age', '$season', '$virtudes', '$defectos', '$villa', '$clan', '$phi', '$psi', '$history', 'no_moderacion', '$extra', '$frase', '$fisico_de_pj', '$como_nos_conociste')
    ");

    // Por defecto el personaje solo desbloquea la BASE de cada árbol fijo
    $tecnicas_iniciales = array(
        'BUKI101B',
        'DEFE101B',
        'RESI101B',
        'TAIJ101B'
    );
    foreach ($tecnicas_iniciales as $tec_tid) {
        $db->query("INSERT IGNORE INTO `mybb_sg_sg_tec_aprendidas`(`tid`, `uid`) VALUES ('$tec_tid','$uid')");
    }

    // Técnica base según el clan elegido (su árbol de clan)
    $clan_tecnicas = array(
        101 => 'ABUR101B',
        102 => 'SENJ101B',
        103 => 'AKIM101B',
        104 => 'HYUG101B',
        105 => 'NARA101B',
        106 => 'UCHI101B',
        108 => 'YAMA101B',
        109 => 'INUZ101B',
        110 => 'SARU101B',
        301 => 'YUKI101B',
        302 => 'KAGU101B',
        304 => 'HOZU101B',
        305 => 'HOSH101B',
        306 => 'HEIZ101B',
        307 => 'TERU101B',
        308 => 'AKIZ101B',
        309 => 'FUNA101B',
        310 => 'KODO101B'
    );
    if (isset($clan_tecnicas[$clan])) {
        $tec_clan = $clan_tecnicas[$clan];
        $db->query("INSERT IGNORE INTO `mybb_sg_sg_tec_aprendidas`(`tid`, `uid`) VALUES ('$tec_clan','$uid')");
    }

    // Progreso/economía del Dojo por defecto (ver docs/arboles_instruciones.txt).
    // El estado del árbol se DERIVA de tec_aprendidas; ya no se guarda un espejo
    // en la columna `arboles` (queda obsoleta).
    $progreso_json = $db->escape_string(json_encode(sg_progreso_defaults(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $db->query("UPDATE `mybb_sg_sg_fichas` SET `arboles_progreso`='$progreso_json' WHERE `fid`='$uid'");

    eval("\$page = \"".$templates->get("sg_nueva_ficha_creada")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_creada")."\";");
    output_page($page);
}
