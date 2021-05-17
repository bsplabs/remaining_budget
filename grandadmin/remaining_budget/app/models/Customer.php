<?php

class Customer
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function getTotalAllCustomers()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) AS rowCount FROM remaining_budget_customers";
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["status"] = "success";
      $fetch_row_count = $stmt->fetch(PDO::FETCH_ASSOC); 
      $result["data"] = $fetch_row_count["rowCount"];
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    return $result;
  }

  public function getCustomers($offset, $limit)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_customers ORDER BY parent_id ASC, main_business DESC LIMIT {$offset}, {$limit}";
      $stmt = $mainDB->prepare($sql);
      // $stmt->bindParam("limit", $limit, PDO::PARAM_INT);
      // $stmt->bindParam("offset", $offset, PDO::PARAM_INT);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }

    return $result;
  }
}

?>