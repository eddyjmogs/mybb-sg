<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('showthread_start', 'postlayouttheme_force', 100);
$plugins->add_hook('admin_formcontainer_output_row', 'postlayouttheme_output_row');
$plugins->add_hook('admin_style_themes_edit_commit', 'postlayouttheme_save_theme');

/**
 * Info function for MyBB plugin system
 */
function postlayouttheme_info()
{
	return array(
		"name"			=> "Postbit Layout Per Theme",
		"description"	=> "Allows you to change the default postbit layout per theme and force users to use it.(fixed for 1.8*)",
		"website"		=> "",
		"author"		=> "Aries-Belgium",
		"authorsite"	=> "http://community.mybb.com/user-3840.html",
		"version"		=> "1.12",
		"compatibility" => "18*"
	);
}

/**
 * The install function for the MyBB plugin system
 */
function postlayouttheme_install()
{
	global $db;
	$db->query("ALTER TABLE  ".TABLE_PREFIX."themes ADD  `postlayout` VARCHAR( 15 ) NOT NULL");
}

/**
 * The is_installed function for the MyBB plugin system
 */
function postlayouttheme_is_installed()
{
	global $db;
	return $db->field_exists("postlayout", "themes");
}

/**
 * The install function for the MyBB plugin system
 */
function postlayouttheme_uninstall()
{
	global $db;
	
	$db->query("ALTER TABLE  ".TABLE_PREFIX."themes DROP  `postlayout`");
}

/**
 * Implementation of the showthread_start hook
 *
 * Force to use the default layout style
 */
function postlayouttheme_force()
{
	global $mybb, $db, $theme;
	
	$query = $db->simple_select("themes", "postlayout", "tid='{$theme['tid']}'");
	$postlayout = $db->fetch_field($query, "postlayout");
	switch($postlayout)
	{
		case 'horizontal':
			$mybb->settings['postlayout'] = 'horizontal';
			$mybb->user['classicpostbit'] = 0;
			break;
		case 'classic':
			$mybb->settings['postlayout'] = 'classic';
			$mybb->user['classicpostbit'] = 1;
			break;
	}
}

/**
 * Implementation of the admin_formcontainer_output_row hook
 * 
 * Show the settings for the postbit layout in the theme form
 */
function postlayouttheme_output_row($args)
{
	global $mybb;
	$module = $mybb->version_code >= 1800 ? 'style-themes' : 'style/themes';
	if($mybb->input['module'] == $module && $mybb->input['action'] == "edit" && $args['label_for'] == "imgdir")
	{
		global $form, $theme;
		
		$form_container = $args['this'];
		$form_container->output_row(
			"Post Layout",
			"Specify the post layout to be used for this theme.",
			$form->generate_select_box(
				'postlayout',
				array(
					'default' => "MyBB Default(default global)",
					'horizontal' => "Horizontal",
					'classic' => "Classic"
				),
				$theme['postlayout']
			),
			'postlayout'
		);
	}
}

/**
 * Implementation of the admin_style_themes_edit_commit hook
 * 
 * When the theme gets saved, also save the postbit layout
 */
function postlayouttheme_save_theme()
{
	global $mybb, $theme, $db;
	
	if(isset($mybb->input['postlayout']))
	{
		$postlayout = $db->escape_string($mybb->input['postlayout']);
		$db->update_query("themes", array('postlayout' => $postlayout), "tid='{$theme['tid']}'");
	}
}