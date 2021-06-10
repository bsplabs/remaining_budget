<?php

require_once dirname(__FILE__) . "/../config/config.php";

require_once ROOTPATH . "/app/vendors/PHPExcel/PHPExcel/IOFactory.php";

function dbCon($charset = 'utf8')
{
  try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME .";charset={$charset}", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection successfully\n";
    return $conn;
  } catch (PDOException $e) {
    // echo "Connection fail: " . $e->getMessage() . "\n";
    exit;
  }
}

function dbClose()
{
}

$month = '03';
$year = '2021';
$file_path = "./../../public/temp/remaining_budget_mcc/03_รายงานงบประมาณ_Root_6008230127.xlsx";
getAccessRemainingICERootFIle($file_path, $month, $month);

function getAccessRemainingICERootFIle($file_path, $month, $year)
{
  if (php_sapi_name() === "cli") {
    $allowedFileType = array('xlsx', 'xls', 'csv');
    $ext = pathinfo($file_path, PATHINFO_EXTENSION);
    $file_name = pathinfo($file_path, PATHINFO_FILENAME);
    // $file = new SplFileInfo($file_path);
    if (in_array($ext, $allowedFileType)) {
      $objPHPExcel = PHPExcel_IOFactory::load($file_path);
      $excelSheet = $objPHPExcel->getActiveSheet();
      $highestRow = $excelSheet->getHighestDataRow();

      $remaining_ice_root = array();
      for ($row = 1; $row <= $highestRow; ++$row) {
        $payment_method = $excelSheet->getCell("L" . $row)->getValue();
        if ($payment_method !== 'การออกใบแจ้งหนี้รายเดือน') continue;
        
        $currency = $excelSheet->getCell("M" . $row)->getValue();
        if ($currency !== 'THB') continue;

        $account_status = $excelSheet->getCell("A" . $row)->getValue();
        $account_name = $excelSheet->getCell("B" . $row)->getValue();
        $ads_id = $excelSheet->getCell("C" . $row)->getValue();
        $ads_type = $excelSheet->getCell("D" . $row)->getValue();
        $account_payment = $excelSheet->getCell("E" . $row)->getValue();
        $account_number_payment = $excelSheet->getCell("F" . $row)->getValue();
        $customer_paid = $excelSheet->getCell("G" . $row)->getValue();
        $customer_paid_code = $excelSheet->getCell("H" . $row)->getValue();
        $budget_code = $excelSheet->getCell("I" . $row)->getValue();
        $budget_account = $excelSheet->getCell("J" . $row)->getValue();
        $purchases_order = $excelSheet->getCell("K" . $row)->getValue();
        $budget_total = $excelSheet->getCell("N" . $row)->getValue();
        $budget_balance = $excelSheet->getCell("O" . $row)->getValue();
        $percent_used = $excelSheet->getCell("P" . $row)->getFormattedValue();
        $start_date = $excelSheet->getCell("Q" . $row)->getValue();
        $end_date = $excelSheet->getCell("R" . $row)->getValue();
        $budget_daily_total = $excelSheet->getCell("S" . $row)->getValue();
        $account_tag = $excelSheet->getCell("T" . $row)->getValue();

        $get_customer_info = getCustomerInfo($ads_id);
        // if ($get_customer_info[''])

        $remaining_ice_root = array(
          'remaining_budget_customer_id' => $get_customer_info["remaining_budget_customer_id"],
          'month' => $month,
          'year' => $year,
          'grandadmin_customer_id' => $get_customer_info["grandadmin_customer_id"],
          'grandadmin_customer_name' => $get_customer_info["grandadmin_customer_name"],
          'account_status' => $account_status,
          'account_name' => $account_name,
          'ads_id' => $ads_id,
          'ads_type' => $ads_type,
          'account_payment' => $account_payment,
          'account_number_payment' => $account_number_payment,
          'customer_paid' => $customer_paid,
          'customer_paid_code' => $customer_paid_code,
          'budget_code' => $budget_code,
          'budget_account' => $budget_account,
          'purchases_order' => $purchases_order,
          'payment_method' => $payment_method,
          'currency' => $currency,
          'budget_total' => $budget_total,
          'budget_balance' => $budget_balance,
          'percent_used' => $percent_used,
          'start_date' => $start_date,
          'end_date' => $end_date,
          'budget_daily_total' => $budget_daily_total,
          'account_tag' => $account_tag
        );

        // delete data before insert
        clearRemainingICERoot($month, $year);

        // insert data
        $insert = insertRemainingICERoot($remaining_ice_root);
        print_r($insert);
        echo "\n\n";        
      }

      // print_r($remaining_ice_root);
      // echo "\n";

    } else {
      echo "Not allowed file \n";
    }
  } else {
    exit("Allowed only CLI");
  }
}

