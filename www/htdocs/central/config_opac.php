<?php
/*
2021-12-24 fho4abcd Read default message file from central, with central processing, lineends
2022-08-31 rogercgui Included the variable $opac_path to allow changing the Opac root directory
2023-02-23 fho4abcd Check for existence of config.php
2025-09-29 rogercgui Improved language selection
2025-11-25 rogercgui Added file_exists checks for .def files to prevent errors
*/

error_reporting(E_ALL);

//CHANGE THIS
$opac_path = "opac/";

include realpath(__DIR__ . '/../central/config_inc_check.php');
include realpath(__DIR__ . '/../central/config.php'); //Access to the ABCD config.php

if (isset($_SESSION["db_path"])) {
	$db_path = $_SESSION["db_path"];   //si hay multiples carpetas de bases de datos
} elseif (isset($_REQUEST["db_path"])) {
	$db_path = $_REQUEST["db_path"];
}

$actualScript = basename($_SERVER['PHP_SELF']);
$CentralPath = $ABCD_scripts_path . $app_path . "/";
$CentralHttp = $server_url;
$Web_Dir = $ABCD_scripts_path . $opac_path;
$NovedadesDir = "";
$_REQUEST["modo"] = "integrado";

$lang_config = $lang; // Keep the default configuration language

if (isset($_SESSION["permiso"]) && isset($_SESSION["lang"])) {
	// 1. Maximum priority: If it is a logged user (administrator), respect the language of the session.
	$lang = $_SESSION["lang"];
} elseif (isset($_REQUEST["lang"])) {
	// 2. Priority: If language is being actively changed (eg by the OPAC language selector).
	$lang = $_REQUEST["lang"];
	$_SESSION["opac_lang"] = $lang; // Use a separate session for the OPAC visitor
} elseif (isset($_SESSION["opac_lang"])) {
	// 3. Maintains the language of the OPAC visitor during your navigation.
	$lang = $_SESSION["opac_lang"];
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	// 4. Fallback to the public: detects the browser language.
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
} else {
	// 5. Final Fallback: Uses the default system language.
	$lang = $lang_config;
}

include($CentralPath . "/lang/opac.php");
include($CentralPath . "/lang/admin.php");

$galeria = "N";
$facetas = "Y";

//$logo="assets/img/logoabcd.png";
$link_logo = "/" . $opac_path;

$multiplesBases = "Y";   //No access is presented for each of the databases
$afinarBusqueda = "Y";   //Allows you to refine search expression
$IndicePorColeccion = "Y";  //Separate indices are maintained for the terms of the collections


// --- CORREÇÃO 1: Verificar opac.def ---
$opac_global_def = $db_path . "/opac_conf/opac.def";
if (file_exists($opac_global_def)) {
	$opac_gdef = parse_ini_file($opac_global_def, true);
} else {
	$opac_gdef = array(); // Array vazio se não existir
}

if (isset($opac_gdef['charset'])) {
	$charset = $opac_gdef['charset'];
} else {
	$charset = "UTF-8";
}

// Define the restricted opac variable
if (isset($opac_gdef['RESTRICTED_OPAC'])) {
	$restricted_opac = $opac_gdef['RESTRICTED_OPAC'];
} else {
	$restricted_opac = "";
}


if (isset($opac_gdef['shortIcon'])) {
	$shortIcon = $opac_gdef['shortIcon'];
} else {
	$shortIcon = "";
}

// --- CORREÇÃO 2: Verificar global_style.def ---
$opac_global_style_def = $db_path . "/opac_conf/global_style.def";
if (file_exists($opac_global_style_def)) {
	$opac_gstyle = parse_ini_file($opac_global_style_def, true);
} else {
	$opac_gstyle = array(); // Array vazio se não existir
}

if (isset($opac_gdef['hideFILTER'])) {
	$restricted_opac = $opac_gdef['hideFILTER'];
} else {
	$restricted_opac = "N";
}

$db_path = trim(urldecode($db_path));
$ix = explode('/', $db_path);
$xxp = "";
for ($i = 1; $i < count($ix); $i++) {
	$xxp .= $ix[$i];
	if ($i != count($ix) - 1) $xxp .= '/';
}


if (!is_dir($db_path . "opac_conf/" . $lang)) {
	$lang = "en";
}

$modo = "";
if (isset($_REQUEST["base"]))
	$actualbase = $_REQUEST["base"];
else
	$actualbase = "";
if (isset($_REQUEST["xmodo"]) and $_REQUEST["xmodo"] != "") {
	unset($_REQUEST["base"]);
	$modo = "integrado";
}

if (isset($_REQUEST["search_form"])) {
	$search_form = $_REQUEST["search_form"];
} else {
	$search_form = "free";
}
