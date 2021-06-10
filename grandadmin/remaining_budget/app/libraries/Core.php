<?php

// Core App Class
// ini_set("memory_limit","3072M");
// ini_set('max_execution_time', '360');
class Core
{
  protected $currentController = 'ReportsController';
  protected $currentMethod = 'index';
  protected $params = array();
  protected $queryString = "";

  public function __construct()
  {
    $url = $this->getUrl();
    if (!empty($url[0])) {
      if (strpos($url[0], "-")) {
        $controllerName = ucwords(str_replace('-', ' ', $url[0]));
        $controllerName = str_replace(' ', '', $controllerName);
      } else if (strpos($url[0], "_")) {
        $controllerName = ucwords(str_replace('_', ' ', $url[0]));
        $controllerName = str_replace(' ', '', $controllerName);
      } else {
        $controllerName = ucfirst($url[0]);
      }
      
      $controllerName = $controllerName . "Controller";
      $this->currentController = $controllerName;
    }

    // Look in 'controllers' for first value, ucwords will capitallize first letter
    if (file_exists('../app/controllers/' . $this->currentController . '.php')) {
      unset($url[0]);
    }
    // print_r($url);
    // exit;
    // Require the controller
    require_once '../app/controllers/' . $this->currentController . '.php';
    $this->currentController = new $this->currentController;

    // Check for second part of the URL
   
    if (isset($url[1])) {
      if (strpos($url[1], "-")) {
        $methodName = ucwords(str_replace('-', ' ', $url[1]));
        $methodName = str_replace(' ', '', $methodName);
        $methodName[0] = strtolower($methodName[0]);
      } else if (strpos($url[1], "_")) {
        $methodName = ucwords(str_replace('_', ' ', $url[1]));
        $methodName = str_replace(' ', '', $methodName);
        $methodName[0] = strtolower($methodName[0]);
      } else {
        $methodName = strtolower($url[1]);
      }

      if (method_exists($this->currentController, $methodName)) {
        $this->currentMethod = $methodName;
        unset($url[1]);
      }
    }

    // Get parameters
    $this->params = $url ? array_values($url) : array();

    // Call a callback with array of params
    call_user_func_array(array($this->currentController, $this->currentMethod), $this->params);
  }

  public function getUrl()
  {
    if (isset($_SERVER["REQUEST_URI"])) {
      $uriOrigin = explode(PN_URI, trim($_SERVER["REQUEST_URI"], '/'));
      $uriOrigin = trim($uriOrigin[1], "/");
      $expUriOrigin = explode("?", $uriOrigin);

      $uri = $expUriOrigin[0];
      if (!empty($expUriOrigin[1])) {
        $qrString = $expUriOrigin[1];
        $qrStringParse = array();
        parse_str($qrString, $qrStringParse);
        $this->queryString = $qrStringParse;
      }

      // Allows you to filter variable as string/number
      $url = filter_var($uri, FILTER_SANITIZE_URL);
      // Breaking it into an array
      $url = explode('/', $url);
      return $url;
    }
  }
}
