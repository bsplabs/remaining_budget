<?php

// setting server env
require_once 'config/config.php';

if (php_sapi_name() !== "cli") {
  $white_lists = explode(",", REMAINING_BUDGET_RELAXED_HOST);
  $host_name = trim(HOST_NAME);
  if (!in_array($host_name, $white_lists)) {
    $res = array(
      "status" => "error",
      "message" => "Not allowed the request url"
    );
    echo json_encode($res);
    exit;
  }
}

// require libraries from folder libraries
require_once 'libraries/Core.php';
require_once 'libraries/Controller.php';
require_once 'libraries/Database.php';

if (!isset($_SESSION)) {
  session_start();
}
if (SERVER_ENV !== 'local') {
  if (empty($_SESSION['admin_username'])) {
    header("Location: " . GRANDADMIN_URL);
    exit(0);
  }
}


// Instantiate core class
$init = new Core();

?>