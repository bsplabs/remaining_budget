<?php

class Data
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function updateGLRevenue($dataSet)
  {

  }

  public function insertGLRevenue($dataSet)
  {
    try {
      $mainDB = $this->db->dbCon();
      
      $sql = "INSERT INTO remaining_budget_gl_revenue (
                posting_date,
                due_date,
                series,
                doc_no,
                trans_no,
                gl_code,
                remarks,
                offset_acct,
                offset_acct_name,
                indicator,
                debit_lc,
                credit_lc,
                cumulative_balance_lc,
                series_code
              )
              VALUES (
                :posting_date,
                :due_date,
                :series,
                :doc_no,
                :trans_no,
                :gl_code,
                :remarks,
                :offset_acct,
                :offset_acct_name,
                :indicator,
                :debit_lc,
                :credit_lc,
                :cumulative_balance_lc,
                :series_code
              )";

      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("posting_date", $dataSet["posting_date"]);     
      $stmt->bindParam("due_date", $dataSet["due_date"]);     
      $stmt->bindParam("series", $dataSet["series"]);     
      $stmt->bindParam("doc_no",  $dataSet["doc_no"]);     
      $stmt->bindParam("trans_no", $dataSet["trans_no"]);
      $stmt->bindParam("gl_code", $dataSet["gl_code"]);
      $stmt->bindParam("remarks", $dataSet["remarks"]);    
      $stmt->bindParam("offset_acct", $dataSet["offset_acct"]);     
      $stmt->bindParam("offset_acct_name", $dataSet["offset_acct_name"]);
      $stmt->bindParam("indicator", $dataSet["indicator"]);
      $stmt->bindParam("debit_lc", $dataSet["debit_lc"]);
      $stmt->bindParam("credit_lc", $dataSet["credit_lc"]);
      $stmt->bindParam("cumulative_balance_lc", $dataSet["cumulative_balance_lc"]);
      $stmt->bindParam("series_code", $dataSet["series_code"]);

      $stmt->execute();

      $result["status"] = "success";
      $result["data"]["row_affected"] = $stmt->rowCount(); 

    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getRemainigBugetByMonthAndYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "SELECT rbv.*, rbc.* 
              FROM remaining_budget_value AS rbv
              LEFT JOIN remaining_budget_customers AS rbc ON rbv.remaining_budget_customer_id = rbc.id
              WHERE rbv.month = :month AND rbv.year = :year";

      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);

      $stmt->execute();

      // $result["data"] = $stmt->fetchAll(PDO::FETCH_OBJ);
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $result["status"] = "success";
      
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }
}
