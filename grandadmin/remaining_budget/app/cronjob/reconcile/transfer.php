<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class WalletTransfer
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $wallet_transfer_job = $this->getJob();
    $wallet_transfer_job = $wallet_transfer_job["data"];
    if($wallet_transfer_job['transfer'] == 'waiting'){
      $this->updateStatus('in_progress',$wallet_transfer_job["id"]);
      $wallet_transfer_raw_data = $this->getRawData($wallet_transfer_job['month'],$wallet_transfer_job['year']);
      foreach($wallet_transfer_raw_data["data"] as $wallet_transfer){
          $source_value = $wallet_transfer["source_value"] * - 1;
          $destination_value = $wallet_transfer["source_value"];
          $source_remaining_budget_customer_id = $wallet_transfer["source_remaining_budget_customer_id"];
          $destination_remaining_budget_customer_id = $wallet_transfer["destination_remaining_budget_customer_id"];
          $month = $wallet_transfer["month"];
          $year = $wallet_transfer["year"];
          $reconcile_source = $this->moveToReport($source_value,$source_remaining_budget_customer_id,$month,$year);
          $reconcile_destination = $this->moveToReport($destination_value,$destination_remaining_budget_customer_id,$month,$year);
          if($reconcile_destination["status"] == "success"){
            $mark_reconcile = $this->markReconcile($wallet_transfer["id"]);
          }
      }
      $this->updateStatus('completed',$wallet_transfer_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE transfer = 'waiting' AND overall_status = 'waiting' AND cash_advance = 'completed'
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
              FROM remaining_budget_wallet_transfer
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
              SET transfer = transfer + :total, is_reconcile = false
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
      $sql = "UPDATE remaining_budget_wallet_transfer SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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
      $sql = "UPDATE remaining_budget_report_status SET transfer = :status where id = :id";

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

$obj = new WalletTransfer();
$obj->run();
?>