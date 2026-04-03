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
define('THIS_SCRIPT', 'ficha.php');
require_once "./../global.php";
require "./../inc/config.php";

global $templates, $mybb;

$uid = $mybb->get_input('uid'); 

$ficha_existe = false;
$moderated = false;
eval('$userid = $uid;');

$query_ficha = $db->query("
    SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
");

while ($f = $db->fetch_array($query_ficha)) {
    $moderated = $f['moderated'] != 'no_moderacion';
    $ficha_existe = true;
}

if ($ficha_existe == true && $moderated == true && ($mybb->user['uid'] == $uid || $g_is_staff)) {
    $query_abc = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");

    while ($f = $db->fetch_array($query_abc)) {
        eval('$ficha = $f;');
    }

    eval("\$page = \"".$templates->get("sg_editar_descripcion")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}



