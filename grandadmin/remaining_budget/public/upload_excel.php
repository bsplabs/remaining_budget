<?php

// $dbHost = 'localhost';
define('DB_HOST', 'localhost');
// $dbUser = 'root';
define('DB_USER', 'root');
// $dbPass = '';
define('DB_PASS', '');
// $dbNme = 'remaining_budget';
define('DB_NAME', 'remaining_budget');

function dbCon()
{
  try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
    return $conn;
  } catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
  }
}


require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;

if (isset($_POST["import"])) {
  $allowedFileType = [
    'application/vnd.ms-excel',
    'text/xls',
    'text/xlsx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ];

  if (in_array($_FILES["file"]["type"], $allowedFileType)) {
    $targetPath = __DIR__ . "/uploads/" . $_FILES["file"]["name"];
    $explodeFileName = explode("_", $_FILES["file"]["name"]);
    $year = $explodeFileName[0];
    $month = $explodeFileName[1];
    echo $targetPath . "<br>";
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)) {
      // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($targetPath);
      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
      $spreadSheet = $reader->load($targetPath);
      $excelSheet = $spreadSheet->getActiveSheet();
      // $spreadSheetAry = $excelSheet->toArray();
      // $sheetCount = count($spreadSheetAry);

      $highestRow = $excelSheet->getHighestRow(); // e.g. 10
      $highestColumn = $excelSheet->getHighestColumn(); // e.g 'F'

      // echo $highestRow . "<br>";
      // echo $highestColumn . "<br>";

      $invoiceSet = [];
      $receiveSet = [];
      $adsCreditNoteSet = [];
      $seriesSet = [];
      $docNoSet = [];
      $offsetAcctInvoice = [];
      $offsetAcctReceive = [];
      $offsetAcctAdsCreditNote = [];
      // print_r($spreadSheetAry);
      for ($row = 3; $row <= $highestRow; ++$row) {
        $series = $excelSheet->getCell("C" . $row)->getValue();
        array_push($seriesSet, $series);

        $offsetAcct = $excelSheet->getCell("I" . $row)->getValue();
        array_push($offsetAcctSet, $offsetAcct);

        $offsetAcctName = $excelSheet->getCell("J" . $row)->getValue();
        $debit = $excelSheet->getCell("L" . $row)->getValue();
        $credit = $excelSheet->getCell("M" . $row)->getValue();
        $seriesPrefix = $series[0] . $series[1];

        if ($seriesPrefix === 'IN') {
          array_push($offsetAcctInvoice, $offsetAcct);
          array_push($invoiceSet, array(
            'series' => $series,
            'offset_acct' => $offsetAcct,
            'offset_acct_name' => $offsetAcctName,
            'debit' => $debit,
            'credit' => $credit,
            'add_to' => "invoice"
          ));
        } else if ($seriesPrefix === 'IV') {
          array_push($offsetAcctReceive, $offsetAcct);
          array_push($receiveSet, array(
            'series' => $series,
            'offset_acct' => $offsetAcct,
            'offset_acct_name' => $offsetAcctName,
            'debit' => $debit,
            'credit' => $credit,
            'add_to' => "receive"
          ));
        } else if ($seriesPrefix === 'CV' || $seriesPrefix === 'CN') {
          array_push($offsetAcctAdsCreditNote, $offsetAcct);
          array_push($adsCreditNoteSet, array(
            'series' => $series,
            'offset_acct' => $offsetAcct,
            'offset_acct_name' => $offsetAcctName,
            'debit' => $debit,
            'credit' => $credit,
            'add_to' => "ads_credit_note"
          ));
        }
        
      }

      $invoiceDataClassify = classifyData($offsetAcctInvoice, $invoiceSet);
      $receiveDataClassify = classifyData($offsetAcctReceive, $receiveSet);
      $adsCreditNoteDataClassify = classifyData($offsetAcctAdsCreditNote, $adsCreditNoteSet);

      echo "<pre>";
      // print_r($adsCreditNoteDataClassify);
      echo "</pre>";
      
      echo "<br> <h1>invoiceDataClassify -----------> </h1> <br>";
      // coreUpdateRemainingBudget($invoiceDataClassify, $month, $year);
      echo "<br> <h1>receiveDataClassify -----------> </h1> <br>";
      // coreUpdateRemainingBudget($receiveDataClassify, $month, $year);
      echo "<br> <h1>adsCreditNoteDataClassify -----------> </h1> <br>";
      coreUpdateRemainingBudget($adsCreditNoteDataClassify, $month, $year);

    } else {
      echo "Failed! <br>";
    }
  }
  
} else {
  $type = "error";
  $message = "Invalid File Type. Upload Excel File.";
}

