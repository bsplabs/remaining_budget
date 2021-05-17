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
                offset_acct_name
              )
              VALUES (
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :offset_acct,
                :offset_acct_name
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("grandadmin_customer_id", $offsetAcct);
      $stmt->bindParam("grandadmin_customer_name", $offsetAcctName);
      $stmt->bindParam("offset_acct", $offsetAcct);
      $stmt->bindParam("offset_acct_name", $offsetAcctName);

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

  public function getReconcileData($year, $month)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT c.company,c.payment_method,c.parent_id,c.grandadmin_customer_id,c.grandadmin_customer_name,
      c.offset_acct,c.offset_acct_name,r.id as report_id,
      sum(r.last_month_remaining) as last_month_remaining, sum(r.adjustment_remain) as adjustment_remain, sum(r.receive) as `receive`, sum(r.invoice) as invoice, sum(r.transfer) as `transfer`,
      sum(r.ads_credit_note) as ads_credit_note, sum(r.spending_invoice) as spending_invoice, sum(r.adjustment_free_click_cost) as adjustment_free_click_cost, sum(r.adjustment_free_click_cost_old) as adjustment_free_click_cost_old,
      sum(r.adjustment_cash_advance) as adjustment_cash_advance,sum(r.adjustment_max) as adjustment_max,sum(r.cash_advance) as cash_advance, sum(r.remaining_ice) as remaining_ice,
      sum(r.wallet) as wallet, sum(r.wallet_free_click_cost) as wallet_free_click_cost, sum(r.withholding_tax) as withholding_tax, sum(r.adjustment_front_end) as adjustment_front_end,
      sum(r.remaining_budget) as remaining_budget, sum(r.difference) as difference,r.note as note,
      count(c.parent_id) as amount FROM remaining_budget_report r LEFT JOIN remaining_budget_customers c ON c.id = r.remaining_budget_customer_id WHERE r.year = :year and r.month = :month GROUP BY c.parent_id ORDER BY c.parent_id ASC LIMIT 100;";
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
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report SET adjustment_remain = :value, adjustment_remain_note = :note where id = :report_id;";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("value", $value);
      $stmt->bindParam("note", $note);
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
}
