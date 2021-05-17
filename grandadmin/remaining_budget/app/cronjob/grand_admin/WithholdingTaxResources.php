<?php


class WithholdingTaxResources
{
  private $db;
  private $remainingBudgetCustomer;

  public function __construct()
  {
    $this->db = new Database();
    $this->remainingBudgetCustomer = new RemainingBudgetCustomer();
    // echo "===================> Withholding Tax Resources <==================== \n";
  }

  public function getWithholdingTax($insertDateTarget)
  {
    try {
      $mainDB = $this->db->dbCon('latin1');
      $sql = "SELECT *
              FROM ready_topup_withholding_tax
              WHERE InsertDate < :insertDate
                AND Status LIKE 'wait'
              ORDER BY 'ID' ASC";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("insertDate", $insertDateTarget);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getCustomerName($withholdingTax)
  {
    try {
      $mainDB = $this->db->dbCon("latin1");
      $sql = "SELECT 
                bill_firstname,
                bill_lastname,
                bill_company
              FROM ready_new_members 
              WHERE ID = :ID 
                AND CustomerID = :CustomerID";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("ID", $withholdingTax["newmemberid"], PDO::PARAM_INT);
      $stmt->bindParam("CustomerID", $withholdingTax["CustomerID"], PDO::PARAM_INT);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkWithholdingTaxExists($month, $year, $withholdingTax)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) AS row_count
              FROM remaining_budget_withholding_tax
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND service = :service
             ";

      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $withholdingTax["CustomerID"]);
      $stmt->bindParam("service", $withholdingTax["Service"]);

      $stmt->execute();
      $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
      $rowCount = $fetchRowCount["row_count"];
      $result["status"] = "success";
      if ($rowCount > 0) {
        $result["data"] = TRUE;
      } else {
        $result["data"] = FALSE;
      }
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function updateWithholdingTax($month, $year, $withholdingTax)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_withholding_tax
              SET remaining_budget_customer_id = :remaining_budget_customer_id,
                  clearing_id = :clearing_id,
                  amount = :amount,
                  wallet_insert_date,
                  wait = :wait,
                  admin_name = :admin_name,
                  updated_at = NOW()
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND service = :service
              ";
      $stmt = $mainDB->prepare($sql);
 
      $stmt->bindParam("remaining_budget_customer_id", $withholdingTax["remaining_budget_customer_id"]);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("clearing_id", $withholdingTax["newmemberid"]);
      $stmt->bindParam("amount", $withholdingTax["Amount"]);
      $stmt->bindParam("wallet_insert_data", $withholdingTax["InsertDate"]);
      if ($withholdingTax["Status"] == "wait") {
        $stmt->bindValue("wait", TRUE, PDO::PARAM_BOOL);
      } else {
        $stmt->bindValue("wait", FALSE, PDO::PARAM_BOOL);
      }
      $stmt->bindParam("admin_name", $withholdingTax["Admin"]);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = "";
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function insertWithholdingTax($month, $year, $withholdingTax)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_withholding_tax 
              (
                remaining_budget_customer_id,
                month,
                year,
                grandadmin_customer_id,
                grandadmin_customer_name,
                clearing_id,
                service,
                amount,
                wallet_insert_date,
                wait,
                admin_name
              )
              VALUES 
              (
                :remaining_budget_customer_id,
                :month,
                :year,
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :clearing_id,
                :service,
                :amount,
                :wallet_insert_date,
                :wait,
                :admin_name
              )";

      $stmt = $mainDB->prepare($sql);

      print_r($withholdingTax["remaining_budget_customer_id"]);
      $stmt->bindParam("remaining_budget_customer_id", $withholdingTax["remaining_budget_customer_id"]);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $withholdingTax["CustomerID"]);
      $stmt->bindParam("grandadmin_customer_name", $withholdingTax["grandadmin_customer_name"]);
      $stmt->bindParam("clearing_id", $withholdingTax["newmemberid"]);
      $stmt->bindParam("service", $withholdingTax["Service"]);
      $stmt->bindParam("amount", $withholdingTax["Amount"]);
      $stmt->bindParam("wallet_insert_date", $withholdingTax["InsertDate"]);
      $wait_status = false;
      if ($withholdingTax["Status"] == "wait") {
        $wait_status = true;
      }
      $stmt->bindParam("wait", $wait_status);
      $stmt->bindParam("admin_name", $withholdingTax["Admin"]);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = "";

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getMonthYearFromReportStatusTable()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT month, year FROM remaining_budget_report_status WHERE type = 'default' AND withholding_tax = 'pending' ORDER BY year ASC, month ASC Limit 1;";
      $stmt = $mainDB->query($sql);
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function createReportStatus($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_report_status (month, year) VALUES (:month, :year)";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = "";

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function updateReportStatus($month, $year, $resource_type, $status)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status SET {$resource_type} = '{$status}' WHERE month = '{$month}' AND year = '{$year}' AND type = 'default'";
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = "";

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  function run()
  {
    $last_month_timestamp =  strtotime("-1 month");
    $primary_month = date("m", $last_month_timestamp);
    $primary_year = date("Y", $last_month_timestamp);

    $get_month_year = $this->getMonthYearFromReportStatusTable();
    if (empty($get_month_year["data"])) {
      $this->createReportStatus($primary_month, $primary_year);
      $get_month_year["data"][0] = array(
        "month" => $primary_month,
        "year" => $primary_year
      );
    }

    foreach ($get_month_year["data"] as $key => $val) {
      $sixthDateOfMonth = $val["year"] . "-" . $val["month"] . "-" . "06";
      $get_withholding_tax = $this->getWithholdingTax($sixthDateOfMonth);
      
      // echo "Month: {$val['month']}, Year: {$val['year']} ----------------------> \n";
      // echo "Founded: " . count($get_withholding_tax["data"]) . "\n";
      // echo "\n";
      // // print_r($get_withholding_tax);
      // echo "\n------------------------------------------------------------------\n";
      
      if (empty($get_withholding_tax["data"])) continue;
      
      foreach ($get_withholding_tax["data"] as $withholding_tax) {
        $getCustomerName = $this->getCustomerName($withholding_tax);
        if ($getCustomerName["status"] == "success" && !empty($getCustomerName["data"])) {
          if ($getCustomerName["data"]["bill_company"] !== "") {
            $withholding_tax["grandadmin_customer_name"] = iconv('TIS-620','UTF-8',$getCustomerName["data"]["bill_company"]);
          } else if ($getCustomerName["data"]["bill_firstname"] !== "" || $getCustomerName["data"]["bill_lastname"] !== "") {
            $withholding_tax["grandadmin_customer_name"] = iconv('TIS-620','UTF-8',$getCustomerName["data"]["bill_firstname"]) . " " . iconv('TIS-620','UTF-8',$getCustomerName["data"]["bill_lastname"]);
          } else {
            $withholding_tax["grandadmin_customer_name"] = "";
          }
        } else {
          $withholding_tax["grandadmin_customer_name"] = "";
        }

        $customerData = array(
          "grandadmin_customer_id" => $withholding_tax["CustomerID"],
          "grandadmin_customer_name" => $withholding_tax["grandadmin_customer_name"]
        );
        $getRemainingCustomerID = $this->remainingBudgetCustomer->getRemainingBudgetCustomerID($customerData);
        $withholding_tax["remaining_budget_customer_id"] = $getRemainingCustomerID;
  
        $checkWithholdingTaxExists = $this->checkWithholdingTaxExists($val["month"], $val["year"], $withholding_tax);
        // print_r($checkWithholdingTaxExists);
        // echo "\n";
        if ($checkWithholdingTaxExists["data"]) {
          $update = $this->updateWithholdingTax($val["month"], $val["year"], $withholding_tax);
        } else {
          $insert = $this->insertWithholdingTax($val["month"], $val["year"], $withholding_tax);
        }  
      }

      $this->updateReportStatus($val["month"], $val["year"], "withholding_tax", "waiting");
    
    }
  }
}
