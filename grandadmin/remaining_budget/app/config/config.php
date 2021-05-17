<?php

date_default_timezone_set('Asia/Bangkok');

// header('Content-Type: text/html; charset=UTF-8');

// BASE URL
if (php_sapi_name() !== "cli") {
  $uri_name_project = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
  $uri_name_project = $uri_name_project[0];
  $server_name = $_SERVER['SERVER_NAME'];
  if ($_SERVER["SERVER_PORT"] !== "80") {
    $server_name = $server_name . ":" . $_SERVER["SERVER_PORT"];
  }
  $url = $server_name . "/" . $uri_name_project;
  $protocol = stripos($_SERVER['REQUEST_SCHEME'],'https') === 0 ? 'https://' : 'http://';
  $base_url = rtrim($protocol . $url, "/");
  define('BASE_URL', $base_url);
  define('PN_URI', $uri_name_project);
}

// Database params
define('DB_HOST', 'db');
define('DB_USER', 'rembg');
define('DB_PASS', 'rembg9999');
define('DB_NAME', 'remaining_budget');

// ROOT
define('APPROOT', dirname(dirname(__FILE__)));
define('ROOTPATH', dirname(dirname(dirname(__FILE__))));

// Site name
define('SITENAME', 'Remaining Budget');

$last_month_timestamp =  strtotime("-1 month");
define("PRIMARY_MONTH", date("m", $last_month_timestamp));
define("PRIMARY_YEAR", date("Y", $last_month_timestamp));
define("START_MONTH", "12");
define("START_YEAR", "2020");
