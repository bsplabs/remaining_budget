<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class RemainingIce 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $remaining_ice_job = $this->getJob();
    $remaining_ice_job = $remaining_ice_job["data"];
    if($remaining_ice_job['remaining_ice'] == 'waiting'){
      $this->updateStatus('in_progress',$remaining_ice_job["id"]);
      $set_zero = $this->setZero($remaining_ice_job['month'],$remaining_ice_job['year']);
      $remaining_ice_raw_data = $this->getRawData($remaining_ice_job['month'],$remaining_ice_job['year']);
      foreach($remaining_ice_raw_data["data"] as $remaining_ice){
          $total = $remaining_ice["remaining_ice"];
          $remaining_budget_id = $remaining_ice["remaining_budget_customer_id"];
          $month = $remaining_ice["month"];
          $year = $remaining_ice["year"];
          $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
          if($reconcile["status"] == "success"){
            $mark_reconcile = $this->markReconcile($remaining_ice["id"]);
          }
      }

      $remaining_ice_under_root = $this->getRemainingICEUnderRoot($remaining_ice_job['month'], $remaining_ice_job['year']); 
      foreach ($remaining_ice_under_root['data'] as $key => $value) 
      {
        $get_previous_remaining_ice = $this->getPreviousRemainingICE($value['remaining_budget_customer_id'], $remaining_ice_job['month'], $remaining_ice_job['year']);
        if (!empty($get_previous_remaining_ice['data']) && floatval($get_previous_remaining_ice['data']['remaining_ice']) == 0) {
          if (floatval($value['remaining_budget']) != 0) {
            // echo "----------------------------- \n";
            // echo "Update -----> " . $value['remaining_budget'] . "\n";
            // print_r($get_previous_remaining_ice['data']);
            // echo "\n";

            $this->updateRemainingIceOnReport(
              $value['remaining_budget_customer_id'], 
              $remaining_ice_job['month'], 
              $remaining_ice_job['year'], 
              $value['remaining_budget']
            );
          }
        }
      }

      $this->updateStatus('completed',$remaining_ice_job["id"]);
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE remaining_ice = 'waiting' AND overall_status = 'waiting' AND cash_advance = 'completed'
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
      $sql = "SELECT i.*
              FROM remaining_budget_remaining_ice i left join remaining_budget_customers c
              ON c.id = i.remaining_budget_customer_id
              WHERE i.month = :month and i.year = :year and i.is_reconcile = false and c.payment_method = 'prepaid'
              ORDER BY i.id";

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

  private function getRemainingICEUnderRoot($month, $year) 
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_remaining_ice_root WHERE month = :month AND year = :year";
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

  public function getPreviousRemainingICE($remaining_budget_customer_id, $month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_report
              WHERE remaining_budget_customer_id = :remaining_budget_customer_id 
                AND month = :month 
                AND year = :year";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("remaining_budget_customer_id", $remaining_budget_customer_id);
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

  private function updateRemainingIceOnReport($remaining_budget_customer_id, $month, $year, $remaining_ice)
  {
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET remaining_ice = :remaining_ice
              WHERE remaining_budget_customer_id = :remaining_budget_customer_id 
                AND month = :month 
                AND year = :year
              ";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("remaining_budget_customer_id", $remaining_budget_customer_id);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("remaining_ice", $remaining_ice);
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

  private function setZero($month, $year){
    try {

      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report
              SET remaining_ice = 0, is_reconcile = false
              WHERE month = :month and year = :year and remaining_ice != 0;";

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
              SET remaining_ice = remaining_ice + :total, is_reconcile = false
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
      $sql = "UPDATE remaining_budget_remaining_ice SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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
      $sql = "UPDATE remaining_budget_report_status SET remaining_ice = :status where id = :id";

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

$obj = new RemainingIce();
$obj->run();
?>