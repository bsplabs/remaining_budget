<?php

class FreeClickCostResources
{
  protected $db;
  protected $remainingBudgetCustomer;

  public function __construct()
  {
    //echo "===================> Free Click Cost Resources <==================== \n";
    $this->db = new Database();
    $this->remainingBudgetCustomer = new RemainingBudgetCustomer();
  }

  public function getFreeClickCost($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon('latin1');
      $sql1 = " SELECT * 
                FROM ready_topup_wallet
                WHERE (`InsertDate` LIKE :date_target) 
                  AND Income > 0 AND newmemberid > 0
                GROUP BY CustomerID, Service
                ORDER BY `InsertDate` ASC
              ";
      $stmt1 = $mainDB->prepare($sql1);
      $dateTarget = $year . "-" . $month . "%";
      $stmt1->bindParam('date_target', $dateTarget);
      $stmt1->execute();

      $allFreeClickCost = array();
      $i = 0;
      while ($customerWallet = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $allFreeClickCost[$i] = $customerWallet;
        $sql2 = " SELECT 
                    a.*,
                    b.coupon_rp, 
                    b.pay_on_date, 
                    b.clickcost_paygoogle
                  FROM ready_topup_wallet AS a
                  INNER JOIN ready_office_clearing AS b ON a.newmemberid = b.newmemberid
                  WHERE a.CustomerID = '{$customerWallet["CustomerID"]}'
                    AND a.Service = '{$customerWallet["Service"]}'
                    AND b.newmemberid = '{$customerWallet["newmemberid"]}' 
                    AND a.Income > 0
                ";
        $stmt2 = $mainDB->prepare($sql2);
        $stmt2->execute();

        while ($officeClearing = $stmt2->fetch(PDO::FETCH_ASSOC)) {
          $allFreeClickCost[$i]["coupon_rp"] = $officeClearing["coupon_rp"];
          $allFreeClickCost[$i]["pay_on_date"] = $officeClearing["pay_on_date"];
          $allFreeClickCost[$i]["clickcost_paygoogle"] = $officeClearing["clickcost_paygoogle"];
          $sql3 = " SELECT
                      a.newmemberid AS clearing_id,
                      b.bill_firstname,
                      b.bill_lastname,
                      b.bill_company,
                      b.taxID_corporation,
                      b.cardID
                    FROM ready_topup_wallet AS a 
                    INNER JOIN ready_new_members AS b ON a.newmemberid = b.ID
                    WHERE b.CustomerID = '{$customerWallet["CustomerID"]}' 
                      AND a.Service = '{$customerWallet["Service"]}' 
                      AND a.newmemberid != '0' 
                    ORDER BY a.ID DESC 
                    LIMIT 1
                  ";
          $stmt3 = $mainDB->prepare($sql3);
          $stmt3->execute();
          $tempData = $stmt3->fetch(PDO::FETCH_ASSOC);

          if (!empty($tempData)) {
            if (empty($tempData["bill_company"])) {
              $allFreeClickCost[$i]["grandadmin_customer_name"] = iconv('TIS-620','UTF-8',$tempData["bill_firstname"]) . " " . iconv('TIS-620','UTF-8',$tempData["bill_lastname"]);
            } else {
              $allFreeClickCost[$i]["grandadmin_customer_name"] = iconv('TIS-620','UTF-8',$tempData["bill_company"]);
            }
            $allFreeClickCost[$i]["clearing_id"] = $tempData["clearing_id"];
          } else {
            $allFreeClickCost[$i]["grandadmin_customer_name"] = "";
            $allFreeClickCost[$i]["clearing_id"] = "";
          }

        }

        $i++;
      }

      $result["status"] = "success";
      $result["data"] = $allFreeClickCost;
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkFreeClickCostDataExists($month, $year, $valueFreeClickCost)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) AS total 
              FROM remaining_budget_free_click_cost 
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND grandadmin_customer_name = :grandadmin_customer_name
                AND service = :service";

