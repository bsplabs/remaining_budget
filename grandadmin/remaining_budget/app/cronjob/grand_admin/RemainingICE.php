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

  public function run()
  {
    $month = PRIMARY_MONTH;
    $year = PRIMARY_YEAR;

    $post_request = array(
      "service" => "google",
      "year" => $year,
      "month" => $month,
      "accounts" => "",
      "offset" => 0,
      "limit" => 100
    );
    
    $Init = curl_init();
    
    // $cURLConnection = curl_init('http://adproicedev.readyplanet.com/api/remaining_budget');
    // curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post_request);
    // curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    // $api_response = curl_exec($cURLConnection);

    // print_r($api_response);

    // curl_close($cURLConnection);
  }

}
