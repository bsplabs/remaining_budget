<?php

// master_postpaid_account.csv

const SERVERNAME = "db";
const USERNAME = "root";
const PASSWORD = "root9999";

function dbCon()
{
  try {
    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=remaining_budget;charset=utf8", USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection successfully\n";
    return $conn;
  } catch (PDOException $e) {
    echo "Connection fail: " . $e->getMessage() . "\n";
    exit;
  }
}

function dbClose()
{
}

// echo __DIR__;
$filePath = "./../../public/temp/master_postpaid_account.csv";
getCustomerMappingData($filePath);
// echo file_exists($filePath) . "\n";

function getCustomerMappingData($filePath)
{
  if (($handle = fopen("{$filePath}", "r")) !== FALSE) {
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
      if ($i > 0) {
        print_r($row);
        $paymentMethod = array(
          "source" => $row[0],
          "customer_id" => $row[1],
          "customer_name" => $row[2],
          "payment_method" => $row[3]
        );

        $checkCustomerPaymentMethod = checkCustomerPaymentMethod($paymentMethod);
        if ($checkCustomerPaymentMethod["status"] === "success" && $checkCustomerPaymentMethod["data"] > 0) {
          // update
          $updateCustomerPayment = updateCustomerPaymentMethod($paymentMethod);
          if ($updateCustomerPayment) {
            echo "ROW " . $i . " - {$paymentMethod["customer_id"]} update success \n";
          } else {
            echo "ROW " . $i . " - {$paymentMethod["customer_id"]} update fail \n --> " . $updateCustomerPayment["data"] . "\n";
          }
        } else if ($checkCustomerPaymentMethod["status"] === "success" && $checkCustomerPaymentMethod["data"] == 0) {
          // insert
          $insertCustomerPayment = insertCustomerPaymentMethod($paymentMethod);
          if ($insertCustomerPayment["status"] === "success") {
            echo "ROW " . $i . " - {$paymentMethod["customer_id"]} insert success \n";
          } else {
            echo "ROW " . $i . " - {$paymentMethod["customer_id"]} insert fail \n --> " . $insertCustomerPayment["data"] . "\n";
          }
        } else {
          echo " --- Something went wrong! --- \n";
        }
      }
      echo "\n";
    }
  } else {
    echo "Can't open target file! \n";
  }
}

function checkCustomerPaymentMethod($customerDetail)
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT COUNT(*) AS count_row 
            FROM remaining_budget_customers 
            WHERE grandadmin_customer_id = :grandadmin_customer_id 
              AND grandadmin_customer_name = :grandadmin_customer_name";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    $stmt->execute();
    $result["status"] = "success";
    $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $result["data"] = $fetchRowCount["count_row"];
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function insertCustomerPaymentMethod($customerDetail)
{
  try {
    $mainDB = dbCon();
    $sql = "INSERT INTO remaining_budget_customers (
              grandadmin_customer_id, 
              grandadmin_customer_name,
              offset_acct,
              offset_acct_name,
              payment_method,
              company,
              parent_id,
              updated_by
            ) 
            VALUES (
              :grandadmin_customer_id,
              :grandadmin_customer_name,
              :grandadmin_customer_id,
              :grandadmin_customer_name,
              :payment_method,
              :company,
              :parent_id,
              :updated_by
            )";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    $stmt->bindParam("payment_method", $customerDetail["payment_method"]);
    $stmt->bindValue("company","RPTH");
    $stmt->bindParam("parent_id", $customerDetail["customer_id"]);
    $stmt->bindValue("updated_by", "script");
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

function updateCustomerPaymentMethod($customerDetail)
{
  try {
    $mainDB = dbCon();
    $sql = "UPDATE remaining_budget_customers 
            SET payment_method = :payment_method,
                updated_at = NOW()
            WHERE grandadmin_customer_id = :grandadmin_customer_id
              AND grandadmin_customer_name = :grandadmin_customer_name";

    $stmt = $mainDB->prepare($sql);
    // WHERE
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    // SET
    $stmt->bindParam("payment_method", $customerDetail["payment_method"]);
    // EXECUTE
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
