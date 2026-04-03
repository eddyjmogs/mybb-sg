<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("parse_message_end", "myspoiler_run");

function myspoiler_info()
{
global $mybb;
	return array(
		"name"				=> "MyCode: [spoiler]",
		"description"		=> "Add Spoiler MyCode and button to sceditor for mybb forums.",
		"website"			=> "https://www.mybb.com",
		"author"			=> "Whiteneo",
		"authorsite"		=> "https://mybb.es",
		"version"			=> "1.2.3",
		"codename"			=> "my_spoiler",
		"compatibility"		=> "*",
	);
}


function myspoiler_activate()
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
var partialmode = {$mybb->settings[\'partialmode\']},').'#siU', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/spoiler.js?ver=1805"></script>
<script type="text/javascript">
var partialmode = {$mybb->settings[\'partialmode\']},');	
    find_replace_templatesets("codebuttons", '#'.preg_quote('{$link}').'#', '{$link},spoiler');
}


function myspoiler_deactivate()
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
    find_replace_templatesets("codebuttons", '#'.preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/spoiler.js?ver=1805"></script>').'#', '',0);
    find_replace_templatesets("codebuttons", '#'.preg_quote(',spoiler').'#', '',0);
}


function myspoiler_run(&$message)
{
	global $lang;
	
    $lang->load("my_spoiler", false, true);

	while(preg_match('#\[spoiler\](.*?)\[\/spoiler\]#si',$message,$matches))
	{
		$content = $matches[1];
		$message = preg_replace('#\[spoiler\](.*?)\[\/spoiler\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\''
				.$lang->my_spoiler_show.'\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\''.$lang->my_spoiler_hide.'\'; }">'.$lang->my_spoiler_show.'</span></div>
			<div class="spoiler_content" style="display: none;">'.$content.'</div>
		</div>',$message, 1);
	}
	while(preg_match('#\[spoiler="(.*?)"\](.*?)\[\/spoiler\]#si',$message,$matches))
	{
		$content = $matches[2];
		$message = preg_replace('#\[spoiler="(.*?)"\](.*?)\[\/spoiler\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\'$1\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\'$1\'; }">$1</span></div>
			<div class="spoiler_content" style="display: none;">'.$content.'</div>
		</div>',$message, 1);
	}	

	while(preg_match('#\[spoiler=(.*?)\](.*?)\[\/spoiler\]#si',$message,$matches))
	{
		$content = $matches[2];
		$message = preg_replace('#\[spoiler=(.*?)\](.*?)\[\/spoiler\]#si','<div class="spoiler">
			<div class="spoiler_title"><span class="spoiler_button" onclick="javascript: if(parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display == \'block\'){ parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'none\'; this.innerHTML=\'$1\'; } else { parentNode.parentNode.getElementsByTagName(\'div\')[1].style.display = \'block\'; this.innerHTML=\'$1\'; }">$1</span></div>
			<div class="spoiler_content" style="display: none;">'.$content.'</div>
		</div>',$message, 1);
	}
	
	return $message;
}