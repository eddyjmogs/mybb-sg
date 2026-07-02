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

global $templates, $mybb, $db;
$uid = $mybb->user['uid'];

if (!(is_mod($uid) || is_staff($uid))) {
    eval("\$page = \"".$templates->get("sin_permisos")."\";");
    output_page($page);
    exit;
}

// ── Filtros ───────────────────────────────────────────────────
$f_staff = trim($mybb->get_input('f_staff'));
$q       = trim($mybb->get_input('q'));
$page    = intval($mybb->get_input('page'));
if ($page < 1) { $page = 1; }
$perpage = 25;

$where = array();
if ($f_staff !== '') {
    $e = $db->escape_string($f_staff);
    $where[] = "staff LIKE '%$e%'";
}
if ($q !== '') {
    $e = $db->escape_string($q);
    $where[] = "(razon LIKE '%$e%' OR log LIKE '%$e%')";
}
$where_sql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

// ── Total + paginación ────────────────────────────────────────
$total = 0;
$qc = $db->query("SELECT COUNT(*) AS c FROM mybb_sg_sg_audit_consola_tec_mod $where_sql");
while ($r = $db->fetch_array($qc)) { $total = intval($r['c']); }

$pages = (int) max(1, ceil($total / $perpage));
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $perpage;

// ── Registros de la página actual ─────────────────────────────
$logs = "";
$query_log_tec = $db->query("
    SELECT * FROM mybb_sg_sg_audit_consola_tec_mod
    $where_sql
    ORDER BY id DESC
    LIMIT $offset, $perpage
");
while ($l = $db->fetch_array($query_log_tec)) {
    $id     = intval($l['id']);
    $tiempo = htmlspecialchars($l['tiempo'], ENT_QUOTES);
    $staff  = htmlspecialchars($l['staff'], ENT_QUOTES);
    $razon  = htmlspecialchars($l['razon'], ENT_QUOTES);
    $log    = htmlspecialchars($l['log'], ENT_QUOTES);
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
if ($logs === "") {
    $logs = "<div class='sg-log-empty'>No hay registros que coincidan con el filtro.</div>";
}

// ── Resumen y controles de paginación ─────────────────────────
$desde = ($total === 0) ? 0 : ($offset + 1);
$hasta = min($offset + $perpage, $total);
$resumen = "Mostrando {$desde}–{$hasta} de {$total}";

$qs = array();
if ($f_staff !== '') { $qs[] = 'f_staff=' . urlencode($f_staff); }
if ($q       !== '') { $qs[] = 'q='       . urlencode($q); }
$base_qs = implode('&', $qs);

$mk = function ($p, $label, $cls = '') use ($base_qs) {
    $qy = 'page=' . $p . ($base_qs !== '' ? '&' . $base_qs : '');
    return "<a class='sg-page {$cls}' href='?{$qy}'>{$label}</a>";
};

$pagination = '';
if ($pages > 1) {
    if ($page > 1) { $pagination .= $mk($page - 1, '‹'); }

    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1) {
        $pagination .= $mk(1, '1');
        if ($start > 2) { $pagination .= "<span class='sg-page sg-page--gap'>…</span>"; }
    }
    for ($p = $start; $p <= $end; $p++) {
        $pagination .= $mk($p, $p, $p === $page ? 'sg-page--active' : '');
    }
    if ($end < $pages) {
        if ($end < $pages - 1) { $pagination .= "<span class='sg-page sg-page--gap'>…</span>"; }
        $pagination .= $mk($pages, $pages);
    }

    if ($page < $pages) { $pagination .= $mk($page + 1, '›'); }
}

// Valores para prellenar los inputs del filtro
$f_staff_val = htmlspecialchars($f_staff, ENT_QUOTES);
$q_val       = htmlspecialchars($q, ENT_QUOTES);

eval('$logs_li = $logs;');
eval("\$page_html = \"".$templates->get("staff_log_tecnicas")."\";");
output_page($page_html);
