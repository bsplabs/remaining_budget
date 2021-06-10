<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class GlCashAdvance 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $gl_cash_advance_job = $this->getJob();
    $gl_cash_advance_job = $gl_cash_advance_job["data"];
    if($gl_cash_advance_job['gl_cash_advance'] == 'waiting'){
      $this->updateStatus('in_progress',$gl_cash_advance_job["id"]);
      $gl_cash_advance_raw_data = $this->getRawData($gl_cash_advance_job['month'],$gl_cash_advance_job['year']);
      foreach($gl_cash_advance_raw_data["data"] as $gl_cash_advance){
        $gl_type = $gl_cash_advance["series_code"];
        $total = $gl_cash_advance["credit_lc"] - $gl_cash_advance["debit_lc"];
        $remaining_budget_id = $gl_cash_advance["remaining_budget_customer_id"];
        $month = $gl_cash_advance["month"];
        $year = $gl_cash_advance["year"];
        if($gl_type == "IV"){
          $reconcile = $this->moveToReportReceive($total,$remaining_budget_id,$month,$year);
        }elseif($gl_type == "IN"){
          $reconcile = $this->moveToReportInvoice($total,$remaining_budget_id,$month,$year);
        }elseif($gl_type == "CN" || $gl_type == "CV"){
          $reconcile = $this->moveToReportCreditNote($total,$remaining_budget_id,$month,$year);
        }else{
          $reconcile = array("status"=>"success");
        }
        
        if($reconcile["status"] == "success"){
          $mark_reconcile = $this->markReconcile($gl_cash_advance["id"]);
        }
      }
      $this->updateStatus('completed',$gl_cash_advance_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE gl_cash_advance = 'waiting' AND overall_status = 'waiting' AND cash_advance = 'completed'
              ORDER BY month,year,id Limit 1";

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

  private function getRawData($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_gl_cash_advance
              WHERE month = :month and year = :year and is_reconcile = false
              ORDER BY id LIMIT 5000;";

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

  private function moveToReportReceive($total,$remaining_budget_id,$month,$year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET receive = receive + :total, is_reconcile = false
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

  private function moveToReportInvoice($total,$remaining_budget_id,$month,$year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET invoice = invoice + :total, is_reconcile = false
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

  private function moveToReportCreditNote($total,$remaining_budget_id,$month,$year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET ads_credit_note = ads_credit_note + :total, is_reconcile = false
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
      $sql = "UPDATE remaining_budget_gl_cash_advance SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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

  private function updateStatus($status,$id){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status SET gl_cash_advance = :status where id = :id";

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

  
}

$obj = new GlCashAdvance();
$obj->run();
?>