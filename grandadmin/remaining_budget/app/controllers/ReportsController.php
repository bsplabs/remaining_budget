<?php

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
      $reconcile_data = $this->reportModel->getReconcileData($year, $month);
      echo json_encode($reconcile_data);
    }
  }

  public function getReportDataByParent()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $parent_id = $_POST['parent_id'];
      $reconcile_data = $this->reportModel->getReconcileDataByParent($parent_id);
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
      $reconcile_data = $this->reportModel->updateReportData($report_id, $value, $note, $type);
      $re_calculate = $this->reCalculate($report_id);
      echo json_encode($reconcile_data);
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
      $filter_cash_advance_condition = $_POST["filter_cash_advance_condition"];
      $filter_cash_advance = $_POST["filter_cash_advance"];
      $filter_remaining_budget_condition = $_POST["filter_remaining_budget_condition"];
      $filter_remaining_budget = $_POST["filter_remaining_budget"];
      $filter_difference_condition = $_POST["filter_difference_condition"];
      $filter_difference = $_POST["filter_difference"];
      $search = $_POST["search"];

      $condition_signs = array("equal"=> "=", "less_than" => "<", "greater_than" => ">" , "not_equal" => "!=");
      $condition = "";
      if($filter_cash_advance != "" && !empty($condition_signs[$filter_cash_advance_condition]) ){
        $condition .= "cash_advance ".$condition_signs[$filter_cash_advance_condition]." :cash_advance";
      }else{
        $filter_cash_advance = "";
      }

      if($filter_remaining_budget != "" && !empty($condition_signs[$filter_remaining_budget_condition])){
        if($condition!=""){
          $condition .= " AND ";
        }
        $condition .= "remaining_budget ".$condition_signs[$filter_remaining_budget_condition]." :remaining_budget";
      }else{
        $filter_remaining_budget = "";
      }

      if($filter_difference != "" && !empty($condition_signs[$filter_difference_condition])){
        if($condition!=""){
          $condition .= " AND ";
        }
        $condition .= "difference ".$condition_signs[$filter_difference_condition]." :difference";
      }else{
        $filter_difference = "";
      }

      if($search != "" ){
        if($condition!=""){
          $condition .= " AND ";
        }
        $condition .= "c.parent_id like concat('%', :search, '%') ";
      }else{
        $search = "";
      }
    
  
      $results = $this->reportModel->getReportByQuery($filter_cash_advance,$filter_remaining_budget,$filter_difference,$search,$condition);
  
      if($results["status"] == "success"){
        fputcsv($fp, $results["header"]);
        foreach ($results["data"] as $data){
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
      $created_by = $_POST["created_by"];
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
          $is_this_month_job_exit = $this->reportModel->checkThisMonthJob($server_month, $server_year);
          if(!$is_this_month_job_exit){
            $create_job = $this->reportModel->createReportStatus($server_month, $server_year);
          }
          if($mark_close){
            $res = array(
              "status" => "success"
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
      }else{
        $res = array(
          "status" => "fail"
        );
        echo json_encode($res);
        exit;
      }
      
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
        'text/csv',
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


        $this->updateCashAdvanceData($_FILES["cashAdvanceInputFile"]["tmp_name"], $month, $year);

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
    $highestRow = $excelSheet->getHighestRow(); // e.g. 10

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

    for ($row = 1; $row <= $highestRow; ++$row) {
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
        "year" => $year
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
    // -----------------
    $waiting = 0;
    foreach ($get_report_status["data"] as $rs => $status) {
      if ($rs === "overall_status") continue;
      if ($rs !== "transfer") {
        if ($status === "waiting") $waiting++;
      }
    }

    if ($waiting === 7 && $overall_status !== "waiting") {
      $this->reportModel->updateReportStatus($month, $year, "overall_status", "waiting");
      $overall_status = "waiting";
    }
    // ------------------

    // ------------------
    $allowed_generate_data = false;
    if ($month === START_MONTH && $year === START_YEAR) {
      if ($overall_status === "waiting") $allowed_generate_data = true;
    } else {
      $get_previous_report_status = $this->resourceModel->getProviousReportStatus($month, $year);
      if ($get_previous_report_status["status"] === "success" && $get_previous_report_status["data"] == 0) {
        $allowed_generate_data = true;
      }
    }
    // ------------------

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

  public function updateCashAdvanceData($filePath, $month, $year)
  {
    $ignore_invalid_data = true;
    header("Content-Type: application/json");
    $objPHPExcel = PHPExcel_IOFactory::load($filePath);
    $excelSheet = $objPHPExcel->getActiveSheet();
    $highestRow = $excelSheet->getHighestRow(); // e.g. 10

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

    for ($row = 1; $row <= $highestRow; ++$row) {
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
        "year" => $year
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

  function parseMonthYear($month, $year)
  {
    $date_obj = DateTime::createFromFormat("!m", $month);
    return $date_obj->format("F") . " / " . $year;
  }
}