function coreUpdateRemainingBudget($dataClassify, $month, $year)
{
  foreach ($dataClassify as $idcKey => $idcData) {
    $checkOffsetAcct = checkOffsetAcct($idcKey)["data"];
    $remainingBudgetCustomerId = $checkOffsetAcct;
    $idcData["month"] = $month;
    $idcData["year"] = $year;
    echo "<pre>";
    // print_r($remainingBudgetCustomerId);
    echo "</pre>";
    // echo "<br>";
    if ($checkOffsetAcct == "" || empty($checkOffsetAcct)) {
      // insert to db
      $addChecking = addOffsetAcct($idcKey, $idcData["offset_acct_name"]);
      if ($addChecking["status"] === "success") {
        // insert remaining_budget_value
        insertRemainigBudgetValue($remainingBudgetCustomerId, $idcData);
      }
    } else {
      // get id to update remaining_budget_value table
      echo "Case idc exists <br>";
      // print_r($remainingBudgetCustomerId);
      $rbChecking = checkRemainingBudget($remainingBudgetCustomerId, $idcKey, $idcData);
      echo "<pre>";
      // print_r($rbChecking);
      echo "</pre>";
      // echo "<br>";

      if ($rbChecking["data"] == 0 || $rbChecking["data"] == "") {
        // insert
        insertRemainigBudgetValue($remainingBudgetCustomerId, $idcData);
      } else {
        // update
        // echo "Update ---> " . $idcData["add_to"] . "<br>";
        updateRemainigBudgetValue($remainingBudgetCustomerId, $idcData);
      }

    }
  }
}

function classifyData($offsetAcctData, $dataSet)
{
  $dataClassify = [];
  foreach ($offsetAcctData as $item) {
    $dataClassify[$item] = [];
    $totalCreditDebit = 0;
    foreach ($dataSet as $data) {
      if ($item == $data['offset_acct']) {
        $totalCreditDebit += abs($data["debit"] - $data["credit"]);
        $dataClassify[$item]["offset_acct"] = $data['offset_acct'];
        $dataClassify[$item]["offset_acct_name"] = $data['offset_acct_name'];
        $dataClassify[$item]["add_to"] = $data["add_to"];
        $dataClassify[$item]["credit_debit_total"] = $totalCreditDebit;
        // array_push($dataClassify[$item], $data);
      }
    }
  }
  return $dataClassify;
}

