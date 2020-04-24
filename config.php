<?php
// directory separator
defined("DS") || define("DS", DIRECTORY_SEPARATOR);
// path separator
defined("PS") || define("PS", PATH_SEPARATOR);
// URL separator
defined("US") || define("US", '/');

$rootFolder = str_replace(str_replace(US,DS,$_SERVER['DOCUMENT_ROOT']),'',__DIR__);
$baseFolder 	=  !empty($rootFolder) ? ltrim(str_replace(DS,US, $rootFolder), US ).US: '';

//Defining the website domain url
$_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';  // Set the http or https

// Define Port
$PORT = (($_protocol == 'http' && (int)$_SERVER['SERVER_PORT'] !== 80) || ($_protocol == 'https' && (int)$_SERVER['SERVER_PORT'] !== 443)) ? ":" . $_SERVER['SERVER_PORT'] : '';

defined("URL") || define("URL", $_protocol.'://'.$_SERVER['SERVER_NAME'].$PORT. US); //assign the global site url www.yoursite.com/

$baseUrl = URL . $baseFolder;
$apiUrl = rtrim($baseUrl,US);