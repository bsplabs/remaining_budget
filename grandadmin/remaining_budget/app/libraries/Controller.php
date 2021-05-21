<?php

// Load the model and the view
class Controller
{
  public function model($model)
  {
    // Require model file
    require_once '../app/models/' . $model . '.php';
    // Instantiate model 
    return new $model();
  }

  // Load the view (checks for the file)
  public function view($view, $data = array())
  {
    if (file_exists('../app/views/' . $view . '.php')) {
      require_once '../app/views/' . $view . '.php';
    } else {
      die("View does not exists.");
    }
  }

  public function findPageName()
  {
    $uri = trim($_SERVER["REQUEST_URI"], "/");
    $uriArr = explode("/", $uri);
    if (empty($uriArr[1])) {
      $pageName = "reports";
    } else {
      $pageName = $uriArr[1];
    }

    return strtolower($pageName);
  }
  
  public function getQueryString()
  {
    $query_string_not_parse =  parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
    $query_string = array();
    parse_str($query_string_not_parse, $query_string);
    return $query_string;
  }

  public function getRemainingBudgetCustomerId($type,$id,$name,$source){
    $resource_model = $this->model("Resource"); 
    //type 'grandadmin', 'offset'
    if($type == 'grandadmin'){
      $remaining_budget_customer_id = $resource_model->isRemainingBudgetByGrandAdminData($id,$name);
    }else{
      $remaining_budget_customer_id = $resource_model->isRemainingBudgetByOffsetData($id,$name);
    }

    if($remaining_budget_customer_id == NULL){
      $remaining_budget_customer_id = $resource_model->createRemainingBudgetCustomerId($id,$name,$source);
    }

    return $remaining_budget_customer_id;
    
  }
}
