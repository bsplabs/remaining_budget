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

$rpMaxFilePath = "./../../public/temp/max_account.csv";
rpMaxAccountHandler($rpMaxFilePath);

function checkRPMaxAccountExists($rpMaxData)
{
  try {
    $mainDB = dbCon();

    $sql = "SELECT COUNT(*) AS rowCount FROM remaining_budget_customers WHERE grandadmin_customer_id = :gci AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("gci", $rpMaxData["grandadmin_customer_id"]);
    $stmt->bindParam("gcn", $rpMaxData["grandadmin_customer_name"]);

    $stmt->execute();
    
    $result["status"] = "success";
    $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $result["data"] = $fetchRowCount["rowCount"];
    
  } catch (PDOException $e) {
    $result["status"] = "success";
    $result["data"] = $e->getMessage();
  }

  $mainDB = NULL;
  return $result;
}

function updateRPMaxAccount($rpMaxData)
{
  try {
    $mainDB = dbCon();

    $sql = "UPDATE remaining_budget_customers 
            SET company = :company
            WHERE grandadmin_customer_id = :gci 
              AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("gci", $rpMaxData["grandadmin_customer_id"]);
    $stmt->bindParam("gcn", $rpMaxData["grandadmin_customer_name"]);
    $stmt->bindParam("company", $rpMaxData["company"]);

    $stmt->execute();
    
    $result["status"] = "success";
    $result["data"] = "";

  } catch (PDOException $e) {
    $result["status"] = "success";
    $result["data"] = $e->getMessage();
  }

  $mainDB = NULL;
  return $result;
}

function insertRPMaxAccount($rpMaxData)
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
            )
            SET company = :company
            WHERE grandadmin_customer_id = :gci 
              AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("gci", $rpMaxData["grandadmin_customer_id"]);
    $stmt->bindParam("gcn", $rpMaxData["grandadmin_customer_name"]);
    $stmt->bindParam("company", $rpMaxData["company"]);
    $stmt->bindParam("parent_id", $rpMaxData["grandadmin_customer_id"]);
    $stmt->bindValue("payment_method", "prepaid");
    $stmt->bindValue("updated_by", "rpmax_script");

    $stmt->execute();
    
    $result["status"] = "success";
    $result["data"] = "";

  } catch (PDOException $e) {
    $result["status"] = "success";
    $result["data"] = $e->getMessage();
  }

  $mainDB = NULL;
  return $result;
}

function rpMaxAccountHandler($rpMaxFilePath)
{
  if (($handle = fopen($rpMaxFilePath, "r")) !== FALSE) {
    $rpMaxAccount = array();
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
      if ($i > 0) {
        $rpMaxAccount[$i - 1] = array(
          "grandadmin_customer_id" => $row[0],
          "grandadmin_customer_name" => $row[1],
          "company" => $row[2]
        );
      }
    }
    fclose($handle);

    foreach ($rpMaxAccount as $key => $value) {
      $checkRPMaxAccountExists = checkRPMaxAccountExists($value);
      if ($checkRPMaxAccountExists != 0) {
        // echo "RPMAX CustomerID : " . $value["grandadmin_customer_id"] . " ---> MUST BE UPDATE \n";
        $update = updateRPMaxAccount($value);
        if ($update["status"] === "success") {
          echo "RPMAX CustomerID : " . $value["grandadmin_customer_id"] . " ---> UPDATE SUCCESS \n";
        } else {
          echo "RPMAX CustomerID : " . $value["grandadmin_customer_id"] . " ---> UPDATE FAIL >> " . $update["data"] . "\n";
        }
      } else {
        $insert = insertRPMaxAccount($value);
        if ($insert["status"] === "success") {
          echo "RPMAX CustomerID : " . $value["grandadmin_customer_id"] . " ---> INSERT SUCCESS \n";
        } else {
          echo "RPMAX CustomerID : " . $value["grandadmin_customer_id"] . " ---> INSERT FAIL >> " . $insert["data"] . "\n";
        }
      }
    }

  } else {
    echo " --- Can not open RPMAX file! --- \n";
  }
}
