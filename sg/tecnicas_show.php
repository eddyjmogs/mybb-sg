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
define('THIS_SCRIPT', 'tecnicas_show.php');
require_once "./../global.php";
require "./../inc/config.php";
global $templates;

$tipo = $mybb->get_input('tipo'); // clan, invo, taijutsu, ninjutsu, genjutsu, maestria
$aldea = $mybb->get_input('aldea'); // general, konoha, suna, kiri, iwa, kumo (SS: invo, maestrias) 

$query_clan = $db->query("
SELECT * FROM mybb_sg_sg_clanes WHERE nombreClan='$tipo'
");

$query_tecnicas_e = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='E'
");

$query_tecnicas_d = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='D'
");

$query_tecnicas_c = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='C'
");

$query_tecnicas_b = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='B'
");

$query_tecnicas_a = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='A'
");

$query_tecnicas_a_plus = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='A+'
");

$query_tecnicas_s = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='S'
");

$query_tecnicas_s_plus = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE tipo='$tipo' AND aldea='$aldea' AND rango='S+'
");

while ($clan = $db->fetch_array($query_clan)) {
    eval('$clan_desc = "'.nl2br($clan['descripcion']).'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_e)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_e .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_d)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_d .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_c)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_c .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_b)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_b .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_a)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_a .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_a_plus)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_a_plus .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_s)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_s .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

while ($tecnica = $db->fetch_array($query_tecnicas_s_plus)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['tipo'] = strtoupper($tecnica['tipo']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ_s_plus .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}

eval("\$page = \"".$templates->get("sg_tecnicas_show")."\";");
output_page($page);


