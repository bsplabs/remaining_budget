<?php

class MediaWalletResources
{
  protected $db;
  protected $remainingBudgetCustomer;

  public function __construct()
  {
    $this->db = new Database();
    $this->remainingBudgetCustomer = new RemainingBudgetCustomer();
  }

  public function getMediaWallet($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon("latin1");

      $firstDateOfMonth = $year . "-" . $month . "-" . "01";
      $sixthDateOfMonth = $year . "-" . $month . "-" . "06";

      $report_date = date($firstDateOfMonth);
      $first_date_of_report_next_month = date('Y-m-d', strtotime('+1 month', strtotime($firstDateOfMonth)));
      $sixth_date_of_report_next_month = date('Y-m-d', strtotime('+1 month', strtotime($sixthDateOfMonth)));
      $last_data_of_report_this_month = date('Y-m-d', strtotime('last day of this month', strtotime($firstDateOfMonth)));

      
      $sql = "SELECT * FROM ready_topup_wallet 
              WHERE InsertDate < '{$first_date_of_report_next_month}'
              GROUP BY CustomerID, Service
              ORDER BY CustomerID ASC
            ";
      
      $stmt = $mainDB->prepare($sql);
      $stmt->execute();

      $i = 0;
      $mediaWallet = array();
      // $remainingWallet = array();
      while ($customerWalletServie = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql2 = "SELECT * 
                  FROM ready_topup_wallet 
                  WHERE InsertDate < '{$first_date_of_report_next_month}'
                    AND CustomerID = '{$customerWalletServie["CustomerID"]}' 
                    AND Service = '{$customerWalletServie["Service"]}' 
                  ORDER BY ID DESC 
                ";
        $stmt2 = $mainDB->prepare($sql2);
        $stmt2->execute(); // get last row of cusID + Service
        $remainingWallet = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Clearing
        $sql3 = "SELECT sum(rtw.Income) AS clearing_nextmonth
                 FROM ready_topup_wallet AS rtw
                 INNER JOIN ready_office_clearing AS roc ON rtw.newmemberid = roc.newmemberid
                 WHERE rtw.InsertDate >= '{$first_date_of_report_next_month}'
                  AND rtw.InsertDate < '{$sixth_date_of_report_next_month}'
                  AND rtw.CustomerID = :CustomerID
                  AND rtw.Service = '{$customerWalletServie["Service"]}'
                  AND roc.pay_on_date <= '{$last_data_of_report_this_month}'
                ";
        $stmt3 = $mainDB->prepare($sql3);
        $stmt3->bindParam('CustomerID', $customerWalletServie["CustomerID"], PDO::PARAM_INT);
        $stmt3->execute();
        $clearing = $stmt3->fetch(PDO::FETCH_ASSOC);

        // Members
        $sql4 = "SELECT
                  a.newmemberid,
                  b.bill_firstname,
                  b.bill_lastname,
                  b.bill_company,
                  b.taxID_corporation,
                  b.cardID
                 FROM ready_topup_wallet AS a
                 INNER JOIN ready_new_members AS b ON a.newmemberid = b.id
                 WHERE a.InsertDate < '{$sixth_date_of_report_next_month}'
                  AND a.CustomerID = '{$customerWalletServie["CustomerID"]}'
                  AND a.newmemberid != '0'
                 ORDER BY a.ID DESC
                 LIMIT 1
                ";
        $stmt4 = $mainDB->prepare($sql4);
        $stmt4->execute();
        $member = $stmt4->fetch(PDO::FETCH_ASSOC);

        // Group Data
        $mediaWallet[$i]["grandadmin_customer_id"] = $customerWalletServie["CustomerID"];
        if (!empty($member)) {
          if (empty($member["bill_company"])) {
            $mediaWallet[$i]["grandadmin_customer_name"] = iconv('TIS-620','UTF-8',$member["bill_firstname"]) . " " . iconv('TIS-620','UTF-8',$member["bill_lastname"]);
          } else {
            $mediaWallet[$i]["grandadmin_customer_name"] = $member["bill_company"] ? iconv('TIS-620','UTF-8',$member["bill_company"]) : "";
          }
          $mediaWallet[$i]["newmemberid"] = $member["newmemberid"] ? $member["newmemberid"] : "";
        } else {
          $mediaWallet[$i]["grandadmin_customer_name"] = "";
          $mediaWallet[$i]["newmemberid"] = "";
        }
        $mediaWallet[$i]["service"] = $customerWalletServie["Service"];
        $mediaWallet[$i]["remaining_wallet"] = $remainingWallet["Balance"];
        $mediaWallet[$i]["previous_clearing"] = $clearing["clearing_nextmonth"];

        // $remainingWallet[$i] = array_merge($remainingWallet[$i], $stmt3->fetch(PDO::FETCH_ASSOC));
        // array_push($remainingWalletSet, $remainingWallet[$i]);

        $i++;
      }

      $result["status"] = "success";
      $result["data"] = $mediaWallet;

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function checkWalletDataExists($month, $year, $walletData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT COUNT(*) as count_row
              FROM remaining_budget_media_wallet 
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND grandadmin_customer_name = :grandadmin_customer_name
                AND service = :service
              ";
      $stmt = $mainDB->prepare($sql);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $walletData["grandadmin_customer_id"]);
      $stmt->bindParam("grandadmin_customer_name", $walletData["grandadmin_customer_name"]);
      $stmt->bindParam("service", $walletData["service"]);
      $stmt->execute();

      $result["status"] = "success";
      $fetRowCount = $stmt->fetch(PDO::FETCH_ASSOC);
      $result["data"] = $fetRowCount["count_row"];

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = $e->getMessage();
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function addNewMediaWallet($month, $year, $walletData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "INSERT INTO remaining_budget_media_wallet (
                remaining_budget_customer_id,
                month, 
                year, 
                grandadmin_customer_id,
                grandadmin_customer_name,
                service,
                remaining_wallet,
                previous_clearing
              ) 
              VALUES (
                :remaining_budget_customer_id,
                :month,
                :year,
                :grandadmin_customer_id,
                :grandadmin_customer_name,
                :service,
                :remaining_wallet,
                :previous_clearing
              )";

      $stmt = $mainDB->prepare($sql);
      
      $stmt->bindParam('remaining_budget_customer_id', $walletData["remaining_budget_customer_id"]);
      $stmt->bindParam('month', $month);
      $stmt->bindParam('year', $year);
      $stmt->bindParam('grandadmin_customer_id', $walletData["grandadmin_customer_id"]);
      $stmt->bindParam('grandadmin_customer_name', $walletData["grandadmin_customer_name"]);
      $stmt->bindParam('service', $walletData["service"]);
      $stmt->bindParam('remaining_wallet', $walletData["remaining_wallet"]);
      if (empty($walletData["previous_clearing"])) {
        $walletData["previous_clearing"] = 0.0;
      }
      $stmt->bindParam('previous_clearing', $walletData["previous_clearing"]);

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

  public function updateMediaWallet($month, $year, $walletData)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_media_wallet
              SET remaining_budget_customer_id = :remaining_budget_customer_id,
                  remaining_wallet = :remaining_wallet,
                  previous_clearing = :previous_clearing,
                  updated_at = NOW()
              WHERE month = :month
                AND year = :year
                AND grandadmin_customer_id = :grandadmin_customer_id
                AND grandadmin_customer_name = :grandadmin_customer_name
                AND service = :service 
              ";

      $stmt = $mainDB->prepare($sql);

      $stmt->bindParam('remaining_budget_customer_id', $walletData["remaining_budget_customer_id"], PDO::PARAM_INT);
      $stmt->bindParam("remaining_wallet", $walletData["remaining_wallet"]);
      $stmt->bindParam("previous_clearing", $walletData["previous_clearing"]);
      $stmt->bindParam("month", $month);
      $stmt->bindParam("year", $year);
      $stmt->bindParam("grandadmin_customer_id", $walletData["grandadmin_customer_id"]);
      $stmt->bindParam("grandadmin_customer_name", $walletData["grandadmin_customer_name"]);
      $stmt->bindParam("service", $walletData["service"]);

      $stmt->execute();
      
      $result["status"] = "success";
      $result["data"] = $stmt->rowCount();

    } catch (PDOException $e) {
      $result["status"] = "fail";
      $result["data"] = 0;
    }

