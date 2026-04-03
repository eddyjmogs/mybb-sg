<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'censo.php');
require_once "./../global.php";
require "./../inc/config.php";

global $templates, $mybb, $db;
$uid = $mybb->user['uid'];
$username = $mybb->user['username'];
$tiempo = 14;
$last_two_weeks = time() - (14 * 24 * 3600);
$accion = 'censo';
$dateformat = '';

if ($mybb->get_input('tiempo')) {
    $tiempo = $mybb->get_input('tiempo'); 
}

if ($mybb->get_input('accion')) {
    $accion = $mybb->get_input('accion');
}

if ($tiempo >= 0 && $tiempo <= 60) {
    $timestamp = time() - ($tiempo * 24 * 3600);
    $dateformat = date("Y-m-d H:i:s", $timestamp);
    echo "<script>console.log('$timestamp ====  $dateformat || $tiempo');</script>";
}

function queryVilla($villa) {
    global $db, $timestamp;
    return $db->query("
        SELECT villa, nombreClan, count(nombreClan) as numeroPjs FROM (SELECT DISTINCT fichas.villa, clanes.nombreClan, p.username FROM mybb_sg_posts as p 
            INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
            INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
            INNER JOIN mybb_sg_sg_fichas as fichas ON p.uid = fichas.fid 
            INNER JOIN mybb_sg_sg_clanes as clanes ON clanes.cid = fichas.clan 
            WHERE p.dateline > $timestamp
            AND fichas.villa = '$villa'                                                             
            AND f.parentlist LIKE '37,%') t
        GROUP BY nombreClan
        ORDER BY villa, nombreClan
    ");
}

function queryNumeroPosts() {
    global $db, $timestamp;
    return $db->query("
        SELECT COUNT(*) as numeroPosts FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        WHERE p.dateline > $timestamp
        AND f.parentlist LIKE '37,%'
    ");
}

function queryTop10Posts() {
    global $db, $timestamp;
    return $db->query("
        SELECT p.username, COUNT(p.username) as numPosts FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        WHERE p.dateline > $timestamp
        AND f.parentlist LIKE '37,%'
        GROUP BY p.username
        ORDER BY numPosts DESC
        LIMIT 10
    ");
}

function queryTop10PostsTotal() {
    global $db, $timestamp;
    return $db->query("
        SELECT p.username, COUNT(p.username) as numPosts FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND p.username != 'Narrador General'
        AND f.parentlist LIKE '37,%'
        GROUP BY p.username
        ORDER BY numPosts DESC
        LIMIT 10
    ");
}

function queryTop10Temas() {
    global $db, $timestamp;
    return $db->query("
        SELECT t.username, COUNT(t.username) as numThreads FROM mybb_sg_threads as t
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        WHERE t.dateline > $timestamp
        AND f.parentlist LIKE '37,%'
        GROUP BY t.username
        ORDER BY numThreads DESC
        LIMIT 10
    ");
}

function queryTop10TemasTotal() {
    global $db, $timestamp;
    return $db->query("
        SELECT t.username, COUNT(t.username) as numThreads FROM mybb_sg_threads as t
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND f.parentlist LIKE '37,%'
        AND t.username != 'Narrador General'
        GROUP BY t.username
        ORDER BY numThreads DESC
        LIMIT 10
    ");
}

function queryTop10Prs() {
    global $db, $dateformat;
    return $db->query("
        SELECT *, ROUND(SUM(puntos_rol), 2) as sumPuntosRol FROM `mybb_sg_newpoints_log`
        WHERE date > '$dateformat'
        GROUP BY username
        ORDER BY sumPuntosRol DESC
        LIMIT 10
    ");
}

function queryTop10PrsTotal() {
    global $db, $dateformat;
    return $db->query("
        SELECT *, ROUND(SUM(puntos_rol), 2) as sumPuntosRol FROM `mybb_sg_newpoints_log`
        GROUP BY username
        ORDER BY sumPuntosRol DESC
        LIMIT 10
    ");
}

function queryTopPosts() {
    global $db, $timestamp;
    return $db->query("
        SELECT p.username, COUNT(p.username) as numPosts FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        WHERE p.dateline > $timestamp
        AND f.parentlist LIKE '37,%'
        GROUP BY p.username
        ORDER BY numPosts DESC
    ");
}

function queryTopPrs() {
    global $db, $dateformat;
    return $db->query("
        SELECT *, ROUND(SUM(puntos_rol), 2) as sumPuntosRol FROM `mybb_sg_newpoints_log`
        WHERE date > '$dateformat'
        GROUP BY username
        ORDER BY sumPuntosRol DESC
    ");
}

function queryTopRecompensasActuales() {
    global $db, $timestamp;
    return $db->query("
        SELECT * FROM `mybb_sg_sg_recompensas_usuarios` 
        WHERE tiempo > '$timestamp'
        ORDER BY dia DESC
        LIMIT 10
    ");
}

function queryTopRecompensasReclamadas() {
    global $db;
    return $db->query("
        SELECT COUNT(*) as total_recompensas FROM `mybb_sg_sg_audit_recompensas`
    ");
}

function queryTopRecompensasTotal() {
    global $db;
    return $db->query("
        SELECT a.id, a.tiempo_completado, a.uid, a.nombre, a.dia, a.audit
        FROM mybb_sg_sg_audit_recompensas a
        INNER JOIN (
            SELECT uid, MAX(dia) dia
            FROM mybb_sg_sg_audit_recompensas
            GROUP BY uid
        ) b ON a.uid = b.uid AND a.dia = b.dia
        ORDER BY dia DESC
        LIMIT 10
    ");
}

$pjNombresTop10Posts = "";
$pjNombresTop10Prs = "";
$pjNombresTop10Temas = "";
$pjNombresTop10PostsTotal = "";
$pjNombresTop10PrsTotal = "";
$pjNombresTop10TemasTotal = "";
$pjNombresTopPosts = "";
$pjNombresTopPrs = "";
$topRecompensasActual = "";
$totalRecompensasReclamadas = "";
$topUsuariosRecompensas = "";
$texto_extra = "";

if ($accion == 'top-10') {
    $query_top_10_posts = queryTop10Posts();
    $query_top_10_prs = queryTop10Prs();
    $query_top_10_temas = queryTop10Temas();

    while ($q = $db->fetch_array($query_top_10_posts)) {
        $nombrePj = $q['username'];
        $numPosts = $q['numPosts'];
        $pjNombresTop10Posts .= "<h4>$nombrePj - $numPosts posts</h4>";
    }
    while ($q = $db->fetch_array($query_top_10_prs)) {
        $nombrePj = $q['username'];
        $sumPuntosRol = $q['sumPuntosRol'];
        $pjNombresTop10Prs .= "<h4>$nombrePj - $sumPuntosRol PR</h4>";
    }
    while ($q = $db->fetch_array($query_top_10_temas)) {
        $nombrePj = $q['username'];
        $numThreads = $q['numThreads'];
        $pjNombresTop10Temas .= "<h4>$nombrePj - $numThreads temas</h4>";
    }
}

if ($accion == 'top-10-historico') {
    $query_top_10_posts_total = queryTop10PostsTotal();
    $query_top_10_prs_total = queryTop10PrsTotal();
    $query_top_10_temas_total = queryTop10TemasTotal();

    while ($q = $db->fetch_array($query_top_10_posts_total)) {
        $nombrePj = $q['username'];
        $numPosts = $q['numPosts'];
        $pjNombresTop10PostsTotal .= "<h4>$nombrePj - $numPosts posts</h4>";
    }
    while ($q = $db->fetch_array($query_top_10_prs_total)) {
        $nombrePj = $q['username'];
        $sumPuntosRol = $q['sumPuntosRol'];
        $pjNombresTop10PrsTotal .= "<h4>$nombrePj - $sumPuntosRol PR</h4>";
    }
    while ($q = $db->fetch_array($query_top_10_temas_total)) {
        $nombrePj = $q['username'];
        $numThreads = $q['numThreads'];
        $pjNombresTop10TemasTotal .= "<h4>$nombrePj - $numThreads temas</h4>";
    }
}

if ($accion == 'posts') {
    $query_top_posts = queryTopPosts();
    while ($q = $db->fetch_array($query_top_posts)) {
        $nombrePj = $q['username'];
        $numPosts = $q['numPosts'];
        $pjNombresTopPosts .= "<h4>$nombrePj - $numPosts posts</h4>";
    }
}

if ($accion == 'puntos-rol') {
    $query_top_prs = queryTopPrs();
    while ($q = $db->fetch_array($query_top_prs)) {
        $nombrePj = $q['username'];
        $sumPuntosRol = $q['sumPuntosRol'];
        $pjNombresTopPrs .= "<h4>$nombrePj - $sumPuntosRol PR</h4>";
    }
}

if ($accion == 'recompensas') {
    $query_top_recompensas_actuales = queryTopRecompensasActuales();
    $query_top_recompensas_reclamadas = queryTopRecompensasReclamadas();
    $query_top_recompensas_total = queryTopRecompensasTotal();

    while ($q = $db->fetch_array($query_top_recompensas_actuales)) {
        $nombrePj = $q['nombre'];
        $dia = $q['dia'];
        $topRecompensasActual .= "<h4>$nombrePj - $dia días</h4>";
    }
    while ($q = $db->fetch_array($query_top_recompensas_reclamadas)) {
        $totalRecompensas = $q['total_recompensas'];
        $totalRecompensasReclamadas .= "<span>$totalRecompensas</span>";
    }
    while ($q = $db->fetch_array($query_top_recompensas_total)) {
        $topUsuarioUid = $q['nombre'];
        $topUsuarioDia = $q['dia'];
        $topUsuariosRecompensas .= "<h4>$topUsuarioUid - $topUsuarioDia días</h4>";
    }
}

if ($accion == 'personales') {
    $texto_extra = "<span><strong>$username, has:</strong></span><br>";

    $total_posts = $db->query("SELECT COUNT(*) as total FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND f.parentlist LIKE '37,%'
        WHERE p.uid = '$uid'");

    $total_temas = $db->query("SELECT COUNT(*) as total FROM mybb_sg_threads as t
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND f.parentlist LIKE '37,%'
        WHERE t.uid = '$uid'");

    $total_pr = $db->query("SELECT ROUND(SUM(puntos_rol), 2) as total FROM `mybb_sg_newpoints_log` WHERE uid='196'");

    // $total_horas_entrenadas = 0;
    // $total_horas_entrenadas_query = $db->query("SELECT * FROM `mybb_sg_sg_audit_entrenamientos` WHERE fid=196");
    // while ($q = $db->fetch_array($total_horas_entrenadas_query)) { 
    //     $tiempo_iniciado = intval($q['tiempo_iniciado']);
    //     $tiempo_finaliza = intval($q['tiempo_finaliza']);

    //     if ($tiempo_finaliza > $tiempo_iniciado && (($tiempo_finaliza - $tiempo_iniciado) < 864000)) {
    //         $total_horas_entrenadas += $tiempo_finaliza - $tiempo_iniciado;
    //     }
    // }

    $total_entrenos = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_entrenamientos` WHERE fid='$uid'");
    $total_misiones = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_misiones` WHERE fid='$uid'");
    $total_recompensas = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_recompensas` WHERE uid='$uid'");
    $total_hides = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_hide` WHERE uid='$uid'");

    $total_stats = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_stats` WHERE fid='$uid'");
    $total_codigos = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_codigos_usuarios` WHERE uid='$uid'");
    $total_personaje = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_thread_personaje` WHERE uid='$uid'");
    $total_peticiones = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_peticiones` WHERE uid='$uid'");


    while ($q = $db->fetch_array($total_pr)) { 
        $total = $q['total']; 
        $caracteres = strval(intval($total) * 400);
        $palabras = strval($caracteres / 5);
        $texto_extra .= "- Acumulado un total de $total puntos de rol posteando. <br>- Escrito apróximadamente $palabras palabras o $caracteres carácteres.<br>";
    }
    while ($q = $db->fetch_array($total_posts)) { $total = $q['total']; $texto_extra .= "- Escrito $total posts de rol en el foro<br>"; }
    while ($q = $db->fetch_array($total_temas)) { $total = $q['total']; $texto_extra .= "- Creado $total temas de rol en el foro <br>"; }
    while ($q = $db->fetch_array($total_entrenos)) { $total = $q['total']; $texto_extra .= "- Entrenado $total técnicas<br>"; }
    while ($q = $db->fetch_array($total_misiones)) { $total = $q['total']; $texto_extra .= "- Realizado $total misiones automáticas<br>"; }
    while ($q = $db->fetch_array($total_recompensas)) { $total = $q['total']; $texto_extra .= "- Cobrado $total recompensas diarias<br>"; }
    while ($q = $db->fetch_array($total_hides)) { $total = $q['total']; $texto_extra .= "- Hecho $total hides<br>"; }
    while ($q = $db->fetch_array($total_stats)) { $total = $q['total']; $texto_extra .= "- Actualizado $total veces las estadísticas<br>"; }
    while ($q = $db->fetch_array($total_codigos)) { $total = $q['total']; $texto_extra .= "- Agregado $total códigos promocionales<br>"; }
    while ($q = $db->fetch_array($total_personaje)) { $total = $q['total']; $texto_extra .= "- Utilizado $total veces el código de [personaje]<br>"; }
    while ($q = $db->fetch_array($total_peticiones)) { $total = $q['total']; $texto_extra .= "- Enviado $total peticiones administrativas<br>"; }
}

if ($accion == 'extras') {
    $texto_extra = '<span><strong>Los usuarios han:</strong></span><br>';

    $total_posts = $db->query("SELECT COUNT(*) as total FROM mybb_sg_posts as p 
        INNER JOIN mybb_sg_threads as t ON p.tid = t.tid 
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND f.parentlist LIKE '37,%'");

    $total_temas = $db->query("SELECT COUNT(*) as total FROM mybb_sg_threads as t
        INNER JOIN mybb_sg_forums as f ON t.fid = f.fid 
        AND f.parentlist LIKE '37,%'");

    $total_pr = $db->query("SELECT ROUND(SUM(puntos_rol), 2) as total FROM `mybb_sg_newpoints_log`");

    $total_entrenos = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_entrenamientos`");
    $total_misiones = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_misiones`");
    $total_recompensas = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_recompensas`");
    $total_hides = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_hide`");
    
    $total_entrenos_actual = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_entrenamientos_usuarios` WHERE tiempo_finaliza > $last_two_weeks");
    $total_misiones_actual = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_misiones_usuarios` WHERE tiempo_finaliza > $last_two_weeks");
    
    $total_stats = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_audit_stats`");
    $total_fichas = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_fichas`");
    $total_codigos = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_codigos_usuarios`");
    $total_personaje = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_thread_personaje`");
    $total_peticiones = $db->query("SELECT COUNT(*) as total FROM `mybb_sg_sg_peticiones`");

    while ($q = $db->fetch_array($total_pr)) { 
        $total = $q['total']; 
        $caracteres = strval(intval($total) * 400);
        $palabras = strval($caracteres / 5);
        $texto_extra .= "- Acumulado un total de $total puntos de rol posteando. <br>- Escrito apróximadamente $palabras palabras o $caracteres carácteres.<br>";
    }
    while ($q = $db->fetch_array($total_posts)) { $total = $q['total']; $texto_extra .= "- Escrito $total posts de rol en el foro<br>"; }
    while ($q = $db->fetch_array($total_temas)) { $total = $q['total']; $texto_extra .= "- Creado $total temas de rol en el foro <br>"; }
    while ($q = $db->fetch_array($total_entrenos)) { $total = $q['total']; $texto_extra .= "- Entrenado $total técnicas<br>"; }
    while ($q = $db->fetch_array($total_misiones)) { $total = $q['total']; $texto_extra .= "- Realizado $total misiones automáticas<br>"; }
    while ($q = $db->fetch_array($total_recompensas)) { $total = $q['total']; $texto_extra .= "- Cobrado $total recompensas diarias<br>"; }
    while ($q = $db->fetch_array($total_hides)) { $total = $q['total']; $texto_extra .= "- Hecho $total hides<br>"; }
    while ($q = $db->fetch_array($total_stats)) { $total = $q['total']; $texto_extra .= "- Actualizado $total veces las estadísticas<br>"; }
    while ($q = $db->fetch_array($total_fichas)) { $total = $q['total']; $texto_extra .= "- Creado $total fichas<br>"; }
    while ($q = $db->fetch_array($total_codigos)) { $total = $q['total']; $texto_extra .= "- Agregado $total códigos promocionales<br>"; }
    while ($q = $db->fetch_array($total_personaje)) { $total = $q['total']; $texto_extra .= "- Utilizado $total veces el código de [personaje]<br>"; }
    while ($q = $db->fetch_array($total_peticiones)) { $total = $q['total']; $texto_extra .= "- Enviado $total peticiones administrativas<br>"; }

    $texto_extra .= '<br><span><strong>Actualmente los usuarios están:</strong></span><br>';
    while ($q = $db->fetch_array($total_entrenos_actual)) { $total = $q['total']; $texto_extra .= "- Entrenando $total técnicas<br>"; }
    while ($q = $db->fetch_array($total_misiones_actual)) { $total = $q['total']; $texto_extra .= "- Realizando $total misiones<br>"; }    
}

$clanesKonoha = 0;
$clanesKonohaStr = "";
$clanesKiri = 0;
$clanesKiriStr = "";
$clanesIwa = 0;
$clanesIwaStr = "";
$clanesKumo = 0;
$clanesKumoStr = "";
$clanesSinAldea = 0;
$clanesSinAldeaStr = "";
$clanesRenegados = 0;
$clanesRenegadosStr = "";
$numeroPosts = 0;
$totalPjs = 0;

$uchihaCount = 0;
$sinClanCount = 0;

if ($accion == 'censo') {
    $query_posts = queryNumeroPosts();
    $query_konoha = queryVilla('1');
    $query_kiri = queryVilla('3');
    $query_iwa = queryVilla('4');
    $query_kumo = queryVilla('5');
    $query_sinaldea = queryVilla('7');
    $query_renegados = queryVilla('6');

    $konohaCerrada = false;
    $kiriCerrada = false;
    $iwaCerrada = false;
    $kumoCerrada = false;
    $sinAldeaCerrada = false;
    
    while ($q = $db->fetch_array($query_posts)) {
        $numeroPosts = $q['numeroPosts'];
    }
    
    while ($q = $db->fetch_array($query_konoha)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesKonoha = $clanesKonoha + $numeroPjs;
        if ($nombreClan == 'Uchiha') {  $uchihaCount = $numeroPjs; }
        if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesKonohaStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }
    
    while ($q = $db->fetch_array($query_kiri)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesKiri = $clanesKiri + $numeroPjs;
        if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesKiriStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }
    
    while ($q = $db->fetch_array($query_iwa)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesIwa = $clanesIwa + $numeroPjs;
        if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesIwaStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }
    
    while ($q = $db->fetch_array($query_kumo)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesKumo = $clanesKumo + $numeroPjs;
        if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesKumoStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }
    
    while ($q = $db->fetch_array($query_sinaldea)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesSinAldea = $clanesSinAldea + $numeroPjs;
        if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesSinAldeaStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }
    
    while ($q = $db->fetch_array($query_renegados)) {
        $nombreClan = ucwords($q['nombreClan']);
        $numeroPjs = $q['numeroPjs'];
        $clanesRenegados = $clanesRenegados + $numeroPjs;
        // if ($nombreClan == 'Sin Clan') { $sinClanCount += $numeroPjs; }
        $clanesRenegadosStr .= "<h6>$nombreClan - $numeroPjs</h6>";
    }

    if ($tiempo == 14) {
        if ($sinClanCount >= 10) {
            $db->query("UPDATE `mybb_sg_sg_clanes` SET `abierto`=0 WHERE `cid`=1001");
        } else {
            $db->query("UPDATE `mybb_sg_sg_clanes` SET `abierto`=1 WHERE `cid`=1001");
        }

        if ($uchihaCount >= 4) {
            $db->query("UPDATE `mybb_sg_sg_clanes` SET `abierto`=0 WHERE `cid`=106");
        } else {
            $db->query("UPDATE `mybb_sg_sg_clanes` SET `abierto`=1 WHERE `cid`=106");
        }

        if ($clanesKonoha >= 12) {
            $konohaCerrada = true;
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=0 WHERE `vid`=1");
        } else {
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=1 WHERE `vid`=1");
        }

        if ($clanesKiri >= 12) {
            $kiriCerrada = true;
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=0 WHERE `vid`=3");
        } else {
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=1 WHERE `vid`=3");
        }

        if ($clanesIwa >= 12) {
            $iwaCerrada = true;
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=0 WHERE `vid`=4");
        } else {
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=1 WHERE `vid`=4");
        }

        if ($clanesKumo >= 12) {
            $kumoCerrada = true;
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=0 WHERE `vid`=5");
        } else {
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=1 WHERE `vid`=5");
        }

        if ($clanesSinAldea >= 12) {
            $sinAldeaCerrada = true;
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=0 WHERE `vid`=7");
        } else {
            $db->query("UPDATE `mybb_sg_sg_villas` SET `abierta`=1 WHERE `vid`=7");
        }
    }
    
    $totalPjs = $clanesKonoha + $clanesKiri + $clanesIwa + $clanesKumo + $clanesSinAldea + $clanesRenegados;    
}


eval("\$page = \"".$templates->get("sg_censo")."\";");
output_page($page);