      $stmt = $mainDB->prepare($sql);
      
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $valueFreeClickCost["CustomerID"]);
      $stmt->bindParam("grandadmin_customer_name", $valueFreeClickCost["grandadmin_customer_name"]);
      $stmt->bindParam("service", $valueFreeClickCost["Service"]);
      
      $stmt->execute();

      $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
      $rowCount = $fetchRowCount["total"];
      $result["status"] = "success";
      $result["data"] = $rowCount;
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function insertFreeClickCost($month, $year, $valueFreeClickCost)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_free_click_cost (
                remaining_budget_customer_id,
                month,
                year,
                grandadmin_customer_id,
                clearing_id,
                pay_date,
                grandadmin_customer_name,
                service,
                coupon
              ) VALUES (
                :remaining_budget_customer_id,
                :month,
                :year,
                :grandadmin_customer_id,
                :clearing_id,
                :pay_date,
                :grandadmin_customer_name,
                :service,
                :coupon
              )";
      
      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("remaining_budget_customer_id", $valueFreeClickCost["remaining_budget_customer_id"]);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $valueFreeClickCost["CustomerID"]);
      $stmt->bindParam("clearing_id", $valueFreeClickCost["clearing_id"]);
      $stmt->bindParam("pay_date", $valueFreeClickCost["pay_on_date"]);
      $stmt->bindParam("grandadmin_customer_name", $valueFreeClickCost["grandadmin_customer_name"]);
      $stmt->bindParam("service", $valueFreeClickCost["Service"]);
      $stmt->bindParam("coupon", $valueFreeClickCost["coupon_rp"]);

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

  public function updateFreeClickCost($month, $year, $valueFreeClickCost)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_free_click_cost
              SET remaining_budget_customer_id = :remaining_budget_customer_id,
                  clearing_id = :clearing_id,
                  pay_date = :pay_date,
                  coupon = :coupon,
                  updated_at = NOW()
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND grandadmin_customer_name = :grandadmin_customer_name
                AND service = :service";
    
      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("remaining_budget_customer_id", $valueFreeClickCost["remaining_budget_customer_id"]);
      $stmt->bindValue("clearing_id", $valueFreeClickCost["clearing_id"]);
      $stmt->bindValue("pay_date", $valueFreeClickCost["pay_on_date"]);
      $stmt->bindValue("coupon", $valueFreeClickCost["coupon_rp"]);
      // Where
      $stmt->bindValue("month", $month);
      $stmt->bindValue("year", $year);
      $stmt->bindValue("grandadmin_customer_id", $valueFreeClickCost["CustomerID"]);
      $stmt->bindValue("grandadmin_customer_name", $valueFreeClickCost["grandadmin_customer_name"]);
      $stmt->bindValue("service", $valueFreeClickCost["Service"]);

      $stmt->execute();
      $resutl["status"] = "success";
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
      $sql = "SELECT * FROM remaining_budget_report_status WHERE overall_status != 'completed' ORDER BY year ASC, month ASC, id ASC Limit 1;";
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

  public function updateReportStatusById($id, $resource_type, $status)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status SET {$resource_type} = '{$status}' WHERE id = :id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("id", $id);
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

  public function clearFreeClickCostByMonthYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
     
      $sql = "DELETE FROM remaining_budget_free_click_cost WHERE month = :month AND year = :year";
      
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

  public function run()
  {
    $get_month_year = $this->getMonthYearFromReportStatusTable();
  
    foreach ($get_month_year["data"] as $key => $val) 
    {
      if($val["free_click_cost"] == 'pending'){
        $get_free_click_cost = $this->getFreeClickCost($val["month"], $val["year"]);

        if (empty($get_free_click_cost["data"])){
          $this->updateReportStatusById($val["id"], "free_click_cost", "waiting");
          continue;
        } 
        $this->updateReportStatusById($val["id"], "free_click_cost", "in_progress");
        // clear free click cost data by month and year
        $this->clearFreeClickCostByMonthYear($val["month"], $val["year"]);
  
        foreach ($get_free_click_cost["data"] as $free_click_cost) 
        {
          $customerData = array(
            "grandadmin_customer_id" => $free_click_cost["CustomerID"] ? $free_click_cost["CustomerID"] : "",
            "grandadmin_customer_name" => $free_click_cost["grandadmin_customer_name"] ? $free_click_cost["grandadmin_customer_name"] : ""
          );
          $getRemainingBudgetCustomerID = $this->remainingBudgetCustomer->getRemainingBudgetCustomerID($customerData);
          $free_click_cost["remaining_budget_customer_id"] = $getRemainingBudgetCustomerID;
  
          // insert free click cost
          $this->insertFreeClickCost($val["month"], $val["year"], $free_click_cost); 
        }
  
        $this->updateReportStatusById($val["id"], "free_click_cost", "waiting");
        $is_last_process = $this->remainingBudgetCustomer->checkLastProcess($val["id"]);
        if($is_last_process){
          $this->updateReportStatusById($val["id"], "overall_status", "waiting");
        }
      }
      
    
    }

  }
}