function checkOffsetAcct($idcKey)
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT id FROM remaining_budget_customers WHERE offset_acct = :offset_acct";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam("offset_acct", $idcKey);
    $stmt->execute();
    $result["status"] = "success";
    // $result["data"] = $stmt->fetchColumn();
    $result["data"] = $stmt->fetch()["id"];
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function checkRemainingBudget($remainingBudgetCustomerId, $idcKey, $idcData)
{
  try {
    $mainDB = dbCon();

    $sql = "SELECT * 
            FROM remaining_budget_value 
            WHERE remaining_budget_customer_id = :remaining_budget_customer_id
              AND month = :month
              AND year = :year";

    $stmt = $mainDB->prepare($sql);
    $stmt->bindValue("remaining_budget_customer_id", $remainingBudgetCustomerId);
    $stmt->bindValue("month", $idcData["month"]);
    $stmt->bindValue("year", $idcData["year"]);

    $stmt->execute();

    $result["status"] = "success";
    $result["data"] = $stmt->fetch();
    // $result["data"] = $stmt->fetch()["id"];
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function addOffsetAcct($offsetAcct, $offsetAcctName)
{
  try {
    $mainDB = dbCon();
    $sql = "INSERT INTO remaining_budget_customers(offset_acct, offset_acct_name) VALUES(:offset_acct, :offset_acct_name)";
    $stmt = $mainDB->prepare($sql);
    $stmt->bindParam('offset_acct', $offsetAcct);
    $stmt->bindParam('offset_acct_name', $offsetAcctName);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $mainDB->lastInsertId();
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function getIdCustomerTable($offset_acct)
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT id FROM remaining_budget_customers WHERE offset_acct = $offset_acct";
    $stmt = $mainDB->prepare($sql);
    $stmt->execute();
    $result["status"] = "success";
    $result["data"] = $stmt->fetch()["id"];
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function insertRemainigBudgetValue($remainingBudgetCustomerId, $idcData)
{
  try {
    $mainDB = dbCon();

    if ($idcData["add_to"] === "invoice") {
      $sql = "INSERT INTO remaining_budget_value
            (
              remaining_budget_customer_id,
              invoice,
              month,
              year
            )
            VALUES
            (
              :remaining_budget_customer_id,
              :invoice,
              :month,
              :year
            )";
    } else if ($idcData["add_to"] === "receive") {
      $sql = "INSERT INTO remaining_budget_value
              (
                remaining_budget_customer_id,
                receive,
                month,
                year
              )
              VALUES
              (
                :remaining_budget_customer_id,
                :receive,
                :month,
                :year
              )";
    } else if ($idcData["add_to"] === "ads_credit_note") {
      $sql = "INSERT INTO remaining_budget_value
              (
                remaining_budget_customer_id,
                ads_credit_note,
                month,
                year
              )
              VALUES
              (
                :remaining_budget_customer_id,
                :ads_credit_note,
                :month,
                :year
              )";
    }

    $stmt = $mainDB->prepare($sql);

    if ($idcData["add_to"] === "invoice") {
      $stmt->bindParam('invoice', $idcData["credit_debit_total"]);
    } else if ($idcData["add_to"] === "receive") {
      $stmt->bindParam('receive', $idcData["credit_debit_total"]);
    } else if ($idcData["add_to"] === "ads_credit_note") {
      $stmt->bindParam('ads_credit_note', $idcData["credit_debit_total"]);
    }

    $stmt->bindParam('remaining_budget_customer_id', $remainingBudgetCustomerId);
    $stmt->bindParam('month', $idcData["month"]);
    $stmt->bindParam('year', $idcData["year"]);

    $stmt->execute();

    $result["status"] = "success";
    $result["data"] = "";
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}

function updateRemainigBudgetValue($remainingBudgetCustomerId, $idcData)
{
  try {
    $mainDB = dbCon();

    $sql = "UPDATE remaining_budget_value ";

    if ($idcData["add_to"] === "invoice") {
      $sql .= "SET invoice = :invoice ";
    } else if ($idcData["add_to"] === "receive") {
      $sql .= "SET receive = :receive ";
    } else if ($idcData["add_to"] === "ads_credit_note") {
      $sql .= "SET ads_credit_note = :ads_credit_note ";
    }

    $sql .= "WHERE remaining_budget_customer_id = :remaining_budget_customer_id AND month = :month AND year = :year";

    echo "<br>" . $sql . "<br>";

    $stmt = $mainDB->prepare($sql);

    if ($idcData["add_to"] === "invoice") {
      $stmt->bindParam('invoice', $idcData["credit_debit_total"]);
    } else if ($idcData["add_to"] === "receive") {
      $stmt->bindParam('receive', $idcData["credit_debit_total"]);
    } else if ($idcData["add_to"] === "ads_credit_note") {
      $stmt->bindParam('ads_credit_note', $idcData["credit_debit_total"]);
    }

    $stmt->bindParam('remaining_budget_customer_id', $remainingBudgetCustomerId);
    $stmt->bindParam('month', $idcData["month"]);
    $stmt->bindParam('year', $idcData["year"]);

    $stmt->execute();

    $result["status"] = "success";
    $result["data"] = "";
  } catch (PDOException $e) {
    $result["status"] = "error";
    $result["data"] = $e->getMessage();
  }
  $mainDB = null;
  return $result;
}
