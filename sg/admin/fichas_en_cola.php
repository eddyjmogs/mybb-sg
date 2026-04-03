<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'fichas_en_cola.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$action = $mybb->get_input('action');
$fid = $mybb->get_input('fid');
$villa = $mybb->get_input('villa');

$reload_js = "<script>window.location.href = window.location.pathname;</script>";

if ($action == 'aprobar' && $fid && $villa) {
    $usergroup = '2';

    switch ($villa) {
        case '1':
            $usergroup = '9'; // konoha
            break;
        case '3':
            $usergroup = '8'; // kiri
            break;
        case '4':
            $usergroup = '14'; // iwa
            break;
        case '5':
            $usergroup = '15'; // kumo
            break;
        case '7':
            $usergroup = '12'; // sin aldea
            break;
        default:
            $usergroup = '2'; // registered
    }

    $db->query(" 
        UPDATE `mybb_sg_sg_fichas` SET `moderated`='$username' WHERE moderated='no_moderacion' AND fid='$fid'
    ");
    $db->query(" 
        UPDATE `mybb_sg_users` SET `usergroup`='$usergroup' WHERE uid=$fid;
    ");
    eval('$reload_script = $reload_js;');
} else if ($action == 'borrar' && $fid) {
    $db->query("
        DELETE FROM mybb_sg_sg_fichas WHERE moderated='no_moderacion' AND fid='$fid';
    ");
    eval('$reload_script = $reload_js;');
    
}

if (is_mod($uid) || is_staff($uid) || is_user($uid)) { 
    $fichas_li = "";
    $query_fichas = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE moderated='no_moderacion'
    ");
    while ($f = $db->fetch_array($query_fichas)) {
        $fid = $f['fid'];
        $nombre = $f['nombre'];
        $villa = $f['villa'];
        $url = "/sg/admin/fichas_en_cola.php";
        $aprobar_a = "$url?action=aprobar&fid=$fid&villa=$villa";
        $borrar_a = "$url?action=borrar&fid=$fid";
        $fichas_li .= "<li>";
        $fichas_li .= "UID: <span><a href='/member.php?action=profile&uid=$fid' target='_blank'>$fid</a></span> ||| Cuenta: $nombre ||| ";
        $fichas_li .= "<span><a href='/sg/ficha.php?action=profile&uid=$fid' target='_blank'>Link de la ficha</a></span> ||| ";
        if (is_mod($uid) || is_staff($uid)) {
            $fichas_li .= "<span><a href='$aprobar_a' target='_blank'>Aprobar</a></span> ||| ";
            $fichas_li .= "<span><a href='$borrar_a' target='_blank'>Borrar</a></span>";
        }
        $fichas_li .= "</li>";
    }
    eval('$li_fichas = $fichas_li;');
    eval("\$page = \"".$templates->get("staff_fichas_en_cola")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}
