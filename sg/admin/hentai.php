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
define('THIS_SCRIPT', 'hentai.php');
require_once "./../../global.php";
require "./../../inc/config.php";
require_once "./../functions/sg_functions.php";

global $templates, $mybb;

// $enable_hentai = $mybb->get_input('enable_hentai'); 
$user_uid = $mybb->user['uid'];
$user_username = $mybb->user['username'];
$fid = null;

$query_fid = $db->query("
    SELECT * FROM mybb_sg_sg_hentai WHERE uid='$user_uid'
");

while ($q = $db->fetch_array($query_fid)) {
    $fid = $q;
}

if ($fid && $user_uid != 0) {
    $enable_hentai = null;

    if ($fid['enable_hentai'] == '1' || $fid['enable_hentai'] == '2') {
        $enable_hentai = '0';
    } else {
        $enable_hentai = '1';
    }

    $db->query(" 
        UPDATE `mybb_sg_sg_hentai` SET `enable_hentai`='$enable_hentai' WHERE `uid`='$user_uid';
    ");
} else if ($user_uid != 0) {

    $db->query(" 
        INSERT INTO `mybb_sg_sg_hentai`(`uid`, `enable_hentai`, `username`) VALUES ('$user_uid','1','$user_username');
    ");
}

echo "Pervertid@.";
echo "<script>window.location.replace('/');</script>";