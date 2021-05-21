<?php

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

$filePath = "./../../public/temp/master_cusid_mapping.csv";
getCustomerMappingData($filePath);

function getCustomerMappingData($filePath)
{
  if (($handle = fopen("{$filePath}", "r")) !== FALSE) {
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
      if ($i > 0) {
        $customerDetail = array(
          "type" => $row[0],
          "offset_acct" => $row[1],
          "offset_acct_name" => $row[2],
          "customer_id" => $row[3],
          "customer_name" => $row[4]
        );

        $checkCtm = checkCustomerExists($customerDetail);
        if ($checkCtm["status"] == "success" && $checkCtm["data"] > 0) {
          echo "ROW " . $i . " - {$customerDetail["customer_id"]}/{$customerDetail["offset_acct"]} is exists \n";
          // update
          // $updateCustomer = updateCustomerData($customerDetail);
          // if ($updateCustomer["status"] === "success") {
          //   echo "ROW " . $i . " - {$customerDetail["customer_id"]}/{$customerDetail["offset_acct"]} update success \n";
          // } else {
          //   echo "ROW " . $i . " - {$customerDetail["customer_id"]}/{$customerDetail["offset_acct"]} update fail \n --> " . $updateCustomer["data"] . "\n";
          // }
        } else if ($checkCtm["status"] == "success" && $checkCtm["data"] == 0) {
          // insert
          $insertCustomer = insertCustomerData($customerDetail);
          if ($insertCustomer["status"] === "success") {
            echo "ROW " . $i . " - {$customerDetail["customer_id"]}/{$customerDetail["offset_acct"]} insert success \n";
          } else {
            echo "ROW " . $i . " - {$customerDetail["customer_id"]}/{$customerDetail["offset_acct"]} insertcl fail \n --> " . $insertCustomer["data"] . "\n";
          }
        } else {
          echo " --- Something went wrong! --- \n";
        }
      }
    }
  } else {
    echo "Can't open target file! \n";
  }
}

function checkCustomerExists($customerDetail)
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT COUNT(*) AS count_row 
            FROM remaining_budget_customers 
            WHERE grandadmin_customer_id = :grandadmin_customer_id
              AND grandadmin_customer_name = :grandadmin_customer_name
              AND offset_acct = :offset_acct 
              AND offset_acct_name = :offset_acct_name";
    
    $stmt = $mainDB->prepare($sql);
    
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    $stmt->bindParam("offset_acct", $customerDetail["offset_acct"]);
    $stmt->bindParam("offset_acct_name", $customerDetail["offset_acct_name"]);
    
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

function insertCustomerData($customerDetail)
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
              :grandadmin_customer_id,
              :grandadmin_customer_name,
              :offset_acct,
              :offset_acct_name,
              :company,
              :parent_id,
              :payment_method,
              :updated_by
            )";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    $stmt->bindParam("offset_acct", $customerDetail["offset_acct"]);
    $stmt->bindParam("offset_acct_name", $customerDetail["offset_acct_name"]);
    $stmt->bindValue("company","RPTH");
    $stmt->bindParam("parent_id", $customerDetail["customer_id"]);
    $stmt->bindValue("payment_method", "prepaid");
    $stmt->bindValue("updated_by", "customer_mapping");
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

function updateCustomerData($customerDetail)
{
  try {
    $mainDB = dbCon();
    $sql = "UPDATE remaining_budget_customers 
            SET updated_at = NOW()
            WHERE grandadmin_customer_id = :grandadmin_customer_id
              AND grandadmin_customer_name = :grandadmin_customer_name
              AND offset_acct = :offset_acct
              AND offset_acct_name = :offset_acct_name";

    $stmt = $mainDB->prepare($sql);
    // WHERE
    $stmt->bindParam("grandadmin_customer_id", $customerDetail["customer_id"]);
    $stmt->bindParam("grandadmin_customer_name", $customerDetail["customer_name"]);
    $stmt->bindParam("offset_acct", $customerDetail["offset_acct"]);
    $stmt->bindParam("offset_acct_name", $customerDetail["offset_acct_name"]);
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
