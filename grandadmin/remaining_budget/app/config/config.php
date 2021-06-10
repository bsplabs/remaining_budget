<?php

date_default_timezone_set('Asia/Bangkok');
// header('Content-Type: text/html; charset=UTF-8');

// Path
define('APPROOT', dirname(dirname(__FILE__)));
define('ROOTPATH', dirname(dirname(dirname(__FILE__))));

// BASE URL
if (php_sapi_name() !== "cli") {
  $uri_name_project = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
  $uri_name_project = $uri_name_project[0];
  $server_name = $_SERVER['SERVER_NAME'];
  define('HOST_NAME', $server_name);
  if ($_SERVER["SERVER_PORT"] !== "80") {
    $server_name = $server_name . ":" . $_SERVER["SERVER_PORT"];
  }
  $url = $server_name . "/" . $uri_name_project;
  $protocol = stripos($_SERVER['REQUEST_SCHEME'], 'https') === 0 ? 'https://' : 'http://';
  $base_url = rtrim($protocol . $url, "/");
  define('BASE_URL', $base_url);
  define('PN_URI', $uri_name_project);
}

// Main way to get ENV
$json_path = ROOTPATH . '/config.json';
$json_content = file_get_contents($json_path);
$json_config = json_decode($json_content);
if (!isset($json_config->server_env) || empty($json_config->server_env)) {
  define("SERVER_ENV", "local");
} else {
  define("SERVER_ENV", $json_config->server_env);
}
define("VERSION_NUMBER", $json_config->version_number);

if (SERVER_ENV === 'local') {
  // config development env
  define('DB_HOST', 'db');
  define('DB_USER', 'rembg');
  define('DB_PASS', 'rembg9999');
  define('DB_NAME', 'remaining_budget');
  define('ADPRO_ICE_API_TOKEN', '9fc5faeff1e9e49bc2db82b4481ddc00');
  define('ADPRO_ICE_REMAINING_BUDGET_URL', 'http://adpromobile.readyplanet.com/api/remaining_budget');
  define('REMAINING_BUDGET_RELAXED_HOST', "grandadmin.bsplabs.com,localhost");
  define('GRANDADMIN_URL','http://localhost');
} else if (SERVER_ENV === 'staging') {
  // config staging env
  require_once ROOTPATH . '/../env_setup.php';
  define('DB_HOST', $dbservername);
  define('DB_USER', $dbusername);
  define('DB_PASS', $dbpassword);
  define('DB_NAME', $dbname);
  define('ADPRO_ICE_API_TOKEN', '9fc5faeff1e9e49bc2db82b4481ddc00');
  define('ADPRO_ICE_REMAINING_BUDGET_URL', 'http://adpromobile.readyplanet.com/api/remaining_budget');
  define('REMAINING_BUDGET_RELAXED_HOST', 'grandadmin-stg.readyplanet.com');
  define('GRANDADMIN_URL','http://grandadmin-stg.readyplanet.com');
} else if (SERVER_ENV === 'production') {
  // config production env
  require_once ROOTPATH . '/../env_setup.php';
  define('DB_HOST', $dbservername);
  define('DB_USER', $dbusername);
  define('DB_PASS', $dbpassword);
  define('DB_NAME', $dbname);
  define('ADPRO_ICE_API_TOKEN', '9fc5faeff1e9e49bc2db82b4481ddc00');
  define('ADPRO_ICE_REMAINING_BUDGET_URL', 'http://adpromobile.readyplanet.com/api/remaining_budget');
  define('REMAINING_BUDGET_RELAXED_HOST', 'grandadmin.readyplanet.com');
  define('GRANDADMIN_URL','https://grandadmin.readyplanet.com');
}

// Site name
define('SITENAME', 'Remaining Budget');

$last_month_timestamp =  strtotime("-1 month");
define("PRIMARY_MONTH", date("m", $last_month_timestamp));
define("PRIMARY_YEAR", date("Y", $last_month_timestamp));
define("START_MONTH", "12");
define("START_YEAR", "2020");
