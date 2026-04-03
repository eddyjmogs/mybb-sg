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
define('THIS_SCRIPT', 'tecnicas_show2.php');
require_once "./../global.php";
require "./../inc/config.php";
global $templates;

$arbol = $mybb->get_input('arbol'); // clan, invo, taijutsu, ninjutsu, genjutsu, maestria
$rama = $mybb->get_input('rama'); // general, konoha, suna, kiri, iwa, kumo (SS: invo, maestrias) 

$query_tecnicas = $db->query("
SELECT * FROM mybb_sg_sg_tecnicas WHERE arbol='$arbol' AND rama='$rama'
");

while ($tecnica = $db->fetch_array($query_tecnicas)) {
    $tecnica['tid'] = strtoupper($tecnica['tid']);
    $tecnica['sellos'] = strtoupper($tecnica['sellos']);
    $tecnica['arbol'] = strtoupper($tecnica['arbol']);
    $tecnica['categoria'] = strtoupper($tecnica['categoria']);

    $tecnica['descripcion'] = nl2br($tecnica['descripcion']);
    eval('$tecnicas_templ .= "'.$templates->get('sg_tecnicas_show_tec').'";');
}


eval("\$page = \"".$templates->get("sg_tecnicas_show2")."\";");
output_page($page);


