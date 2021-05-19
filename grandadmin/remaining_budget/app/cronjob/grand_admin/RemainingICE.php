<?php
date_default_timezone_set('Asia/Bangkok');

class RemainingICE
{
  protected $db;
  protected $remainingBudgetCustomer;

  public function __construct()
  {
    $this->db = new Database();
    $this->remainingBudgetCustomer = new RemainingBudgetCustomer();
  }

  public function getMonthYearFromReportStatusTable()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT month, year FROM remaining_budget_report_status WHERE type = 'default' AND free_click_cost = 'pending' ORDER BY year ASC, month ASC Limit 1;";
      $stmt = $mainDB->query($sql);
      $result["status"] = "success";
      $result["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }
    $this->db->dbClose($mainDB);
    return $result;
  }

  public function createReportStatus($month, $year)
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

  public function updateReportStatus($month, $year, $resource_type, $status)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status 
              SET {$resource_type} = '{$status}' 
              WHERE month = '{$month}' 
                AND year = '{$year}' 
                AND type = 'default'";

      $stmt = $mainDB->prepare($sql);
      
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

  public function run()
  {
    $primary_month = PRIMARY_MONTH;
    $primary_year = PRIMARY_YEAR;
    $limit = 100;

    // $get_month_year = $this->getMonthYearFromReportStatusTable();
    if (empty($get_month_year["data"])) {
      $this->createReportStatus($primary_month, $primary_year);
      $get_month_year["data"][0] = array(
        "month" => $primary_month,
        "year" => $primary_year
      );
    }

    foreach ($get_month_year["data"] as $month_year_key => $month_year_val) 
    {
      $month = $month_year_val['month'];  
      $year = $month_year_val['year']; 

      $this->updateReportStatus($month, $year, "remaining_ice", "pending");

      // ========== Google Service
      $service_name = "google";
      $service_column = "AdwordsCusId";
      // clear data before insert new
      $this->clearRemainingICE($service_name, $month, $year);
      $this->handleRemainingICEData($service_name, $service_column, $limit, $month, $year);
      // echo "Get remaining ICE google finished \n\n";

      // ========== Facebook Service
      $service_name = "facebook";
      $service_column = "FacebookID";
      $this->clearRemainingICE($service_name, $month, $year);
      $this->handleRemainingICEData($service_name, $service_column, $limit, $month, $year);
      // echo "Get remaining ICE facebook finished \n\n";

      // ========== Instagram Service
      $service_name = "instagram";
      $service_column = "InstagramID";
      $this->clearRemainingICE($service_name, $month, $year);
      $this->handleRemainingICEData($service_name, $service_column, $limit, $month, $year);
      // echo "Get remaining ICE instagram finished \n\n";

      // update report status after tasks finished
      $this->updateReportStatus($month, $year, "remaining_ice", "waiting");
    } 

  }

  public function handleRemainingICEData($service_name, $service_column, $limit, $month, $year)
  {
    $get_remaining_ice_data_first_time = $this->getRemainigICE($service_name, $month, $year, 0, $limit);
    if (!empty($get_remaining_ice_data_first_time) && $get_remaining_ice_data_first_time['status']) {
      $total = $get_remaining_ice_data_first_time['total'];
      if ($total > 100) {
        $loop = ceil($total / 100);
        // echo $loop;
        for ($i = 1; $i <= $loop; $i++) 
        {
          if ($i == 1) {
            $remaining_ice_data = $get_remaining_ice_data_first_time['data'];
            $this->remainingICEServiceHandler($remaining_ice_data, $month, $year, $service_name, $service_column);
          } else {
            $offset = (($i - 1) * $limit);
            $get_remaining_ice_data = $this->getRemainigICE($service_name, $month, $year, $offset, $limit);
            $remaining_ice_data = $get_remaining_ice_data['data'];
            $this->remainingICEServiceHandler($remaining_ice_data, $month, $year, $service_name, $service_column);
          }
        }
      } else {
        $remaining_ice_data = $get_remaining_ice_data_first_time['data'];
        $this->remainingICEServiceHandler($remaining_ice_data, $month, $year, $service_name, $service_column);
      }
    }
  }

  public function remainingICEServiceHandler($remaining_ice_data, $month, $year, $service_name, $service_column)
  {
    foreach ($remaining_ice_data as $key => $value) {
      $remaining_ice = array(
        "remaining_budget_customer_id" => NULL,
        "month" => $month,
        "year" => $year,
        "grandadmin_customer_id" => "",
        "grandadmin_customer_name" => "",
        "service" => $service_name,
        "account_id" => $value['account_id'],
        "payment_method" => $value['payment_method'],
        "remaining_ice" => $value['remaining_budget']
      );

      // find customer id on ready topup
      $find_customer_id = $this->findCustomerIdOnReadyTopupTable($value['account_id']);
      if ($find_customer_id["status"] === 'success' && !empty($find_customer_id['data'])) {
        $remaining_ice['grandadmin_customer_id'] = $find_customer_id['data'];
      } else {
        // google service -->  AdwordsCusId column
        $find_customer_id = $this->findCustomerIdOnTrackingWebProNewMembersTable($value['account_id'], $service_column);
        if ($find_customer_id['status'] === 'success' && !empty($find_customer_id['data'])) {
          $remaining_ice['grandadmin_customer_id'] = $find_customer_id['data'];
        }
      }

      // find customer name
      if (!empty($remaining_ice['grandadmin_customer_id'])) {
        $find_customer_name = $this->findCustomerNameOnTrackingWebProNewMembersTable($remaining_ice['grandadmin_customer_id']);
        if ($find_customer_name["status"] === "success" && !empty($find_customer_name["data"])) {
          if (empty($find_customer_name['data']["bill_company"])) {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name['data']["bill_firstname"]) . " " . iconv('TIS-620','UTF-8',$find_customer_name['data']["bill_lastname"]);
          } else {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name['data']["bill_company"]);
          }
          $remaining_ice["grandadmin_customer_name"] = $customer_name;
        }
      }

