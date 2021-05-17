<?php



class Resource
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  public function addGoogleSpendingData($month, $year, $googleSpendingData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_google_spending (
                remaining_budget_customer_id,
                month,
                year,
                google_id,
                google_account,
                grandadmin_customer_id,
                grandadmin_customer_name,
                account_budget,
                purchase_order,
                campaign,
                volume,
                unit,
                spending_total_price
              )
              VALUES (
                :remaining_budget_customer_id,
                :month,
                :year,
                :google_id,
                :google_account,
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :account_budget,
                :purchase_order,
                :campaign,
                :volume,
                :unit,
                :spending_total_price
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("remaining_budget_customer_id",  $googleSpendingData["remaining_budget_customer_id"]);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("google_id", $googleSpendingData["google_id"]);
      $stmt->bindParam("google_account", $googleSpendingData["google_account"]);
      $stmt->bindParam("grandadmin_customer_id", $googleSpendingData["grandadmin_customer_id"]);
      $stmt->bindParam("grandadmin_customer_name", $googleSpendingData["grandadmin_customer_name"]);
      $stmt->bindParam("account_budget", $googleSpendingData["budget_account"]);
      $stmt->bindParam("purchase_order", $googleSpendingData["purchase_order"]);
      $stmt->bindParam("campaign", $googleSpendingData["campaign"]);
      $stmt->bindParam("volume", $googleSpendingData["volume"]);
      $stmt->bindParam("unit", $googleSpendingData["unit"]);
      $stmt->bindParam("spending_total_price", $googleSpendingData["spending_total_price"]);

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

  public function updateGoogleSpendingData($month, $year, $googleSpendingData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_google_spending
              SET account_budget = :account_budget,
                  purchase_order = :purchase_order,
                  volume = :volume,
                  unit = :unit,
                  spending_total_price = :spending_total_price
              WHERE month = :month
                AND year = :year
                AND google_id = :google_id
                AND campaign = :campaign
             ";

      $stmt = $mainDB->prepare($sql);

      // Where cause
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("google_id", $googleSpendingData["google_id"]);
      $stmt->bindParam("campaign", $googleSpendingData["campaign"]);

      // Set update data
      // $stmt->bindParam("google_account", $googleSpendingData["google_account"]);
      $stmt->bindParam("account_budget", $googleSpendingData["budget_account"]);
      $stmt->bindParam("purchase_order", $googleSpendingData["purchase_order"]);
      $stmt->bindParam("volume", $googleSpendingData["volume"]);
      $stmt->bindParam("unit", $googleSpendingData["unit"]);
      $stmt->bindParam("spending_total_price", $googleSpendingData["spending_total_price"]);

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

  public function getGoogleSpending()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_customers";
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

  public function clearrGoogleSpendingByMonthAndYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "DELETE FROM remaining_budget_google_spending WHERE month = :month AND year = :year";
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

  public function getStatusResources($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT  media_wallet, 
                      withholding_tax, 
                      free_click_cost, 
                      remaining_ice, 
                      gl_cash_advance, 
                      google_spending, 
                      facebook_spending,
                      transfer,
                      overall_status
              FROM remaining_budget_report_status 
              WHERE type = 'default'
                AND month = '{$month}' 
                AND year = '{$year}'
              Order by id desc
              Limit 1";
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

  public function findCustomerID($spending_data, $ad_type)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT CustomerID FROM ready_topup WHERE ad_id = :ad_id";
      $stmt = $mainDB->prepare($sql);
      if ($ad_type === "facebook") {
        $stmt->bindParam("ad_id", $spending_data["facebook_id"]);
      } else {
        $stmt->bindParam("ad_id", $spending_data["google_id"]);
      }
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

  public function findCustomerName($ads_type, $ads_id)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT bill_firstname, bill_lastname, bill_company FROM tracking_webpro_new_members WHERE {$ads_type} = '{$ads_id}'";
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

  public function findRemainingBudgetCustomerID()
  {
    return "";
  }

  public function addNewGrandAdminCustomer($customer_id, $customer_name) 
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_customers (
                grandadmin_customer_id,
                grandadmin_customer_name,
                offset_acct,
                offset_acct_name
              )
              VALUES (
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :offset_acct,
                :offset_acct_name
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("grandadmin_customer_id", $customer_id);
      $stmt->bindParam("grandadmin_customer_name", $customer_name);
      $stmt->bindParam("offset_acct", $customer_id);
      $stmt->bindParam("offset_acct_name", $customer_name);

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

  public function clearFacebookSpendingByMonthYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "DELETE FROM remaining_budget_facebook_spending WHERE month = :month AND year = :year";
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

  public function insertFacebookSpending($month, $year, $facebook_spending)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_facebook_spending (
                remaining_budget_customer_id,
                month,
                year,
                facebook_id,
                grandadmin_customer_id,
                grandadmin_customer_name,
                billing_period,
                currency,
                payment_status,
                spending_total_price
              )
              VALUES (
                :remaining_budget_customer_id,
                :month,
                :year,
                :facebook_id,
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :billing_period,
                :currency,
                :payment_status,
                :spending_total_price
              )";

      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam("remaining_budget_customer_id", $facebook_spending["remaining_budget_customer_id"]);
      $stmt->bindParam("month", $facebook_spending["month"]);
      $stmt->bindParam("year", $facebook_spending["year"]);
      $stmt->bindParam("facebook_id", $facebook_spending["facebook_id"]);
      $stmt->bindParam("grandadmin_customer_id", $facebook_spending["grandadmin_customer_id"]);
      $stmt->bindParam("grandadmin_customer_name", $facebook_spending["grandadmin_customer_name"]);
      $stmt->bindParam("billing_period", $facebook_spending["billing_period"]);
      $stmt->bindParam("currency", $facebook_spending["currency"]);
      $stmt->bindParam("payment_status", $facebook_spending["payment_status"]);
      $stmt->bindParam("spending_total_price", $facebook_spending["spending_total_price"]);

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

  public function getTotalDataUpdate($table_name, $month, $year)
  {
    try {
      $table_name = "remaining_budget_" . $table_name;
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) AS rowCount FROM {$table_name} WHERE month = :month AND year = :year";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $fetchRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["data"]["row_count"] = $fetchRowCount["rowCount"];

      $sql2 = "SELECT updated_at FROM {$table_name} WHERE month = :month AND year = :year ORDER BY updated_at DESC LIMIT 1";
      $stmt2 = $mainDB->prepare($sql2);
      $stmt2->bindParam("month", $month);
      $stmt2->bindParam("year", $year);
      $stmt2->execute();
      $fetchUpdatedAt = $stmt2->fetch(PDO::FETCH_ASSOC);
      $result["data"]["updated_at"] = $fetchUpdatedAt["updated_at"];

      $result["status"] = "success";

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getProviousReportStatus($month, $year) 
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as row_count FROM remaining_budget_report_status WHERE month < :month AND year <= :year AND overall_status != 'completed' AND type = 'default'";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->execute();
      $result["status"] = "success";
      $fetch_row_count = $stmt->fetch(PDO::FETCH_ASSOC);
      $row_count = $fetch_row_count["row_count"];
      $result["data"] = $row_count; 

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getFirstMonthYearReportStatus($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT overall_status FROM remaining_budget_report_status WHERE month = :month AND year = :year AND type = 'default'";
      $stmt = $mainDB->prepare($sql);
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

}
