<?php

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
}