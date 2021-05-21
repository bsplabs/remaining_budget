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

$firstRemainingFilePath = "./../../public/temp/first_remaining.csv";
initializeFirstRemaining($firstRemainingFilePath);

function checkCustomerExists($firstRemainingData)
{
  try {
    $mainDB = dbCon();

    $sql = "SELECT id FROM remaining_budget_customers WHERE grandadmin_customer_id = :gci AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("gci", $firstRemainingData["customer_id"]);
    $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);

    $stmt->execute();

    $result["status"] = "success";
    $remainingBudgetCustomerID = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($remainingBudgetCustomerID["id"])) {
      $result["data"] = "";
    } else {
      $result["data"] = $remainingBudgetCustomerID["id"];
    }
  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function addNewCustomer($firstRemainingData)
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
    $stmt->bindParam("gci", $firstRemainingData["customer_id"]);
    $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);
    $stmt->bindValue("company","RPTH");
    $stmt->bindParam("parent_id", $firstRemainingData["customer_id"]);
    $stmt->bindValue("payment_method", "prepaid");
    $stmt->bindValue("updated_by", "first_remaining");
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

function checkFirstRemainingExists($firstRemainingData)
{
  try {
    $mainDB = dbCon();

    $sql = "SELECT COUNT(*) AS countRow 
            FROM remaining_budget_first_remaining 
            WHERE grandadmin_customer_id = :gci 
              AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    
    $stmt->bindParam("gci", $firstRemainingData["customer_id"]);
    $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);

    $stmt->execute();

    $result["status"] = "success";
    $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $rowCount = $fetchRowCount["countRow"];
    $result["data"] = $rowCount;

  } catch (PDOException $e) {
    $result["status"] = "fail";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function updateFirstRemaining($firstRemainingData)
{
  try {
    $mainDB = dbCon();

    $sql = "UPDATE remaining_budget_first_remaining
            SET remain_value = remain_value + :remain_value,
                updated_at = NOW()
            WHERE grandadmin_customer_id = :gci
              AND grandadmin_customer_name = :gcn";

    $stmt = $mainDB->prepare($sql);
    
    // SET
    $stmt->bindParam("remain_value", $firstRemainingData["cash_advance"]);
    // $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);

    // WHERE
    $stmt->bindParam("gci", $firstRemainingData["customer_id"]);
    $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);

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

function insertFirstRemaining($firstRemainingData)
{
  try {
    $mainDB = dbCon();

    $sql = "INSERT INTO remaining_budget_first_remaining (
              remaining_budget_customer_id,
              grandadmin_customer_id,
              grandadmin_customer_name,
              remain_value
            ) 
            VALUES (
              :remaining_budget_customer_id,
              :gci,
              :gcn,
              :remain_value
            )";

    $stmt = $mainDB->prepare($sql);
    
    $stmt->bindParam("remaining_budget_customer_id", $firstRemainingData["remaining_budget_customer_id"]);
    $stmt->bindParam("gci", $firstRemainingData["customer_id"]);
    $stmt->bindParam("gcn", $firstRemainingData["customer_name"]);
    $stmt->bindParam("remain_value", $firstRemainingData["cash_advance"]);

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

function initializeFirstRemaining($firstRemainingFilePath)
{
  if (($handle = fopen($firstRemainingFilePath, "r")) !== FALSE) {
    $firstRemainingData = array();
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
      if ($i > 0) {
        $firstRemainingData[$i - 1] = array(
          "customer_id" => $row[0],
          "customer_name" => $row[1],
          "cash_advance" => (($row[2] === "-") | ($row[2] == "") | ($row[2] == 0)) ? 0 : str_replace(",", "", $row[2])
        );
      }
    }
    fclose($handle);

    foreach ($firstRemainingData as $key => $value) {
      $checkCustomerExists = checkCustomerExists($value);
      if ($checkCustomerExists["status"] === "success") {
        $rembgCustomerID = $checkCustomerExists["data"];
      } else {
        $rembgCustomerID = "";
      }

      // separate loop because read file might slow
      if (empty($rembgCustomerID)) {
        $addNewCustomer = addNewCustomer($value);
        if ($addNewCustomer["status"] === "success") {
          $rembgCustomerID = $addNewCustomer["data"];
        } else {
          $rembgCustomerID = "";
        }
      }
      $value["remaining_budget_customer_id"] = $rembgCustomerID;

      $checkFirstRemainingExists = checkFirstRemainingExists($value);
      if ($checkFirstRemainingExists["data"] != 0) {
        // update
        $update = updateFirstRemaining($value);
        if ($update["status"] === "success") {
          echo "CustomerID " . $value["customer_id"] . " ---------> UPDATE SUCCESS \n";
        } else {
          echo "CustomerID " . $value["customer_id"] . " ---------> UPDATE FAIL : " . $update["data"] . "\n";
        }
      } else {
        // insert
        $insert = insertFirstRemaining($value);
        if ($insert["status"] === "success") {
          // echo "CustomerID " . $value["customer_id"] . " ---------> INSERT SUCCESS \n";
        } else {
          echo "CustomerID " . $value["customer_id"] . " : " . $value["cash_advance"] . " ---------> INSERT FAIL : " . $insert["data"] . "\n";
        }
      }
    }

  } else {
  }
}
