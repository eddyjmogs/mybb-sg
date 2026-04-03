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
define('THIS_SCRIPT', 'recompensa_diaria.php');
require_once "./../global.php";
require "./../inc/config.php";

global $templates, $mybb;

$uid = $mybb->user['uid'];
$recompensa_accepted = $_POST["rec_ready"];
$reload_js = "<script>window.location.href = window.location.href;</script>";

$recompensas_reclamadas = 0;
$recompensas_racha_maxima = 0;

$total_recompensas_query = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_recompensas` WHERE uid='$uid'");
while ($q = $db->fetch_array($total_recompensas_query)) { $recompensas_reclamadas = $q['total']; }

$recompensas_maxima_query = $db->query("
    SELECT a.id, a.tiempo_completado, a.uid, a.nombre, a.dia, a.audit
    FROM mybb_sg_sg_audit_recompensas a
    INNER JOIN (
        SELECT uid, MAX(dia) dia
        FROM mybb_sg_sg_audit_recompensas
        GROUP BY uid
    ) b ON a.uid = b.uid AND a.dia = b.dia
    WHERE a.uid=$uid
    ORDER BY dia DESC
    LIMIT 10
");

while ($q = $db->fetch_array($recompensas_maxima_query)) {
    $recompensas_racha_maxima = $q['dia'];
}

$should_delete_recompensa = false;
$ficha_existe = false;
$moderated = false;
$should_accept = true;
$two_days = 2 * 24 * 3600;
$time_now = time();
$next_two_days = time() + $two_days;
$last_two_days = time() - $two_days;
$time_to_accept = 0;
$days_count = 0;

/* CHECK IF THERE IS A RECOMPENSA THAT EXISTS */
$query_recompensa_actual = $db->query("
    SELECT * FROM mybb_sg_sg_recompensas_usuarios WHERE uid='$uid'
");

while ($q = $db->fetch_array($query_recompensa_actual)) {
    $days_count = $q['dia']; 
    // modify last two days based on last time reward was accepted
    $last_two_days = $q['tiempo'] - $two_days;
    // enough time to accept recompensa
    $should_accept = time() > $q['tiempo'];
    // too much time passed to accept recompensa
    $claimed_after_96_hours = time() > ($q['tiempo'] + $two_days);
    $claimed_after_48_hours = time() > $q['tiempo'];
    // time left to accept before accepting again (so not just right two posts after)
    $time_to_accept = $q['tiempo'] - time();
}

// recompensa was accepted after time has passed
if ($recompensa_accepted == 'true') {

    $query_ficha = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");
    while ($f = $db->fetch_array($query_ficha)) {
        $f_var = $f;
    }
    $query_usuario = $db->query("
        SELECT * FROM mybb_sg_users WHERE uid='$uid'
    ");
    while ($u = $db->fetch_array($query_usuario)) {
        $u_var = $u;
    }

    $puntos_rol = $u_var['newpoints'];
    $nombre = $f_var['nombre'];
    $ryos = $f_var['ryos'];
    $ph = $f_var['puntos_habilidad'];
    $pe = $f_var['pe'];

    $recompensa_items = '';
    $log = '';
    if ($days_count == 0) {
        $recompensa_items = '50 Ryos, 3 PR y 0.5 PE';
        $new_ryos = intval($ryos) + 50;
        $new_pr = floatval($puntos_rol) + 3.00;
        $new_pe = intval($pe) + 0.5;
        $log = "Ryos: $ryos->$new_ryos & PR: $puntos_rol->$new_pr";
        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET pe='$new_pe', ryos='$new_ryos' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET newpoints='$new_pr' WHERE `uid`='$uid';
        "); 
    } else if ($days_count == 1) {
        $recompensa_items = '100 Ryos, 5 PR y 0.5 PE';
        $new_ryos = intval($ryos) + 100;
        $new_pr = floatval($puntos_rol) + 5.00;
        $new_pe = intval($pe) + 0.5;
        $log = "Ryos: $ryos->$new_ryos & PR: $puntos_rol->$new_pr";
        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET pe='$new_pe', ryos='$new_ryos' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET newpoints='$new_pr' WHERE `uid`='$uid';
        "); 
    } else if ($days_count == 2) {
        $recompensa_items = '100 Ryos, 5 PR y 1 PE';
        $new_ryos = intval($ryos) + 150;
        $new_pr = floatval($puntos_rol) + 5.00;
        $new_pe = intval($pe) + 1;
        $log = "Ryos: $ryos->$new_ryos & PR: $puntos_rol->$new_pr";
        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET pe='$new_pe', ryos='$new_ryos' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET newpoints='$new_pr' WHERE `uid`='$uid';
        "); 
    } else if ($days_count == 3 || ($days_count >= 4 && $days_count % 2 == 1)) {
        $recompensa_items = '100 Ryos, 5 PR, 1 PE y 1 PH';
        $new_ryos = intval($ryos) + 200;
        $new_pr = floatval($puntos_rol) + 5.00;
        $new_pe = intval($pe) + 1;
        $new_ph = intval($ph) + 1;
        $log = "PE: $pe->$new_pe & PH: $ph->$new_ph & Ryos: $ryos->$new_ryos & PR: $puntos_rol->$new_pr";
        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET pe='$new_pe', puntos_habilidad='$new_ph', ryos='$new_ryos' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET newpoints='$new_pr' WHERE `uid`='$uid';
        "); 
    } else if ($days_count >= 4 && $days_count % 2 == 0) {
        $recompensa_items = '100 Ryos, 5 PR, 1 PE y 1 PH';
        $new_ryos = intval($ryos) + 300;
        $new_pr = floatval($puntos_rol) + 5.00;
        $new_pe = intval($pe) + 2;
        $new_ph = intval($ph) + 2;
        $log = "PE: $pe->$new_pe & PH: $ph->$new_ph & Ryos: $ryos->$new_ryos & PR: $puntos_rol->$new_pr";
        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET pe='$new_pe', puntos_habilidad='$new_ph', ryos='$new_ryos' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET newpoints='$new_pr' WHERE `uid`='$uid';
        "); 
    }

    $days_count = $days_count + 1;

    $db->query("
        DELETE FROM mybb_sg_sg_recompensas_usuarios WHERE uid='$uid'
    ");

    $db->query(" 
        INSERT INTO `mybb_sg_sg_recompensas_usuarios`(`uid`, `nombre`, `dia`, `tiempo`) VALUES ($uid,'$nombre',$days_count,$next_two_days)
    ");

    $complete_log = "$nombre ($uid). Has ganado $recompensa_items en esta ronda. $log";

    $db->query(" 
        INSERT INTO `mybb_sg_sg_audit_recompensas`(`tiempo_completado`, `tiempo_nuevo`, `dia`, `uid`, `nombre`, `audit`) VALUES ($time_now, $next_two_days, $days_count, '$uid','$nombre','$complete_log')
    ");

    eval('$log_var = $complete_log;');
    eval('$reload_script = $reload_js;');
}

/* Check if ficha exists */
$query_ficha = $db->query("
    SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
");

$nombre = '';

while ($f = $db->fetch_array($query_ficha)) {
    $moderated = $f['moderated'] != 'no_moderacion';
    $ficha_existe = true;
    $nombre = $f['nombre'];
}

/* Render page */
if ($ficha_existe == true && $moderated == true) {

    $num_posts = 0;
    $time_left = '';

    // last_two_days could be last 48 hours posts for the last time that reward was accepted
    // or last 48 hours that there were posts when reward has not been accepted
    $query_posts = $db->query("
        SELECT p.dateline as post_date FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        WHERE p.dateline > $last_two_days
        AND f.parentlist LIKE '37,%'
        AND p.uid = '$uid'
        AND p.visible = 1
        ORDER BY p.dateline ASC
    ");

    $dates_arr = array();

    while ($q = $db->fetch_array($query_posts)) {
        $post_date = $q['post_date'];
        // 48 hours left after this post
        $time_left = $two_days - (time() - $q['post_date']);
        array_push($dates_arr, $time_left);
    }

    $num_posts = count($dates_arr);

    // recompensa is expired either because 96 hours after claim time is expired
    // or because after 48 hours it was claimed, there was less than two posts.
    if ($claimed_after_96_hours || ($num_posts < 2 && $claimed_after_48_hours)) {
        $db->query("
            DELETE FROM mybb_sg_sg_recompensas_usuarios WHERE uid='$uid'
        ");
        $days_count = 0;
        $last_two_days = time() - $two_days;
        $query_posts = $db->query("
            SELECT p.dateline as post_date FROM mybb_sg_posts as p 
            INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
            INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
            WHERE p.dateline > $last_two_days
            AND f.parentlist LIKE '37,%'
            AND p.uid = '$uid'
            AND p.visible = 1
            ORDER BY p.dateline ASC
        ");

        $dates_arr = array();

        while ($q = $db->fetch_array($query_posts)) {
            $post_date = $q['post_date'];
            // 48 hours left after this post
            $time_left = $two_days - (time() - $q['post_date']);
            array_push($dates_arr, $time_left);
        }

        $num_posts = count($dates_arr);
    }

    if ($num_posts >= 2 && $should_accept && $days_count > 0) {
        $time_left = $two_days + $time_to_accept;
    } else if ($num_posts >= 2 && $should_accept) {
        // if no rewards yet, time left is 48 hours before second to last post
        $time_left = $dates_arr[$num_posts - 2];
    } else if ($num_posts >= 2 && !$should_accept) {
        // reward has been claimed, and it has to wait 48 hours after it was claimed
        $time_left = $time_to_accept;
    } else if ($num_posts < 2 && $days_count > 0) {
        // time to write the two posts before it can be claimed again or it expires
        $time_left = $time_to_accept;
    } 

    $recompensa_items = '';

    if ($days_count == 0) {
        $recompensa_items = '50 Ryos, 3 PR y 0.5 PE';
    } else if ($days_count == 1) {
        $recompensa_items = '100 Ryos, 5 PR y 0.5 PE';
    } else if ($days_count == 2) {
        $recompensa_items = '100 Ryos, 5 PR y 1 PE';
    } else if ($days_count == 3 || ($days_count >= 4 && $days_count % 2 == 1)) {
        $recompensa_items = '100 Ryos, 5 PR, 1 PE y 1 PH';
    } else if ($days_count >= 4 && $days_count % 2 == 0) {
        $recompensa_items = '100 Ryos, 5 PR, 1 PE y 1 PH';
    }

    eval("\$page = \"".$templates->get("sg_recompensa")."\";");
    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}
