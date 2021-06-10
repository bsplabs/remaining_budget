<?php

require_once APPROOT . '/vendors/PHPExcel/PHPExcel.php';

class CustomersController extends Controller
{
  private $customerModel;

  public function __construct()
  {
    $this->customerModel = $this->model("Customer");
  }

  public function index()
  {
    // $data["customers"] = $this->customerModel->getCustomers();
    $data["page_name"] = $this->findPageName();
    $this->view("layout/header", array("title" => "Customers - The Remaining Budget"));
    $this->view("customer/index", $data);
    $this->view("layout/script");
    $this->view("layout/footer");
  }

  public function importCustomers()
  {
    header("Content-Type: application/json");
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );
      if (in_array($_FILES["inputCustomerFileImport"]["type"], $allowedFileType)) {
        $file_path = $_FILES["inputCustomerFileImport"]['tmp_name'];
        $objPHPExcel = PHPExcel_IOFactory::load($file_path);
        $excelSheet = $objPHPExcel->getActiveSheet();
        $highestRow = $excelSheet->getHighestDataRow(); // e.g. 10

        $customers = array();

        for ($row = 2; $row <= $highestRow; ++$row) 
        {
          $id = $excelSheet->getCell('A' . $row)->getValue();
          $parent_id = $excelSheet->getCell("B" . $row)->getValue();
          $grandadmin_customer_id = $excelSheet->getCell("C" . $row)->getValue();
          $grandadmin_customer_name = $excelSheet->getCell("D" . $row)->getValue();
          $offset_acct = $excelSheet->getCell("E" . $row)->getValue();
          $offset_acct_name = $excelSheet->getCell("F" . $row)->getValue();
          $main_business = $excelSheet->getCell("G" . $row)->getValue() == 'Yes' ? true : false;
          $company = $excelSheet->getCell("H" . $row)->getValue();
          $payment_method = $excelSheet->getCell("I" . $row)->getValue();
    
          array_push($customers, array(
            "id" => $id,
            "parent_id" => $parent_id,
            "grandadmin_customer_id" => $grandadmin_customer_id,
            "grandadmin_customer_name" => $grandadmin_customer_name,
            "offset_acct" => $offset_acct,
            "offset_acct_name" => $offset_acct_name,
            "main_business" => $main_business,
            "company" => $company,
            "payment_method" => $payment_method,
            "updated_by" => isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : ""
          ));
        }

        $insert = 0;
        $replace = 0;
        $insert_success = 0;
        $insert_fail = 0;
        $replace_success = 0;
        $replace_fail = 0;
        $insert_fail_lists = array();
        $replace_fail_lists = array();
        $result_lists = array();
        foreach ($customers as $index => $customer) 
        {
          if (empty($customer["id"])) {
            $insert++;
            $error_occured = false;
            // insert
            
            if (empty($customer['grandadmin_customer_id']) && empty($customer['grandadmin_customer_name']) && empty($customer['offset_acct']) && empty($customer['offset_acct_name'])) {
              $error_occured = true;
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "insert",
                "error_message" => "Customer ID and Customer Name and Offset Acct and Offset Acct Name => Empty"
              ));
              $insert_fail++;
              continue;
            }

            $check_customer_key = $this->customerModel->checkCustomerKey($customer);
            if ($check_customer_key['status'] === 'fail' || $check_customer_key == 0) {
              $error_occured = true;
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "insert",
                "error_message" => "These CustomerID, Customer Name, Offset Acct and Offset Acct Name keys already exists"
              ));
              $insert_fail++;
              continue;
            }

            $insert_customer = $this->customerModel->insertCustomer($customer);
            if ($insert_customer['status'] === 'success') {
              $insert_success++;
            } else {
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "insert",
                "error_message" => "Insert failed"
              ));
              $insert_fail++;
            }
          } else {
            $replace++;
            // replace
            $error_occured = false;
            // check id exists ?
            $check_id = $this->customerModel->checkID($customer["id"]);
            if ($check_id["status"] === 'fail' || $check_id['data'] == 0) {
              $error_occured = true;
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "replace",
                "error_message" => 'ID not found in database'
              ));
              $replace_fail++;
              continue;
            }

            if (empty($customer['grandadmin_customer_id']) && empty($customer['grandadmin_customer_name']) && empty($customer['offset_acct']) && empty($customer['offset_acct_name'])) {
              $error_occured = true;
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "replace",
                "error_message" => "Customer ID and Customer Name and Offset Acct and Offset Acct Name => Empty"
              ));
              $replace_fail++;
              continue;
            }

            // check customer_id, customer_name, offset_acct and offset_acct_name
            $check_customer_key = $this->customerModel->checkCustomerKey($customer);
            if ($check_customer_key['status'] === 'fail' || $check_customer_key == 0) {
              $error_occured = true;
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "replace",
                "error_message" => "These CustomerID, Customer Name, Offset Acct and Offset Acct Name keys already exists"
              ));
              $replace_fail++;
              continue;
            }

            // if ($error_occured) {
            //   $replace_fail++;
            //   continue;
            // }

            $replace_customer = $this->customerModel->replaceCustomer($customer);
            if ($replace_customer['status'] === 'success') {
              $replace_success++;
            } else {
              array_push($result_lists, array(
                "row" => ($index + 1),
                "action" => "replace",
                "error_message" => "Replace failed"
              ));
              $replace_fail++;
            }
          }
        }

        $res = array(
          "status" => "success",
          "data" => array(
            "insert_total" => $insert,
            "replace_total" => $replace,
            "insert_success_total" => $insert_success,
            "insert_fail_total" => $insert_fail,
            // "insert_fail_lists" => $insert_fail_lists,
            "replace_success_total" => $replace_success,
            "replace_fail_total" => $replace_fail,
            // "replace_fail_lists" => $replace_fail_lists,
            "result_lists" => $result_lists
          )
        );

        echo json_encode($res);
        exit;

      } else {
        $res = array(
          'status' => 'error',
          'message' => 'Invalid file type'
        );
        echo json_encode($res);
        exit;  
      }
    } else {
      $res = array(
        'status' => 'error',
        'message' => 'Allow only POST method'
      );
      echo json_encode($res);
      exit;
    }

  }

  public function exportCustomers($customers)
  {
    $objPHPExcel = new PHPExcel();
    // $objPHPExcel->setActiveSheetIndex(0);

    // -------------------- Set Default ------------------------- //
    // Set default font type to 'Verdana'
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
 
    // Set default font size to '12'
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    // ---------------------------------------------------------- //

    $sheet = $objPHPExcel->getActiveSheet();

    // set customer header
    $sheet->SetCellValue('A1', 'ID');
    $sheet->SetCellValue('B1', 'Parent ID');
    $sheet->SetCellValue('C1', 'Customer ID');
    $sheet->SetCellValue('D1', 'Customer Name');
    $sheet->SetCellValue('E1', 'Offset Acct');
    $sheet->SetCellValue('F1', 'Offset Acct Name');
    $sheet->SetCellValue('G1', 'Main Business');
    $sheet->SetCellValue('H1', 'Company');
    $sheet->SetCellValue('I1', 'Payment Method');

    $sheet->getStyle("A1:I1")->getFont()->setBold(true);

    foreach (range('A', 'I') AS $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // set customer data
    $i = 2;
    // $get_all_customers = $this->customerModel->getAllCustomers();
    foreach ($customers as $customer) {
      $sheet->setCellValueExplicit('A' . $i, $customer['id'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('B' . $i, $customer['parent_id'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('C' . $i, $customer['grandadmin_customer_id'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->SetCellValue('D' . $i, $customer['grandadmin_customer_name']);
      $sheet->setCellValueExplicit('E' . $i, $customer['offset_acct'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->SetCellValue('F' . $i, $customer['offset_acct_name']);
      if ($customer['main_business']) {
        $sheet->SetCellValue('G' . $i, "Yes");
      } else {
        $sheet->SetCellValue('G' . $i, "No");
      }
      $sheet->SetCellValue('H' . $i, $customer['company']);
      $sheet->SetCellValue('I' . $i, $customer['payment_method']);
      $i++;
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-Type: application/vnd.ms-excel'); //mime type
    header('Content-Disposition: attachment;filename="customers_' . date('d-m-Y_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0'); //no cache
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');  
    $objWriter->save('php://output');
  }

  public function getCustomers()
  {
    header("Content-Type: application/json");

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) ) {
      $_POST = json_decode(file_get_contents('php://input'), true);
    }

    $offset = $_POST["start"];
    $limit = $_POST["length"];
    $columns = $_POST["columns"];
    $orders = isset($_POST["order"]) ? $_POST["order"] : NULL;
    $search = isset($_POST["search"]) ?$_POST["search"] : NULL;
    $filters = isset($_POST["filters"]) ?$_POST["filters"] : NULL;
    $action_type = isset($_POST["action_type"]) ? $_POST["action_type"] : NULL;

    // searching
    $where = '';
    if (!empty($search) || $search['value'] == '') {
      $kw = $search['value'];
      if ($kw !== '') {
        $where .= "WHERE (";
        $where .= "id LIKE '%{$kw}%' OR ";
        $where .= "grandadmin_customer_id LIKE '%{$kw}%' OR ";
        $where .= "grandadmin_customer_name LIKE '%{$kw}%' OR ";
        $where .= "offset_acct LIKE '%{$kw}%' OR ";
        $where .= "offset_acct_name LIKE '%{$kw}%' OR ";
        $where .= "company LIKE '%{$kw}%' OR ";
        $where .= "parent_id LIKE '%{$kw}%' OR ";
        $where .= "payment_method LIKE '%{$kw}%' OR ";
        // $where .= "created_at LIKE '%{$kw}%' OR ";
        // $where .= "updated_at LIKE '%{$kw}%' OR ";
        $where .= "updated_by LIKE '%{$kw}%')";
      }
    }

    // filtering
    if ($filters && count($filters) > 0) {
      $filter_where = '';
      foreach ($filters as $filter_key => $filter_value) {
        if ($filter_key === 'filter_parent_id') {
          if ($filter_value === 'more_than_one_child') {
            $filter_where .= "parent_id IN (SELECT parent_id FROM remaining_budget_customers GROUP BY parent_id HAVING count(*) > 1) AND ";
          } else if ($filter_value === 'one_child') {
            $filter_where .= "parent_id IN (SELECT parent_id FROM remaining_budget_customers GROUP BY parent_id HAVING count(*) = 1) AND ";
          } else {
            $filter_where .= "";
          }
        }

        if ($filter_key === 'filter_payment') {
          if ($filter_value === 'postpaid') {
            $filter_where .= "payment_method = 'postpaid' AND ";
          } else if ($filter_value === 'prepaid') {
            $filter_where .= "payment_method = 'prepaid' AND ";
          } else {
            $filter_where .= "";
          }
        }
      }


      if ($filter_where !== '') {
        $filter_where = rtrim($filter_where, " AND ");
        $filter_where = "(" . $filter_where . ")";
        if ($where === '') {
          $where .= "WHERE ";
        } else {
          $where .= " AND ";
        }
        $where .= $filter_where;
      }
    }

    // ordering
    $order_by = '';
    if (!empty($orders) && count($orders) > 0) {
      foreach ($orders as $order) {
        $col_name = $columns[$order['column']]['data'];
        $order_by .= $this->mappingColumn($col_name, strtoupper($order['dir'])) ;
      }

      if ($order_by !== "") {
        $order_by = rtrim($order_by, ", ");
        $order_by = 'ORDER BY ' . $order_by;
      }
    }

    $get_total_all_customers = $this->customerModel->getTotalAllCustomers();
    $get_customers = $this->customerModel->getCustomers($offset, $limit, $order_by, $where);

    if ($action_type === 'get_data') {
      $response = array (
        "draw" => $_POST["draw"],
        "recordsTotal" => $get_total_all_customers["data"],
        "recordsFiltered" => $get_customers["data"]["total_customers"],
        "data" => $get_customers["data"]["customers"]
      );
      echo json_encode($response);
    } else if ($action_type === 'export') {
      $get_customers = $this->customerModel->getCustomers($offset, $limit, $order_by, $where, true);
      // print_r($get_customers);
      $this->exportCustomers($get_customers["data"]["customers"]);
    }
  }

  private function mappingColumn($col_name, $dir)
  {
    switch ($col_name) {
      case 'id':
        return "id {$dir}, ";
        break;

      case 'parent_id':
        return "parent_id {$dir}, ";
        break;

      case 'grandadmin_customer_id':
        return "grandadmin_customer_id {$dir}, ";
        break;

      case 'grandadmin_customer_name':
        return "grandadmin_customer_name {$dir}, ";
        break;

      case 'offset_acct':
        return "offset_acct {$dir}, ";
        break;

      case 'offset_acct_name':
        return "offset_acct_name {$dir}, ";
        break;

      case 'company':
        return "company {$dir}, ";
        break;

      case 'payment_method':
        return "payment_method {$dir}, ";
        break;

      case 'created_at':
        return "created_at {$dir}, ";
        break;

      case 'updated_at':
        return "updated_at {$dir}, ";
        break;

      case 'updated_by':
        return "updated_by {$dir}, ";
        break;

      case 'main_business':
        return "main_business {$dir}, ";
        break;
      
      default:
        return "";
        break;
    }
  }

  public function editCustomer()
  {
    header("Content-Type: application/json", true);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) ) {
        $_POST = json_decode(file_get_contents('php://input'), true);
      }
      
      $customerData = $_POST;

      if (isset($_SESSION['admin_name'])) {
        $customerData['updated_by'] = $_SESSION['admin_name'];
      }

      // check id
      $check_customer_id = $this->customerModel->checkCustomerID($customerData['id']);
      if ($check_customer_id["status"] === "success" && $check_customer_id["data"] > 0) {
        // if main business == true --> reset all main business same parent id
        // clear main business
        if ($customerData['main_business'] == true || $customerData['main_business'] === 'true') {
          $this->customerModel->resetMainBusiness($customerData['parent_id']);
        }
        
        $update_customer = $this->customerModel->updateCustomer($customerData);
        if ($update_customer["status"] === 'success') {
          $response = array(
            "status" => "success",
            "message" => "Updated"
          );
          echo json_encode($response);
          exit;
        } else {
          $response = array(
            "status" => "error",
            "message" => "Customer update failed"
          );
          echo json_encode($response);
          exit;
        }

      } else {
        $response = array(
          "status" => "error",
          "message" => "id not found"
        );
        echo json_encode($response);
      }

    } else {
      $response = array(
        "status" => "error",
        "message" => "Accept only POST method"
      );
      echo json_encode($response);
    }
  }

  public function deleteCustomer()
  {
    header("Content-Type: application/json", true);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) ) {
        $_POST = json_decode(file_get_contents('php://input'), true);
      }
      
      $id = $_POST['id'];

      if (empty($id)) {
        $res = array(
          'status' => 'error',
          'message' => 'Not found id'
        );
        echo json_encode($res);
      } else {
        // check id on report table
        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_report', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_media_wallet', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_withholding_tax', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_free_click_cost', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_remaining_ice', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_gl_cash_advance', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_google_spending', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_facebook_spending', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_wallet_transfer', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $check_id_exists = $this->customerModel->checkIdIsReconcile('remaining_budget_first_remaining', $id);
        if ($check_id_exists["data"] > 0) {
          $res = array(
            'status' => 'error',
            'message' => 'Can\'t delete this customer because its id is already mapped'
          );
          echo json_encode($res);
          exit;
        }

        $this->customerModel->deleteCustomer($id);
        $res = array(
          'status' => 'success',
          'message' => 'Successfully deleted'
        );
        echo json_encode($res);
      }
    } else {
      $res = array(
        'status' => 'error',
        'message' => 'Allow only POST method'
      );
      echo json_encode($res);
    }
  }

  public function checkMainBusiness($parent_id = "")
  {
    header("Content-Type: application/json", true);
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      if (empty($parent_id)) {
        $res = array(
          "status" => "error",
          "message" => "Require parent id"
        );
        echo json_encode($res);
        exit;
      }

      $check_main_business = $this->customerModel->checkMainBusiness($parent_id);
      
      $customer_data = "";
      if ($check_main_business['status'] === 'success' && !empty($check_main_business['data'])) {
        $customer_data = $check_main_business['data'];
      }

      $res = array(
        "status" => "success",
        "data" => $customer_data,
        "message" => ""
      );
      echo json_encode($res);
      exit;

    } else {
      $res = array(
        "status" => "error",
        "message" => "Allow only GET method"
      );
      echo json_encode($res);
      exit;
    }
  }
}