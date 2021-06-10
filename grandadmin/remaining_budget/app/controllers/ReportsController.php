<?php
error_reporting(E_ALL & ~E_NOTICE);

require_once ROOTPATH . "/app/vendors/PHPExcel/PHPExcel/IOFactory.php";

class ReportsController extends Controller
{
  private $reportModel;
  private $resourceModel;
  protected $generateRemainingBudget = false;
  protected $month;
  protected $year;

  public function __construct()
  {
    $this->reportModel = $this->model("Report");
    $this->resourceModel = $this->model("Resource");
    $last_month_timestamp =  strtotime("-1 month");
    $this->month = date("m", $last_month_timestamp);
    $this->year = date("Y");
  }

  public function index($year = "", $month = "")
  {
    
    $data = array();
    $month_year_lists = array();
    $getMonthYearLists = $this->reportModel->getMonthYearLists();
    // print_r($getMonthYearLists);
    if ($getMonthYearLists["status"] === "success" && !empty($getMonthYearLists["data"])) {
      foreach ($getMonthYearLists["data"] as $key => $my) {
        $month_year_lists[$my["month"] . "_" . $my["year"]] = $this->parseMonthYear($my["month"], $my["year"]);
        $month_default = $my["month"];
        $year_default = $my["year"];
      }
    } else {
      $month = "01";
      $year = "2021";
      $data["month_year_selected"] = $month . "_" . $year;
      $month_year_lists[$data["month_year_selected"]] = $this->parseMonthYear($month, $year);
    }
    if ($month == "" || $year == "") {
      $month = $month_default;
      $year = $year_default;
    }

    $data["month_year_selected"] = $month . "_" . $year;
    $data["month_year_lists"] = $month_year_lists;
    $data["month"] = $month;
    $data["year"] = $year;
    $data["page_name"] = $this->findPageName();

    // check report status
    $reportStatus = $this->reportModel->getReporttStatus($month, $year);
    $is_update_not_complete = $this->reportModel->getNotCompleteReportUpdateStatus($month, $year);
    $this->view('layout/header', array("title" => "Reports - The Remaining Budget"));
    if ($reportStatus["status"] === "success" && $reportStatus["data"]["overall_status"] === "completed" && !$is_update_not_complete["data"]) {
      $is_closed = $this->reportModel->checkClosed($data["month"],$data["year"]);
      if($is_closed["status"] == "success"){
        $data["is_closed"] = $is_closed["data"];
        $get_closed_by = $this->reportModel->getUserClosed($data["month"],$data["year"]);
        if ($get_closed_by['status'] === 'success' && !empty($get_closed_by['data'])) {
          $data['closed_by'] = $get_closed_by['data']['created_by'];
        } else {
          $data['closed_by'] = '';
        }
      }else{
        $data["is_closed"] = false;
      }
      $this->view('report/reconcile', $data);
    } else if ($reportStatus["status"] === "success" && ($reportStatus["data"]["overall_status"] === "pending")) {
      $this->view('report/index', $data);
    } else if (($reportStatus["status"] === "success" && $reportStatus["data"]["overall_status"] === "waiting") || $is_update_not_complete["data"]) {
      $this->view('report/processing', $data);
    } else {
      $this->view('report/error');
    }
    $this->view('layout/footer');
  }

  public function reconcile()
  {
    $data["page_name"] = $this->findPageName();
    if (!$this->generateRemainingBudget) {
      header("Location: /reports");
    }

    $data = array();
    $data["report_data"] = $this->reportModel->gropParentID();

    $data["report_data"] = $data["report_data"]["data"];
    $rembudgetBasic = $this->reportModel->getBasicRemainingBudgetValue();
    $data["testData"] = $rembudgetBasic["data"];
    $is_closed = $this->reportModel->checkClosed($data["month"],$data["year"]);
    if($is_closed["status"] == "success"){
      $data["is_closed"] = $is_closed["data"];
    }else{
      $data["is_closed"] = false;
    }
    $this->view('layout/header', array("title" => "Reports - The Remaining Budget"));
    $this->view('report/reconcile', $data);
    $this->view('layout/footer');
  }

