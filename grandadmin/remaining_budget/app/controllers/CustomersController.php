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
        $highestRow = $excelSheet->getHighestRow(); // e.g. 10

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

  public function exportCustomers()
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
    $get_all_customers = $this->customerModel->getAllCustomers();
    foreach ($get_all_customers["data"] as $customer) {
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

    $query_string = $this->getQueryString();
    $offset = $query_string["start"];
    $limit = $query_string["length"];

    $get_total_all_customers = $this->customerModel->getTotalAllCustomers();
    $get_customers = $this->customerModel->getCustomers($offset, $limit);
    // $customers = array();
    // foreach ($get_customers["data"] as $key => $val) {
    //   $temp_array = array(
    //     $val["parent_id"],
    //     $val["grandadmin_customer_id"],
    //     $val["grandadmin_customer_name"],
    //     $val["offset_acct"],
    //     $val["offset_acct_name"],
    //     $val["company"],
    //     $val["payment_method"],
    //     $val["created_at"],
    //     $val["updated_at"],
    //     $val["updated_by"]
    //   );

    //   array_push($customers, $temp_array);
    // }

    $response = array (
      "draw" => $query_string["draw"],
      "recordsTotal" => $get_total_all_customers["data"],
      "recordsFiltered" => $get_total_all_customers["data"],
      "data" => $get_customers["data"]
    );
    echo json_encode($response);
  }

  public function editCustomer()
  {
    header("Content-Type: application/json", true);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) ) {
        $_POST = json_decode(file_get_contents('php://input'), true);
      }
      
      $customerData = $_POST;

      // check id
      $check_customer_id = $this->customerModel->checkCustomerID($customerData['id']);
      if ($check_customer_id["status"] === "success" && $check_customer_id["data"] > 0) {
        // check parent id
        $check_parent_id = $this->customerModel->checkParentID($customerData["parent_id"]);
        if ($check_parent_id["status"] === "success" && $check_parent_id["data"] > 0) {
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
              "message" => "Customer update is failed"
            );
            echo json_encode($response);
            exit;
          }
        } else {
          $response = array(
            "status" => "error",
            "message" => "parent id not found"
          );
          echo json_encode($response);
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
}