function getCustomerInfo($ads_id)
{
  $find_customer_id = findCustomerID($ads_id);
  $customer_id = '';
  $customer_name = '';
  if ($find_customer_id['status'] === 'success' && !empty($find_customer_id['data'])) {
    $customer_id = $find_customer_id['data']['CustomerID'];
    
    // find customer name
    $find_customer_name = findCustomerName($customer_id);
    if ($find_customer_name["status"] === 'success' && !empty($find_customer_name['data'])) {
      if (empty($find_customer_name["data"]["bill_company"])) {
        $customer_name = iconv('TIS-620', 'UTF-8', $find_customer_name["data"]["bill_firstname"]) . " " . iconv('TIS-620', 'UTF-8', $find_customer_name["data"]["bill_lastname"]);
      } else {
        $customer_name = iconv('TIS-620', 'UTF-8', $find_customer_name["data"]["bill_company"]);
      }
    } else {
      // $find_customer_name_by_customer_id = findCustomerNameByCustomerID($customer_id);
      // if ($find_customer_name_by_customer_id["status"] === 'success' && !empty($find_customer_name_by_customer_id['data'])) {
      //   if (empty($find_customer_name_by_customer_id["data"]["bill_company"])) {
      //     $customer_name = iconv('TIS-620', 'UTF-8', $find_customer_name_by_customer_id["data"]["bill_firstname"]) . " " . iconv('TIS-620', 'UTF-8', $find_customer_name_by_customer_id["data"]["bill_lastname"]);
      //   } else {
      //     $customer_name = iconv('TIS-620', 'UTF-8', $find_customer_name_by_customer_id["data"]["bill_company"]);
      //   }
      // } else {
      //   $customer_name = '';
      // }
    }
  } else { // get 
    $find_customer = findCustomer($ads_id);
    if ($find_customer["status"] === 'success' && !empty($find_customer["data"])) {
      if (empty($find_customer["CustomerID"])) {
        $customer_id = $find_customer["data"]["CustomerID"];
      }

      if (empty($find_customer["data"]["bill_company"])) {
        $customer_name = iconv('TIS-620', 'UTF-8', $find_customer["data"]["bill_firstname"]) . " " . iconv('TIS-620', 'UTF-8', $find_customer["data"]["bill_lastname"]);
      } else {
        $customer_name = iconv('TIS-620', 'UTF-8', $find_customer["data"]["bill_company"]);
      }
    }
  }

  // find remaining_budget_customer_id
  $remaining_budget_customer_id = NULL;
  if (!empty($customer_id) && !empty($customer_name)) {
    $find_remaining_budget_customer_id = findRemainingBudgetCustomerID($customer_id, $customer_name);
    if ($find_remaining_budget_customer_id["status"] === 'success' && !empty($find_remaining_budget_customer_id["data"])) {
      $remaining_budget_customer_id = $find_remaining_budget_customer_id["data"];
    } else {
      // add new grandadmin_customer
      $addnew_gac = addNewGrandAdminCustomer($customer_id, $customer_name);
      if ($addnew_gac["status"] === 'success' && !empty($addnew_gac["data"])) {
        $remaining_budget_customer_id = $addnew_gac["data"];
      } else {
        $remaining_budget_customer_id = NULL;
      }
    } 
  } else {
    $remaining_budget_customer_id = NULL;
  }

  $customer_info = array(
    'grandadmin_customer_id' => $customer_id,
    'grandadmin_customer_name' => $customer_name,
    'remaining_budget_customer_id' => $remaining_budget_customer_id
  );
 
  return $customer_info;
}

