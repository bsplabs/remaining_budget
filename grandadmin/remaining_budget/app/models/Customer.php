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

  public function getCustomers($offset, $limit, $order_by, $where, $export = false)
  {
    try {
      $mainDB = $this->db->dbCon();
      if ($export) {
        $sql = "SELECT * FROM remaining_budget_customers {$where} {$order_by}";
      } else {
        $sql = "SELECT * FROM remaining_budget_customers {$where} {$order_by} LIMIT {$offset}, {$limit}";
      }
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();
      $result["data"]["customers"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql2 = "SELECT COUNT(*) AS rowCount FROM remaining_budget_customers {$where} {$order_by}";
      $stmt2 = $mainDB->prepare($sql2);
      $stmt2->execute();
      $fetch_row_count = $stmt2->fetch(PDO::FETCH_ASSOC);
      $result["data"]["total_customers"] = $fetch_row_count["rowCount"];
      
      $result["status"] = "success";

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

  public function resetMainBusiness($parent_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_customers SET main_business = 0, updated_by = :updated_by WHERE parent_id = :parent_id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("parent_id", $parent_id);
      if (isset($_SESSION['admin_username'])) {
        $stmt->bindParam("updated_by", $_SESSION['admin_username']);
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
                  updated_at = NOW(),
                  updated_by = :updated_by
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
      $stmt->bindParam("updated_by", $customer_data['updated_by']);

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

  public function checkID($id)
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

  public function checkCustomerKey($customer)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as rowCount 
              FROM remaining_budget_customers 
              WHERE gramdadmin_customer_id = :grandadmin_customer_id
                AND grandadmin_customer_name = :grandadmin_customer_name
                AND offset_acct = :offset_acct
                AND offset_acct_name = :offset_acct_name 
            ";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("grandadmin_customer_id", $customer['grandadmin_customer_id']);
      $stmt->bindParam("grandadmin_customer_name", $customer['grandadmin_customer_name']);
      $stmt->bindParam("offset_acct", $customer['offset_acct']);
      $stmt->bindParam("offset_acct_name", $customer['offset_acct_name']);
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

  public function insertCustomer($customer)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_customers(
                grandadmin_customer_id,
                grandadmin_customer_name,
                offset_acct,
                offset_acct_name,
                company,
                main_business,
                parent_id,
                payment_method,
                updated_by
              )
              VALUES (
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :offset_acct,
                :offset_acct_name,
                :company,
                :main_business,
                :parent_id,
                :payment_method,
                :updated_by
              )
            ";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("grandadmin_customer_id", $customer['grandadmin_customer_id']);
      $stmt->bindParam("grandadmin_customer_name", $customer['grandadmin_customer_name']);
      $stmt->bindParam("offset_acct", $customer['offset_acct']);
      $stmt->bindParam("offset_acct_name", $customer['offset_acct_name']);
      $stmt->bindParam("company", $customer['company']);
      $stmt->bindParam("main_business", $customer['main_business']);
      $stmt->bindParam("parent_id", $customer['parent_id']);
      $stmt->bindParam("payment_method", $customer['payment_method']);
      $stmt->bindParam("updated_by", $customer['updated_by']);
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

  public function replaceCustomer($customer)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "UPDATE remaining_budget_customers
              SET grandadmin_customer_id = :grandadmin_customer_id,
                  grandadmin_customer_name = :grandadmin_customer_name,
                  offset_acct = :offset_acct,
                  offset_acct_name = :offset_acct_name,
                  company = :company,
                  main_business = :main_business,
                  parent_id = :parent_id,
                  payment_method = :payment_method,
                  updated_at = NOW(),
                  updated_by = :updated_by
              WHERE id = :id
            ";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("grandadmin_customer_id", $customer['grandadmin_customer_id']);
      $stmt->bindParam("grandadmin_customer_name", $customer['grandadmin_customer_name']);
      $stmt->bindParam("offset_acct", $customer['offset_acct']);
      $stmt->bindParam("offset_acct_name", $customer['offset_acct_name']);
      $stmt->bindParam("company", $customer['company']);
      $stmt->bindParam("main_business", $customer['main_business']);
      $stmt->bindParam("parent_id", $customer['parent_id']);
      $stmt->bindParam("payment_method", $customer['payment_method']);
      $stmt->bindParam("updated_by", $customer['updated_by']);
      $stmt->bindParam("id", $customer['id']);
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

  public function deleteCustomer($id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "DELETE FROM remaining_budget_customers WHERE id = :id";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("id", $id);
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

  public function checkIdIsReconcile($table, $id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as rowCount FROM {$table} WHERE remaining_budget_customer_id = :id";
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

  public function checkMainBusiness($parent_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_customers WHERE parent_id = :parent_id AND main_business = 1";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("parent_id", $parent_id);
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "error";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

}

?>