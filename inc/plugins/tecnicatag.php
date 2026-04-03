<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("parse_message_end", "tecnicatag_run");
$plugins->add_hook('datahandler_post_insert_post_end', 'dadotag_newpost');
$plugins->add_hook('datahandler_post_insert_thread_end', 'dadotag_newpost');


function tecnicatag_info()
{
global $mybb;
	return array(
		"name"				=> "Tecnica Tag",
		"description"		=> "Tag para Shinobi Gaiden.",
		"website"			=> "",
		"author"			=> "Kurosame",
		"authorsite"		=> "https://shinobigaiden.net",
		"version"			=> "1.0.0",
		"codename"			=> "tecnicatag",
		"compatibility"		=> "*",
	);
}


function tecnicatag_activate()
{
	global $db, $mybb;
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		$estilo = array(
				'name'         => 'spoiler.css',
				'tid'          => $theme['tid'],
				'attachedto'   => 'showthread.php|newthread.php|newreply.php|editpost.php|private.php|announcements.php',
				'stylesheet'   => '.spoiler {background: #f5f5f5;border: 1px solid #bbb;margin-bottom: 5px;border-radius: 5px}
.spoiler_button {background-color: #bab7b7;border-radius: 4px 4px 0 0;border: 1px solid #c2bfbf;display: block;color: #605d5d;font-family: Tahoma;font-size: 11px;font-weight: bold;padding: 10px;text-align: center;text-shadow: 1px 1px 0px #b4b3b3;margin: auto auto;cursor: pointer}
.spoiler_title {text-align: center}
.spoiler_content_title{font-weight: bold;border-bottom:1px dashed #bab7b7}
.spoiler_content {padding: 5px;height: auto;overflow:hidden;width:95%;background: #f5f5f5;word-wrap: break-word}',
			'lastmodified' => TIME_NOW
		);
		$sid = $db->insert_query('themestylesheets', $estilo);
		$db->update_query('themestylesheets', array('cachefile' => "css.php?stylesheet={$sid}"), "sid='{$sid}'", 1);
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}
	
	require MYBB_ROOT.'inc/adminfunctions_templates.php';

    find_replace_templatesets("codebuttons", '#'.preg_quote('<script type="text/javascript">
var partialmode = {$mybb->settings[\'partialmode\']},').'#siU', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/spoiler.js?ver=1804"></script>
<script type="text/javascript">
var partialmode = {$mybb->settings[\'partialmode\']},');	
    find_replace_templatesets("codebuttons", '#'.preg_quote('{$link}').'#', '{$link},spoiler');
}


function tecnicatag_deactivate()
{
	global $db;
	$db->delete_query('themestylesheets', "name='spoiler.css'");
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}
   	require MYBB_ROOT.'inc/adminfunctions_templates.php';
    find_replace_templatesets("codebuttons", '#'.preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/spoiler.js?ver=1804"></script>').'#', '',0);
    find_replace_templatesets("codebuttons", '#'.preg_quote(',spoiler').'#', '',0);
}


function tecnicatag_run(&$message)
{
	global $db, $post;

	while(preg_match('#\[cdtecnica=(.*?)\]#si',$message,$matches)) {

		$content = $matches[1];
		$values = explode(",", $content);
		$day = $values[0];
		$month = $values[1];
		$year = $values[2];
		$rango = str_replace("PLUS","+",strtoupper($values[3]));
		$aldea = $values[4];

		$cd = array();
		$cd['D'] = 10;
		$cd['C'] = 15;
		$cd['B'] = 30;
		$cd['A'] = 45;
		$cd['A+'] = 75;
		$cd['S'] = 90;
		$cd['S+'] = 120;

		$fecha = mktime(0, 0, 0, $month, $day, $year);
		$multiplier = $aldea ? 1 : 0.7;
		$new_date = ceil($cd[$rango] * $multiplier);

		$new_tm = $fecha + ($new_date * 3600 * 24);
		$new_fecha = date('d/m/Y', $new_tm);

		$texto = "
			<div style='text-align: center;border: 1px dotted #61567e;padding: 7px;'>
			Cálculo de CD para una técnica de rango $rango: <br />
			Técnica enviada a moderar el $day/$month/$year. <br />
			El nuevo CD es de $new_date días, y la fecha para pedir una nueva técnica es $new_fecha.
			</div>
		";

		$message = preg_replace("#\[cdtecnica=$content\]#si","$texto",$message);
	}


	while(preg_match('#\[personaje=(.*?)\]#si',$message,$matches))
	{
		$uid = $post['uid'];
		$tid = $matches[1];
		$thread_ficha = null;
		$personaje_message = "[personajeinvalido=$tid]";

		$query_personaje = $db->query("
			SELECT * FROM mybb_sg_sg_thread_personaje WHERE tid='$tid' AND uid='$uid'
		");
		while ($q = $db->fetch_array($query_personaje)) {
			$thread_ficha = $q;
		}

		if (!$thread_ficha) {
			$message = preg_replace("#\[personaje=$tid\]#si","$personaje_message",$message);
		} else {
			$nombre = $thread_ficha['nombre'];
			$fue = $thread_ficha['fue'];
			$res = $thread_ficha['res'];
			$vel = $thread_ficha['vel'];
			$agi = $thread_ficha['agi'];
			$des = $thread_ficha['des'];
			$pre = $thread_ficha['pre'];
			$int = $thread_ficha['int'];
			$cck = $thread_ficha['cck'];
			$vida = $thread_ficha['vida'];
			$chakra = $thread_ficha['chakra'];
			$regchakra = $thread_ficha['regchakra'];
			$espe = $thread_ficha['espe'];
			$estilo = $thread_ficha['estilo'];
			$maestria = $thread_ficha['maestria'];
			$maestria2 = $thread_ficha['maestria2'];

			if ($espe) {
				$espe_format = "[tecnica=ESPE$espe]";
			}
	
			if ($estilo) {
				$estilo_format = "[tecnica=ESTI$estilo]";
			}
	
			if ($maestria) {
				$maestria_format = "[tecnica=$maestria]";
			}
	
			if ($maestria2) {  
				$maestria2_format = "[tecnica=$maestria2]";
			}
	
			$clase = 'E';
			$sum_stats = intval($fue) + intval($res) + intval($vel) + intval($agi) + intval($des) + intval($pre) + intval($int) + intval($cck); 
	
			if ($sum_stats >= 560) { $clase = 'S+'; }
			else if ($sum_stats >= 480) { $clase = 'S'; }
			else if ($sum_stats >= 400) { $clase = 'A+'; }
			else if ($sum_stats >= 320) { $clase = 'A'; }
			else if ($sum_stats >= 240) { $clase = 'B'; }
			else if ($sum_stats >= 160) { $clase = 'C'; }
			else if ($sum_stats >= 80) { $clase = 'D'; }
	
			$personaje_message = "[spoiler=Estadísticas de $nombre]
				<div style='text-align: center;'><span class='personaje_stats_title'>Estadísticas de $nombre:</span></div>
				<div style='text-align: center;'><span class='personaje_stats'>$fue FUE | $res RES | $vel VEL | $agi AGI | $des DES |  $pre PRE | $int INT | $cck CCK</span></div><br />
				Vida: <span class='personaje_vida'>$vida</span> [hp]<br />
				Chakra: <span class='personaje_chakra'>$chakra</span> [ch]<br />
				Reg. Chakra: <span class='personaje_chakra'>$regchakra</span><br />
				Clase: <span style='font-weight: bold;'>$clase</span><br /><br />
				$espe_format
				$estilo_format
				$maestria_format
				$maestria2_format
			[/spoiler]
			";
	
			$message = preg_replace("#\[personaje=$tid\]#si","$personaje_message",$message);
		}
	}

	while(preg_match('#\[mytest\]#si',$message))
	{
		$personaje_message = "[spoiler=Mytest]Mytest[/spoiler]";

		$message = preg_replace('#\[mytest\]#si',$personaje_message,$message);

	}

	while(preg_match('#\[arma=(.*?)\]#si',$message,$matches))
	{
		$uid = $post['uid'];
		$tid = $post['tid'];
		
		$arma_id = $matches[1];
		$query_personaje = $db->query(" SELECT * FROM mybb_sg_sg_thread_personaje WHERE tid='$tid' AND uid='$uid' ");
		while ($q = $db->fetch_array($query_personaje)) { $thread_ficha = $q; }

		$fuerzaUsuario = 0;
		$destrezaUsuario = 0;

		if ($thread_ficha) {
			$fuerzaUsuario = $thread_ficha['fue'];
			$destrezaUsuario = $thread_ficha['des'];
		} else {
			$query_ficha = $db->query(" SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid' ");
			while ($q = $db->fetch_array($query_ficha)) { $ficha = $q; }
			$fuerzaUsuario = intval($ficha['str']);
			$destrezaUsuario = intval($ficha['dex']);
		}

		$objeto = null;

		$query_objetos = $db->query(" SELECT * FROM `mybb_sg_sg_objetos` WHERE objeto_id='$arma_id' ORDER BY categoria, tipo, nombre ");
		while ($q = $db->fetch_array($query_objetos)) { 
			$objeto = $q;
		}

		if ($objeto) {
			$efectos_str = convertObjectEffectsTecnica($objeto['efecto'], $destrezaUsuario, $fuerzaUsuario);

			$nombre = $objeto['nombre'];
			$arma_message = "<span style='font-size: x-small;'><strong>$nombre</strong> - $efectos_str</span><script>console.log($('#usersts_8')[0]);</script>";

			

			if ($efectos_str) {
				$message = preg_replace("#\[arma=$arma_id\]#si","$arma_message",$message);	
			} else {
				$message = preg_replace("#\[arma=$arma_id\]#si","[armainvalida=$arma_id]",$message);	
			}
				
		} else {
			$message = preg_replace("#\[arma=$arma_id\]#si","[armainvalida=$arma_id]",$message);
		}

		$message = preg_replace('#\[arma\]#si',$personaje_message,$message);
	}

	while(preg_match('#\[personaje\]#si',$message))
	{
		$uid = $post['uid'];
		$tid = $post['tid'];
		$pid = $post['pid'];
		
		$ficha = null;
		$thread_ficha = null;
		$personaje_message = "";

		$query_personaje = $db->query("
			SELECT * FROM mybb_sg_sg_thread_personaje WHERE tid='$tid' AND uid='$uid'
		");

		while ($q = $db->fetch_array($query_personaje)) {
			$thread_ficha = $q;
		}

		if (!$thread_ficha) {

			$query_ficha = $db->query("
				SELECT * FROM mybb_sg_sg_fichas WHERE fid='$uid'
			");

			while ($q = $db->fetch_array($query_ficha)) {
				$ficha = $q;
			}

			$nombre = $ficha['nombre'];
			$fue = $ficha['str'];
			$res = $ficha['res'];
			$vel = $ficha['spd'];
			$agi = $ficha['agi'];
			$des = $ficha['dex'];
			$pre = $ficha['pres'];
			$int = $ficha['inte'];
			$cck = $ficha['ctrl'];
			$vida = $ficha['vida'];
			$chakra = $ficha['chakra'];
			$regchakra = $ficha['regchakra'];
			$espe = $ficha['espe'];
			$estilo = $ficha['espe_estilo'];
			$maestria = $ficha['maestria'];
			$maestria2 = $ficha['maestria_secundaria'];

			if ($tid && $pid) {
				$db->query(" 
					INSERT INTO `mybb_sg_sg_thread_personaje` (`tid`, `pid`, `uid`, `nombre`, `clase`,
						`vida`, `chakra`, `regchakra`, 
						`fue`, `res`, `vel`, `agi`, `des`, `pre`, `int`, `cck`,
						`espe`, `estilo`, `maestria`, `maestria2`) 
					VALUES ('$tid', '$pid', '$uid', '$nombre', '$clase',
						'$vida', '$chakra', '$regchakra', 
						'$fue', '$res', '$vel', '$agi', '$des', '$pre', '$int', '$cck',
						'$espe', '$estilo', '$maestria', '$maestria2');
				");
			}

		} else {
			$nombre = $thread_ficha['nombre'];
			$fue = $thread_ficha['fue'];
			$res = $thread_ficha['res'];
			$vel = $thread_ficha['vel'];
			$agi = $thread_ficha['agi'];
			$des = $thread_ficha['des'];
			$pre = $thread_ficha['pre'];
			$int = $thread_ficha['int'];
			$cck = $thread_ficha['cck'];
			$vida = $thread_ficha['vida'];
			$chakra = $thread_ficha['chakra'];
			$regchakra = $thread_ficha['regchakra'];
			$espe = $thread_ficha['espe'];
			$estilo = $thread_ficha['estilo'];
			$maestria = $thread_ficha['maestria'];
			$maestria2 = $thread_ficha['maestria2'];
		}

		if ($espe) {
			$espe_format = "[tecnica=ESPE$espe]";
		}

		if ($estilo) {
			$estilo_format = "[tecnica=ESTI$estilo]";
		}

		if ($maestria) {
			$maestria_format = "[tecnica=$maestria]";
		}

		if ($maestria2) {  
			$maestria2_format = "[tecnica=$maestria2]";
		}

		$clase = 'E';
		$sum_stats = intval($fue) + intval($res) + intval($vel) + intval($agi) + intval($des) + intval($pre) + intval($int) + intval($cck); 

		if ($sum_stats >= 560) { $clase = 'S+'; }
		else if ($sum_stats >= 480) { $clase = 'S'; }
		else if ($sum_stats >= 400) { $clase = 'A+'; }
		else if ($sum_stats >= 320) { $clase = 'A'; }
		else if ($sum_stats >= 240) { $clase = 'B'; }
		else if ($sum_stats >= 160) { $clase = 'C'; }
		else if ($sum_stats >= 80) { $clase = 'D'; }

		$personaje_message = "[spoiler=Estadísticas de $nombre]
			<div style='text-align: center;'><span class='personaje_stats_title'>Estadísticas de $nombre:</span></div>
			<div style='text-align: center;'><span class='personaje_stats'>$fue FUE | $res RES | $vel VEL | $agi AGI | $des DES |  $pre PRE | $int INT | $cck CCK</span></div><br />
			Vida: <span class='personaje_vida'>$vida</span> [hp]<br />
			Chakra: <span class='personaje_chakra'>$chakra</span> [ch]<br />
			Reg. Chakra: <span class='personaje_chakra'>$regchakra</span><br />
			Clase: <span style='font-weight: bold;'>$clase</span><br /><br />
			$espe_format
			$estilo_format
			$maestria_format
			$maestria2_format
		[/spoiler]
		";

		$message = preg_replace('#\[personaje\]#si',$personaje_message,$message);
	}

	while(preg_match('#\[cerrado\]#si',$message))
	{
		$tid = $post['tid'];
		$db->query("
			UPDATE mybb_sg_threads SET `closed`='1' WHERE tid='$tid'
		");
		$message = preg_replace('#\[cerrado\]#si','<div style=" text-align: center;border: 1px dotted #61567e; "><h1>Este tema ha sido cerrado.</h1></div>',$message);
	}

	while(preg_match('#\[hp\]#si',$message))
	{
		$hp = '<img title="Vida" style=" width: 13px; top: -1px; position: relative; " src="./images/sg/icons/vida.png" />';
		$message = preg_replace('#\[hp\]#si',$hp,$message);
	}

	while(preg_match('#\[ch\]#si',$message))
	{
		$ch = '<img title="Chakra" style=" width: 13px; top: -1px; position: relative; " src="./images/sg/icons/chakra.png" />';
		$message = preg_replace('#\[ch\]#si',$ch,$message);
	}

	while(preg_match('#\[pa\]#si',$message))
	{
		$pa = "
		<div style=\"
			background-image: url(https://media.discordapp.net/attachments/930970888466223177/1086409819289829477/9b1c1cd4-35dd-4273-8a81-98fc60839422.png?width=50&amp;height=50);
			width: 50px;
			height: 50px;
			display: inline-block;
		\"></div>";
		$message = preg_replace('#\[pa\]#si',$pa,$message);
	}

	while(preg_match('#\[ke\]#si',$message))
	{
		$ke = "
		<div style=\"
			background-image: url(https://media.discordapp.net/attachments/930970888466223177/1086409872280662016/78a6d4ee-56dc-414c-b9bf-dfe239cdfc79.png?width=50&amp;height=50);
			width: 50px;
			height: 50px;
			display: inline-block;
		\"></div>";
		$message = preg_replace('#\[ke\]#si',$ke,$message);
	}

	while(preg_match('#\[masaka\]#si',$message))
	{
		$masaka = "
		<div style=\"
			background-image: url(https://cdn.discordapp.com/attachments/1021482429447413985/1121826936289755146/ezgif-4-323bfb26ae.png);
			width: 50px;
			height: 50px;
			display: inline-block;
			background-size: contain;
		\"></div>";
		$message = preg_replace('#\[masaka\]#si',$masaka,$message);
	}

	while(preg_match('#\[caminar\]#si',$message))
	{
		$message = preg_replace('#\[caminar\]#si','[tecnica=NIN106][tecnica=NIN107]',$message);
	}

	while(preg_match('#\[tiempo=(.*?)\]#si',$message,$matches))
	{
		$dateline = $post['dateline'];
		$tiempo = $matches[1];

		if (intval($tiempo) > 0 && intval($tiempo) < 8760) {

			$timeNow = time();
			$timeAfter = intval($dateline) + (intval($tiempo) * 3600);

			// <div style=" text-align: center;border: 1px dotted #61567e; "><h1>Este tema ha sido cerrado.</h1></div>

			if ($timeNow > $timeAfter) {
				$texto = "<div style=' text-align: center;border: 1px dotted #61567e; '><h3>El tiempo para postear de $tiempo horas ya ha expirado.</h3></div>";
			} else {
				$timeLeft =  round(($timeAfter - $timeNow) / 3600, 2);
				$texto = "<div style=' text-align: center;border: 1px dotted #61567e; '><h3>Este post tiene un límite de $tiempo horas.</h3><h3>Quedan $timeLeft horas para postear.</h3></div>";
			}

			$message = preg_replace("#\[tiempo=$tiempo\]#si","$texto",$message);
		} else {
			$message = preg_replace("#\[tiempo=$tiempo\]#si","[tiempoinvalido=$tiempo]",$message);
		}
	}

	while(preg_match('#\[vida=(.*?)\]#si',$message,$matches))
	{
		$pid = $post['pid'];
		
		$vida = $matches[1];
		$vida_pt = explode(",", $vida);

		if (count($vida_pt) == 2) {
			$vida_actual = $vida_pt[0];
			$vida_max = trim($vida_pt[1]);

			if (intval($vida_max) > 0) {
				$max_width = 294;
				$max_width_avatar = 293;

				$actual_width = "0px";
				$remainingChakra = "293px";

				if (intval($vida_actual) <= intval($vida_max)) {
					if (intval($vida_actual) > 0) {
						$actual_width = strval(intval(($vida_actual / $vida_max) * $max_width)) . 'px';
						$remainingChakra = strval(intval(($vida_actual / $vida_max) * $max_width_avatar)) . 'px';
					} 
				} else if (intval($vida_actual) > intval($vida_max)) {
					$actual_width = "294px";
				}
				
				$vida_avatar = "<script>
					$('#post_$pid .personaje_vida')[0].innerText = '$vida_actual/$vida_max';
					$('#post_$pid .subBarraVida').css('width', '$remainingChakra');
				</script>";

				$barra = "
			<div class=\"vidaStatusBar\">
				<div class=\"barrasVida\">
					<div class=\"barraVidaRoja\" style=\"width: 294px\"></div>
					<div class=\"barraVidaVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraVidaText\">Vida: $vida_actual/$vida_max</span><br />
				</div>
			</div> $vida_avatar";
			} else {
				$message = preg_replace("#\[vida=$vida\]#si","[vidainvalida=$vida]",$message);
			}
	
			$message = preg_replace("#\[vida=$vida\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[vida=$vida\]#si","[vidainvalida=$vida]",$message);
		}
	}

	while(preg_match('#\[vidaextra=(.*?)\]#si',$message,$matches))
	{
		$vida = $matches[1];
		$vida_pt = explode(",", $vida);

		if (count($vida_pt) == 2) {
			$vida_actual = $vida_pt[0];
			$vida_max = trim($vida_pt[1]);

			if (intval($vida_max) > 0) {
				$max_width = 294;
				$actual_width = "0px";

				if (intval($vida_actual) <= intval($vida_max)) {
					if (intval($vida_actual) > 0) {
						$actual_width = strval(intval(($vida_actual / $vida_max) * $max_width)) . 'px';
					} 
				} else if (intval($vida_actual) > intval($vida_max)) {
					$actual_width = "294px";
				}
				
				$barra = "
			<div class=\"vidaStatusBar\">
				<div class=\"barrasVida\">
					<div class=\"barraVidaRoja\" style=\"width: 294px\"></div>
					<div class=\"barraVidaVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraVidaText\">Vida: $vida_actual/$vida_max</span><br />
				</div>
			</div>";
			} else {
				$message = preg_replace("#\[vidaextra=$vida\]#si","[vidainvalida=$vida]",$message);
			}
	
			$message = preg_replace("#\[vidaextra=$vida\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[vidaextra=$vida\]#si","[vidainvalida=$vida]",$message);
		}
	}
	
	while(preg_match('#\[chakra=(.*?)\]#si',$message,$matches))
	{
		$chakra = $matches[1];
		$chakra_pt = explode(",", $chakra);

		if (count($chakra_pt) == 2) {
			$chakra_actual = $chakra_pt[0];
			$chakra_max = trim($chakra_pt[1]);

			if (intval($chakra_max) > 0) {
				$max_width = 294;
				$max_width_avatar = 293;

				$actual_width = "0px";
				$remainingChakra = "293px";

				if (intval($chakra_actual) <= intval($chakra_max)) {
					if (intval($chakra_actual) > 0) {
						$actual_width = strval(intval(($chakra_actual / $chakra_max) * $max_width)) . 'px';
						$remainingChakra = strval(intval(($chakra_actual / $chakra_max) * $max_width_avatar)) . 'px';
					} 
				} else if (intval($chakra_actual) > intval($chakra_max)) {
					$actual_width = "294px";
				}

				$chakra_avatar = "<script>
					$('#post_$pid .personaje_chakra')[0].innerText = '$chakra_actual/$chakra_max';
					$('#post_$pid .subBarraChakra').css('width', '$remainingChakra');
				
					</script>";

				$barra = "
			<div class=\"chakraStatusBar\">
				<div class=\"barrasChakra\">
					<div class=\"barraChakraRoja\" style=\"width: 294px\"></div>
					<div class=\"barraChakraVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraChakraText\">Chakra: $chakra_actual/$chakra_max</span><br />
				</div>
			</div> $chakra_avatar";
			} else {
				$message = preg_replace("#\[chakra=$chakra\]#si","[chakrainvalido=$chakra]",$message);
			}
	
			$message = preg_replace("#\[chakra=$chakra\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[chakra=$chakra\]#si","[chakrainvalido=$chakra]",$message);
		}
	}

	while(preg_match('#\[chakraextra=(.*?)\]#si',$message,$matches))
	{
		$chakra = $matches[1];
		$chakra_pt = explode(",", $chakra);

		if (count($chakra_pt) == 2) {
			$chakra_actual = $chakra_pt[0];
			$chakra_max = trim($chakra_pt[1]);

			if (intval($chakra_max) > 0) {
				$max_width = 294;
				$actual_width = "0px";

				if (intval($chakra_actual) <= intval($chakra_max)) {
					if (intval($chakra_actual) > 0) {
						$actual_width = strval(intval(($chakra_actual / $chakra_max) * $max_width)) . 'px';
					} 
				} else if (intval($chakra_actual) > intval($chakra_max)) {
					$actual_width = "294px";
				}
				
				$barra = "
			<div class=\"chakraStatusBar\">
				<div class=\"barrasChakra\">
					<div class=\"barraChakraRoja\" style=\"width: 294px\"></div>
					<div class=\"barraChakraVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraChakraText\">Chakra: $chakra_actual/$chakra_max</span><br />
				</div>
			</div>";
			} else {
				$message = preg_replace("#\[chakraextra=$chakra\]#si","[chakrainvalido=$chakra]",$message);
			}
	
			$message = preg_replace("#\[chakraextra=$chakra\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[chakraextra=$chakra\]#si","[chakrainvalido=$chakra]",$message);
		}
	}

	while(preg_match('#\[senjutsu=(.*?)\]#si',$message,$matches))
	{
		$senjutsu = $matches[1];
		$senjutsu_pt = explode(",", $senjutsu);

		if (count($senjutsu_pt) == 2) {
			$senjutsu_actual = $senjutsu_pt[0];
			$senjutsu_max = $senjutsu_pt[1];

			if (intval($senjutsu_max) >= 0) {
				$max_width = 294;
				$actual_width = "0px";

				if (intval($senjutsu_actual) <= intval($senjutsu_max)) {
					if (intval($senjutsu_actual) > 0) {
						$actual_width = strval(intval(($senjutsu_actual / $senjutsu_max) * $max_width)) . 'px';
					} 
				} else if (intval($senjutsu_actual) > intval($senjutsu_max)) {
					$actual_width = "294px";
				}
				
				$barra = "
			<div class=\"senjutsuStatusBar\">
				<div class=\"barrasSenjutsu\">
					<div class=\"barraSenjutsuRoja\" style=\"width: 294px\"></div>
					<div class=\"barraSenjutsuVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraSenjutsuText\">Senjutsu: $senjutsu_actual/$senjutsu_max</span><br />
				</div>
			</div>";
			} else {
				$message = preg_replace("#\[senjutsu=$senjutsu\]#si","[senjutsuinvalido=$senjutsu]",$message);
			}
	
			$message = preg_replace("#\[senjutsu=$senjutsu\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[senjutsu=$senjutsu\]#si","[senjutsuinvalido=$senjutsu]",$message);
		}
	}

	while(preg_match('#\[kujaku=(.*?)\]#si',$message,$matches))
	{
		$kujaku = $matches[1];
		$kujaku_pt = explode(",", $kujaku);

		if (count($kujaku_pt) == 2) {
			$kujaku_actual = $kujaku_pt[0];
			$kujaku_max = $kujaku_pt[1];

			if (intval($kujaku_max) >= 0) {
				$max_width = 294;
				$actual_width = "0px";

				if (intval($kujaku_actual) <= intval($kujaku_max)) {
					if (intval($kujaku_actual) > 0) {
						$actual_width = strval(intval(($kujaku_actual / $kujaku_max) * $max_width)) . 'px';
					} 
				} else if (intval($kujaku_actual) > intval($kujaku_max)) {
					$actual_width = "294px";
				}
				
				$barra = "
			<div class=\"kujakuStatusBar\">
				<div class=\"barrasKujaku\">
					<div class=\"barraKujakuRoja\" style=\"width: 294px\"></div>
					<div class=\"barraKujakuVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraKujakuText\">Kujaku: $kujaku_actual/$kujaku_max</span><br />
				</div>
			</div>";
			} else {
				$message = preg_replace("#\[kujaku=$kujaku\]#si","[kujakuinvalido=$kujaku]",$message);
			}
	
			$message = preg_replace("#\[kujaku=$kujaku\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[kujaku=$kujaku\]#si","[kujakuinvalido=$kujaku]",$message);
		}
	}

	while(preg_match('#\[byakugo=(.*?)\]#si',$message,$matches))
	{
		$byakugo = $matches[1];
		$byakugo_pt = explode(",", $byakugo);

		if (count($byakugo_pt) == 2) {
			$byakugo_actual = $byakugo_pt[0];
			$byakugo_max = $byakugo_pt[1];

			if (intval($byakugo_max) >= 0) {
				$max_width = 294;
				$actual_width = "0px";

				if (intval($byakugo_actual) <= intval($byakugo_max)) {
					if (intval($byakugo_actual) > 0) {
						$actual_width = strval(intval(($byakugo_actual / $byakugo_max) * $max_width)) . 'px';
					} 
				} else if (intval($byakugo_actual) > intval($byakugo_max)) {
					$actual_width = "294px";
				}
				
				$barra = "
			<div class=\"byakugoStatusBar\">
				<div class=\"barrasByakugo\">
					<div class=\"barraByakugoRoja\" style=\"width: 294px\"></div>
					<div class=\"barraByakugoVerde\" style=\"width: $actual_width\"></div>
					<span class=\"barraByakugoText\">Byakugo no In: $byakugo_actual/$byakugo_max</span><br />
				</div>
			</div>";
			} else {
				$message = preg_replace("#\[byakugo=$byakugo\]#si","[byakugoinvalido=$byakugo]",$message);
			}
	
			$message = preg_replace("#\[byakugo=$byakugo\]#si","$barra",$message);
		} else {
			$message = preg_replace("#\[byakugo=$byakugo\]#si","[byakugoinvalido=$byakugo]",$message);
		}
	}

	while(preg_match('#\[tecnica="(.*?)"\]#si',$message,$matches))
	{
		$tecnica = null;
		$tec_tid = $matches[1];
		$uid = $post['uid'];

		$query_tecnica = $db->query("
			SELECT * FROM mybb_sg_sg_tecnicas WHERE tid='".$tec_tid."'
		");

		while ($tec = $db->fetch_array($query_tecnica)) {
			$tecnica = $tec;
		}

		if ($tecnica != null) {

			$query_tec_aprendida = $db->query("
				SELECT * FROM mybb_sg_sg_tec_aprendidas WHERE uid='$uid' AND tid='$tec_tid'
			");

			$tecnica_aprendida = ' - No Aprendida';

			while ($q = $db->fetch_array($query_tec_aprendida)) {
				$tecnica_aprendida = ' - Aprendida en ' . $q['tiempo'];
			}

			$tec_nombre = $tecnica['nombre'] . $tecnica_aprendida;
			$tec_sellos = $tecnica['sellos']; 
			$tec_descripcion = nl2br($tecnica['descripcion']);
			$tec_coste = $tecnica['coste'];
			$tec_efecto = $tecnica['efecto'];
			$tec_tipo = $tecnica['tipo'];
			$tec_rango = $tecnica['rango'];

			$tec_mostrar = '<p class="tecnicaId">ID - '.$tec_tid.'</p><p class="tecnicaLvl">Rango '.$tec_rango.'</p> <p class="tecnicaType">'.$tec_tipo.'</p><p class="tecnicaSello">Sellos - '.$tec_sellos.'</p><p class="tecnicaDesTit">Descripción:</p><p class="tecnicaDes">'.$tec_descripcion.'</p><p class="tecnicaCost">Coste - '.$tec_coste.'</p><p class="tecnicaEfect">Efecto - '.$tec_efecto.'</p>';

			$message = preg_replace('#\[tecnica="'.$tec_tid.'"\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" style="font-size: medium;" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\''.$tec_nombre.'\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\''.$tec_nombre.'\'; }">'.$tec_nombre.'</span></div>
			<div class="tecnica_spoiler_content" style="display: none;">'.$tec_mostrar.'</div>
		</div>',$message);
		} else {
			$message = preg_replace('#\[tecnica="'.$tec_tid.'"\]#si','[tecnicainvalida="'.$matches[1].'"]',$message);
		}
	}

	while(preg_match('#\[tecnica=(.*?)\]#si',$message,$matches))
	{
		$tecnica = null;
		$tec_tid = strtoupper($matches[1]);
		$uid = $post['uid'];

		$query_tecnica = $db->query("
			SELECT * FROM mybb_sg_sg_tecnicas WHERE tid='".$tec_tid."'
		");
		
		while ($tec = $db->fetch_array($query_tecnica)) {
			$tecnica = $tec;
		}

		if ($tecnica != null) {

			$query_tec_aprendida = $db->query("
				SELECT * FROM mybb_sg_sg_tec_aprendidas WHERE uid='$uid' AND tid='$tec_tid'
			");

			$tecnica_aprendida = 'No Aprendida';

			while ($q = $db->fetch_array($query_tec_aprendida)) {
				$tecnica_aprendida = 'Aprendida en ' . $q['tiempo'];
			}

			$tec_nombre = $tecnica['nombre'];
			$tec_sellos = strtoupper($tecnica['sellos']); 
			$tec_descripcion = nl2br($tecnica['descripcion']);
			$tec_coste = $tecnica['coste'];
			$tec_efecto = $tecnica['efecto'];
			$tec_requisito = $tecnica['requisito'];
			$tec_tipo = strtoupper($tecnica['tipo']);
			$tec_categoria = strtoupper($tecnica['categoria']);

			$tec_sellos_html = '';
			$tec_categoria_html = '';
			$tec_requisito_html = '';

			if ($tec_sellos) {
				$tec_sellos_html .= "<div class='tecnicaType'><span>SELLOS: $tec_sellos</span></div>";
			}

			if ($tec_requisito) {
				$tec_requisito_html .= "<div class='tecnicaLvl'><span>$tec_requisito</span></div>";
			}

			if ($tec_categoria && ($tec_categoria != $tec_tipo)) {
				$tec_categoria_html .= "<div class='tecnicaType'><span>$tec_categoria</span></div>";
			}
			
			
			$tec_rango = $tecnica['rango'];
	
			// $tec_mostrar = '<p class="tecnicaId">ID - '.$tec_tid.'</p><p class="tecnicaLvl">Rango '.$tec_rango.'</p> <p class="tecnicaType">'.$tec_tipo.'</p><p class="tecnicaSello">Sellos - '.$tec_sellos.'</p><p class="tecnicaDesTit">Descripción:</p><p class="tecnicaDes">'.$tec_descripcion.'</p><p class="tecnicaCost">Coste - '.$tec_coste.'</p><p class="tecnicaEfect">Efecto - '.$tec_efecto.'</p>';
			// $tec_mostrar = "
			// 	<p class='tecnicaId'><span>ID $tec_tid</span></p>
			// 	<p class='tecnicaLvl'><span>Rango $tec_rango</span></p> 
			// 	<p class='tecnicaType'><span>$tec_tipo</span></p>
			// 	<p class='tecnicaSello'><span>Sellos $tec_sellos</span></p>
			// 	<p class='tecnicaDes'><span>$tec_descripcion</span></p> ";

			$tec_mostrar = "
				<div style='display: flex;flex-direction: column;'>
					<div style='display: flex;flex-direction: row;justify-content: space-between;margin-bottom: 10px;margin-top: 7px;'><div class='tecnicaLvl'><span>Rango $tec_rango</span></div> <div class='tecnicaLvl'><span>$tecnica_aprendida</span></div></div>
					<div style='display: flex;flex-direction: row;justify-content: space-between;margin-bottom: 10px;margin-top: -11px;'>$tec_requisito_html</div>
					<div style='display: flex;flex-direction: row;justify-content: center;margin: 9px 8px 10px;'><div class='tecnicaId'><span>ID: $tec_tid</span></div> $tec_sellos_html <div class='tecnicaType'><span>$tec_tipo</span></div> $tec_categoria_html </div>
					<p class='tecnicaDes'><span>$tec_descripcion</span></p> ";
				
			if ($tec_coste) {
				$tec_mostrar .= "<div class='tecnicaCost'><span class='tecnicaLvl'>Coste:</span> <span class='efectoDescripcion'>$tec_coste</span></div>";
			}

			if ($tec_efecto) {
				$tec_mostrar .= "<div class='tecnicaEfect'><span class='tecnicaLvl'>Efecto:</span> <span class='efectoDescripcion'>$tec_efecto</span></div>";
			}

			$tec_mostrar .= '</div>';

			$message = preg_replace('#\[tecnica='.$tec_tid.'\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" style="font-size: medium;" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\''.$tec_nombre.'\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\''.$tec_nombre.'\'; }">'.$tec_nombre.'</span></div>
			<div class="tecnica_spoiler_content" style="display: none;">'.$tec_mostrar.'</div>
		</div>',$message);
		} else {
			$message = preg_replace('#\[tecnica='.$tec_tid.'\]#si','[tecnicainvalida='.$matches[1].']',$message);
		}

	}

	while(preg_match('#\[dado_guardado=(.*?)\]#si',$message,$matches))
	{
		$dado_counter = $matches[1];

		$uid = $post['uid'];
		$pid = $post['pid'];
		$tid = $post['tid'];
		$is_edited = $post['edittime'];
		
		$post_editado = "";
		if ($is_edited) {
			$post_editado = "[[Pilas. Este post ha sido editado.]]<br />";
		}

		$query_dado = $db->query("
			SELECT * FROM mybb_sg_sg_dados WHERE pid='".$pid."' AND tid='".$tid."' AND dado_counter='".$dado_counter."'
		");

		$dado = null;
		while ($d = $db->fetch_array($query_dado)) {
			$dado = $d;
		}

		$dado_content = $dado['dado_content'];

		$message = preg_replace('#\[dado_guardado=(.*?)\]#si',"$post_editado $dado_content",$message, 1);
	}
	
	while(preg_match('#\[regalo_guardado=(.*?)\]#si',$message,$matches))
	{
		$dado_counter = $matches[1];

		$uid = $post['uid'];
		$pid = $post['pid'];
		$tid = $post['tid'];
		$is_edited = $post['edittime'];
		
		$post_editado = "";
		if ($is_edited) {
			$post_editado = "[[Pilas. Este post ha sido editado.]]<br />";
		}

		$query_dado = $db->query("
			SELECT * FROM mybb_sg_sg_dados WHERE pid='".$pid."' AND tid='".$tid."' AND dado_counter='".$dado_counter."'
		");

		$dado = null;
		while ($d = $db->fetch_array($query_dado)) {
			$dado = $d;
		}

		$dado_content = $dado['dado_content'];

		$message = preg_replace('#\[regalo_guardado=(.*?)\]#si',"$post_editado $dado_content",$message, 1);
	}
	
	return $message;
}

function dadotag_newpost(&$data)
{
	global $db, $mybb, $post;

	$uid = $data->post_insert_data['uid'];
	$pid = $data->return_values['pid'];
	$tid = $data->post_insert_data['tid'];
	$username = $data->post_insert_data['username'];
	$my_uid = $data->post_insert_data['uid'];

	$message = $data->post_insert_data['message'];
	$dado_counter = 0;

	while(preg_match('#\[dado=(.*?)\]#si',$message,$matches))
	{
		$dadosTexto = $matches[1];
		$dadosArr = explode("d", $dadosTexto);
		$dados = $dadosArr[0];
		$caras = $dadosArr[1];

		$outputText = "Dado inválido.";

		if (count($dadosArr) == 2 && is_int(intval($dados)) && is_int(intval($caras)) && intval($dados) <= 20 && intval($caras) <= 100000) {
			$dado_counter += 1;
			$outputText = "";
			$outputText .= "[ID $dado_counter] $username ha lanzado $dados dados de $caras caras. El resultado es: <br />";

			for ($x = 1; $x <= intval($dados); $x++) {
				$dadoResultado = rand(1, intval($caras));
				$outputText .= "- Dado $x: $dadoResultado<br />";
			}
			
			$message = preg_replace('#\[dado=(.*?)\]#si','[dado_guardado='.$dado_counter.']',$message, 1);
			$db->query(" 
				INSERT INTO `mybb_sg_sg_dados` (`tid`, `pid`, `uid`, `dado_counter`, `dado_content`) VALUES ('".$tid."','".$pid."','".$uid."', '".$dado_counter."', '".$outputText."');
			");
	
		} else {
			$message = preg_replace('#\[dado=(.*?)\]#si','[dadoinvalido='.$dado_counter.']',$message, 1);
			// $db->query(" 
			// 	INSERT INTO `mybb_sg_sg_dados` (`tid`, `pid`, `uid`, `dado_counter`, `dado_content`) VALUES ('".$tid."','".$pid."','".$uid."', '".$dado_counter."', 'Dado invalido.');
			// ");
		}

	}

	if ($dado_counter > 0) {
		$db->query(" 
			UPDATE `mybb_sg_posts` SET message='".$message."' WHERE pid='".$pid."';
		");
	}

	while(preg_match('#\[regalo_navidad\]#si',$message,$matches))
	{

		$regalo = '';
		$random_number = rand(1, 5);
		if ($random_number == 1) { $regalo = '20 PH'; }
		if ($random_number == 2) { $regalo = '25 PE'; }
		if ($random_number == 3) { $regalo = '20 PR'; }
		if ($random_number == 4) { $regalo = '5000 Ryos'; }
		if ($random_number == 5) { $regalo = '10 Bastones de caramelo'; }

		$dado_counter += 1;
		$outputText = "";
		
		if ($my_uid == '320') {
			$outputText = "
			<i><span style=\" color: #ffbbcc; \"><strong>Namida</strong></span> va al arbolito a buscar su regalo. Lo recoge y nota que la etiqueta dice <strong><i>De: Kurosame</i></strong>. Abre el lazo y lo destapa, y su regalo es... </i><br />
			<div style=\"display: flex;margin: auto;flex-direction: row;text-align: center;justify-content: center;margin-top: 15px;margin-bottom: 15px;\">
				<img src=\"https://cdn.discordapp.com/attachments/1185752167521468436/1187483438794674267/nami.png?\" height=\"100\">
				<img src=\"https://media1.giphy.com/media/l3vRebb6HyeIgvmQ8/giphy.gif?cid=6c09b952w79qjj7664x3pynb9gkwcc7x4xpiotk045rwoxtm&amp;ep=v1_gifs_search&amp;rid=giphy.gif&amp;ct=g\" width=\"100\" height=\"100\">
				<img src=\"https://cdn.discordapp.com/attachments/1185752167521468436/1187486634673967104/New_Project_1.png\" height=\"100\">
			</div><br />
			<div style=\" text-align: center; \">1 Boleto para el Sorteo, $regalo y un beso de navidad.</div>
			<div style=\" text-align: center; \"><img src=\"https://cdn.discordapp.com/attachments/884606837943582784/1187508526378127380/a9fd19f0b370766296e2f346dbf9a0f4e6d031d0.gif\"></div><br />
			";
		} else if ($my_uid == '335') {
			$outputText = "
			<i><strong>Karai</strong> va al arbolito a buscar su regalo de navidad. Lo recoge, abre el lazo y lo destapa, y su regalo es... </i><br />
			<div style=\"display: flex;margin: auto;flex-direction: row;text-align: center;justify-content: center;margin-top: 15px;margin-bottom: 15px;\">
				<img src=\"https://cdn.discordapp.com/attachments/1185752167521468436/1189005623883468830/250.png\" width=\"100\">
				<div style=\"margin-left: 10px;margin-top: 42px;\">Carbón. Por portarse muy mal y solo hacerle caso a la Karai diablita.<br /> ¡Por suerte, la Karai angelical recibe 1 Boleto para el Sorteo y $regalo! ¡Feliz Navidad!</div>
			</div>";
		} else {
			$outputText = "
			<i><strong>$username</strong> va al arbolito a buscar su regalo de navidad. Lo recoge, abre el lazo y lo destapa, y su regalo es... </i><br />
			<div style=\"display: flex;margin: auto;flex-direction: row;text-align: center;justify-content: center;margin-top: 15px;margin-bottom: 15px;\">
				<img src=\"https://ugokawaii.com/wp-content/uploads/2022/10/gift.gif\" width=\"100\" height=\"100\">
				<div style=\"margin-left: 10px;margin-top: 42px;\">¡1 Boleto para el Sorteo y $regalo! ¡Feliz Navidad!</div>
			</div>";
		}
		
		$message = preg_replace('#\[regalo_navidad\]#si','[regalo_guardado='.$dado_counter.']',$message, 1);
		$db->query(" 
			INSERT INTO `mybb_sg_sg_dados` (`tid`, `pid`, `uid`, `dado_counter`, `dado_content`) VALUES ('".$tid."','".$pid."','".$uid."', '".$dado_counter."', '$outputText');
		");
	}

	if ($dado_counter > 0) {
		$db->query(" 
			UPDATE `mybb_sg_posts` SET message='".$message."' WHERE pid='".$pid."';
		");
	}
}

/* 
<i><strong>Namida</strong> va al arbolito a buscar un árbol de navid. Lo recoge y nota que la etiqueta dice <i>De: Kurosame</i>. Abre el lazo y lo destapa, y su regalo es... </i>

<div style="display: flex;margin: auto;flex-direction: row;text-align: center;justify-content: center;margin-top: 15px;margin-bottom: 15px;">
<img src="https://cdn.discordapp.com/attachments/1185752167521468436/1187483438794674267/nami.png?" width="100" height="100"><img src="https://media1.giphy.com/media/l3vRebb6HyeIgvmQ8/giphy.gif?cid=6c09b952w79qjj7664x3pynb9gkwcc7x4xpiotk045rwoxtm&amp;ep=v1_gifs_search&amp;rid=giphy.gif&amp;ct=g" width="100" height="100"><div style="margin-left: 10px;margin-top: 42px;">¡1 Boleto para el Sorteo y 5000 Ryos! ¡Feliz Navidad!</div></div>

Feliz Namidad.

<div style="display: flex;margin: auto;flex-direction: row;text-align: center;justify-content: center;margin-top: 15px;margin-bottom: 15px;">
<img src="https://cdn.discordapp.com/attachments/1185752167521468436/1187483438794674267/nami.png?" height="100"><img src="https://media1.giphy.com/media/l3vRebb6HyeIgvmQ8/giphy.gif?cid=6c09b952w79qjj7664x3pynb9gkwcc7x4xpiotk045rwoxtm&amp;ep=v1_gifs_search&amp;rid=giphy.gif&amp;ct=g" width="100" height="100">
<img src="https://cdn.discordapp.com/attachments/1185752167521468436/1187486634673967104/New_Project_1.png" height="100">
</div>
<div style=" text-align: center; ">¡Feliz Namidad!</div>
<div style=" text-align: center; ">Tu regalo es 1 Boleto para el Sorteo, 5000 Ryos y un beso de navidad.</div>
<div style=" text-align: center; "><img src="https://cdn.discordapp.com/attachments/884606837943582784/1187508526378127380/a9fd19f0b370766296e2f346dbf9a0f4e6d031d0.gif"></div>

¡1 Boleto para el Sorteo, 5000 Ryos! Y un beso de Namidad.


*/