<?php

class RemainingBudgetCustomer
{
  protected $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function checkCustomerExisting($customerData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT id FROM remaining_budget_customers WHERE grandadmin_customer_id = :gci AND grandadmin_customer_name = :gcn";
      
      // if ($customerData["grandadmin_customer_name"] == NULL || $customerData["grandadmin_customer_name"] == "") {
      //   $sql .= " AND grandadmin_customer_name IS NULL";
      // } else {
      //   $sql .= " AND grandadmin_customer_name = :gcn";
      // }

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("gci", $customerData["grandadmin_customer_id"]);
      // if (!empty($customerData["grandadmin_customer_name"])) {
      // }
      $stmt->bindParam("gcn", $customerData["grandadmin_customer_name"]);
      $stmt->execute();
      $result["status"] = "success";
      $customerId = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($customerId)) {
        $result["data"] = "";
      } else {
        $result["data"] = $customerId["id"];
      }
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function addNewCustomer($customerData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_customers (
                grandadmin_customer_id,
                grandadmin_customer_name,
                offset_acct,
                offset_acct_name,
                company,
                parent_id,
                payment_method,
                updated_by
              )
              VALUES (
                :gci,
                :gcn,
                :gci,
                :gcn,
                :company,
                :parent_id,
                :payment_method,
                :updated_by
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("gci", $customerData["grandadmin_customer_id"]);
      // if ($customerData["grandadmin_customer_name"] == "" || $customerData["grandadmin_customer_name"] == NULL) {
      //   $stmt->bindValue("gcn", NULL);
      // } else {
      // }
      $stmt->bindParam("gcn", $customerData["grandadmin_customer_name"]);
      $stmt->bindValue("company","RPTH");
      $stmt->bindParam("parent_id", $customerData["grandadmin_customer_id"]);
      $stmt->bindValue("payment_method", "prepaid");
      $stmt->bindValue("updated_by", "script");
      $stmt->execute();
      $result["status"] = "success";
      $result["data"] = $mainDB->lastInsertId();
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getRemainingBudgetCustomerID($customerData)
  {
    $checkCustomerExists = $this->checkCustomerExisting($customerData);
    if ($checkCustomerExists["status"]) {
      if ($checkCustomerExists["data"] != "") {
        return $checkCustomerExists["data"];
      } else {
        // insert new customer
        $addNewCus = $this->addNewCustomer($customerData);
        if ($addNewCus["status"] == "success" && $addNewCus["data"] !== "") {
          return $addNewCus["data"];
        } else {
          return "";
        }
      }
    } else {
      return "";
    }
    return $checkCustomerExists["data"];
  }
}
