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
define('THIS_SCRIPT', 'entrenamientos.php');
require_once "./../global.php";
require "./../inc/config.php";

global $templates, $mybb;

$uid = $mybb->user['uid'];
$f_var = null;

$modo_vista_input = $mybb->get_input('modo_vista'); 
$modo_vista = ($modo_vista_input && ($mybb->user['uid'] == '196') || $mybb->user['uid'] == $modo_vista_input);
if ($modo_vista) {
    $uid = $modo_vista_input;
}

$tecnica_id = $_POST["tid"];
$tid_en_curso = $_POST["tid_en_curso"];
$tid_completo = $_POST["tid_completo"];
$mision_en_curso = false;
$entrenamiento_en_curso_antibug = false;
$reload_js = "<script>window.location.href = window.location.href;</script>";
// $reload_js = "";

$query_user_mision = $db->query("
    SELECT * FROM mybb_sg_sg_misiones_usuarios WHERE uid='$uid'
");
while ($m = $db->fetch_array($query_user_mision)) {
    $mision_en_curso = true;
}
if ($mision_en_curso) {
    $modo_vista = true;
    echo "<script>alert('Estás haciendo una misión en este momento así que no podrás entrenar técnicas nuevas.');</script>";
}

if ($tid_completo) {
    $tiempo_iniciado = '';
    $tiempo_finaliza = '';
    $has_entreno = false;
    $query_user_entreno = $db->query("SELECT * FROM mybb_sg_sg_entrenamientos_usuarios WHERE uid='$uid'");
    
    while ($q = $db->fetch_array($query_user_entreno)) {
        $tiempo_iniciado = $q['tiempo_iniciado'];
        $tiempo_finaliza = $q['tiempo_finaliza'];
        $has_entreno = true;
    }

    if ($has_entreno) {
        $db->query("
            DELETE FROM mybb_sg_sg_entrenamientos_usuarios WHERE uid='$uid'
        ");

        $query_tecnica = $db->query("
            SELECT * FROM mybb_sg_sg_tecnicas WHERE tid='$tid_completo'
        ");
        while ($t = $db->fetch_array($query_tecnica)) {
            $t_var = $t;
            eval('$tecnica = $t_var;');
        }

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

        $coste_pr = 0;
        $recompensa_ph = 0;

        $suma_stats_var = $f_var['str'] + $f_var['res'] + $f_var['spd'] + $f_var['agi'] + $f_var['dex'] + $f_var['pres'] + $f_var['inte'] + $f_var['ctrl'];

        if ($t_var) {
            $rango = $t_var['rango'];
            if ($rango == 'E') {
                $coste_pr = 0;
                $recompensa_ph = 5;
            } else if ($rango == 'D') {
                if ($suma_stats_var < 160 && $suma_stats_var >= 80) {
                    $recompensa_ph = 3;
                } else {
                    $recompensa_ph = 2;
                }

                $coste_pr = 5;
            } else if ($rango == 'C') {
                $coste_pr = 8;
                $recompensa_ph = 2;            
            } else if ($rango == 'B') {
                $coste_pr = 12;
                $recompensa_ph = 3;
            } else if ($rango == 'A') {
                $coste_pr = 16;
                $recompensa_ph = 3;
            } else if ($rango == 'A+') {
                $coste_pr = 20;
                $recompensa_ph = 4;
            } else if ($rango == 'S') {
                $coste_pr = 25;
                $recompensa_ph = 5;
            } else if ($rango == 'S+') {
                $coste_pr = 30;
                $recompensa_ph = 7;
            } 
        }

        $nombre = $f_var['nombre'];
        $old_ph = $f_var['puntos_habilidad'];
        $old_pr = $u_var['newpoints'];
        $new_ph = intval($old_ph) + $recompensa_ph;
        $new_pr = floatval($old_pr) - $coste_pr;

        if ($tid_completo == 'ESPENIN' || $tid_completo == 'ESPETAI' || $tid_completo == 'ESPEGEN') {
            $ficha_espe = str_replace("ESPE","",$tid_completo);
            $db->query(" 
                UPDATE `mybb_sg_sg_fichas` SET `espe`='$ficha_espe' WHERE `fid`='$uid';
            ");
        }

        if ($tid_completo == 'ESTIEST' || $tid_completo == 'ESTIFUE' || $tid_completo == 'ESTIFAN' ||
            $tid_completo == 'ESTIVER' || $tid_completo == 'ESTIFOR' || $tid_completo == 'ESTINAT') {
            $ficha_estilo = str_replace("ESTI","",$tid_completo);
            $db->query(" 
                UPDATE `mybb_sg_sg_fichas` SET `espe_estilo`='$ficha_estilo' WHERE `fid`='$uid';
            ");
        }

        $db->query(" 
            UPDATE `mybb_sg_sg_fichas` SET `puntos_habilidad`='$new_ph' WHERE `fid`='$uid';
        ");
        $db->query(" 
            UPDATE `mybb_sg_users` SET `newpoints`='$new_pr' WHERE `uid`='$uid';
        ");
        $db->query(" 
            INSERT INTO `mybb_sg_sg_tec_aprendidas` (`tid`, `uid`) VALUES 
            ('$tid_completo', '$uid');
        ");
        $db->query(" 
            INSERT INTO `mybb_sg_sg_audit_entrenamientos` (`fid`, `nombre`, `tid`, `puntos_habilidad`, `pr`, `tiempo_iniciado`, `tiempo_finaliza`) VALUES 
            ('$uid', '$nombre', '$tid_completo', '$old_ph->$new_ph', '$old_pr->$new_pr', '$tiempo_iniciado', '$tiempo_finaliza');
        ");

        $log = "Entrenamiento para técnica ID: $tid_completo finalizado \nPH: $old_ph->$new_ph \nPR: $old_pr->$new_pr\n";
        
    }

    eval('$log_var = $log;');
    eval('$reload_script = $reload_js;');
}

// cancelar mision
if ($tid_en_curso == 'cancel') {
    $db->query("
        DELETE FROM mybb_sg_sg_entrenamientos_usuarios WHERE uid='$uid'
    ");
    eval('$reload_script = $reload_js;');
}

// post para entrenar técnica
if ($tecnica_id) {
    $query_ficha = $db->query("
        SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
    ");
    while ($f = $db->fetch_array($query_ficha)) {
        $f_var = $f;
    }
    $nombre = $f_var['nombre'];
    $query_tecnicas_post = $db->query("
        SELECT * FROM mybb_sg_sg_tecnicas WHERE tid='$tecnica_id'
    ");

    $nuevo_tiempo = 0;
    while ($t = $db->fetch_array($query_tecnicas_post)) {

        $rango = $t['rango'];
        $tiempo_de_tecnica = 0;

        if ($rango == 'E') {
            $tiempo_de_tecnica = 300;
        } else if ($rango == 'D') {
            $tiempo_de_tecnica = 12 * 3600;
        } else if ($rango == 'C') {
            $tiempo_de_tecnica = 24 * 3600;
        } else if ($rango == 'B') {
            $tiempo_de_tecnica = 48 * 3600;
        } else if ($rango == 'A') {
            $tiempo_de_tecnica = 60 * 3600;
        } else if ($rango == 'A+') {
            $tiempo_de_tecnica = 84 * 3600;
        } else if ($rango == 'S') {
            $tiempo_de_tecnica = 120 * 3600;
        } else if ($rango == 'S+') {
            $tiempo_de_tecnica = 168 * 3600;
        } else {   
            $tiempo_de_tecnica = 600 * 3600;
        }

        // if ($tecnica_id == 'ESPENIN' || $tecnica_id == 'ESPETAI' || $tecnica_id == 'ESPEGEN') {
        //     $ficha_espe = str_replace("ESPE","",$tecnica_id);
        //     $db->query(" 
        //         UPDATE `mybb_sg_sg_fichas` SET `espe`='$ficha_espe' WHERE `fid`='$uid';
        //     ");
        // }

        // if ($tecnica_id == 'ESTIEST' || $tecnica_id == 'ESTIFUE' || $tecnica_id == 'ESTIFAN' ||
        //     $tecnica_id == 'ESTIVER' || $tecnica_id == 'ESTIFOR' || $tecnica_id == 'ESTINAT') {
        //     $ficha_estilo = str_replace("ESTI","",$tecnica_id);
        //     $db->query(" 
        //         UPDATE `mybb_sg_sg_fichas` SET `espe_estilo`='$ficha_estilo' WHERE `fid`='$uid';
        //     ");
        // }

        $tiempo_iniciado = time();
        $tiempo_finaliza = $tiempo_iniciado + $tiempo_de_tecnica;
        if ($tiempo_finaliza != 0) {
            $db->query(" 
                INSERT INTO `mybb_sg_sg_entrenamientos_usuarios` (`tid`,`nombre`,`uid`,`tiempo_iniciado`,`tiempo_finaliza`,`duracion`) VALUES ('$tecnica_id','$nombre','$uid','$tiempo_iniciado','$tiempo_finaliza','$tiempo_de_tecnica');
            ");
        }
    }
    eval('$reload_script = $reload_js;');
}

$ficha_existe = false;
$moderated = false;


$query_ficha = $db->query("
    SELECT * FROM mybb_sg_sg_fichas as f 
    INNER JOIN mybb_sg_users as u ON f.fid = u.uid 
    INNER JOIN mybb_sg_sg_clanes as c ON c.cid = f.clan
    WHERE f.fid='$uid'
");

while ($f = $db->fetch_array($query_ficha)) {
    $moderated = $f['moderated'] != 'no_moderacion';
    $ficha_existe = true;
    $f_var = $f;
    $pr = $f['newpoints'];
    eval('$ficha = $f;');
}

if ($ficha_existe == true && $moderated == true) {

    $query_user_entrenamientos = $db->query("
        SELECT * FROM mybb_sg_sg_entrenamientos_usuarios WHERE uid='$uid'
    ");
    $entrenamiento_en_curso = false;
    $entrenamiento_completo = false;
    $tid = 0;
    $tiempo = 0;
    $t_var = null;

    while ($e = $db->fetch_array($query_user_entrenamientos)) {
        $efecto = 1;
        $time_now = time();
        $codigo_usuario = get_obj_from_query($db->query("
            SELECT * FROM mybb_sg_sg_codigos_usuarios WHERE uid='$uid' AND expiracion > $time_now
        "));
        if ($codigo_usuario) {
            $codigo_admin = select_one_query_with_id('mybb_sg_sg_codigos_admin', 'codigo', $codigo_usuario['codigo']);

            $categoria = $codigo_admin['categoria'];

            if ($categoria == 'entrenamientoX2') {
                $efecto = 2;
            } else if ($categoria == 'entrenamientoX3') {
                $efecto = 3;
            } else if ($categoria == 'entrenamientoX1.5') {
                $efecto = 1.5;
            } else if ($categoria == 'entrenamientoX1.02') {
                $efecto = 1.02;
            }
        }

        $entrenamiento_duracion = intval($e['duracion']);
        $extra_time = $entrenamiento_duracion - ($entrenamiento_duracion * (1 / $efecto)); 
        $tiempo_var = intval($e['tiempo_finaliza'] - $extra_time) * 1000;
        $tid = $e['tid'];

        if (time() > (intval($e['tiempo_finaliza']) - $extra_time)) {
            $entrenamiento_completo = true;
        } else {
            $entrenamiento_en_curso = true;
        }
    }    

    if ($entrenamiento_completo || $entrenamiento_en_curso) {
        $query_tecnica = $db->query("
            SELECT * FROM mybb_sg_sg_tecnicas WHERE tid='$tid'
        ");

        while ($t = $db->fetch_array($query_tecnica)) {
            $t_var = $t;
            eval('$tecnica = $t_var;');
        }
    }

    if ($entrenamiento_en_curso && !$modo_vista) {

        eval('$tiempo = $tiempo_var;');
        eval("\$page = \"".$templates->get("sg_entrenamiento_en_curso")."\";");
    } else if ($entrenamiento_completo) {

        $coste_pr = 0;
        $recompensa_ph = 0;
        $suma_stats_var = $f_var['str'] + $f_var['res'] + $f_var['spd'] + $f_var['agi'] + $f_var['dex'] + $f_var['pres'] + $f_var['inte'] + $f_var['ctrl'];

        if ($t_var) {
            $rango = $t_var['rango'];
            if ($rango == 'E') {
                $coste_pr = 0;
                $recompensa_ph = 5;
            } else if ($rango == 'D') {
                if ($suma_stats_var < 160 && $suma_stats_var >= 80) {
                    $recompensa_ph = 3;
                } else {
                    $recompensa_ph = 2;
                }
                $coste_pr = 5;
                
            } else if ($rango == 'C') {
                $coste_pr = 8;
                $recompensa_ph = 2;            
            } else if ($rango == 'B') {
                $coste_pr = 12;
                $recompensa_ph = 3;
            } else if ($rango == 'A') {
                $coste_pr = 16;
                $recompensa_ph = 3;
            } else if ($rango == 'A+') {
                $coste_pr = 20;
                $recompensa_ph = 4;
            } else if ($rango == 'S') {
                $coste_pr = 25;
                $recompensa_ph = 5;
            } else if ($rango == 'S+') {
                $coste_pr = 30;
                $recompensa_ph = 7;
            } 
        }

        eval('$pr_coste = $coste_pr;');
        eval('$ph_recompensa = $recompensa_ph;');
        eval("\$page = \"".$templates->get("sg_entrenamiento_completo")."\";");
    } else {

        $query_user_entrenamiento = $db->query("
            SELECT * FROM mybb_sg_sg_entrenamientos_usuarios WHERE uid='$uid'
        ");

        $suma_stats_var = $f_var['str'] + $f_var['res'] + $f_var['spd'] + $f_var['agi'] + $f_var['dex'] + $f_var['pres'] + $f_var['inte'] + $f_var['ctrl'];
        $max_rango = 'A';
        $rango_filter = '';

        if ($suma_stats_var >= 480 && intval($f_var['limite_nivel']) > 12) {
            $rango_filter .= "OR rango='S' ";
        }

        if ($suma_stats_var >= 400 && intval($f_var['limite_nivel']) > 10) {
            $rango_filter .= "OR rango='A+' ";
        }

        if ($suma_stats_var >= 320) {
            $rango_filter .= "OR rango='A' ";
        }

        if ($suma_stats_var >= 240) {
            $rango_filter .= "OR rango='B' ";
        }

        if ($suma_stats_var >= 160) {
            $rango_filter .= "OR rango='C' ";
        }

        if ($suma_stats_var >= 80) {
            $rango_filter .= "OR rango='D' ";
        }
            
        $nombrePersonaje = $f_var['nombre'];
        $nombreClan = $f_var['nombreClan'];
        $cleanNombreClan = strtolower(join("_", explode(" ", $nombreClan)));

        $espe = $f_var['espe'];
        $espe_estilo = $f_var['espe_estilo'];
        $elemento1 = $f_var['elemento1'];
        $elemento2 = $f_var['elemento2'];
        $elemento3 = $f_var['elemento3'];
        $elemento4 = $f_var['elemento4'];
        $elemento5 = $f_var['elemento5'];
        $maestria1 = $f_var['maestria'];
        $maestria2 = $f_var['maestria_secundaria'];
        $pasiva_slot = $f_var['pasiva_slot'];

        $is_ninshu = ($maestria1 == 'NIN' || $maestria2 == 'NIN');
        $is_senjutsu = ($maestria1 == 'SEN' || $maestria2 == 'SEN');
        $should_train_espe = $espe == '' && $suma_stats_var >= 160;
        $should_train_estilo = $espe_estilo == '' && $suma_stats_var >= 160;

        $renunciar_elemento = $f_var['renunciar_elemento'];

        $invo1 = $f_var['invocacion'];
        $invo2 = $f_var['invocacion_secundaria'];

        $maestria1_query = '';
        $maestria2_query = '';
        $invo1_query = '';
        $invo2_query = '';
        $pasiva_slot_query = '';

        $maestria1_nombre = '';
        $maestria2_nombre = '';
        $invo1_nombre = '';
        $invo2_nombre = '';
        $pasiva_slot_nombre = '';
        $espe_query = '';
        $estilo_query = '';

        if ($should_train_espe) {
            $espe_query = "OR (tid = 'ESPENIN' OR tid = 'ESPETAI' OR tid = 'ESPEGEN')";
        }

        if ($should_train_estilo && $espe == 'TAI') {
            $estilo_query = "OR (tid = 'ESTIEST' OR tid = 'ESTIFUE')";
        } else if ($should_train_estilo && $espe == 'GEN') {
            $estilo_query = "OR (tid = 'ESTIFAN' OR tid = 'ESTIVER')";
        } else if ($should_train_estilo && $espe == 'NIN') {
            $estilo_query = "OR (tid = 'ESTIFOR' OR tid = 'ESTINAT')";
        }

        if ($maestria1) {
            
            switch ($maestria1) {
                case 'NIN':
                    $maestria1_nombre = "ninshu";
                    break;
                case 'BOU':
                    $maestria1_nombre = "bōjutsu";
                    break;
                case 'SHU':
                    $maestria1_nombre = "shurikenjutsu";
                    break;
                case 'GOT':
                    $maestria1_nombre = "gotai";
                    break;
                case 'HAC':
                    $maestria1_nombre = "hachimon";
                    break;
                case 'TAN':
                    $maestria1_nombre = "tansakujutsu";
                    break;
                case 'SEN':
                    $maestria1_nombre = "senjutsu";
                    break;
                case 'FUI':
                    $maestria1_nombre = "fūinjutsu";
                    break;
                case 'KEK':
                    $maestria1_nombre = "kekkaijutsu";
                    break;
                case 'IRY':
                    $maestria1_nombre = "iryou ninjutsu";
                    break;
                case 'ANK':
                    $maestria1_nombre = "ankoku ijutsu";
                    break;
                case 'YIN':
                    $maestria1_nombre = "yin";
                    break;
                case 'SHO':
                    $maestria1_nombre = "shoten";
                    break;
                case 'KEN':
                    $maestria1_nombre = "kenjutsu";
                    break;
            }

            if ($maestria1_nombre) {
                $maestria1_query = "OR (tipo='$maestria1_nombre' AND aldea='maestrias')";
            }
        }

        if ($maestria2) {
            switch ($maestria2) {
                case 'NIN':
                    $maestria2_nombre = "ninshu";
                    break;
                case 'BOU':
                    $maestria2_nombre = "bōjutsu";
                    break;
                case 'SHU':
                    $maestria2_nombre = "shurikenjutsu";
                    break;
                case 'GOT':
                    $maestria2_nombre = "gotai";
                    break;
                case 'HAC':
                    $maestria2_nombre = "hachimon";
                    break;
                case 'TAN':
                    $maestria2_nombre = "tansakujutsu";
                    break;
                case 'SEN':
                    $maestria2_nombre = "senjutsu";
                    break;
                case 'FUI':
                    $maestria2_nombre = "fūinjutsu";
                    break;
                case 'KEK':
                    $maestria2_nombre = "kekkaijutsu";
                    break;
                case 'IRY':
                    $maestria2_nombre = "iryou ninjutsu";
                    break;
                case 'ANK':
                    $maestria2_nombre = "ankoku ijutsu";
                    break;
                case 'YIN':
                    $maestria2_nombre = "yin";
                    break;
                case 'SHO':
                    $maestria2_nombre = "shoten";
                    break;
                case 'KEN':
                    $maestria2_nombre = "kenjutsu";
                    break;
            }

            if ($maestria2_nombre) {
                $maestria2_query = "OR (tipo='$maestria2_nombre' AND aldea='maestrias')";
            }
        }

        if ($invo1) {
            switch ($invo1) {
                case 'GAM':
                    $invo1_nombre = 'gama';
                    break;
                case 'HEB':
                    $invo1_nombre = 'serpientes';
                    break;
                case 'NAM':
                    $invo1_nombre = 'babosas';
                    break;
                case 'NEK':
                    $invo1_nombre = 'gatos';
                    break;
                case 'KUM':
                    $invo1_nombre = 'arañas';
                    break;
                case 'KOM':
                    $invo1_nombre = 'murcielagos';
                    break;
                case 'OKA':
                    $invo1_nombre = 'lobos';
                    break;
                case 'TORI':
                    $invo1_nombre = 'aves';
                    break;
                case 'KMA':
                    $invo1_nombre = 'osos';
                    break;
                case 'SAN':
                    $invo1_nombre = 'salamandras';
                    break;
                case 'KAM':
                    $invo1_nombre = 'tortugas';
                    break;
                case 'SAR':
                    $invo1_nombre = 'monos';
                    break;
            }
            if ($invo1_nombre) {
                $invo1_query = "OR (tipo='$invo1_nombre' AND aldea='invo')";
            }
        }

        if ($invo2) {
            switch ($invo2) {
                case 'GAM':
                    $invo2_nombre = 'gama';
                    break;
                case 'HEB':
                    $invo2_nombre = 'serpientes';
                    break;
                case 'NAM':
                    $invo2_nombre = 'babosas';
                    break;
                case 'NEK':
                    $invo2_nombre = 'gatos';
                    break;
                case 'KUM':
                    $invo2_nombre = 'arañas';
                    break;
                case 'KOM':
                    $invo2_nombre = 'murcielagos';
                    break;
                case 'OKA':
                    $invo2_nombre = 'lobos';
                    break;
                case 'TORI':
                    $invo2_nombre = 'aves';
                    break;
                case 'KUM':
                    $invo2_nombre = 'osos';
                    break;
                case 'SAN':
                    $invo2_nombre = 'salamandras';
                    break;
                case 'KAM':
                    $invo2_nombre = 'tortugas';
                    break;
                case 'SAR':
                    $invo2_nombre = 'monos';
                    break;
            }
            if ($invo2_nombre) {
                $invo2_query = "OR (tipo='$invo2_nombre' AND aldea='invo')";
            }
        }

        if ($pasiva_slot) {
            
            switch ($pasiva_slot) {
                case 'JAS':
                    $pasiva_slot_nombre = "jashin";
                    break;
                case 'GEM':
                    $pasiva_slot_nombre = "Gemelo Parasito";
                    break;
                case 'PSE':
                    $pasiva_slot_nombre = "Pseudo Jinchuuriki";
                    break;
                case 'MAL':
                    $pasiva_slot_nombre = "Sello Maldito";
                    break;
                case 'JIO':
                    $pasiva_slot_nombre = "Jiongu";
                    break;
            }

            if ($pasiva_slot_nombre) {
                $pasiva_slot_query = "OR (tipo='$pasiva_slot_nombre' AND aldea='general')";
            }
        }

        if ($is_ninshu) {
            $aprender_elemento3 = "";
            
            if ($renunciar_elemento) {
                $aprender_elemento2 = "AND rango != 'S' AND rango != 'S+'";
            } else {
                $aprender_elemento2 = "AND rango != 'A+' AND rango != 'S' AND rango != 'S+'";
            }
        } else {
            if ($renunciar_elemento) {
                $aprender_elemento2 = "AND rango != 'S' AND rango != 'S+'";
            } else {
                $aprender_elemento2 = "AND rango != 'A+' AND rango != 'S' AND rango != 'S+'";
            }
            $aprender_elemento3 = "AND rango != 'A' AND rango != 'A+' AND rango != 'S' AND rango != 'S+'";
        }

        $aprender_elemento4 = "AND rango != 'A' AND rango != 'A+' AND rango != 'S' AND rango != 'S+'";
        $aprender_elemento5 = "AND rango != 'A' AND rango != 'A+' AND rango != 'S' AND rango != 'S+'";

        $el1_general = "OR (tipo='$elemento1' AND aldea='general')";
        $el2_general = $elemento2 ? "OR (tipo='$elemento2' AND aldea='general' $aprender_elemento2)" : "";
        $el3_general = $elemento3 ? "OR (tipo='$elemento3' AND aldea='general' $aprender_elemento3)" : "";
        $el4_general = $elemento4 ? "OR (tipo='$elemento4' AND aldea='general' $aprender_elemento4)" : "";
        $el5_general = $elemento5 ? "OR (tipo='$elemento5' AND aldea='general' $aprender_elemento5)" : "";

        $espe_nin = $espe == "NIN" ? "" : "AND rango != 'S+' AND rango != 'S' AND rango != 'A+'";
        $espe_tai = $espe == "TAI" ? "" : "AND rango != 'S+' AND rango != 'S' AND rango != 'A+'";
        $espe_gen = $espe == "GEN" ? "" : "AND rango != 'S+' AND rango != 'S' AND rango != 'A+'";

        $villa_num = $f_var['villa'];
        $villa_nombre = '';
        $villa_tecs = '';
        if ($villa_num == '1') {
            $villa_nombre = 'konoha';
        } else if ($villa_num == '3') {
            $villa_nombre = 'kiri';
        } else if ($villa_num == '4') {
            $villa_nombre = 'iwa';
        } else if ($villa_num == '5') {
            $villa_nombre = 'kumo';
        } 

        if ($villa_nombre) {
            $el1_villa = "OR (tipo='$elemento1' AND aldea='$villa_nombre')";
            $el2_villa = $elemento2 ? "OR (tipo='$elemento2' AND aldea='$villa_nombre' $aprender_elemento2)" : "";
            $el3_villa = $elemento3 ? "OR (tipo='$elemento3' AND aldea='$villa_nombre' $aprender_elemento3)" : "";
            $el4_villa = $elemento4 ? "OR (tipo='$elemento4' AND aldea='$villa_nombre' $aprender_elemento4)" : "";
            $el5_villa = $elemento5 ? "OR (tipo='$elemento5' AND aldea='$villa_nombre' $aprender_elemento5)" : "";

            $villa_tecs = "
                OR (tipo='ninjutsu' AND aldea='$villa_nombre' $espe_nin)
                OR (tipo='genjutsu' AND aldea='$villa_nombre' $espe_gen)
                OR (tipo='taijutsu' AND aldea='$villa_nombre' $espe_tai)
                $el1_villa
                $el2_villa
                $el3_villa
                $el4_villa
                $el5_villa
            ";
        }

        $query_tecs = $db->query("
            SELECT * FROM mybb_sg_sg_tecnicas as t1 
            WHERE ((tipo='ninjutsu' AND aldea='general' $espe_nin) 
            OR (tipo='genjutsu' AND aldea='general' $espe_gen) 
            OR (tipo='taijutsu' AND aldea='general' $espe_tai)
            $el1_general
            $el2_general
            $el3_general
            $el4_general
            $el5_general
            $villa_tecs
            $maestria1_query
            $maestria2_query
            $invo1_query
            $invo2_query
            $espe_query
            $estilo_query
            OR t1.tid IN (SELECT DISTINCT t3.tid from mybb_sg_sg_tec_para_aprender as t3 WHERE t3.uid='$uid')
            OR (tipo='$nombreClan'))
            AND ((rango='E' $rango_filter) AND exclusiva = 0)
            AND t1.tid NOT IN (SELECT DISTINCT t2.tid from mybb_sg_sg_tec_aprendidas as t2 WHERE t2.uid='$uid')
            ORDER BY t1.tid ASC
        ");
        
        $tecs = array();
        while ($tec = $db->fetch_array($query_tecs)) {
            $clean_tipo = join("_", explode(" ", $tec['tipo']));

            $key = strtolower($clean_tipo) . '_' . strtolower($tec['aldea']);
        
            if (!$tecs[$key]) {
                $tecs[$key] = array();
            }
            array_push($tecs[$key], $tec);
        }
        $tecs_json = json_encode($tecs);
        // create variables
        eval('$tecs = "'.addslashes($tecs_json).'";');
        
        $spoiler_onclick_var = "if(parentNode.getElementsByTagName('div')[1].style.display == 'block'){ parentNode.getElementsByTagName('div')[1].style.display = 'none'; } else { parentNode.getElementsByTagName('div')[1].style.display = 'block'; }";
        eval('$spoiler_onclick = "'.$spoiler_onclick_var.'";');
        eval("\$page = \"".$templates->get("sg_entrenamientos")."\";");
    }

    output_page($page);
} else {
    eval("\$page = \"".$templates->get("sg_ficha_no_existe")."\";");
    output_page($page);
}




