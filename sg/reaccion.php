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
define('THIS_SCRIPT', 'reaccion.php');
require_once "./../global.php";
require "./../inc/config.php";
require_once "./functions/sg_functions.php";
global $templates, $mybb;


eval("\$page = \"".$templates->get("sg_reaccion")."\";");
output_page($page);
