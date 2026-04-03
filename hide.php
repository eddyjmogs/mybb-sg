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
define('THIS_SCRIPT', 'hide.php');
require_once "./global.php";
require "./inc/config.php";
global $templates;

$hid = $_POST["hid"];
$show_hide = $_POST["show_hide"];
$tid = $_POST["tid"];

if ($hid && $show_hide && $tid) {
    $db->query(" 
        UPDATE `mybb_sg_sg_hide` SET show_hide='".$show_hide."' WHERE hid='".$hid."';
    ");
}

eval("\$page = \"".$templates->get("sg_hide")."\";");
output_page($page);


