<?php

class ResourcesController extends Controller
{
  private $resourceModel;
  private $reportModel;
  protected $month;
  protected $year;

  public function __construct()
  {
    $this->resourceModel = $this->model("Resource");
    $this->reportModel = $this->model("Report");
    $last_month_timestamp =  strtotime("-1 month");
    $this->month = date("m", $last_month_timestamp);
    $this->year = date("Y");
  }

  public function index()
  {
    // header("Location: ");
  }

  public function getIce()
  {
    $data = $this->resourceModel->getGoogleSpending();
    echo "<pre>";
    print_r($data);
    echo "</pre>";
  }

  public function ice()
  {
    // $this->uploadFacebookSpending();
    // $this->uploadGoogleSpending();
  }

  public function importGoogleSpending()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (!in_array($_FILES["googleSpendingInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "fail",
          "type" => "alert",
          "error" => "File type not allowed"
        );
        echo json_encode($res);
        exit;
      }

      if (empty($_POST["month"]) || empty($_POST["month"])) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "Month and year are required"
        );
        echo json_encode($res);
        exit;
      }
      $month = $_POST["month"];
      $year = $_POST["year"];

      $targetFilePath = $_FILES["googleSpendingInputFile"]["tmp_name"];
      $googleSpending = array();
      $dataIndex = 0;

      // Open the file for reading
      if (($handle = fopen("{$targetFilePath}", "r")) !== FALSE) {
        $rowStartNumber = NULL;  
        for ($i = 0; $row = fgetcsv($handle); ++$i) {
          if ($row[0] === "รหัสบัญชี" && $row[1] === "บัญชี") {
            $rowStartNumber = $i;
            continue;
          }
          if ($rowStartNumber != NULL && $i > $rowStartNumber) {
            $googleSpending[$dataIndex]["google_id"] = $row[0];
            $googleSpending[$dataIndex]["google_account"] = $row[1];
            $googleSpending[$dataIndex]["budget_account"] = $row[2];
            $googleSpending[$dataIndex]["purchase_order"] = $row[3];
            $googleSpending[$dataIndex]["campaign"] = $row[4];
            $googleSpending[$dataIndex]["volume"] = $row[5];
            $googleSpending[$dataIndex]["unit"] = $row[6];
            $googleSpending[$dataIndex]["spending_total_price"] = $row[7];
            $dataIndex++;
          }
        }
        fclose($handle);
      }

      // clear data by month and year
      $this->resourceModel->clearrGoogleSpendingByMonthAndYear($month, $year);

      foreach ($googleSpending as $idx => $value) 
      {
        // Find grandadmin_customer_id
        $find_customer_id = $this->resourceModel->findCustomerID($value, "google");
        if ($find_customer_id["status"] === "success") {
          $value["grandadmin_customer_id"] = $find_customer_id["data"]["CustomerID"];
        } else {
          $value["grandadmin_customer_id"] = "";
        }

        // Find grandadmin_customer_name
        $find_customer_name = $this->resourceModel->findCustomerName("AdwordsCusId", $value["google_id"]);
        if ($find_customer_name["status"] === "success" && !empty($find_customer_name["data"])) {
          if (empty($find_customer_name["data"]["bill_company"])) {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_firstname"]) . " " . iconv('TIS-620','UTF-8', $find_customer_name["data"]["bill_lastname"]);
          } else {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_company"]);
          }
          $value["grandadmin_customer_name"] = $customer_name;
        } else {
          $value["grandadmin_customer_name"] = "";
        }

        // Find remaining_budget_customer_id
        if ($value["grandadmin_customer_name"] !== "" && $value["grandadmin_customer_id"] !== "") {
          $find_remaining_budget_customer_id = $this->resourceModel->findRemainingBudgetCustomerID($value["grandadmin_customer_id"], $value["grandadmin_customer_name"]);
          if ($find_remaining_budget_customer_id["status"] === "success") {
            $value["remaining_budget_customer_id"] = $find_remaining_budget_customer_id["data"];
          } else {
            // add new grandadmin_customer
            $addnew_gac = $this->resourceModel->addNewGrandAdminCustomer($value["grandadmin_customer_id"], $value["grandadmin_customer_name"]);
            if ($addnew_gac["status"] === "success" && !empty($addnew_gac["data"])) {
              $value["remaining_budget_customer_id"] = $addnew_gac["data"];
            } else {
              $value["remaining_budget_customer_id"] = NULL;
            }
          } 
        } else {
          $value["remaining_budget_customer_id"] = NULL;
        }

        $insert_google_spending = $this->resourceModel->addGoogleSpendingData($month, $year, $value);
        if ($insert_google_spending["status"] === "success") {}
      }

      // update google spending status on report status table
      $this->reportModel->updateReportStatus($month, $year, "google_spending", "waiting");
      
      $get_google_spending_detail = $this->resourceModel->getTotalDataUpdate("google_spending", $month, $year);
      $get_status_resource = $this->resourceModel->getStatusResources($month, $year);
      $overall_status = $get_status_resource["data"]["overall_status"];
      $waiting = 0;
      foreach ($get_status_resource["data"] as $rs => $status) {
        if ($rs === "overall_status") continue;

        if ($rs !== "transfer") {
          if ($status === "waiting") $waiting++;
        }
      }

      if ($waiting === 7 && $overall_status !== "waiting") {
        // update overall status
        $this->reportModel->updateReportStatus($month, $year, "overall_status", "waiting");
        $overall_status = "waiting";
      }

      // ------------------
      $allowed_generate_data = $this->getIsAllowGenerateButton($month, $year, $overall_status);

      $response = array(
        "status" => "success",
        "type" => "alert",
        "overall_status" => $overall_status,
        "allowed_generate_data" => $allowed_generate_data,
        "message" => "",
        "data" => array(
          "import_total" => $get_google_spending_detail["data"]["row_count"],
          "updated_at" => $get_google_spending_detail["data"]["updated_at"]
        )
      );
      echo json_encode($response);
      exit;

    } else {
      $response = array(
        "status" => "error",
        "type" => "alert",
        "message" => "Allow only POST method"
      );
      echo json_encode($response);
      exit;
    }

  }

  public function updateGoogleSpending()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (!in_array($_FILES["googleSpendingInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "fail",
          "type" => "alert",
          "error" => "File type not allowed"
        );
        echo json_encode($res);
        exit;
      }

      if (empty($_POST["month"]) || empty($_POST["year"])) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "Month and year are required"
        );
        echo json_encode($res);
        exit;
      }
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];

      $targetFilePath = $_FILES["googleSpendingInputFile"]["tmp_name"];
      $googleSpending = array();
      $remaining_budget_customer_id_clear_list = array();
      $dataIndex = 0;

      // Open the file for reading
      if (($handle = fopen("{$targetFilePath}", "r")) !== FALSE) {
        $rowStartNumber = NULL;  
        for ($i = 0; $row = fgetcsv($handle); ++$i) {
          if ($row[0] === "รหัสบัญชี" && $row[1] === "บัญชี") {
            $rowStartNumber = $i;
            continue;
          }
          if ($rowStartNumber != NULL && $i > $rowStartNumber) {
            $googleSpending[$dataIndex]["google_id"] = $row[0];
            $googleSpending[$dataIndex]["google_account"] = $row[1];
            $googleSpending[$dataIndex]["budget_account"] = $row[2];
            $googleSpending[$dataIndex]["purchase_order"] = $row[3];
            $googleSpending[$dataIndex]["campaign"] = $row[4];
            $googleSpending[$dataIndex]["volume"] = $row[5];
            $googleSpending[$dataIndex]["unit"] = $row[6];
            $googleSpending[$dataIndex]["spending_total_price"] = $row[7];
            $dataIndex++;
          }
          
        }
        fclose($handle);
      }

      $worker_id = $this->reportModel->createUpdateWorker("google_spending",$month, $year, $updated_by);
      $worker_id = $worker_id["data"];

      // check remaining_budget_customer_id
      foreach ($googleSpending as $idx => $value) 
      {
        // Find grandadmin_customer_id
        $find_customer_id = $this->resourceModel->findCustomerID($value, "google");
        if ($find_customer_id["status"] === "success") {
          $value["grandadmin_customer_id"] = $find_customer_id["data"]["CustomerID"];
        } else {
          $value["grandadmin_customer_id"] = "";
        }

        // Find grandadmin_customer_name
        $find_customer_name = $this->resourceModel->findCustomerName("AdwordsCusId", $value["google_id"]);
        
        if ($find_customer_name["status"] === "success" && !empty($find_customer_name["data"])) {
          if (empty($find_customer_name["data"]["bill_company"])) {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_firstname"]) . " " . iconv('TIS-620','UTF-8', $find_customer_name["data"]["bill_lastname"]);
          } else {
            $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_company"]);
          }
          $value["grandadmin_customer_name"] = $customer_name;
        } else {
          $value["grandadmin_customer_name"] = "";
        }

        // Find remaining_budget_customer_id
        if ($value["grandadmin_customer_name"] !== "" && $value["grandadmin_customer_id"] !== "") {
          $find_remaining_budget_customer_id = $this->resourceModel->findRemainingBudgetCustomerID($value["grandadmin_customer_id"], $value["grandadmin_customer_name"]);
          if ($find_remaining_budget_customer_id["status"] === "success") {
            $value["remaining_budget_customer_id"] = $find_remaining_budget_customer_id["data"];
          } else {
            // add new grandadmin_customer
            $addnew_gac = $this->resourceModel->addNewGrandAdminCustomer($value["grandadmin_customer_id"], $value["grandadmin_customer_name"]);
            if ($addnew_gac["status"] === "success" && !empty($addnew_gac["data"])) {
              $value["remaining_budget_customer_id"] = $addnew_gac["data"];
            } else {
              $value["remaining_budget_customer_id"] = NULL;
            }
          } 
        } else {
          $value["remaining_budget_customer_id"] = NULL;
        }
        $googleSpending[$idx]["remaining_budget_customer_id"] = $value["remaining_budget_customer_id"];
        $googleSpending[$idx]["grandadmin_customer_id"] = $value["grandadmin_customer_id"];
        $googleSpending[$idx]["grandadmin_customer_name"] = $value["grandadmin_customer_name"];
        if(!in_array($value["remaining_budget_customer_id"],$remaining_budget_customer_id_clear_list)){
          $remaining_budget_customer_id_clear_list[] = $value["remaining_budget_customer_id"];
        }
        
      }

      //check and clear before replace
      foreach ($remaining_budget_customer_id_clear_list as $idx => $value) 
      {
        if($value != NULL){
          $this->resourceModel->clearGoogleSpendingByID($month, $year,$value);
        }
      }

      //add new data
      foreach ($googleSpending as $idx => $value) 
      {
        $insert_google_spending = $this->resourceModel->addGoogleSpendingData($month, $year, $value);
      }


      // update google spending status on report status table
      $update_status = $this->reportModel->updateReportStatusById($worker_id, "google_spending", "waiting");
      $update_status = $this->reportModel->updateReportStatusById($worker_id, "overall_status", "waiting");
      
      $response = array(
        "status" => "success"
      );
      echo json_encode($response);
      exit;

    } else {
      $response = array(
        "status" => "error",
        "type" => "alert",
        "message" => "Allow only POST method"
      );
      echo json_encode($response);
      exit;
    }

  }

  public function importFacebookSpending()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );
      
      if (!in_array($_FILES["facebookSpendingInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "File type not allowed"
        );
        echo json_encode($res);
        exit;
      }

      if (empty($_POST["month"]) || empty($_POST["month"])) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "Month and year are required"
        );
        echo json_encode($res);
        exit;
      }
      $month = $_POST["month"];
      $year = $_POST["year"];

      $ignore_invalid_data = false;
      if (isset($_POST["ignore_invalid_data"])) {
        $ignore_invalid_data = $_POST["ignore_invalid_data"];
      }

      $file_path = $_FILES["facebookSpendingInputFile"]["tmp_name"];
      $total = 0;
      $valid_total = 0;
      $invalid_total = 0;
      $invalid_lists = array();
      
      $fb_spending_lists = array();
      $data_index = 0;
      
      if (($handle = fopen($file_path, "r")) !== FALSE) {
        while (!feof($handle)) {
          $row = fgetcsv($handle);
          if ($row[0] == "Facebook Ireland Limited" && $row[3] == "ReadyPlanet Co., Ltd") {
            $total++;
            $fb_spending = array(
              "month" => $month,
              "year" => $year,
              "billing_period" => $row[4],
              "currency" => $row[10],
              "payment_status" => $row[13],
              "facebook_id" => trim($row[16], "_"),
              "spending_total_price" => $this->getPrice($row[26]),
              "campaign_id" => trim($row[18], "_")
            );

            $billing_period = $fb_spending["billing_period"];
            $bp_exp = explode("-", $billing_period);
            $input_date = strtotime($bp_exp[1] . "/" . $bp_exp[2] . "/" . $bp_exp[0]);
            
            $input_month = date("m", $input_date);
            $input_year = date("Y", $input_date);

            // Check month and year
            if ($input_month != $month || $input_year != $year) {
              array_push($invalid_lists, array(
                "facebook_id" => $fb_spending["facebook_id"],
                "error_message" => "Month and Year (billing_date) do not match with last month and year"
              ));
              $invalid_total++;
              continue;
            }

            array_push($fb_spending_lists, $fb_spending);
            $data_index++;
            $valid_total++;
          }
        }
        fclose($handle);

        if (!$ignore_invalid_data && $invalid_total > 0) {
          $response = array(
            "status" => "error",
            "type" => "modal",
            "data" => array(
              "total" => $total,
              "valid_total" => $valid_total,
              "invalid_total" => $invalid_total,
              // "invalid_lists" => $invalid_lists
            )
          );
          echo json_encode($response);
          exit;
        }

        if ($valid_total == 0) {
          $response = array(
            "status" => "error",
            "type" => "alert",
            "data" => "",
            "message" => "There are not any valid data to import"
          );
          echo json_encode($response);
          exit;
        }

        // Clear data by month and year
        $this->resourceModel->clearFacebookSpendingByMonthYear($month, $year);

        foreach ($fb_spending_lists as $idx => $fb_spending) 
        {
          // Find grandadmin_customer_id
          $find_customer_id = $this->resourceModel->findCustomerID($fb_spending, "facebook");
          if ($find_customer_id["status"] === "success") {
            $fb_spending["grandadmin_customer_id"] = $find_customer_id["data"]["CustomerID"];
          } else {
            $fb_spending["grandadmin_customer_id"] = "";
          }

          // Find grandadmin_customer_name
          $find_customer_name = $this->resourceModel->findCustomerName("facebookID", $fb_spending["facebook_id"]);
          if ($find_customer_name["status"] === "success" && !empty($find_customer_name["data"])) {
            if (empty($find_customer_name["data"]["bill_company"])) {
              $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_firstname"]) . " " . iconv('TIS-620','UTF-8', $find_customer_name["data"]["bill_lastname"]);
            } else {
              $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_company"]);
            }
            $fb_spending["grandadmin_customer_name"] = $customer_name;
          } else {
            $fb_spending["grandadmin_customer_name"] = "";
          }
          
          // if (empty($fb_spending["grandadmin_customer_id"])) {
          //   $fb_spending["grandadmin_customer_name"] = "";
          // } else {

          // }

          // Find remaining_budget_customer_id
          if ($fb_spending["grandadmin_customer_name"] !== "" && $fb_spending["grandadmin_customer_id"] !== "") {
            $find_remaining_budget_customer_id = $this->resourceModel->findRemainingBudgetCustomerID();
            if ($find_remaining_budget_customer_id["status"] === "success") {
              $fb_spending["remaining_budget_customer_id"] = $find_remaining_budget_customer_id["data"];
            } else {
              $addnew_gac = $this->resourceModel->addNewGrandAdminCustomer($fb_spending["grandadmin_customer_id"], $fb_spending["grandadmin_customer_name"]);
              if ($addnew_gac["status"] === "success" && empty($addnew_gac["data"])) {
                $fb_spending["remaining_budget_customer_id"] = $addnew_gac["data"];
              } else {
                $fb_spending["remaining_budget_customer_id"] = NULL;
              }
            } 
          } else {
            $fb_spending["remaining_budget_customer_id"] = NULL;
          }

          $insert_facebook_spending = $this->resourceModel->insertFacebookSpending($month, $year, $fb_spending);
          if ($insert_facebook_spending["status"] === "success") { }
        }

        // Update facebook spending status on report status table
        $this->reportModel->updateReportStatus($month, $year, "facebook_spending", "waiting");
        
        $get_facebook_spending_detail = $this->resourceModel->getTotalDataUpdate("facebook_spending", $month, $year);
        $get_status_resource = $this->resourceModel->getStatusResources($month, $year);
        $overall_status = $get_status_resource["data"]["overall_status"];
        $waiting = 0;
        foreach ($get_status_resource["data"] as $rs => $status) {
          if ($rs === "overall_status") continue;

          if ($rs !== "transfer") {
            if ($status === "waiting") $waiting++;
          }
        }

        if ($waiting === 7 && $overall_status !== "waiting") {
          $this->reportModel->updateReportStatus($month, $year, "overall_status", "waiting");
          $overall_status = "waiting";
        }

        $allowed_generate_data = $this->getIsAllowGenerateButton($month, $year, $overall_status);

        $response = array(
          "status" => "success",
          "type" => "alert",
          "overall_status" => $overall_status,
          "allowed_generate_data" => $allowed_generate_data,
          "message" => "",
          "data" => array(
            "import_total" => $get_facebook_spending_detail["data"]["row_count"],
            "updated_at" => $get_facebook_spending_detail["data"]["updated_at"]
          )
        );
        echo json_encode($response);
        exit;

      } else {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "Can't access file"
        );
        echo json_encode($res);
        exit;
      }
    } else {
      $res = array(
        "status" => "error",
        "type" => "alert",
        "meesage" => "Allow only POST method"
      );
      echo json_encode($res);
      exit;
    }
  }

  public function updateFacebookSpending()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );
      
      if (!in_array($_FILES["facebookSpendingInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "File type not allowed"
        );
        echo json_encode($res);
        exit;
      }

      if (empty($_POST["month"]) || empty($_POST["month"])) {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "Month and year are required"
        );
        echo json_encode($res);
        exit;
      }
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];

      $file_path = $_FILES["facebookSpendingInputFile"]["tmp_name"];
      $total = 0;
      $valid_total = 0;
      $invalid_total = 0;
      $invalid_lists = array();
      
      $fb_spending_lists = array();
      $remaining_budget_customer_id_clear_list = array();
      $data_index = 0;
      
      if (($handle = fopen($file_path, "r")) !== FALSE) {
        while (!feof($handle)) {
          $row = fgetcsv($handle);
          if ($row[0] == "Facebook Ireland Limited" && $row[3] == "ReadyPlanet Co., Ltd") {
            $total++;
            $fb_spending = array(
              "month" => $month,
              "year" => $year,
              "billing_period" => $row[4],
              "currency" => $row[10],
              "payment_status" => $row[13],
              "facebook_id" => trim($row[16], "_"),
              "spending_total_price" => $this->getPrice($row[26]),
              "campaign_id" => trim($row[18], "_")
            );

            $billing_period = $fb_spending["billing_period"];
            $bp_exp = explode("-", $billing_period);
            $input_date = strtotime($bp_exp[1] . "/" . $bp_exp[2] . "/" . $bp_exp[0]);
            
            $input_month = date("m", $input_date);
            $input_year = date("Y", $input_date);

            // Check month and year
            if ($input_month != $month || $input_year != $year) {
              array_push($invalid_lists, array(
                "facebook_id" => $fb_spending["facebook_id"],
                "error_message" => "Month and Year (billing_date) do not match with last month and year"
              ));
              $invalid_total++;
              continue;
            }

            array_push($fb_spending_lists, $fb_spending);
            $data_index++;
            $valid_total++;
          }
        }
        fclose($handle);
        if ($valid_total == 0) {
          $response = array(
            "status" => "error",
            "type" => "alert",
            "data" => "",
            "message" => "There are not any valid data to import"
          );
          echo json_encode($response);
          exit;
        }

        $worker_id = $this->reportModel->createUpdateWorker("facebook_spending",$month, $year, $updated_by);
        $worker_id = $worker_id["data"];
        
        foreach ($fb_spending_lists as $idx => $fb_spending) 
        {
          
          // Find grandadmin_customer_id
          $find_customer_id = $this->resourceModel->findCustomerID($fb_spending, "facebook");
          print_r($find_customer_id);
          if ($find_customer_id["status"] === "success") {
            $fb_spending["grandadmin_customer_id"] = $find_customer_id["data"]["CustomerID"];
          } else {
            $fb_spending["grandadmin_customer_id"] = "";
          }

          // Find grandadmin_customer_name
          $find_customer_name = $this->resourceModel->findCustomerName("facebookID", $fb_spending["facebook_id"]);
          if ($find_customer_name["status"] === "success" && !empty($find_customer_name["data"])) {
            if (empty($find_customer_name["data"]["bill_company"])) {
              $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_firstname"]) . " " . iconv('TIS-620','UTF-8', $find_customer_name["data"]["bill_lastname"]);
            } else {
              $customer_name = iconv('TIS-620','UTF-8',$find_customer_name["data"]["bill_company"]);
            }
            $fb_spending["grandadmin_customer_name"] = $customer_name;
          } else {
            $fb_spending["grandadmin_customer_name"] = "";
          }

          // Find remaining_budget_customer_id
          if ($fb_spending["grandadmin_customer_name"] !== "" && $fb_spending["grandadmin_customer_id"] !== "") {
            $find_remaining_budget_customer_id = $this->resourceModel->findRemainingBudgetCustomerID($fb_spending["grandadmin_customer_id"],$fb_spending["grandadmin_customer_name"]);
            if ($find_remaining_budget_customer_id["status"] === "success") {
              $fb_spending["remaining_budget_customer_id"] = $find_remaining_budget_customer_id["data"];
            } else {
              $addnew_gac = $this->resourceModel->addNewGrandAdminCustomer($fb_spending["grandadmin_customer_id"], $fb_spending["grandadmin_customer_name"]);
              if ($addnew_gac["status"] === "success" && empty($addnew_gac["data"])) {
                $fb_spending["remaining_budget_customer_id"] = $addnew_gac["data"];
              } else {
                $fb_spending["remaining_budget_customer_id"] = NULL;
              }
            } 
          } else {
            $fb_spending["remaining_budget_customer_id"] = NULL;
          }
          $fb_spending_lists[$idx]["remaining_budget_customer_id"] = $fb_spending["remaining_budget_customer_id"];
          $fb_spending_lists[$idx]["grandadmin_customer_id"] = $fb_spending["grandadmin_customer_id"];
          $fb_spending_lists[$idx]["grandadmin_customer_name"] = $fb_spending["grandadmin_customer_name"];
          if(!in_array($fb_spending["remaining_budget_customer_id"],$remaining_budget_customer_id_clear_list)){
            $remaining_budget_customer_id_clear_list[] = $fb_spending["remaining_budget_customer_id"];
          }
          
        }


        //check and clear before replace
        foreach ($remaining_budget_customer_id_clear_list as $idx => $value) 
        {
          if($value != NULL){
            $this->resourceModel->clearFacebookSpendingByID($month, $year,$value);
          }
        }

        //add new data
        foreach ($fb_spending_lists as $idx => $value) 
        {
          $value["updated_by"] = $updated_by;
          $insert_facebook_spending = $this->resourceModel->insertFacebookSpending($month, $year, $value);
        }

        // update google spending status on report status table
        $update_status = $this->reportModel->updateReportStatusById($worker_id, "facebook_spending", "waiting");
        $update_status = $this->reportModel->updateReportStatusById($worker_id, "overall_status", "waiting");
        
        $response = array(
          "status" => "success"
        );
        echo json_encode($response);
        exit;
      } else {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "meesage" => "Allow only POST method"
        );
        echo json_encode($res);
        exit;
      }
    }
  }

  public function updateApiData()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];
      $media_wallet = $_POST["media_wallet"];
      $withholding_tax = $_POST["withholding_tax"];
      $free_click_cost = $_POST["free_click_cost"];
      $remaining_ice = $_POST["remaining_ice"];
      if($media_wallet == 'true'){
        $worker_id = $this->reportModel->createUpdateWorker("media_wallet",$month, $year, $updated_by);
      }
      if($withholding_tax == 'true'){
        $worker_id = $this->reportModel->createUpdateWorker("withholding_tax",$month, $year, $updated_by);
      }
      if($free_click_cost == 'true'){
        $worker_id = $this->reportModel->createUpdateWorker("free_click_cost",$month, $year, $updated_by);
      }
      if($remaining_ice == 'true'){
        $worker_id = $this->reportModel->createUpdateWorker("remaining_ice",$month, $year, $updated_by);
      }
      
      $worker_id = $worker_id["data"];
      $response = array(
        "status" => "success"
      );
      echo json_encode($response);
      exit;
    }
  }


  public function importWalletTransfer()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (!in_array($_FILES["transferInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "fail",
          "error" => "Not allowed file type"
        );
        echo json_encode($res);
      }

      $file_name = $_FILES["transferInputFile"]["name"];
      $targetFilePath = $_FILES["transferInputFile"]["tmp_name"];
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];
      $wallet_transfers = array();
      $data_index = 0;
      $import_result = array();
      if (($handle = fopen($targetFilePath, "r")) !== FALSE) {
        while (!feof($handle)) {
          $row = fgetcsv($handle);
          if($data_index != 0){
            $wallet_transfer = array(
              "source_grandadmin_customer_id" => $row[0],
              "source_grandadmin_customer_name" => $row[1],
              "destination_grandadmin_customer_id" => $row[2],
              "destination_grandadmin_customer_name" => $row[3],
              "source_value" => $row[4],
              "note" => $row[5],
              "clearing" => $row[6]
            );
            array_push($wallet_transfers, $wallet_transfer);
          }          
          $data_index++;
        }
        fclose($handle);

        // Clear data by month and year
        // $this->resourceModel->clearWalletTransferByMonthYear($month, $year);
        
        foreach ($wallet_transfers as $idx => $wallet_transfer) 
        {
          if ($wallet_transfers[$idx]["source_grandadmin_customer_id"] !== "" && $wallet_transfers[$idx]["source_grandadmin_customer_name"] !== "" && 
          $wallet_transfers[$idx]["destination_grandadmin_customer_id"] !== "" && $wallet_transfers[$idx]["destination_grandadmin_customer_name"] !== "") 
          {
            $wallet_transfers[$idx]["source_remaining_budget_customer_id"] = $this->getRemainingBudgetCustomerId('grandadmin',$wallet_transfers[$idx]["source_grandadmin_customer_id"],$wallet_transfers[$idx]["source_grandadmin_customer_name"],'wallet_transfer');
            $wallet_transfers[$idx]["destination_remaining_budget_customer_id"] = $this->getRemainingBudgetCustomerId('grandadmin',$wallet_transfers[$idx]["destination_grandadmin_customer_id"],$wallet_transfers[$idx]["destination_grandadmin_customer_name"],'wallet_transfer');
            $wallet_transfers[$idx]["updated_by"] = $updated_by;
            $insert_wallet_transfer = $this->resourceModel->insertWalletTransfer($month, $year, $wallet_transfers[$idx]);

          }
        }
      } else {
        $res = array(
          "status" => "fail",
          "error" => "Can not open file"
        );
        echo json_encode($res);
      }
    } else {
      $res = array(
        "status" => "fail",
        "error" => "Allow only POST method"
      );
      echo json_encode($res);
    }
  }

  public function updateWalletTransfer()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (!in_array($_FILES["transferInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "fail",
          "error" => "Not allowed file type"
        );
        echo json_encode($res);
      }

      $file_name = $_FILES["transferInputFile"]["name"];
      $targetFilePath = $_FILES["transferInputFile"]["tmp_name"];
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];
      $remaining_budget_customer_id_clear_list = array();
      $wallet_transfers = array();
      $data_index = 0;
      $import_result = array();
      if (($handle = fopen($targetFilePath, "r")) !== FALSE) {
        while (!feof($handle)) {
          $row = fgetcsv($handle);
          if($data_index != 0){
            $wallet_transfer = array(
              "source_grandadmin_customer_id" => $row[0],
              "source_grandadmin_customer_name" => $row[1],
              "destination_grandadmin_customer_id" => $row[2],
              "destination_grandadmin_customer_name" => $row[3],
              "source_value" => $row[4],
              "note" => $row[5],
              "clearing" => $row[6]
            );
            array_push($wallet_transfers, $wallet_transfer);
          }          
          $data_index++;
        }
        fclose($handle);

        $worker_id = $this->reportModel->createUpdateWorker("transfer",$month, $year, $updated_by);
        $worker_id = $worker_id["data"];
        $remaining_budget_customer_id_tmp = "";
        foreach ($wallet_transfers as $idx => $wallet_transfer) 
        {
          if ($wallet_transfers[$idx]["source_grandadmin_customer_id"] !== "" && $wallet_transfers[$idx]["source_grandadmin_customer_name"] !== "" && 
          $wallet_transfers[$idx]["destination_grandadmin_customer_id"] !== "" && $wallet_transfers[$idx]["destination_grandadmin_customer_name"] !== "") {
            $wallet_transfers[$idx]["source_remaining_budget_customer_id"] = $this->getRemainingBudgetCustomerId('grandadmin',$wallet_transfers[$idx]["source_grandadmin_customer_id"],$wallet_transfers[$idx]["source_grandadmin_customer_name"],'wallet_transfer');
            $wallet_transfers[$idx]["destination_remaining_budget_customer_id"] = $this->getRemainingBudgetCustomerId('grandadmin',$wallet_transfers[$idx]["destination_grandadmin_customer_id"],$wallet_transfers[$idx]["destination_grandadmin_customer_name"],'wallet_transfer');
            $remaining_budget_customer_id_tmp = $wallet_transfers[$idx]["source_remaining_budget_customer_id"].",".$wallet_transfers[$idx]["destination_remaining_budget_customer_id"];
            if(!in_array($remaining_budget_customer_id_tmp,$remaining_budget_customer_id_clear_list)){
              $remaining_budget_customer_id_clear_list[] = $remaining_budget_customer_id_tmp;
            }
          }
        }

        //check and clear before replace
        foreach ($remaining_budget_customer_id_clear_list as $idx => $value) 
        {
          if($value != NULL){
            $value = explode(",", $value);
            $source_remaining_budget_customer_id = $value[0];
            $destination_remaining_budget_customer_id = $value[1];
            $this->resourceModel->clearWalletTransferByID($month, $year,$source_remaining_budget_customer_id,$destination_remaining_budget_customer_id);
          }
        }

        //add new data
        foreach ($wallet_transfers as $idx => $value) 
        {
          $value["updated_by"] = $updated_by;
          $insert_wallet_transfer = $this->resourceModel->insertWalletTransfer($month, $year, $value);
        }

        // update google spending status on report status table
        $update_status = $this->reportModel->updateReportStatusById($worker_id, "transfer", "waiting");
        $update_status = $this->reportModel->updateReportStatusById($worker_id, "overall_status", "waiting");
        $res = array(
          "status" => "success",
          "message" => ""
        );
        echo json_encode($res);
        exit;
        
      } else {
        $res = array(
          "status" => "fail",
          "error" => "Can not open file"
        );
        echo json_encode($res);
      }
    } else {
      $res = array(
        "status" => "fail",
        "error" => "Allow only POST method"
      );
      echo json_encode($res);
    }
  }

  public function updateAdjustment()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (!in_array($_FILES["AdjustmentInputFile"]["type"], $allowedFileType)) {
        $res = array(
          "status" => "fail",
          "error" => "Not allowed file type"
        );
        echo json_encode($res);
      }

      $file_name = $_FILES["AdjustmentInputFile"]["name"];
      $targetFilePath = $_FILES["AdjustmentInputFile"]["tmp_name"];
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];
      $remaining_budget_customer_id_clear_list = array();
      $adjustments = array();
      $data_index = 0;
      $month = $_POST["month"];
      $year = $_POST["year"];
      $import_result = array();
      if (($handle = fopen($targetFilePath, "r")) !== FALSE) {
        while (!feof($handle)) {
          $row = fgetcsv($handle);
          if($data_index != 0 && $row[0] != ""){
            $adjustment = array(
              "report_id" => $row[0],
              "parent_id" => $row[1],
              "grandadmin_customer_id" => $row[2],
              "grandadmin_customer_name" => $row[3],
              "offset_acct" => $row[4],
              "offset_acct_name" => $row[5],
              "adjustment_remain" => $row[6],
              "adjustment_remain_note" => $row[7],
              "adjustment_free_click_cost" => $row[8],
              "adjustment_free_click_cost_note" => $row[9],
              "adjustment_free_click_cost_old" => $row[10],
              "adjustment_free_click_cost_old_note" => $row[11],
              "adjustment_cash_advance" => $row[12],
              "adjustment_cash_advance_note" => $row[13],
              "adjustment_max" => $row[14],
              "adjustment_max_note" => $row[15],
              "adjustment_front_end" => $row[16],
              "adjustment_front_end_note" => $row[17]
            );
            array_push($adjustments, $adjustment);
          }          
          $data_index++;
        }
        fclose($handle);

        
        foreach ($adjustments as $idx => $adjustment) 
        {
          
          $update_adjustment = $this->resourceModel->updateAdjustment($month,$year,$adjustment);
          $re_calculate = $this->reCalculate($adjustment["report_id"]);
          
        }

        $res = array(
          "status" => "success",
          "message" => ""
        );
        echo json_encode($res);
        exit;
        
      } else {
        $res = array(
          "status" => "fail",
          "error" => "Can not open file"
        );
        echo json_encode($res);
      }
    } else {
      $res = array(
        "status" => "fail",
        "error" => "Allow only POST method"
      );
      echo json_encode($res);
    }
  }

  private function reCalculate($report_id)
  {
    $reconcile_data = $this->reportModel->getReconcileDataByReportId($report_id);
    $reconcile_data = $reconcile_data["data"];
    $cash_advance = $reconcile_data['last_month_remaining'] + $reconcile_data['adjustment_remain'] + $reconcile_data['receive'] + $reconcile_data['invoice'] + $reconcile_data['transfer'] + $reconcile_data['ads_credit_note'] + $reconcile_data['spending_invoice'] + $reconcile_data['adjustment_free_click_cost'] + $reconcile_data['adjustment_free_click_cost_old'] + $reconcile_data['adjustment_cash_advance'] + $reconcile_data['adjustment_max'];
    $remaining_budget = $reconcile_data['remaining_ice'] + $reconcile_data['wallet'] + $reconcile_data['wallet_free_click_cost'] + $reconcile_data['withholding_tax'] + $reconcile_data['adjustment_front_end'];
    $difference = $remaining_budget - $cash_advance;

    $reconcile_data = $this->reportModel->updateReconcileReCalculateByReportId($report_id, $cash_advance, $remaining_budget, $difference);
  }
  
  // Get report status
  public function getStatusResources($year = "", $month = "")
  {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      header("Content-Type: application/json");
      
      if (empty($month) || empty($year)) {
        $result = array(
          "status" => "error",
          "message" => "Required month and year to get status resource"
        );
        echo json_encode($result);
        exit;
      }

      $get_status_resources = $this->resourceModel->getStatusResources($month, $year);
      if ($get_status_resources["status"] === 'fail') {
        $result = array(
          "status" => "error",
          "message" => "Can't get status of resources"
        );
        echo json_encode($result);
        exit;
      }

      $report_status = array();
      $waiting = 0;
      foreach ($get_status_resources["data"] as $rs => $value) 
      {
        if ($rs === "overall_status") continue;

        if ($rs === "transfer") {
          $rs = "wallet_" . $rs;
        }

        // re-check all resources --> check out at table
        $get_total_data_update = $this->resourceModel->getTotalDataUpdate($rs, $month, $year);

        $report_status[$rs] = array(
          "status" => $value,
          "total" => $get_total_data_update["data"]["row_count"],
          "updated_at" => $get_total_data_update["data"]["updated_at"]
        );

        if ($rs !== "wallet_transfer") {
          if ($value === "waiting") {
            $waiting++;
          }
        }
      }

      // check overall status for enable generate button
      $overall_status = $get_status_resources["data"]["overall_status"];
      if ($waiting === 7 && $overall_status == "pending") {
        // update overall status
        $this->reportModel->updateReportStatus($month, $year, "overall_status", "waiting");
        $overall_status = "waiting";
      }

      // check previousely reconcile data
      $allowed_generate_data = $this->getIsAllowGenerateButton($month, $year, $overall_status);

      $result = array(
        "status" => "success",
        "message" => "",
        "overall_status" => $overall_status,
        "allowed_generate_data" => $allowed_generate_data,
        "data" => $report_status
      );
      echo json_encode($result);

    } else {
      $result = array(
        "status" => "fail",
        "message" => "Allowed only GET Method"
      );
      echo json_encode($result);
    }

  }

  public function getIsAllowGenerateButton($month, $year, $overall_status)
  {
    $allowed_generate_data = false;
    if ($month === START_MONTH && $year === START_YEAR) {
      if ($overall_status === "waiting") $allowed_generate_data = true;
    } else {
      $get_previous_report_status = $this->resourceModel->getProviousReportStatus($month, $year);
      if ($get_previous_report_status["status"] === "success" && $get_previous_report_status["data"] == 0) {
        $allowed_generate_data = true;
      }
    }
    
    return $allowed_generate_data;
  }

  public function getPrice($priceText)
  {
    return preg_replace('/[^0-9.]/', '', $priceText); 
  }

}
