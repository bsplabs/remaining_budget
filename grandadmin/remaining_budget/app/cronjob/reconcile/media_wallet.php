<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class MediaWallet 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $media_wallet_job = $this->getJob();
    $media_wallet_job = $media_wallet_job["data"];
    if($media_wallet_job['media_wallet'] == 'waiting'){
      $this->updateStatus('in_progress',$media_wallet_job["id"]);
      $set_zero = $this->setZero($media_wallet_job['month'],$media_wallet_job['year']);
      $media_wallet_raw_data = $this->getRawData($media_wallet_job['month'],$media_wallet_job['year']);
      foreach($media_wallet_raw_data["data"] as $media_wallet){
        $total = $media_wallet["remaining_wallet"] + $media_wallet["previous_clearing"];
        $remaining_budget_id = $media_wallet["remaining_budget_customer_id"];
        $month = $media_wallet["month"];
        $year = $media_wallet["year"];
        $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
        if($reconcile["status"] == "success"){
          $mark_reconcile = $this->markReconcile($media_wallet["id"]);
        }
      }
      $this->updateStatus('completed',$media_wallet_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE media_wallet = 'waiting' AND overall_status = 'waiting' AND cash_advance = 'completed'
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
      $sql = "SELECT w.*
              FROM remaining_budget_media_wallet w left join remaining_budget_customers c
              ON c.id = w.remaining_budget_customer_id
              WHERE w.month = :month and w.year = :year and w.is_reconcile = false and c.payment_method = 'prepaid'
              ORDER BY w.id";

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

  private function setZero($month, $year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET media_wallet = 0, is_reconcile = false
              WHERE month = :month and year = :year and media_wallet != 0;";

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

  private function moveToReport($total,$remaining_budget_id,$month,$year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET wallet = wallet + :total, is_reconcile = false
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
      $sql = "UPDATE remaining_budget_media_wallet SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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
      $sql = "UPDATE remaining_budget_report_status SET media_wallet = :status where id = :id";

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

$obj = new MediaWallet();
$obj->run();
?>