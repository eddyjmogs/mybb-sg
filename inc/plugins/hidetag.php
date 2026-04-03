<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

require_once MYBB_ROOT."inc/class_parser.php";
$plugins->add_hook("parse_message", "hidetag_run");
$plugins->add_hook('datahandler_post_insert_post_end', 'hidetag_newpost');
$plugins->add_hook('datahandler_post_insert_thread_end', 'hidetag_newpost');
$parser = new postParser;

function hidetag_info()
{
global $mybb;
	return array(
		"name"				=> "Hide Tag",
		"description"		=> "Tag para Shinobi Gaiden.",
		"website"			=> "",
		"author"			=> "Kurosame",
		"authorsite"		=> "https://www.shinobigaiden.net",
		"version"			=> "1.0.0",
		"codename"			=> "hidetag",
		"compatibility"		=> "*",
	);
}


function hidetag_activate()
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


function hidetag_deactivate()
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


function hidetag_run(&$message)
{
	global $mybb, $db, $post, $thread, $parser;

	$user_uid = $mybb->user['uid'];

	// Set up the parser options.
	$parser_options = array(
		"allow_html" => 1,
		"allow_mycode" => 1,
		"allow_imgcode" => 1,
		"allow_videocode" => 1,
	);

	while(preg_match('#\[hide=(.*?)\](.*?)\[\/hide\]#si',$message,$matches))
	{
		$hide_uids = $matches[1];
		$hide_content = $matches[2];
		$message = preg_replace('#\[hide=(.*?)\](.*?)\[\/susurro\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\'Contenido Oculto (Vista Previa)\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\'Contenido Oculto (Vista Previa)\'; }">Contenido Oculto (Vista Previa)</span></div>
			<div class="spoiler_content" style="display: none;">'.$hide_content.'</div>
		</div>',$message, 1);
	}

	while(preg_match('#\[hide\](.*?)\[\/hide\]#si',$message,$matches))
	{
		$hide_content = $matches[1];
		$message = preg_replace('#\[hide\](.*?)\[\/hide\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\'Contenido Oculto (Vista Previa)\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\'Contenido Oculto (Vista Previa)\'; }">Contenido Oculto (Vista Previa)</span></div>
			<div class="spoiler_content" style="display: none;">'.$hide_content.'</div>
		</div>',$message, 1);
	}

	while(preg_match('#\[hide=(.*?)\]#si',$message,$matches))
	{
		$uid = $post['uid'];
		$pid = $post['pid'];
		$tid = $post['tid'];
		$is_edited = $post['edittime'];
		$is_closed = $thread['closed'] == 1;
		$is_user_same = $mybb->user['uid'] == $uid;
		$hide_counter = $matches[1];
		$is_staff = $mybb->user['uid'] == 1;

		$query_hide = $db->query("
			SELECT * FROM mybb_sg_sg_hide WHERE pid='".$pid."' AND tid='".$tid."' AND hide_counter='".$hide_counter."'
		");

		$hide = null;
		while ($h = $db->fetch_array($query_hide)) {
			$hide = $h;
		}

		$hide_uids = $hide['hide_uids'];

		$hide_uids_arr = explode(",", $hide_uids);

		$show_private_hide = false;
		$susurro_text = '';
		
		if ($hide_uids != '') {
			
			foreach ($hide_uids_arr as $hide_uid) {
				
				if ($hide_uid == $user_uid) { 
					$susurro_text = '(Susurro)';
					$show_private_hide = true; }
			}
		}

		$show_hide = $hide['show_hide'];
		$hide_id = $hide['hid'];
		$hide_content = $hide['hide_content'];

		$contenido = '';
		$hide_button = '<button class="spoiler-button" style="padding: 5px; margin: 10px 43.6%; cursor: pointer; font-family: Helvetica; font-size: 13px; color: #fff; border-style: none; border-radius: 3px; background: #a31520; width: 100px; height: 30px;" onclick="javascript: document.getElementById(\'hideform'.$hide_id.'\').submit()">Mostrar Hide</button>';
		$hidden_form = '<div style="display: none"><form id="hideform'.$hide_id.'" method="post" action="hide.php"><input type="text" name="hid" value="'.$hide_id.'" /><input type="text" name="show_hide" value="1" /><input type="text" name="tid" value="'.$tid.'" /></form></div>';

		if ($is_closed || $show_hide || $show_private_hide || $is_staff) {
			$contenido = $parser->parse_message($hide_content, $parser_options);
		} else {
			$contenido = $hidden_form . $hide_button . '<br /><hr />' . $parser->parse_message($hide_content, $parser_options);
		}

		if (!$is_user_same && !$show_hide && !$show_private_hide && !$is_closed && !$is_staff) {
			$message = preg_replace('#\[hide=(.*?)\]#si','',$message, 1);
		} else {
			$message = preg_replace('#\[hide=(.*?)\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\'Contenido Oculto\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\'Contenido Oculto '.$susurro_text.'\'; }">Contenido Oculto '.$susurro_text.'</span></div>
			<div class="spoiler_content" style="display: none;">'.$contenido.'</div>
		</div>',$message, 1);
		}
	}

	return $message;
}

function hidetag_newpost(&$data)
{
	global $db, $mybb, $post;

	$uid = $data->post_insert_data['uid'];
	$pid = $data->return_values['pid'];
	$tid = $data->post_insert_data['tid'];
	$message = $data->post_insert_data['message'];
	$hide_counter = 0;

	while(preg_match('#\[hide=(.*?)\](.*?)\[\/hide\]#si',$message,$matches))
	{
		$hide_counter += 1;
		$message = preg_replace('#\[hide=(.*?)\](.*?)\[\/hide\]#si','[hide='.$hide_counter.']',$message, 1);
		$hide_uids = $matches[1];
		$hide_content = $matches[2];
		$db->query(" 
			INSERT INTO `mybb_sg_sg_hide` (`tid`, `pid`, `uid`, `hide_counter`, `show_hide`, `hide_uids`, `hide_content`) VALUES ('".$tid."','".$pid."','".$uid."', '".$hide_counter."', 0,'$hide_uids','".$hide_content."');
		");
	}

	while(preg_match('#\[hide\](.*?)\[\/hide\]#si',$message,$matches))
	{
		$hide_counter += 1;
		$message = preg_replace('#\[hide\](.*?)\[\/hide\]#si','[hide='.$hide_counter.']',$message, 1);
		$hide_content = $matches[1];
		$db->query(" 
			INSERT INTO `mybb_sg_sg_hide` (`tid`, `pid`, `uid`, `hide_counter`, `show_hide`, `hide_content`) VALUES ('".$tid."','".$pid."','".$uid."', '".$hide_counter."', 0,'".$hide_content."');
		");
	}

	if ($hide_counter > 0) {
		$db->query(" 
			UPDATE `mybb_sg_posts` SET message='".$message."' WHERE pid='".$pid."';
		");
	}
}