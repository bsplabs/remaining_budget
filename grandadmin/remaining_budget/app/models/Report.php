<?php

class Report
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function getBasicRemainingBudgetValue()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT rbv.*, rbc.* FROM remaining_budget_value AS rbv 
              LEFT JOIN remaining_budget_customers AS rbc ON rbv.remaining_budget_customer_id = rbc.id
              LIMIT 50";
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function gropParentID()
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "SELECT parent_id, COUNT(offset_acct) as cus_id_total
              FROM remaining_budget_customers
              GROUP BY parent_id
            ";
      // HAVING COUNT(offset_acct) > 1

      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $parentDataSet = array();
      while ($parentId = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // print_r($parentId);
        if ($parentId["cus_id_total"] > 1 && $parentId["parent_id"] != NULL) {
          $parentDataSet[$parentId["parent_id"]] = array();
          $sql2 = "SELECT * FROM remaining_budget_customers WHERE parent_id = '{$parentId["parent_id"]}'";
          $stmt2 = $mainDB->prepare($sql2);
          $stmt2->execute();
          $parentDataSet[$parentId["parent_id"]] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
      }

      foreach ($parentDataSet as $keyParentId => $parentData) {
        foreach ($parentData as $ind => $customerData) {
          $sql3 = "SELECT * FROM remaining_budget_value WHERE remaining_budget_customer_id = '{$customerData["id"]}'";
          $stmt3 = $mainDB->prepare($sql3);
          $stmt3->execute();
          $res = $stmt3->fetch(PDO::FETCH_ASSOC);
          if ($res) {
            $parentDataSet[$keyParentId][$ind] = array_merge($parentDataSet[$keyParentId][$ind], $res);
          }
        }
      }

      $result["status"] = "success";
      $result["data"] = $parentDataSet;
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function clearGLCashAdvance($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "DELETE FROM remaining_budget_gl_cash_advance WHERE month = :month AND year = :year";
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

  public function checkOffsetAcct($idcKey)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT id FROM remaining_budget_customers WHERE offset_acct = :offset_acct";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("offset_acct", $idcKey);
      $stmt->execute();
      $result["status"] = "success";
      // $result["data"] = $stmt->fetchColumn();
      $result = array();
      $data = $stmt->fetch();
      $result["data"] = $data["id"];
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkRemainingBudget($remainingBudgetCustomerId, $idcKey, $idcData)
  {
    try {
      $mainDB = $this->db->dbCon();

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
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function addOffsetAcct($offsetAcct, $offsetAcctName)
  {
    try {
      $mainDB = $this->db->dbCon();
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
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getIdCustomerTable($offset_acct)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT id FROM remaining_budget_customers WHERE offset_acct = $offset_acct";
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetch();
      $result["data"] = $result["data"]["id"];
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function insertRemainigBudgetValue($remainingBudgetCustomerId, $idcData)
  {
    try {
      $mainDB = $this->db->dbCon();

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
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function updateRemainigBudgetValue($remainingBudgetCustomerId, $idcData)
  {
    try {
      $mainDB = $this->db->dbCon();

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
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkCustomerExists($offsetAcct, $offsetAcctName)
  {
    try {
      $mainDB = $this->db->dbCon();

      $offsetAcctNameWhere = "";
      if (is_null($offsetAcctName) || empty($offsetAcctName)) {
        $offsetAcctNameWhere = " OR offset_acct IS NULL";
      }

      $sql = "SELECT id 
              FROM remaining_budget_customers 
              WHERE (offset_acct = :offset_acct) AND (offset_acct_name = :offset_acct_name{$offsetAcctNameWhere})";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("offset_acct", $offsetAcct);
      $stmt->bindParam("offset_acct_name", $offsetAcctName);

      $stmt->execute();

      $result["status"] = "success";
      $customerID = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($customerID["id"]) || empty($customerID)) {
        $result["data"] = "";
      } else {
        $result["data"] = $customerID["id"];
      }
    
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function insertNewCustomer($offsetAcct, $offsetAcctName) 
  {
    try {
      $mainDB = $this->db->dbCon();
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
      $stmt->bindParam("gci", $offsetAcct);
      $stmt->bindParam("gcn", $offsetAcctName);
      $stmt->bindValue("company","RPTH");
      $stmt->bindParam("parent_id", $offsetAcct);
      $stmt->bindValue("payment_method", "prepaid");
      $stmt->bindValue("updated_by", "script");
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $mainDB->lastInsertId();
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function insertGLCashAdvance($glCashAdvance)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_gl_cash_advance (
                remaining_budget_customer_id,
                posting_date,
                due_date,
                series,
                doc_no,
                trans_no,
                gl_code,
                remarks,
                offset_acct,
                offset_acct_name,
                debit_lc,
                credit_lc,
                cumulative_balance_lc,
                series_code,
                month,
                year
              )
              VALUES (
                :remaining_budget_customer_id,
                :posting_date,
                :due_date,
                :series,
                :doc_no,
                :trans_no,
                :gl_code,
                :remarks,
                :offset_acct,
                :offset_acct_name,
                :debit_lc,
                :credit_lc,
                :cumulative_balance_lc,
                :series_code,
                :month,
                :year
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("remaining_budget_customer_id", $glCashAdvance["remaining_budget_customer_id"]);
      $stmt->bindParam("posting_date", $glCashAdvance["posting_date"]);
      $stmt->bindParam("due_date", $glCashAdvance["due_date"]);
      $stmt->bindParam("series", $glCashAdvance["series"]);
      $stmt->bindParam("doc_no", $glCashAdvance["doc_no"]);
      $stmt->bindParam("trans_no", $glCashAdvance["trans_no"]);
      $stmt->bindParam("gl_code", $glCashAdvance["gl_code"]);
      $stmt->bindParam("remarks", $glCashAdvance["remarks"], PDO::PARAM_STR);
      $stmt->bindParam("offset_acct", $glCashAdvance["offset_acct"]);
      $stmt->bindParam("offset_acct_name", $glCashAdvance["offset_acct_name"]);
      $stmt->bindParam("debit_lc", $glCashAdvance["debit_lc"]);
      $stmt->bindParam("credit_lc", $glCashAdvance["credit_lc"]);
      $stmt->bindParam("cumulative_balance_lc", $glCashAdvance["cumulative_balance_lc"]);
      $stmt->bindParam("series_code", $glCashAdvance["series_code"]);
      $stmt->bindParam("month", $glCashAdvance["month"]);
      $stmt->bindParam("year", $glCashAdvance["year"]);

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

  public function getReporttStatus($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "SELECT overall_status FROM remaining_budget_report_status WHERE type = 'default' AND month = :month AND year = :year";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
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

  public function getNotCompleteReportUpdateStatus($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "SELECT count(*) as total FROM remaining_budget_report_status WHERE type = 'update' AND month = :month AND year = :year AND overall_status != 'completed' limit 1;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();

      $result["status"] = "success";
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      if($data["total"] > 0){
        $result["data"] = true;
      }else{
        $result["data"] = false;
      }

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = false;
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkThisMonthJob($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total
              FROM remaining_budget_report_status
              WHERE month = :month and year = :year;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      if($data["total"] > 0){
        $result = true;
      }else{
        $result = false;
      }
    } catch (PDOException $e) {
      $result = true;
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getMonthYearLists()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT month, year FROM remaining_budget_report_status WHERE type = 'default' ORDER BY year ASC, month ASC";
      $stmt = $mainDB->prepare($sql);
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

  public function getDefaultReportStatusByMonthYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_report_status WHERE type = 'default' AND month = :month AND year = :year limit 1";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
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

  public function getReconcileData($year, $month)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT c.company,c.payment_method,c.parent_id,c.grandadmin_customer_id,c.grandadmin_customer_name,
      c.offset_acct,c.offset_acct_name,r.id as report_id,
      sum(r.last_month_remaining) as last_month_remaining, sum(r.adjustment_remain) as adjustment_remain, r.adjustment_remain_note, sum(r.receive) as `receive`, sum(r.invoice) as invoice, sum(r.transfer) as `transfer`,
      sum(r.ads_credit_note) as ads_credit_note, sum(r.spending_invoice) as spending_invoice, sum(r.adjustment_free_click_cost) as adjustment_free_click_cost, r.adjustment_free_click_cost_note, sum(r.adjustment_free_click_cost_old) as adjustment_free_click_cost_old,r.adjustment_free_click_cost_old_note,
      sum(r.adjustment_cash_advance) as adjustment_cash_advance,r.adjustment_cash_advance,sum(r.adjustment_max) as adjustment_max,r.adjustment_max,sum(r.cash_advance) as cash_advance, sum(r.remaining_ice) as remaining_ice,
      sum(r.wallet) as wallet, sum(r.wallet_free_click_cost) as wallet_free_click_cost, sum(r.withholding_tax) as withholding_tax, sum(r.adjustment_front_end) as adjustment_front_end,r.adjustment_front_end_note,
      sum(r.remaining_budget) as remaining_budget, sum(r.difference) as difference,r.note as note,
      count(c.parent_id) as amount FROM remaining_budget_report r LEFT JOIN remaining_budget_customers c ON c.id = r.remaining_budget_customer_id WHERE r.year = :year and r.month = :month and c.parent_id is not null GROUP BY c.parent_id ORDER BY c.parent_id ASC LIMIT 100;";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("month", $month);
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

  public function getReconcileDataByParent($parent_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *,r.id as report_id FROM remaining_budget_report r LEFT JOIN remaining_budget_customers c 
      ON c.id = r.remaining_budget_customer_id WHERE c.parent_id = :parent_id ORDER BY c.id ASC";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("parent_id", $parent_id);
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

  public function getReconcileDataByReportId($report_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_report r  WHERE r.id = :report_id limit 1 ";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("report_id", $report_id);
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

  
  public function updateReportData($report_id,$value,$note,$type)
  {
    $types = array("adjustment_remain","adjustment_free_click_cost","adjustment_free_click_cost_old","adjustment_cash_advance", "adjustment_max", "adjustment_front_end");
    $type_note = $type."_note";
    try {
      if(in_array($type,$types)){
        $mainDB = $this->db->dbCon();
        $sql = "UPDATE remaining_budget_report SET {$type} = :value, {$type_note} = :note where id = :report_id;";
        $stmt = $mainDB->prepare($sql);
        $stmt->bindParam("value", $value);
        $stmt->bindParam("note", $note);
        $stmt->bindParam("report_id", $report_id);
        $stmt->execute();
        $result["status"] = "success";
        $result["data"] = "";
      }else{
        $result["status"] = "fail";
        $result["data"] = "wrong type";
      }
      

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function updateReconcileReCalculateByReportId($report_id,$cash_advance,$remaining_budget,$difference)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report SET cash_advance = :cash_advance, remaining_budget = :remaining_budget, `difference` = :difference where id = :report_id;";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("cash_advance", $cash_advance);
      $stmt->bindParam("remaining_budget", $remaining_budget);
      $stmt->bindParam("difference", $difference);
      $stmt->bindParam("report_id", $report_id);
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

  public function getReportByQuery($filter_cash_advance,$filter_remaining_budget,$filter_difference,$search,$condition)
  {
    $result["header"] = array(
      "Remaining Budget ID", "Parent ID", "Customer ID", "Custormer Name", "Customer Acct", "Customer Acct Name",
    "Company", "Payment Method", "Remaining ทางบัญชี", "Adjust ยอดยกมา", "หมายเหตุ adjust ยอดยกมา" ,"Receive", "Invoice", "Transfer (โอนเงินระหว่างบัญชี)",
    "คืนเงินค่าโฆษณา",
    "Spending (-)",
    "JE + Free Clickcost",
    "หมายเหตุ JE + Free Clickcost",
    "Free Clickcost (ค่าใช้จ่ายต้องห้าม)",
    "หมายเหตุ Free Clickcost (ค่าใช้จ่ายต้องห้าม)",
    "Adjustment",
    "หมายเหตุ Adjustment",
    "Max",
    "หมายเหตุ Max",
    "Cash Advance",
    "Remaining ICE",
          "Wallet",
          "Wallet - Free Clickcost (-)",
          "Withholding Tax",
          "Adjust",
          "หมายเหตุ Adjust",
          "Remaining Budget",
          "Difference",
          "Note"
    );
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT r.id,c.parent_id,c.grandadmin_customer_id,c.grandadmin_customer_name,c.offset_acct,c.offset_acct_name,c.company,c.payment_method,
      r.last_month_remaining,r.adjustment_remain,r.adjustment_remain_note,r.receive,r.invoice,r.transfer,r.ads_credit_note,
      r.spending_invoice as spending,r.adjustment_free_click_cost,r.adjustment_free_click_cost_note,r.adjustment_free_click_cost_old,r.adjustment_free_click_cost_old_note,
      r.adjustment_cash_advance,r.adjustment_cash_advance_note,r.adjustment_max,r.adjustment_max_note,r.cash_advance,
      r.remaining_ice,r.wallet,r.wallet_free_click_cost,r.withholding_tax,r.adjustment_front_end,r.adjustment_front_end_note,r.remaining_budget,
      r.difference,r.note FROM remaining_budget_report r LEFT JOIN remaining_budget_customers c ON r.remaining_budget_customer_id = c.id";
      if($condition != ""){
        $sql .= " WHERE ".$condition;
      }
      $sql .= " Limit 50";

      $stmt = $mainDB->prepare($sql);
      if($condition != ""){
        if($filter_cash_advance != ""){
          $stmt->bindParam("cash_advance", $filter_cash_advance);
        }
        if($filter_remaining_budget != ""){
          $stmt->bindParam("remaining_budget", $filter_remaining_budget);
        }
        if($filter_difference != ""){
          $stmt->bindParam("difference", $filter_difference);
        }
        if($search != ""){
          $stmt->bindParam("search", $search);
        }
        
      }
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


  public function createUpdateWorker($input_type,$month,$year,$updated_by){
    try {
      $types = array("cash_advance","media_wallet","withholding_tax", "free_click_cost", "google_spending",
      "facebook_spending","remaining_ice", "gl_cash_advance","transfer");
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_report_status (
                `month`,
                `year`,
                cash_advance,
                media_wallet,
                withholding_tax,
                free_click_cost,
                google_spending,
                facebook_spending,
                remaining_ice,
                gl_cash_advance,
                `transfer`,
                `type`,
                overall_status,
                created_at,
                updated_at,
                updated_by

              )
              VALUES (
                :month,
                :year,
                :cash_advance,
                :media_wallet,
                :withholding_tax,
                :free_click_cost,
                :google_spending,
                :facebook_spending,
                :remaining_ice,
                :gl_cash_advance,
                :transfer,
                :type,
                :overall_status,
                now(),
                now(),
                :updated_by
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      foreach ($types as $type){
        if($input_type == $type){
          $stmt->bindValue($type, 'pending');
        }else{
          $stmt->bindValue($type, 'no');
        }
      }
      
      $stmt->bindValue("type", 'update');
      $stmt->bindValue("overall_status", 'pending');
      $stmt->bindValue("updated_by", $updated_by);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = $mainDB->lastInsertId();
    
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  function checkClosed($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total FROM remaining_budget_close_period where month = :month and year = :year";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $result_data = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["status"] = "success";
      if($result_data["total"] > 0){
        $result["data"] = true;
      }else{
        $result["data"] = false;
      }
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  function closePeriod($month, $year, $created_by)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_close_period (month, year, created_by) VALUES (:month, :year, :created_by)";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("created_by", $created_by);
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

  public function clearGlCashAdvanceByID($month, $year, $remaining_budget_customer_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "DELETE FROM remaining_budget_gl_cash_advance WHERE month = :month AND year = :year AND remaining_budget_customer_id = :remaining_budget_customer_id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("remaining_budget_customer_id", $remaining_budget_customer_id);
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
}
