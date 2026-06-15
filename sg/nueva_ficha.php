<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'nueva_ficha.php');
require_once "./../global.php";
require "./../inc/config.php";
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

    eval("\$page = \"".$templates->get("sg_nueva_ficha_creada")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_creada")."\";");
    output_page($page);
}