      // find remaining budget customer
      if (!empty($remaining_ice["grandadmin_customer_id"]) && !empty($remaining_ice["grandadmin_customer_name"])) {
        $customer_data = array(
          "grandadmin_customer_id" => $remaining_ice["grandadmin_customer_id"],
          "grandadmin_customer_name" => $remaining_ice["grandadmin_customer_name"]
        );
        $find_remaining_budget_customer_id = $this->remainingBudgetCustomer->getRemainingBudgetCustomerID($customer_data);
        if (!empty($find_remaining_budget_customer_id)) {
          $remaining_ice["remaining_budget_customer_id"] = $find_remaining_budget_customer_id;
        }
      }

      // insert remaining ice
      $add_new_remainig_ice_record = $this->addNewRemainingICE($remaining_ice);
    }
  }

  public function getRemainigICE($service, $month, $year, $offset, $limit)
  {
    $headers = array(
      'Api-Access-Token: 9fc5faeff1e9e49bc2db82b4481ddc00',
    );

    $post_request = array(
      "service" => $service,
      "year" => $year,
      "month" => $month,
      "accounts" => "",
      "offset" => $offset,
      "limit" => $limit
    );

    $cURLConnection = curl_init('http://adproicedev.readyplanet.com/api/remaining_budget');
    curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post_request);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $api_response = curl_exec($cURLConnection);

    curl_close($cURLConnection);

    return json_decode($api_response, true);
  }

  public function findCustomerIdOnReadyTopupTable($ads_id)
  {
    try {
      $mainDB = $this->db->dbCon("latin1");
      
      $sql = "SELECT CustomerID FROM ready_topup WHERE ad_id = :ad_id ORDER BY ID DESC LIMIT 1";
      
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("ad_id", $ads_id);

      $stmt->execute();
      $result["status"] = "success";
      $fetch_customer_id = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["data"] = $fetch_customer_id["CustomerID"];

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function findCustomerIdOnTrackingWebProNewMembersTable($ads_id, $service_column)
  {
    try {
      $mainDB = $this->db->dbCon("latin1");

      $sql = "SELECT 	CustomerID FROM tracking_webpro_new_members WHERE {$service_column} = '{$ads_id}'";

      $stmt = $mainDB->prepare($sql);

      $stmt->execute();
      $result["status"] = "success";
      $fetch_customer_id = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["data"] = $fetch_customer_id['CustomerID'];

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function findCustomerNameOnTrackingWebProNewMembersTable($customer_id)
  {
    try {
      $mainDB = $this->db->dbCon("latin1");

      $sql = "SELECT bill_firstname, bill_lastname, bill_company FROM tracking_webpro_new_members WHERE CustomerID = :customer_id";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam('customer_id', $customer_id);

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

  public function clearRemainingICE($service_name, $month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "DELETE FROM remaining_budget_remaining_ice 
              WHERE month = :month
                AND year = :year
                AND service = :service";

      $stmt = $mainDB->prepare($sql);
      // WHERE
      $stmt->bindParam('month', $month);
      $stmt->bindParam('year', $year);
      $stmt->bindParam('service', $service_name);

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

  public function addNewRemainingICE($remaining_ice)
  {
    try {
      $mainDB = $this->db->dbCon();

      $sql = "INSERT INTO remaining_budget_remaining_ice (
                remaining_budget_customer_id,
                month,
                year,
                grandadmin_customer_id,
                grandadmin_customer_name,
                service,
                account_id,
                payment_method,
                remaining_ice
              )
              VALUES (
                :remaining_budget_customer_id,
                :month,
                :year,
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :service,
                :account_id,
                :payment_method,
                :remaining_ice
              )";

      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam('remaining_budget_customer_id', $remaining_ice['remaining_budget_customer_id']);
      $stmt->bindParam('month', $remaining_ice['month']);
      $stmt->bindParam('year', $remaining_ice['year']);
      $stmt->bindParam('grandadmin_customer_id', $remaining_ice['grandadmin_customer_id']);
      $stmt->bindParam('grandadmin_customer_name', $remaining_ice['grandadmin_customer_name']);
      $stmt->bindParam('service', $remaining_ice['service']);
      $stmt->bindParam('account_id', $remaining_ice['account_id']);
      $stmt->bindParam('payment_method', $remaining_ice['payment_method']);
      $stmt->bindParam('remaining_ice', $remaining_ice['remaining_ice']);

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