  public function getReportData()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $year = $_POST['year'];
      $month = $_POST['month'];
      $reconcile_data = $this->reportModel->getReconcileData($month, $year);
      $return = $reconcile_data;
      echo json_encode($return);
    }
  }

  public function getReportDataTable()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $year = $_POST['year'];
      $month = $_POST['month'];
      $start = $_POST['start'];
      $length = $_POST['length'];
      $draw = $_POST['draw'];
      $order = $_POST["order"];
      $columns = $_POST["columns"];
      $search = $_POST["search"];
      $search = $search["value"];
      $order_dir = $order[0]["dir"];
      $order_column = $columns[$order[0]["column"]]["data"];
      $filter = array();
      $filter["filter_cash_advance"] = $_POST["filter_cash_advance"];
      $filter["filter_remaining_budget"] = $_POST["filter_remaining_budget"];
      $filter["filter_difference"] = $_POST["filter_difference"];
      $filter["filter_cash_advance_condition"] = $_POST["filter_cash_advance_condition"];
      $filter["filter_remaining_budget_condition"] = $_POST["filter_remaining_budget_condition"];
      $filter["filter_difference_condition"] = $_POST["filter_difference_condition"];

      $total = $this->reportModel->getTotalReconcileData($month, $year);
      $recordsFiltered = $this->reportModel->getCountReconcileDataTable($month, $year, $start, $length, $order_column, $order_dir, $search, $filter);
      $reconcile_data = $this->reportModel->getReconcileDataTable($month, $year, $start, $length, $order_column, $order_dir, $search, $filter);
      $reconcile_list = array();
      if($reconcile_data["status"] == "success"){
        foreach($reconcile_data["data"] as $key => $report){
          $children = $this->reportModel->getReconcileDataByParent($report["parent_id"],$month, $year);
          $total_children = count($children["data"]);
          if( $total_children > 1){
            $report = $this->reportModel->getReconcileSumParent($month, $year, $report["parent_id"]);
            $report["amount"] = $total_children;
            $reconcile_list[] = $report["data"];
            foreach($children["data"] as $child){
              $child["amount"] = 0;
              $reconcile_list[] = $child;
            }
          }else{
            $reconcile_list[] = $report;
          }
        }
      }
      
      $return = array("draw"=> $draw,"recordsTotal"=> $total["data"], "recordsFiltered"=> count($recordsFiltered["data"]), "data"=>$reconcile_list);
      echo json_encode($return);
    }
  }

  public function getReportChildren()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $year = $_POST['year'];
      $month = $_POST['month'];
      $parent_id = $_POST["parent_id"];
      $search = $_POST["search"];
      $filter = array();
      $filter["filter_cash_advance"] = $_POST["filter_cash_advance"];
      $filter["filter_remaining_budget"] = $_POST["filter_remaining_budget"];
      $filter["filter_difference"] = $_POST["filter_difference"];
      $filter["filter_cash_advance_condition"] = $_POST["filter_cash_advance_condition"];
      $filter["filter_remaining_budget_condition"] = $_POST["filter_remaining_budget_condition"];
      $filter["filter_difference_condition"] = $_POST["filter_difference_condition"];
      $reconcile_data = $this->reportModel->getReportChildren($month, $year, $parent_id, $search, $filter);
      echo json_encode($reconcile_data);
    }
  }

  public function getReportDataByParent()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $parent_id = $_POST['parent_id'];
      $month = $_POST['month'];
      $year = $_POST['year'];
      $reconcile_data = $this->reportModel->getReconcileDataByParent($parent_id,$month, $year);
      echo json_encode($reconcile_data);
    }
  }

  public function updateReportData()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $value = $_POST['value'];
      $note = $_POST['note'];
      $type = $_POST['type'];
      $report_id = $_POST['report_id'];
      $updated_by = $_SESSION['admin_name'];
      $reconcile_data = $this->reportModel->updateReportData($report_id, $value, $note, $type, $updated_by);
      $re_calculate = $this->reCalculate($report_id);
      $report_data = $this->reportModel->getReconcileDataByReportId($report_id);
      echo json_encode($report_data);
    }
  }

  private function reCalculate($report_id)
  {
    $reconcile_data = $this->reportModel->getReconcileDataByReportId($report_id);
    $reconcile_data = $reconcile_data["data"];
    $cash_advance = $reconcile_data['last_month_remaining'] + $reconcile_data['adjustment_remain'] + $reconcile_data['receive'] + $reconcile_data['invoice'] + $reconcile_data['transfer'] + $reconcile_data['ads_credit_note'] + $reconcile_data['spending_invoice'] + $reconcile_data['adjustment_free_click_cost'] + $reconcile_data['adjustment_free_click_cost_old'] + $reconcile_data['adjustment_cash_advance'] + $reconcile_data['adjustment_max'];
    $remaining_budget = $reconcile_data['remaining_ice'] + $reconcile_data['wallet'] + $reconcile_data['wallet_free_click_cost'] + $reconcile_data['withholding_tax'] + $reconcile_data['adjustment_front_end'];
    $difference = $cash_advance - $remaining_budget;

    $reconcile_data = $this->reportModel->updateReconcileReCalculateByReportId($report_id, $cash_advance, $remaining_budget, $difference);
  }

  public function generateReport()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $month = $_POST["month"];
      $year = $_POST["year"];
      $updated_by = $_POST["updated_by"];
      $is_this_month_job_exit = $this->reportModel->checkThisMonthJob($month, $year);
      if($is_this_month_job_exit){
        $report_status = $this->reportModel->getDefaultReportStatusByMonthYear($month, $year);
        if(!empty($report_status["data"])){
          $media_wallet = $report_status["data"]["media_wallet"];
          $withholding_tax = $report_status["data"]["withholding_tax"];
          $free_click_cost = $report_status["data"]["free_click_cost"];
          $google_spending = $report_status["data"]["google_spending"];
          $facebook_spending = $report_status["data"]["facebook_spending"];
          $remaining_ice = $report_status["data"]["remaining_ice"];
          $gl_cash_advance = $report_status["data"]["gl_cash_advance"];
          if($media_wallet == 'waiting' && $withholding_tax == 'waiting' && $free_click_cost == 'waiting' && $google_spending == 'waiting' && $facebook_spending == 'waiting' && $remaining_ice == 'waiting' && $gl_cash_advance == 'waiting'){
            $update_status = $this->reportModel->updateReportStatus($month, $year, "overall_status", "waiting");
            $update_status = $this->reportModel->updateReportStatus($month, $year, "cash_advance", "waiting");
            $res = array(
              "status" => "success"
            );
            echo json_encode($res);
            exit;
          }else{
            $res = array(
              "status" => "not ready"
            );
            echo json_encode($res);
            exit;
        }
        }else{
          $res = array(
            "status" => "not ready"
          );
          echo json_encode($res);
          exit;
        }
      }else{
        $res = array(
          "status" => "not ready"
        );
        echo json_encode($res);
        exit;
      }
    }
  }

  public function export()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $filename = "click_remaining_budget_reconcile_".date("YmdHis").".csv";
      header( 'Content-Type: text/csv' );
      header( 'Content-Disposition: attachment;filename='.$filename);
      $fp = fopen('php://output', 'w');
      $filter = array();
      $filter["filter_cash_advance"] = $_POST["filter_cash_advance"];
      $filter["filter_remaining_budget"] = $_POST["filter_remaining_budget"];
      $filter["filter_difference"] = $_POST["filter_difference"];
      $filter["filter_cash_advance_condition"] = $_POST["filter_cash_advance_condition"];
      $filter["filter_remaining_budget_condition"] = $_POST["filter_remaining_budget_condition"];
      $filter["filter_difference_condition"] = $_POST["filter_difference_condition"];
      $search = $_POST["search"];
      $month = $_POST["month"];
      $year = $_POST["year"];
  
  
      $results = $this->reportModel->getReportParentId($month, $year, $search, $filter);
  
      if($results["status"] == "success"){
        $report_header = array(
          "Report ID", "Parent ID", "Customer ID", "Custormer Name", "Customer Acct", "Customer Acct Name",
        "Company", "Payment Method", "Remaining ทางบัญชี", "Adjust ยอดยกมา", "หมายเหตุ adjust ยอดยกมา" ,"Receive", "Invoice", "Transfer (โอนเงินระหว่างบัญชี)",
        "คืนเงินค่าโฆษณา",
        "Spending (-)",
        "JE + Free Clickcost",
        "หมายเหตุ JE + Free Clickcost",
        "Free Clickcost (ค่าใช้จ่ายต้องห้าม)",
        "หมายเหตุ Free Clickcost (ค่าใช้จ่ายต้องห้าม)",
        "Adjustment",
        "หมายเหตุ Adjustment",
        "Max",
        "หมายเหตุ Max",
        "Cash Advance",
        "Remaining ICE",
        "Wallet",
        "Wallet - Free Clickcost (-)",
        "Withholding Tax",
        "Adjust",
        "หมายเหตุ Adjust",
        "Remaining Budget",
        "Difference",
        "Note"
        );
        fputcsv($fp, $report_header);
        $reconcile_list = array();
        if($results["status"] == "success"){
          foreach($results["data"] as $key => $report){
            $children = $this->reportModel->getReconcileDataByParent($report["parent_id"],$month,$year);
            foreach($children["data"] as $child){
              $reconcile_list[] = array(
                $child["report_id"],
                $child["parent_id"],
                $child["grandadmin_customer_id"],
                $child["grandadmin_customer_name"],
                $child["offset_acct"],
                $child["offset_acct_name"],
                $child["company"],
                $child["payment_method"],
                $child["last_month_remaining"],
                $child["adjustment_remain"],
                $child["adjustment_remain_note"],
                $child["receive"],
                $child["invoice"],
                $child["transfer"],
                $child["ads_credit_note"],
                $child["spending_invoice"],
                $child["adjustment_free_click_cost"],
                $child["adjustment_free_click_cost_note"],
                $child["adjustment_free_click_cost_old"],
                $child["adjustment_free_click_cost_old_note"],
                $child["adjustment_cash_advance"],
                $child["adjustment_cash_advance_note"],
                $child["adjustment_max"],
                $child["adjustment_max_note"],
                $child["cash_advance"],
                $child["remaining_ice"],
                $child["wallet"],
                $child["wallet_free_click_cost"],
                $child["withholding_tax"],
                $child["adjustment_front_end"],
                $child["adjustment_front_end_note"],
                $child["remaining_budget"],
                $child["difference"],
                $child["note"]
              );
            }
          }
        }
        
        foreach ($reconcile_list as $data){
          fputcsv($fp, $data);
        }
        fclose($fp);   
      }else{
        header("Content-Type: application/json");
      $res = array(
        "status" => "fail",
        "message" => $results["data"]
      );
      echo json_encode($res);
      exit;
      }
       
    }else{
      header("Content-Type: application/json");
      $res = array(
        "status" => "fail"
      );
      echo json_encode($res);
      exit;
    }
    
  }

  public function closePeriod()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $month = $_POST["month"];
      $year = $_POST["year"];
      $created_by = $_SESSION['admin_name'];
      $is_closed = $this->reportModel->checkClosed($month, $year);
      if($is_closed["status"] == 'success'){
        if($is_closed["data"]){
          $res = array(
            "status" => "closed"
          );
          echo json_encode($res);
          exit;
        }else{
          $mark_close = $this->reportModel->closePeriod($month, $year, $created_by);
          $server_month = date("m");
          $server_year = date("Y");
          $next_month = date('m', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
          $next_month_year = date('Y', strtotime('+1 month', strtotime($year.'-'.$month.'-01')));
          if($next_month < $server_month || $next_month_year < $server_year){
            $is_this_month_job_exit = $this->reportModel->checkThisMonthJob($next_month, $next_month_year);
            if(!$is_this_month_job_exit){
              $create_job = $this->reportModel->createReportStatus($next_month, $next_month_year);
            }
            if($mark_close){
              $res = array(
                "status" => "success",
                "month" => $month,
                "year" => $year
              );
              echo json_encode($res);
              exit;
            }else{
              $res = array(
                "status" => "fail"
              );
              echo json_encode($res);
              exit;
            }
          }
          
        }
      }else{
        $res = array(
          "status" => "fail"
        );
        echo json_encode($res);
        exit;
      }
      
    }
  }

  public function getStatusPercent()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $month = $_POST["month"];
      $year = $_POST["year"];
      $status_percent = 100;
      $status_amount = 1;
      $success_status = 0;
      $report_status = $this->reportModel->getNotCompletedReportStatus($month, $year);
      if(empty($report_status["data"])){
        $result = array(
          "status" => "success",
          "data" => 100
        );
        echo json_encode($result);
        exit;
      }
      $report_status = $report_status["data"];
      foreach ($report_status as $key => $val) {
        if($val["cash_advance"] != 'no'){
          $status_amount += 1;
          if($val["cash_advance"] == 'pending'){
            $status_amount += 1;
          }elseif($val["cash_advance"] == 'completed'){
            $success_status += 1;
          }
        }
        if($val["media_wallet"] != 'no'){
          $status_amount += 1;
          if($val["media_wallet"] == 'pending'){
            $status_amount += 1;
          }elseif($val["media_wallet"] == 'completed'){
            $success_status += 1;
          }
        }
        if($val["withholding_tax"] != 'no'){
          $status_amount += 1;
          if($val["withholding_tax"] == 'pending'){
            $status_amount += 1;
          }elseif($val["withholding_tax"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["free_click_cost"] != 'no'){
          $status_amount += 1;
          if($val["free_click_cost"] == 'pending'){
            $status_amount += 1;
          }elseif($val["free_click_cost"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["google_spending"] != 'no'){
          $status_amount += 1;
          if($val["google_spending"] == 'pending'){
            $status_amount += 1;
          }elseif($val["google_spending"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["facebook_spending"] != 'no'){
          $status_amount += 1;
          if($val["facebook_spending"] == 'pending'){
            $status_amount += 1;
          }elseif($val["facebook_spending"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["remaining_ice"] != 'no'){
          $status_amount += 1;
          if($val["remaining_ice"] == 'pending'){
            $status_amount += 1;
          }elseif($val["remaining_ice"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["gl_cash_advance"] != 'no'){
          $status_amount += 1;
          if($val["gl_cash_advance"] == 'pending'){
            $status_amount += 1;
          }elseif($val["gl_cash_advance"] == 'completed'){
            $success_status += 1;
          }
        }

        if($val["transfer"] != 'no'){
          $status_amount += 1;
          if($val["transfer"] == 'pending'){
            $status_amount += 1;
          }elseif($val["transfer"] == 'completed'){
            $success_status += 1;
          }
        }

      } //foreach
      $total_percent = ($success_status / $status_amount) * $status_percent;
      $total_percent = round($total_percent, 2);
      $total_percent = $total_percent;
      $result = array(
        "status" => "success",
        "data" => $total_percent
      );
      echo json_encode($result);
      exit;
    }else{
      $result = array(
        "status" => "fail",
        "data" => "Something wrong!"
      );
      echo json_encode($result);
      exit;
    }
  }

  public function uploadCashAdvance()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );
      if (in_array($_FILES["cashAdvanceInputFile"]["type"], $allowedFileType)) {
        

        // Set month and year
        if (empty($_POST["month"]) || empty($_POST["month"])) {
          $res = array(
            "status" => "error",
            "type" => "alert",
            "message" => "Require month and year"
          );
          echo json_encode($res);
          exit;
        }
        $month = $_POST["month"];
        $year = $_POST["year"];

        // Set ignore invalid data
        $ignore_invalid_data = false;
        if (isset($_POST["ignore_invalid_data"])) {
          $ignore_invalid_data = $_POST["ignore_invalid_data"];
        }

        $this->importCashAdvanceData($_FILES["cashAdvanceInputFile"]["tmp_name"], $month, $year, $ignore_invalid_data);

        $response = array(
          "status" => "success",
          "type" => "alert",
          "message" => ""
        );
        echo json_encode($response);
      } else {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "File type not allowed",
          "data" => ""
        );
        echo json_encode($res);
        exit;
      }
    } else {
      $res = array(
        "status" => "error",
        "type" => "alert",
        "message" => "Allowed only POST method",
        "data" => ""
      );
      echo json_encode($res);
      exit;
    }
  }

  public function updateCashAdvance()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );
      if (in_array($_FILES["cashAdvanceInputFile"]["type"], $allowedFileType)) {
        

        // Set month and year
        if (empty($_POST["month"]) || empty($_POST["year"])) {
          $res = array(
            "status" => "error",
            "type" => "alert",
            "message" => "Require month and year"
          );
          echo json_encode($res);
          exit;
        }
        $month = $_POST["month"];
        $year = $_POST["year"];

        // Set ignore invalid data
        $ignore_invalid_data = false;
        if (isset($_POST["ignore_invalid_data"])) {
          $ignore_invalid_data = $_POST["ignore_invalid_data"];
        }


        $this->updateCashAdvanceData($_FILES["cashAdvanceInputFile"]["tmp_name"], $month, $year,$ignore_invalid_data);

        $response = array(
          "status" => "success",
          "type" => "alert",
          "message" => ""
        );
        echo json_encode($response);
        exit;
      } else {
        $res = array(
          "status" => "error",
          "type" => "alert",
          "message" => "File type not allowed",
          "data" => ""
        );
        echo json_encode($res);
        exit;
      }
    } else {
      $res = array(
        "status" => "error",
        "type" => "alert",
        "message" => "Allowed only POST method",
        "data" => ""
      );
      echo json_encode($res);
      exit;
    }
  }

  public function importCashAdvanceData($filePath, $month, $year, $ignore_invalid_data)
  {
    header("Content-Type: application/json");
    $objPHPExcel = PHPExcel_IOFactory::load($filePath);
    $excelSheet = $objPHPExcel->getActiveSheet();
    $highestRow = $excelSheet->getHighestDataRow(); // e.g. 10

    $glCodeWhiteLists = array("212412", "212413", "212415", "412112");
    $glSeriesWhiteLists = array("IN","IV","CN","CV");

    $glCashAdvance = array();
    $total = 0;
    $valid_total = 0;
    $invalid_total = 0;
    $invalid_lists = array();

    $month_year_error = false;
    $debit_error = false;
    $credit_error = false;

    $updated_by = "";
    if (isset($_SESSION['admin_name'])) {
      $updated_by = $_SESSION['admin_name']; 
    }

    for ($row = 1; $row <= $highestRow; ++$row) 
    {
      // check column at row 1
      if ($row === 1) {
        $cols = $excelSheet->rangeToArray("A1:X1");
        $check_columns = $this->resctrictGLCashAdvanceColumn($cols);
        if (!$check_columns) {
          $response = array(
            "status" => "error",
            "type" => "alert",
            "data" => "",
            "message" => "Columns does not match with valid pattern."
          );
          echo json_encode($response);
          exit;
        }
      }

      $glCode = $excelSheet->getCell("F" . $row)->getValue();
      if (empty($glCode) || !is_numeric($glCode)) {
        continue;
      }

      if (!in_array($glCode, $glCodeWhiteLists)) {
        continue;
      }

      $series = $excelSheet->getCell("C" . $row)->getValue();
      $series_type = substr($series, 0, 2);
      if (!in_array(strtoupper($series_type), $glSeriesWhiteLists)) {
        continue;
      }

      $total++;
      $postingDate = $excelSheet->getCell("A" . $row)->getValue();
      $dueDate = $excelSheet->getCell("B" . $row)->getValue();
      $docNo = $excelSheet->getCell("D" . $row)->getValue();
      $transNo = $excelSheet->getCell("E" . $row)->getValue();

      // check date mathing
      $exp_posting_date = explode(".", $postingDate);
      $report_month = $exp_posting_date[1];
      $report_year = $exp_posting_date[2];
      if (strlen($report_year) == 2) {
        $report_year = $year[0] . $year[1] . $report_year;
      }

      // echo $report_month . "_" . $report_year . " -----> ";
      $internal_loop_error = false;

      if ($report_month != $month || $report_year != $year) {
        array_push($invalid_lists, array(
          "trans_no" => $transNo,
          "error_message" => "month and year (posting date) do not match with last month and year"
        ));
        $internal_loop_error = true;
        $month_year_error = true;
      }

      if (intval($report_month + $report_year) > intval($month + $year)) {
        array_push($invalid_lists, array(
          "trans_no" => $transNo,
          "error_message" => "month and year (posting date) are more than last month and year"
        ));
        $internal_loop_error = true;
        $month_year_error = true;
      }

      $debitLc = $excelSheet->getCell("J" . $row)->getValue();
      $creditLc = $excelSheet->getCell("K" . $row)->getValue();
      // check debit and credit
      if (!is_numeric($debitLc)) {
        if ($debitLc != "") {
          array_push($invalid_lists, array(
            "trans_no" => $transNo,
            "error_message" => "Debit (LC) must be numeric"
          ));
          $internal_loop_error = true;
          $debit_error = true;
        }
      }

      if (!is_numeric($creditLc)) {
        if ($creditLc != "") {
          array_push($invalid_lists, array(
            "trans_no" => $transNo,
            "error_message" => "Credit(LC) must be numeric"
          ));
          $internal_loop_error = true;
          $credit_error = true;
        }
      }

      if ($internal_loop_error) {
        $invalid_total++;
        continue;
      }

      // glCode = ...;
      $remarks = $excelSheet->getCell("G" . $row)->getValue();
      $offsetAcct = $excelSheet->getCell("H" . $row)->getValue();
      $offsetAcctName = $excelSheet->getCell("I" . $row)->getValue();
      $cumulativeBalanceLc = $excelSheet->getCell("L" . $row)->getValue();
      $seriesPrefix = $series_type;

      array_push($glCashAdvance, array(
        "posting_date" => $postingDate,
        "due_date" => $dueDate,
        "series" => $series,
        "doc_no" => $docNo,
        "trans_no" => $transNo,
        "gl_code" => $glCode,
        "remarks" => $remarks,
        "offset_acct" => $offsetAcct,
        "offset_acct_name" => $offsetAcctName,
        "debit_lc" => $debitLc,
        "credit_lc" => $creditLc,
        "cumulative_balance_lc" => $cumulativeBalanceLc,
        "series_code" => $seriesPrefix,
        "month" => $month,
        "year" => $year,
        "updated_by" => $updated_by
      ));

      $valid_total++;
    }

    if (!$ignore_invalid_data && $invalid_total > 0) {
      $err_type = array();
      if ($month_year_error) {
        array_push($err_type, "Posting Date");
      }
      if ($debit_error) {
        array_push($err_type, "Debit (LC)");
      }
      if ($credit_error) {
        array_push($err_type, "Credit (LC)");
      }

      $response = array(
        "status" => "error",
        "type" => "modal",
        "data" => array(
          "total" => $total,
          "valid_total" => $valid_total,
          "invalid_total" => $invalid_total,
          "error_type_lists" => $err_type,
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

    // clear gl cash advance 
    $this->reportModel->clearGLCashAdvance($month, $year);

    foreach ($glCashAdvance as $key => $val) {
      $customerID = $this->reportModel->checkCustomerExists($val["offset_acct"], $val["offset_acct_name"]);
      if (empty($customerID["data"])) {
        $customerID = $this->reportModel->insertNewCustomer($val["offset_acct"], $val["offset_acct_name"]);
      }
      $val["remaining_budget_customer_id"] = $customerID["data"];
      $this->reportModel->insertGLCashAdvance($val);
    }

    // create report status record
    $get_report_status = $this->reportModel->getReporttStatus($month, $year);
    $overall_status = $get_report_status["data"]["overall_status"];
    if (empty($get_report_status["data"])) {
      $this->reportModel->createReportStatus($month, $year);
    }

    // update gl cash advance on report status table
    $this->reportModel->updateReportStatus($month, $year, "gl_cash_advance", "waiting");

    $get_gl_cash_advance_detail = $this->resourceModel->getTotalDataUpdate("gl_cash_advance", $month, $year);
    
    $allowed_generate_data = false;
    $get_previous_report_status = $this->resourceModel->getProviousReportStatus($month, $year);
    if ($get_previous_report_status["status"] === "success" && $get_previous_report_status["data"] == 0) {
      $allowed_generate_data = true;
    }

    $response = array(
      "status" => "success",
      "type" => "alert",
      "overall_status" => $overall_status,
      "allowed_generate_data" => $allowed_generate_data,
      "data" => array(
        "import_total" => $get_gl_cash_advance_detail["data"]["row_count"],
        "updated_at" => $get_gl_cash_advance_detail["data"]["updated_at"]
      )
    );

    echo json_encode($response);
    exit;
  }

  public function updateCashAdvanceData($filePath, $month, $year,$ignore_invalid_data)
  {
    if($ignore_invalid_data == 'true'){
      $ignore_invalid_data = true;
    }else{
      $ignore_invalid_data = false;
    }
    header("Content-Type: application/json");
    $objPHPExcel = PHPExcel_IOFactory::load($filePath);
    $excelSheet = $objPHPExcel->getActiveSheet();
    $highestRow = $excelSheet->getHighestDataRow(); // e.g. 10

    $glCodeWhiteLists = array("212412", "212413", "212415", "412112");
    $glSeriesWhiteLists = array("IN","IV","CN","CV");

    $glCashAdvance = array();
    $total = 0;
    $valid_total = 0;
    $invalid_total = 0;
    $invalid_lists = array();

    $month_year_error = false;
    $debit_error = false;
    $credit_error = false;

    $updated_by = "";
    if (isset($_SESSION['admin_name'])) {
      $updated_by = $_SESSION['admin_name']; 
    }

    for ($row = 1; $row <= $highestRow; ++$row) 
    {
      // check column at row 1
      if ($row === 1) {
        $cols = $excelSheet->rangeToArray("A1:X1");
        $check_columns = $this->resctrictGLCashAdvanceColumn($cols);
        if (!$check_columns) {
          $response = array(
            "status" => "error",
            "type" => "alert",
            "data" => "",
            "message" => "Columns does not match with valid pattern."
          );
          echo json_encode($response);
          exit;
        }
      }

      $glCode = $excelSheet->getCell("F" . $row)->getValue();
      if (empty($glCode) || !is_numeric($glCode)) {
        if (!in_array($glCode, $glCodeWhiteLists)) {
          continue;
        }
      }

      $series = $excelSheet->getCell("C" . $row)->getValue();
      $series_type = substr($series, 0, 2);
      
      if (!in_array(strtoupper($series_type), $glSeriesWhiteLists)) {
        continue;
      }

      $total++;
      $postingDate = $excelSheet->getCell("A" . $row)->getValue();
      $dueDate = $excelSheet->getCell("B" . $row)->getValue();
      $docNo = $excelSheet->getCell("D" . $row)->getValue();
      $transNo = $excelSheet->getCell("E" . $row)->getValue();

      // check date mathing
      $exp_posting_date = explode(".", $postingDate);
      $report_month = $exp_posting_date[1];
      $report_year = $exp_posting_date[2];
      if (strlen($report_year) == 2) {
        $report_year = $year[0] . $year[1] . $report_year;
      }

      $internal_loop_error = false;

      if ($report_month != $month || $report_year != $year) {
        array_push($invalid_lists, array(
          "trans_no" => $transNo,
          "error_message" => "month and year (posting date) do not match with last month and year"
        ));
        $internal_loop_error = true;
        $month_year_error = true;
      }

      if (intval($report_month + $report_year) > intval($month + $year)) {
        array_push($invalid_lists, array(
          "trans_no" => $transNo,
          "error_message" => "month and year (posting date) are more than last month and year"
        ));
        $internal_loop_error = true;
        $month_year_error = true;
      }

      $debitLc = $excelSheet->getCell("J" . $row)->getValue();
      $creditLc = $excelSheet->getCell("K" . $row)->getValue();
      // check debit and credit
      if (!is_numeric($debitLc)) {
        if ($debitLc != "") {
          array_push($invalid_lists, array(
            "trans_no" => $transNo,
            "error_message" => "Debit (LC) must be numeric"
          ));
          $internal_loop_error = true;
          $debit_error = true;
        }
      }

      if (!is_numeric($creditLc)) {
        if ($creditLc != "") {
          array_push($invalid_lists, array(
            "trans_no" => $transNo,
            "error_message" => "Credit(LC) must be numeric"
          ));
          $internal_loop_error = true;
          $credit_error = true;
        }
      }

      if ($internal_loop_error) {
        $invalid_total++;
        continue;
      }

      // glCode = ...;
      $remarks = $excelSheet->getCell("G" . $row)->getValue();
      $offsetAcct = $excelSheet->getCell("H" . $row)->getValue();
      $offsetAcctName = $excelSheet->getCell("I" . $row)->getValue();
      $cumulativeBalanceLc = $excelSheet->getCell("L" . $row)->getValue();
      $seriesPrefix = $series_type;

      array_push($glCashAdvance, array(
        "posting_date" => $postingDate,
        "due_date" => $dueDate,
        "series" => $series,
        "doc_no" => $docNo,
        "trans_no" => $transNo,
        "gl_code" => $glCode,
        "remarks" => $remarks,
        "offset_acct" => $offsetAcct,
        "offset_acct_name" => $offsetAcctName,
        "debit_lc" => $debitLc,
        "credit_lc" => $creditLc,
        "cumulative_balance_lc" => $cumulativeBalanceLc,
        "series_code" => $seriesPrefix,
        "month" => $month,
        "year" => $year,
        "updated_by" => $updated_by
      ));

      $valid_total++;
    }

    if (!$ignore_invalid_data && $invalid_total > 0) {
      $err_type = array();
      if ($month_year_error) {
        array_push($err_type, "Posting Date");
      }
      if ($debit_error) {
        array_push($err_type, "Debit (LC)");
      }
      if ($credit_error) {
        array_push($err_type, "Credit (LC)");
      }

      $response = array(
        "status" => "error",
        "type" => "modal",
        "data" => array(
          "total" => $total,
          "valid_total" => $valid_total,
          "invalid_total" => $invalid_total,
          "error_type_lists" => $err_type,
          "invalid_lists" => $invalid_lists
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

    $updated_by = $_POST["updated_by"];
    $remaining_budget_customer_id_clear_list = array();
    $worker_id = $this->reportModel->createUpdateWorker("gl_cash_advance",$month, $year, $updated_by);
    $worker_id = $worker_id["data"];
    

    foreach ($glCashAdvance as $key => $val) {
      $customerID = $this->reportModel->checkCustomerExists($val["offset_acct"], $val["offset_acct_name"]);
      if (empty($customerID["data"])) {
        $customerID = $this->reportModel->insertNewCustomer($val["offset_acct"], $val["offset_acct_name"]);
      }
      $val["remaining_budget_customer_id"] = $customerID["data"];
      $glCashAdvance[$key]["remaining_budget_customer_id"] = $val["remaining_budget_customer_id"];
      if(!in_array($val["remaining_budget_customer_id"],$remaining_budget_customer_id_clear_list)){
        $remaining_budget_customer_id_clear_list[] = $val["remaining_budget_customer_id"];
      }
    }

    //check and clear before replace
    foreach ($remaining_budget_customer_id_clear_list as $idx => $value) 
    {
      if($value != NULL){
        $this->reportModel->clearGlCashAdvanceByID($month, $year,$value);
      }
    }

    //add new data
    
    foreach ($glCashAdvance as $idx => $value) 
    {
      $value["updated_by"] = $updated_by;
      $this->reportModel->insertGLCashAdvance($value);
    }

    // update google spending status on report status table
    $update_status = $this->reportModel->updateReportStatusById($worker_id, "gl_cash_advance", "waiting");
    $update_status = $this->reportModel->updateReportStatusById($worker_id, "overall_status", "waiting");

    
    $response = array(
      "status" => "success",
    );

    echo json_encode($response);
    exit;
  }

  public function updateReconcileNote()
  {
    header("Content-Type: application/json", true);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) ) {
        $_POST = json_decode(file_get_contents('php://input'), true);
      }

      $report_id = $_POST['id'];
      $report_note = $_POST['note'];

      if (empty($report_id)) {
        $res = array(
          "status" => "error",
          "message" => "Require report id"
        );
        echo json_encode($res);
        exit;
      }

      $updated_by = "";
      if (isset($_SESSION['admin_name'])) {
        $updated_by = $_SESSION['admin_name']; 
      }

      $update_note = $this->reportModel->updateReportNote($report_id, $report_note, $updated_by);
      if ($update_note['status'] === 'success') {
        $res = array(
          "status" => "success",
          "message" => ""
        );
        echo json_encode($res);
        exit;
      } else {
        $res = array(
          "status" => "error",
          "message" => "Update report note failed"
        );
        echo json_encode($res);
        exit;
      }

    } else {
      $res = array(
        "status" => "error",
        "message" => "allow only POST method"
      );
      echo json_encode($res);
      exit;
    }
  }

  function parseMonthYear($month, $year)
  {
    $date_obj = DateTime::createFromFormat("!m", $month);
    return $date_obj->format("F") . " / " . $year;
  }

  public function resctrictGLCashAdvanceColumn($incoming_cols)
  {
    $columns = array(
      "Posting Date",
      "Due Date",
      "Series",
      "Doc. No.",
      "Trans. No.",
      "G/L Acct/BP Code",
      "Remarks",
      "Offset Acct",
      "Offset Acct Name",
      "Debit (LC)",
      "Credit (LC)",
      "Cumulative Balance (LC)",
      "Debit (FC)",
      "Credit (FC)",
      "Cumulative Balance (FC)",
      "Department",
      "Business Unit",
      "Product",
      "Intercompany",
      "Blanket Agreement",
      "Seq. No.",
      "Ref. 1 (Header)",
      "Ref. 2 (Header)",
      "Ref. 3 (Header)"
    );

    $not_match = 0;
    foreach ($incoming_cols[0] as $i => $ic) {
      if (strtolower($ic) !== strtolower($columns[$i])) {
        $not_match++;
      }
    }

    if ($not_match === 0) {
      return true;
    } else {
      return false;
    }
  }

}
