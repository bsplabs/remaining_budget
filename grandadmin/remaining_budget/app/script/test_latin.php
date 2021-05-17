<?php

date_default_timezone_set('Asia/Bangkok');

const SERVERNAME = "db";
const USERNAME = "root";
const PASSWORD = "root9999";

function dbCon($charset_name = "utf8")
{
  try {
    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=remaining_budget;charset=" . $charset_name, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
  } catch (PDOException $e) {
    echo "Connection fail: " . $e->getMessage() . "\n";
    exit;
  }
}

function dbClose()
{
}


function getReadyTopup()
{
  try {
    $mainDB = dbCon();
    $sql = "SELECT * FROM ready_topup WHERE ID = '111390'";
    $stmt = $mainDB->query($sql);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo iconv("TIS-620", "UTF-8", $data[0]["account_name"]);
    echo "\n";

  } catch (PDOException $e) {
    print_r($e->getMessage());
  }
}

getReadyTopup();
