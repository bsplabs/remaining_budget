<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class FreeClickCost 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $free_click_cost_job = $this->getJob();
    $free_click_cost_job = $free_click_cost_job["data"];
    if($free_click_cost_job['free_click_cost'] == 'waiting'){
      $free_click_cost_raw_data = $this->getRawData($free_click_cost_job['month'],$free_click_cost_job['year']);
      foreach($free_click_cost_raw_data["data"] as $free_click_cost){
        $total = $free_click_cost["coupon"];
        $remaining_budget_id = $free_click_cost["remaining_budget_customer_id"];
        $month = $free_click_cost["month"];
        $year = $free_click_cost["year"];
        $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
        if($reconcile["status"] == "success"){
          $mark_reconcile = $this->markReconcile($free_click_cost["id"]);
        }
      }
    }
  }

  private function getJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_report_status
              WHERE overall_status != 'completed'
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

  private function getRawData($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT *
              FROM remaining_budget_free_click_cost
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
              SET wallet_free_click_cost = wallet_free_click_cost + :total
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
      $sql = "UPDATE remaining_budget_free_click_cost SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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

  
}

$obj = new FreeClickCost();
$obj->run();
?>