    $this->db->dbClose($mainDB);
    return $result;
  }

  public function getMonthYearFromReportStatusTable()
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "SELECT * FROM remaining_budget_report_status WHERE overall_status != 'completed' ORDER BY year ASC, month ASC, id ASC LIMIT 1";
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
      $sql = "UPDATE remaining_budget_report_status SET {$resource_type} = '{$status}' WHERE month = '{$month}' AND year = '{$year}' AND type = 'default'";
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

  public function updateReportStatusById($id, $resource_type, $status)
  {
    try {
      $mainDB = $this->db->dbCon();
      $sql = "UPDATE remaining_budget_report_status SET {$resource_type} = '{$status}' WHERE id = :id";
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

  

  function clearMediaWalletByMonthYear($month, $year)
  {
    try {
      $mainDB = $this->db->dbCon();
     
      $sql = "DELETE FROM remaining_budget_media_wallet WHERE month = :month AND year = :year";
      
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

  public function run()
  {
    $get_month_year = $this->getMonthYearFromReportStatusTable();

    foreach ($get_month_year["data"] as $key => $val) 
    {
      if($val["media_wallet"] == 'pending'){
        $get_media_wallet = $this->getMediaWallet($val["month"], $val["year"]);
        echo $val["month"] . " - " . $val["year"] . " --------------------------------------------------> \n";
        echo "Founded: " . count($get_media_wallet["data"]) . " rows \n";
        echo "\n";

        if (empty($get_media_wallet["data"])){
          $this->updateReportStatusById($val["id"], "media_wallet", "waiting");
          continue;
        } 
        $this->updateReportStatusById($val["id"], "media_wallet", "in_progress");
        // clear media wallet data by month and year
        $this->clearMediaWalletByMonthYear($val["month"], $val["year"]);

        foreach ($get_media_wallet["data"] as $media_wallet) 
        {
          $customerData = array(
            "grandadmin_customer_id" => $media_wallet["grandadmin_customer_id"],
            "grandadmin_customer_name" => $media_wallet["grandadmin_customer_name"]
          );
          $getRemainingBudgetCustomerID = $this->remainingBudgetCustomer->getRemainingBudgetCustomerID($customerData);
          $media_wallet["remaining_budget_customer_id"] = $getRemainingBudgetCustomerID;

          // insert media wallet
          $add_media_wallet = $this->addNewMediaWallet($val["month"], $val["year"], $media_wallet);
        }
        
        $this->updateReportStatusById($val["id"], "media_wallet", "waiting");
        $is_last_process = $this->remainingBudgetCustomer->checkLastProcess($val["id"]);
        if($is_last_process){
          $this->updateReportStatusById($val["id"], "overall_status", "waiting");
        }
      }
    }   

  }
}
