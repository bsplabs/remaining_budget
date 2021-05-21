<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class WithholdingTax 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function run(){
    $withholding_tax_job = $this->getJob();
    $withholding_tax_job = $withholding_tax_job["data"];
    if($withholding_tax_job['withholding_tax'] == 'waiting'){
      $withholding_tax_raw_data = $this->getRawData($withholding_tax_job['month'],$withholding_tax_job['year']);
      foreach($withholding_tax_raw_data["data"] as $withholding_tax){
        $total = $withholding_tax["amount"];
        $remaining_budget_id = $withholding_tax["remaining_budget_customer_id"];
        $month = $withholding_tax["month"];
        $year = $withholding_tax["year"];
        $reconcile = $this->moveToReport($total,$remaining_budget_id,$month,$year);
        if($reconcile["status"] == "success"){
          $mark_reconcile = $this->markReconcile($withholding_tax["id"]);
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
              FROM remaining_budget_withholding_tax
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
              SET withholding_tax = withholding_tax + :total
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
      $sql = "UPDATE remaining_budget_withholding_tax SET is_reconcile = true, updated_at = now(), updated_by = 'cronjob'
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

$obj = new WithholdingTax();
$obj->run();
?>