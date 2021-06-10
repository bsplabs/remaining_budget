<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class FacebookSpending 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $facebook_spending_job = $this->getJob();
    $facebook_spending_job = $facebook_spending_job["data"];
    if($facebook_spending_job['facebook_spending'] == 'waiting'){
      $this->updateStatus('in_progress',$facebook_spending_job["id"]);
      $facebook_spending_raw_data = $this->getRawData($facebook_spending_job['month'],$facebook_spending_job['year']);
      foreach($facebook_spending_raw_data["data"] as $facebook_spending){
          $total = $facebook_spending["spending_total_price"] * -1 ;
          $remaining_budget_id = $facebook_spending["remaining_budget_customer_id"];
          $month = $facebook_spending["month"];
          $year = $facebook_spending["year"];
          $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
          if($reconcile["status"] == "success"){
            $mark_reconcile = $this->markReconcile($facebook_spending["id"]);
          }
      }
      $this->updateStatus('completed',$facebook_spending_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE facebook_spending = 'waiting' AND overall_status = 'waiting' AND cash_advance = 'completed'
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
              FROM remaining_budget_facebook_spending
              WHERE month = :month and year = :year and is_reconcile = false
              ORDER BY id";

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
              SET spending_invoice = spending_invoice + :total, is_reconcile = false
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
      $sql = "UPDATE remaining_budget_facebook_spending SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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
      $sql = "UPDATE remaining_budget_report_status SET facebook_spending = :status where id = :id";

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

$obj = new FacebookSpending();
$obj->run();
?>