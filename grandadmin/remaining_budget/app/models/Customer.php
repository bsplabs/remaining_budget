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

  public function getAllCustomers()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_customers";
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

  public function checkCustomerID($id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as rowCount FROM remaining_budget_customers WHERE id = :id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("id", $id);
      $stmt->execute();
      $fetch_row_count = $stmt->fetch(PDO::FETCH_ASSOC);
      $row_count = $fetch_row_count["rowCount"];
      $result["status"] = "success";
      $result["data"] = $row_count;
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  } 

  public function checkParentID($parent_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as rowCount FROM remaining_budget_customers WHERE grandadmin_customer_id = :parent_id OR offset_acct = :parent_id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("parent_id", $parent_id);
      $stmt->execute();
      $fetch_row_count = $stmt->fetch(PDO::FETCH_ASSOC);
      $row_count = $fetch_row_count["rowCount"];
      $result["status"] = "success";
      $result["data"] = $row_count;
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function updateCustomer($customer_data)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_customers 
              SET parent_id = :parent_id,
                  grandadmin_customer_id = :grandadmin_customer_id,
                  grandadmin_customer_name = :grandadmin_customer_name,
                  offset_acct = :offset_acct,
                  offset_acct_name = :offset_acct_name,
                  company = :company,
                  payment_method = :payment_method,
                  main_business = :main_business,
                  updated_at = NOW()
              WHERE id = :id";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("id", $customer_data['id']);
      $stmt->bindParam("parent_id", $customer_data['parent_id']);
      $stmt->bindParam("grandadmin_customer_id", $customer_data['grandadmin_customer_id']);
      $stmt->bindParam("grandadmin_customer_name", $customer_data['grandadmin_customer_name']);
      $stmt->bindParam("offset_acct", $customer_data['offset_acct']);
      $stmt->bindParam("offset_acct_name", $customer_data['offset_acct_name']);
      $stmt->bindParam("company", $customer_data['company']);
      $stmt->bindParam("payment_method", $customer_data['payment_method']);
      if ($customer_data['main_business'] == false || $customer_data['main_business'] === 'false') {
        $stmt->bindValue("main_business", false); 
      } else {
        $stmt->bindValue("main_business", true); 
      }

      $stmt->execute();

      $result["status"] = "success";
      $result["data"] = "";

    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

}

?>