<?php

// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DataController extends Controller
{
  private $dataModel;

  public function __construct()
  {
    $this->dataModel = $this->model('Data');
  }

  public function index()
  {
    $data["title"] = "REMBUDGET - Data Store";
    $this->view('layout/header', $data);
    $this->view("data/index");
    $this->view("layout/footer");
  }

  public function importFile()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["importRawData"])) {
      $allowedFileType = array(
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      );

      if (in_array($_FILES["file"]["type"], $allowedFileType)) {
        // $targetPath = __DIR__ . "/uploads/" . $_FILES["file"]["name"];
        $targetPath = $_FILES["file"]["tmp_name"];
        $explodeFileName = explode("_", $_FILES["file"]["name"]);

        $year = $explodeFileName[0];
        $month = $explodeFileName[1];
        // echo $targetPath . "<br>";
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)) {
          $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
          $spreadSheet = $reader->load($targetPath);
          $excelSheet = $spreadSheet->getActiveSheet();

          $highestRow = $excelSheet->getHighestRow(); // e.g. 10
          // $highestColumn = $excelSheet->getHighestColumn(); // e.g 'F'

          for ($row = 3; $row <= $highestRow; ++$row) {
            $dataSet = array();
            $dataSet["posting_date"] =  $this->adjustDateString($excelSheet->getCell("A" . $row)->getValue());
            $dataSet["due_date"] =  $this->adjustDateString($excelSheet->getCell("B" . $row)->getValue());
            $dataSet["series"] =  $excelSheet->getCell("C" . $row)->getValue();
            $dataSet["doc_no"] =  $excelSheet->getCell("D" . $row)->getValue();
            $dataSet["trans_no"] =  $excelSheet->getCell("E" . $row)->getValue();
            $dataSet["gl_code"] =  $excelSheet->getCell("F" . $row)->getValue();
            // $dataSet["j_voucher"] =  $excelSheet->getCell("G" . $row)->getValue();
            $dataSet["remarks"] =  $excelSheet->getCell("H" . $row)->getValue();
            $dataSet["offset_acct"] =  $excelSheet->getCell("I" . $row)->getValue();
            $dataSet["offset_acct_name"] =  $excelSheet->getCell("J" . $row)->getValue();
            $dataSet["indicator"] =  $excelSheet->getCell("K" . $row)->getValue();
            $dataSet["debit_lc"] =  $excelSheet->getCell("L" . $row)->getValue();
            $dataSet["credit_lc"] =  $excelSheet->getCell("M" . $row)->getValue();
            $dataSet["cumulative_balance_lc"] =  $excelSheet->getCell("N" . $row)->getValue();
            $dataSet["series_code"] =  $dataSet["series"][0] . $dataSet["series"][1];
            $dataSet["month"] =  $month;
            $dataSet["year"] =  $year;
            // $dataSet["updated_by"] =  $dataSet["series"][0] . $dataSet["series"][1];
            
            // for ($col = "A"; $col != "O"; ++$col) {
            //   $dataSet[$col] = $excelSheet->getCell($col . $row)->getValue();
            // }

            // insert gl revenue to db
            $this->dataModel->insertGLRevenue($dataSet);
          }
          echo "<br> <h1>Insert Success</h1> <br>";
        } else {
          echo "Failed! <br>";
        }
      }
    } else {
      echo "<h1>Page not found</h1>";
    }
  }

  public function exportRemainigBudget()
  {
    $reportMonthTarget = "12";
    $reportYearTarget = "2020";
    // get remainig buget value follow month and year
    $remBudget = $this->dataModel->getRemainigBugetByMonthAndYear($reportMonthTarget, $reportYearTarget);
    $this->prePint($remBudget);
    exit;
    $spreadSheet = new Spreadsheet();

    // -------------------- Set Default ------------------------- //
    // Set default font type to 'Verdana'
    $spreadSheet->getDefaultStyle()->getFont()->setName('Arial');
 
    // Set default font size to '12'
    $spreadSheet->getDefaultStyle()->getFont()->setSize(10);
    // ---------------------------------------------------------- //
    
    $sheet = $spreadSheet->getActiveSheet();
    
    // -------------------- Set cell by cell ------------------------- //
    // set by range
    // $sheet->getStyle("A1:AA1")->getFont()->setBold(true);
    $sheet->mergeCells("M1:P1");
    $sheet->setCellValue("M1", "Adjustment");
    $sheet->getStyle('M1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("M1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FCE5CD");
    $sheet->getStyle("R")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FFF2CC");
    $sheet->getStyle("Y")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("FFF2CC");
    $sheet->getStyle("G2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("F49826");
    $sheet->getStyle("I2:K2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("F49826");
    // $sheet->getStyle("Y1")->getFill()->getStartColor()->setARGB("FFFF0000");

    $this->setHederRemainingBudgetReport($sheet);

    // set column width
    foreach (range('A', 'Q') AS $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    foreach (range('S', 'X') AS $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    foreach (range('Z', 'AA') AS $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getColumnDimension("R")->setAutoSize(false)->setWidth(3);
    $sheet->getColumnDimension("Y")->setAutoSize(false)->setWidth(3);
    // ---------------------------------------------------------------- //


    foreach ($remBudget["data"] as $index => $rb) {
      $sheet->setCellValue("A" . ($index + 3),"RPTH");
      $sheet->setCellValue("B" . ($index + 3), $this->convertNullToEmptyString($rb["payment_method"]));
      $sheet->setCellValue("C" . ($index + 3), $this->convertNullToEmptyString($rb["parent_id"]));
      $sheet->setCellValueExplicit(
        "D" . ($index + 3),
        $rb["offset_acct"],
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
      );
      $sheet->setCellValue("E" . ($index + 3), $this->convertNullToEmptyString($rb["offset_acct_name"]));
      $sheet->setCellValue("F" . ($index + 3), $this->convertNullToEmptyString($rb["last_month_remaining"]));
      $sheet->setCellValue("G" . ($index + 3), $this->convertNullToEmptyString($rb["adjustment_remain"]));
      $sheet->setCellValue("H" . ($index + 3), $this->convertNullToEmptyString($rb["receive"]));
      $sheet->setCellValue("I" . ($index + 3), $this->convertNullToEmptyString($rb["invoice"]));
      $sheet->setCellValue("J" . ($index + 3), $this->convertNullToEmptyString($rb["transfer"]));
      $sheet->setCellValue("K" . ($index + 3), $this->convertNullToEmptyString($rb["ads_credit_note"]));
      $sheet->setCellValue("L" . ($index + 3), $this->convertNullToEmptyString($rb["spending_invoice"]));
      $sheet->setCellValue("M" . ($index + 3), $this->convertNullToEmptyString($rb["je"] + $rb["free_click_cost"]));
      $sheet->setCellValue("N" . ($index + 3), $this->convertNullToEmptyString($rb["free_click_cost_old"]));
      $sheet->setCellValue("O" . ($index + 3), $this->convertNullToEmptyString($rb["adjustment_cash_advance"]));
      $sheet->setCellValue("P" . ($index + 3), $this->convertNullToEmptyString($rb["max"]));
      $sheet->setCellValue("Q" . ($index + 3), $this->convertNullToEmptyString($rb["cash_advance"]));
      // $sheet->setCellValue("R" . ($index + 3), "");
      $sheet->setCellValue("S" . ($index + 3), $this->convertNullToEmptyString($rb["remaining_ice"]));
      $sheet->setCellValue("T" . ($index + 3), $this->convertNullToEmptyString($rb["wallet"]));
      $sheet->setCellValue("U" . ($index + 3), $this->convertNullToEmptyString($rb["wallet_free_click_cost"]));
      $sheet->setCellValue("V" . ($index + 3), $this->convertNullToEmptyString($rb["withholding_tax"]));
      $sheet->setCellValue("W" . ($index + 3), $this->convertNullToEmptyString($rb["adjustment_front_end"]));
      $sheet->setCellValue("X" . ($index + 3), $this->convertNullToEmptyString($rb["remaining_budget"]));
      // $sheet->setCellValue("Y" . ($index + 3), "");
      $sheet->setCellValue("Z" . ($index + 3), $this->convertNullToEmptyString($rb["difference"]));
      $sheet->setCellValue("AA" . ($index + 3), $this->convertNullToEmptyString($rb["note"]));
    }

    // --------------------- SET BORDER ---------------------------------- //
    $endOfRow = count($remBudget["data"]) + 2;
    $sheet->getStyle("A1:Q{$endOfRow}")->applyFromArray([
      "borders" => [
        "allBorders" => [
          "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          "color" => ["rgb" => "000000"]
        ]
      ]
    ]);
    $sheet->getStyle("S1:X{$endOfRow}")->applyFromArray([
      "borders" => [
        "allBorders" => [
          "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          "color" => ["rgb" => "000000"]
        ]
      ]
    ]);
    $sheet->getStyle("Z1:AA{$endOfRow}")->applyFromArray([
      "borders" => [
        "allBorders" => [
          "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          "color" => ["rgb" => "000000"]
        ]
      ]
    ]);

    // set color border bottom
    $sheet->getStyle("A2:AA2")->getBorders()->applyFromArray([
      "bottom" => [
        "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
        "color" => ["rgb" => "DADFE8"]
      ]
    ]);

    // set vertical align text
    $sheet->getStyle("A2:AA2")->applyFromArray([
      "alignment" => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ]
    ]);
    $sheet->getStyle("M1")->applyFromArray([
      "alignment" => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ]
    ]);
    // ------------------------------------------------------------------- //

    // SET ROW HEIGHT
    $sheet->getRowDimension("1")->setRowHeight(20);
    $sheet->getRowDimension("2")->setRowHeight(20);
    
    $writer = new Xlsx($spreadSheet);

    // write and save the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="hello.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
  }

  // util function
  private function adjustDateString($dateStr)
  {
    $expDate = explode(".", $dateStr);
    $d = $expDate[0];
    $m = $expDate[1];
    $y = $expDate[2];

    if (strlen($y) == 2) {
      $y = date("Y");
    }

    return $y . "-" . $m . "-" . $d;
  }

  private function setHederRemainingBudgetReport($sheet)
  {
    $sheet->setCellValue('A2', 'Company');
    $sheet->setCellValue('B2', "Payment Method");
    $sheet->setCellValue('C2', "Parent Customer ID");
    $sheet->setCellValue('D2', "Customer ID");
    $sheet->setCellValue('E2', "Customer Name");
    $sheet->setCellValue('F2', "Remaining ทางบัญชี");
    $sheet->setCellValue('G2', "Adjust ยอดยกมา");
    $sheet->setCellValue('H2', "Receive");
    $sheet->setCellValue('I2', "Invoice");
    $sheet->setCellValue('J2', "Transfer (โอนเงินระหว่างบัญชี)");
    $sheet->setCellValue('K2', "คืนเงินค่าโฆษณา");
    $sheet->setCellValue('L2', "Spending (-)");
    $sheet->setCellValue('M2', "JE + Free Clickcost");
    $sheet->setCellValue('N2', "Free Clickcost - ค่าใช้จ่ายต้องห้าม");
    $sheet->setCellValue('O2', "Adjustment Cash Advance");
    $sheet->setCellValue('P2', "Max");
    $sheet->setCellValue('Q2', "Cash Advance");
    $sheet->setCellValue('R2', "");
    $sheet->setCellValue('S2', "Remaining ICE");
    $sheet->setCellValue('T2', "Wallet");
    $sheet->setCellValue('U2', "Wallet - Free Clickcost (-)");
    $sheet->setCellValue('V2', "Withholding Tax");
    $sheet->setCellValue('W2', "Adjust");
    $sheet->setCellValue('X2', "Remaining Budget");
    $sheet->setCellValue('Y2', "");
    $sheet->setCellValue('Z2', "Difference");
    $sheet->setCellValue('AA2', "Note");
  }

  private function convertNullToEmptyString($data)
  {// --------------------- SET BORDER ---------------------------------- //
    return $data ? $data : "";
  }

  private function prePint($data)
  {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
  }
}