function findCustomerID($ads_id)
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT CustomerID FROM ready_topup WHERE ad_id = :ad_id ORDER BY ID DESC LIMIT 1";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("ad_id", $ads_id);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function findCustomerName($customer_id)
{
  try {
    $mainDB = dbCon("latin1");
    $sql = "SELECT bill_firstname, bill_lastname, bill_company FROM ready_customer_id WHERE CustomerID = :CustomerID";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam('CustomerID', $customer_id);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function findCustomer($ads_id)
{
  try {
    $mainDB = dbCon("latin1");
    $sql = "SELECT CustomerID, bill_firstname, bill_lastname, bill_company FROM tracking_webpro_new_members WHERE AdwordsCusId = :AdwordsCusId";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam('AdwordsCusId', $ads_id);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function findRemainingBudgetCustomerID($customer_id, $customer_name)
{
  try {
    $mainDB = dbCon();
    
    $sql = "SELECT id 
            FROM remaining_budget_customers 
            WHERE grandadmin_customer_id = :grandadmin_customer_id 
              AND grandadmin_customer_name = :grandadmin_customer_name 
            ORDER BY is_parent LIMIT 1";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("grandadmin_customer_id", $customer_id);
    $stmt->bindParam("grandadmin_customer_name", $customer_name);
    $stmt->execute();
    $result["status"] = "success";
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $result["data"] = $data["id"];
    if($result["data"] == NULL){
      $result["status"] = "fail";
    }
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function addNewGrandAdminCustomer($customer_id, $customer_name)
{
  try {
    $mainDB = dbCon();
    $sql = "INSERT INTO remaining_budget_customers (
              grandadmin_customer_id,
              grandadmin_customer_name,
              offset_acct,
              offset_acct_name,
              company,
              parent_id,
              payment_method,
              updated_by
            )
            VALUES (
              :gci,
              :gcn,
              :gci,
              :gcn,
              :company,
              :parent_id,
              :payment_method,
              :updated_by
            )";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("gci", $customer_id);
    $stmt->bindParam("gcn", $customer_name);
    $stmt->bindValue("company","RPTH");
    $stmt->bindParam("parent_id", $customer_id);
    $stmt->bindValue("payment_method", "prepaid");
    $stmt->bindValue("updated_by", "script");

    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $mainDB->lastInsertId();
    
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function findCustomerNameByCustomerID($customer_id)
{
  try {
    $mainDB = dbCon("latin1");
    $sql = "SELECT bill_firstname, bill_lastname, bill_company FROM tracking_webpro_new_members WHERE CustomerID = :CustomerID";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam('CustomerID', $customer_id);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function insertRemainingICERoot($remaining_ice_root)
{
  try {
    $mainDB = dbCon();
    $sql = "INSERT INTO remaining_budget_remaining_ice_root (
              remaining_budget_customer_id,
              month,
              year,
              grandadmin_customer_id,
              grandadmin_customer_name,
              account_status,
              account_name,
              ads_id,
              ads_type,
              account_payment,
              account_number_payment,
              customer_paid,
              customer_paid_code,
              budget_code,
              budget_account,
              purchases_order,
              payment_method,
              currency,
              budget_total,
              budget_balance,
              percent_used,
              start_date,
              end_date,
              budget_daily_total,
              account_tag
            )
            VALUES (
              :remaining_budget_customer_id,
              :month,
              :year,
              :grandadmin_customer_id,
              :grandadmin_customer_name,
              :account_status,
              :account_name,
              :ads_id,
              :ads_type,
              :account_payment,
              :account_number_payment,
              :customer_paid,
              :customer_paid_code,
              :budget_code,
              :budget_account,
              :purchases_order,
              :payment_method,
              :currency,
              :budget_total,
              :budget_balance,
              :percent_used,
              :start_date,
              :end_date,
              :budget_daily_total,
              :account_tag
            )";

    $stmt = $mainDB->prepare($sql);

    $stmt->bindParam("remaining_budget_customer_id", $remaining_ice_root["remaining_budget_customer_id"]);
    $stmt->bindParam("month", $remaining_ice_root["month"]);
    $stmt->bindParam("year", $remaining_ice_root["year"]);
    $stmt->bindParam("grandadmin_customer_id", $remaining_ice_root["grandadmin_customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $remaining_ice_root["grandadmin_customer_name"]);
    $stmt->bindParam("account_status", $remaining_ice_root["account_status"]);
    $stmt->bindParam("account_name", $remaining_ice_root["account_name"]);
    $stmt->bindParam("ads_id", $remaining_ice_root["ads_id"]);
    $stmt->bindParam("ads_type", $remaining_ice_root["ads_type"]);
    $stmt->bindParam("account_payment", $remaining_ice_root["account_payment"]);
    $stmt->bindParam("account_number_payment", $remaining_ice_root["account_number_payment"]);
    $stmt->bindParam("customer_paid", $remaining_ice_root["customer_paid"]);
    $stmt->bindParam("customer_paid_code", $remaining_ice_root["customer_paid_code"]);
    $stmt->bindParam("budget_code", $remaining_ice_root["budget_code"]);
    $stmt->bindParam("budget_account", $remaining_ice_root["budget_account"]);
    $stmt->bindParam("purchases_order", $remaining_ice_root["purchases_order"]);
    $stmt->bindParam("payment_method", $remaining_ice_root["payment_method"]);
    $stmt->bindParam("currency", $remaining_ice_root["currency"]);
    $stmt->bindParam("budget_total", $remaining_ice_root["budget_total"]);
    $stmt->bindParam("budget_balance", $remaining_ice_root["budget_balance"]);
    $stmt->bindParam("percent_used", $remaining_ice_root["percent_used"]);
    $stmt->bindParam("start_date", $remaining_ice_root["start_date"]);
    $stmt->bindParam("end_date", $remaining_ice_root["end_date"]);
    $stmt->bindParam("budget_daily_total", $remaining_ice_root["budget_daily_total"]);
    $stmt->bindParam("account_tag", $remaining_ice_root["account_tag"]);

    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = "";

  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}

function clearRemainingICERoot($month, $year)
{
  try {
    $mainDB = dbCon();
    $sql = "DELETE FROM remaining_budget_remaining_ice_root WHERE month = :month AND year = :year";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam('month', $month);
    $stmt->bindParam('year', $month);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }

  $mainDB = null;
  return $result;
}