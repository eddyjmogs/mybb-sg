<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'log_tecnicas_mod.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;
$uid = $mybb->user['uid'];

if (is_mod($uid) || is_staff($uid)) { 
    $logs = "";
    $query_log_tec = $db->query("
        SELECT * FROM mybb_sg_sg_audit_consola_tec_mod ORDER BY id DESC LIMIT 500;
    ");
    while ($l = $db->fetch_array($query_log_tec)) {
        $id = $l['id'];
        $tiempo = $l['tiempo'];
        $staff = $l['staff'];
        $razon = $l['razon'];
        $log = $l['log'];
        $logs .= "<article class='sg-log'>
            <div class='sg-log__head'>
                <span class='sg-log__id'>#{$id}</span>
                <span class='sg-log__meta'><span class='sg-log__label'>Staff</span>{$staff}</span>
                <span class='sg-log__meta'><span class='sg-log__label'>Razón</span>{$razon}</span>
                <span class='sg-log__time'>{$tiempo}</span>
            </div>
            <pre class='sg-log__body'>{$log}</pre>
        </article>";
    }

    eval('$logs_li = $logs;');
    eval("\$page = \"".$templates->get("staff_log_tecnicas")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
}

