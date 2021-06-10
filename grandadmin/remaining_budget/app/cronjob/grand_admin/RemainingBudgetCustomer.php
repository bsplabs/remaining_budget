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
      $sql = "SELECT id FROM remaining_budget_customers WHERE grandadmin_customer_id = :gci AND grandadmin_customer_name = :gcn order by is_parent limit 1";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("gci", $customerData["grandadmin_customer_id"]);
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

  public function checkLastProcess($id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT count(*) as total FROM remaining_budget_report_status 
      WHERE cash_advance != 'pending' AND media_wallet != 'pending' AND withholding_tax != 'pending'
      AND free_click_cost != 'pending' AND google_spending != 'pending' AND facebook_spending != 'pending'
      AND remaining_ice != 'pending' AND gl_cash_advance != 'pending' AND transfer != 'pending'
      AND id = :id";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("id", $id);

      $stmt->execute();
      
      $total = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($total["total"] == 0) {
        $result = false;
      } else {
        $result = true;
      }

    } catch (PDOException $e) {
      $result = false;
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
      $stmt->bindParam("gcn", $customerData["grandadmin_customer_name"]);
      $stmt->bindValue("company", "RPTH");
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
      if (!empty($checkCustomerExists["data"])) {
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
