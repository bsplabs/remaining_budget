<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class CashAdvance 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $cash_advance_job = $this->getJob();
    $cash_advance_job = $cash_advance_job["data"];
    
    if($cash_advance_job['cash_advance'] == 'waiting'){
      $month = $cash_advance_job['month'];
      $last_month = date("m", strtotime("-1 month"));
      $year = $cash_advance_job['year'];
      $year_last_month = date("Y", strtotime("-1 month"));
      $check_first_report = $this->checkFirstReport();
      $prepare_report = $this->prepareReport($month,$year);
      if($check_first_report["data"]["row_count"] == 0){
        $cash_advance_raw_data = $this->getFirstRemaining();
      }else{
        $cash_advance_raw_data = $this->getLastMonth($last_month, $year_last_month);
      }
      
      foreach($cash_advance_raw_data["data"] as $cash_advance){
        $total = $cash_advance["total"];
        $remaining_budget_id = $cash_advance["remaining_budget_customer_id"];
        $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
      }
      $this->updateStatus('completed',$cash_advance_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE overall_status = 'waiting'
              ORDER BY month,year Limit 1";

      $stmt = $mainDB->prepare($sql);
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

  private function updateStatus($status,$id){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status SET cash_advance = :status where id = :id";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("status", $status);
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

  private function getRawData($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_cash_advance
              WHERE month = :month and year = :year and is_reconcile = false
              ORDER BY id limit 100;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
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

  private function moveToReport($total,$remaining_budget_id,$month,$year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET last_month_remaining = last_month_remaining + :total
              WHERE remaining_budget_customer_id = :remaining_budget_id and month = :month and year = :year
              ";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("total", $total);
      $stmt->bindParam("remaining_budget_id", $remaining_budget_id);
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

  private function markReconcile($id){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_cash_advance SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
              WHERE id = :id";

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

  private function prepareReport($month,$year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_report (remaining_budget_customer_id,month,year) SELECT id,:month,:year FROM remaining_budget_customers;";

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

  private function checkFirstReport()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total FROM remaining_budget_report;";

      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["status"] = "success";
      $result["data"]["row_count"] = $fetchRowCount["total"];
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  private function getFirstRemaining()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT remaining_budget_customer_id, remain_value as total FROM remaining_budget_first_remaining;";

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

  private function getLastMonth($last_month, $year_last_month)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT remaining_budget_customer_id, cash_advance as total FROM remaining_budget_report WHERE month = :month and year = :year;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $last_month);
      $stmt->bindParam("year", $year_last_month);
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


  
}

$obj = new CashAdvance();
$obj->run();
?>