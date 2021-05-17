<?php

header('Content-Type: text/html; charset=UTF-8');

if (PHP_SAPI !== "cli") {
  exit("\n -- Working on cli only -- \n");
}

require_once __DIR__ . '/../../config/config.php';

require_once ROOTPATH . '/app/libraries/Database.php';

require_once "MediaWalletResources.php";
require_once "WithholdingTaxResources.php";
require_once "FreeClickCostResources.php";
require_once "RemainingBudgetCustomer.php";

if ($argv[1] === "media_wallet") {
  $mediaWalletResources = new MediaWalletResources();
  $mediaWalletResources->run();
} else if ($argv[1] === "withholding_tax") {
  $withholdingTaxResources = new WithholdingTaxResources();
  $withholdingTaxResources->run();
} else if ($argv[1] === "free_click_cost") {
  $freeClickCostResources = new FreeClickCostResources();
  $freeClickCostResources->run();
} else {
  echo "\n -- Not found resource name param -- \n";
  exit;
}

?>