<?php
require_once __DIR__ . '/../../config/config.php';
require_once ROOTPATH . '/app/libraries/Database.php';

class ReportStatus 
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function createMonthly(){
    $month = date("m");
    $year = date("Y");
    $last_month = date("m", strtotime("-1 month"));
    $year_last_month = date("Y", strtotime("-1 month"));
    $is_this_month_job_exist = $this->checkThisMonthJob($month,$year);
    $is_last_month_closed = $this->checkMonthIsClosed($last_month, $year_last_month);
    echo $is_last_month_closed;
    echo $is_this_month_job_exist;
    if(!$is_this_month_job_exist && $is_last_month_closed){
      $created_job = $this->createReportStatus($month,$year);
    }
  }

  private function createReportStatus($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_report_status (month, year) VALUES (:month, :year)";
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

  private function checkThisMonthJob($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total
              FROM remaining_budget_report_status
              WHERE month = :month and year = :year;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      if($data["total"] > 0){
        $result = true;
      }else{
        $result = false;
      }
    } catch (PDOException $e) {
      $result = true;
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  private function checkMonthIsClosed($month,$year){
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total
              FROM remaining_budget_close_period
              WHERE month = :month and year = :year;";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      if($data["total"] > 0){
        $result = true;
      }else{
        $result = false;
      }
    } catch (PDOException $e) {
      $result = false;
      echo $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  
}

$obj = new ReportStatus();
$obj->createMonthly();
?>