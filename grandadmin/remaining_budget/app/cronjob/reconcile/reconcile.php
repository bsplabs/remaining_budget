<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class Reconcile 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run()
  {
    $reconcile_data_list = $this->getReconcileJob();
    $reconcile_data_list = $reconcile_data_list["data"];
    
    foreach($reconcile_data_list as $reconcile_data){
      $cash_advance = $reconcile_data['last_month_remaining'] + $reconcile_data['adjustment_remain'] + $reconcile_data['receive'] + $reconcile_data['invoice'] + $reconcile_data['transfer'] + $reconcile_data['ads_credit_note'] + $reconcile_data['spending_invoice'] + $reconcile_data['adjustment_free_click_cost'] + $reconcile_data['adjustment_free_click_cost_old'] + $reconcile_data['adjustment_cash_advance'] + $reconcile_data['adjustment_max'];
      $remaining_budget = $reconcile_data['remaining_ice'] + $reconcile_data['wallet'] + $reconcile_data['wallet_free_click_cost'] + $reconcile_data['withholding_tax'] + $reconcile_data['adjustment_front_end'];
      $difference = $remaining_budget - $cash_advance;
  
      $reconcile_data = $this->updateReconcileReCalculateByReportId($reconcile_data["id"],$cash_advance,$remaining_budget,$difference);
    }
    
  }

  private function getReconcileJob(){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_report r  WHERE is_reconcile = false limit 1000 ";
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

  private function getReconcileDataByReportId($report_id)
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

  private function updateReconcileReCalculateByReportId($report_id,$cash_advance,$remaining_budget,$difference)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report SET cash_advance = :cash_advance, remaining_budget = :remaining_budget, `difference` = :difference, is_reconcile = true, updated_at = now(), updated_by = 'cronjob' where id = :report_id;";
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

$obj = new Reconcile();
$obj->run();
?>