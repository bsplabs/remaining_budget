<?php


date_default_timezone_set('Asia/Bangkok');

$sttime = strtotime("1/4/2021");
echo $sttime;
// header('Content-Type: text/html; charset=UTF-8');
// $last_month_time_str =  strtotime("-1 month");
// echo date("y", $last_month_time_str);
// phpinfo();
exit;

$mysqli = new mysqli("db", "rembg", "rembg9999", "remaining_budget");

if ($mysqli->connect_error) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit;
}

echo "Connect DB success<br>";

$mysqli->set_charset("latin1");
$charset = $mysqli->character_set_name();
echo "Default character set is: " . $charset;

// exit;
echo "<br>";

$name = "สิทธิกร ประสันลักษ์";
$text = "โซนคาสิโนออนไลน์สำหรับผู้ที่ชื่นชอบคาสิโน อย่างเช่น การเล่น บาคาร่า เสือมังกร รูเล็ต เรามีไว้ให้คุณที่ Betflik กับสาวสวยเซ็กซี่ ที่จะมาแจกไพ่ให้คุณได้ เชยชมความสวยงามของสาวๆ จนลืมคนที่บ้านไปเลย กับค่าย SA Gaming , Sexy bacarrat";

// $name = parseUtf8ToIso88591($name);
// $text = parseUtf8ToIso88591($text);

// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users_latin (name, text) VALUES ('{$name}', '$text')");

// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");
// $mysqli -> query("INSERT INTO users (name, text) VALUES ('{$name}', '$text')");

// $result = $mysqli->query("SELECT * FROM ready_new_members");
// $row = $result->fetch_all(MYSQLI_ASSOC);
// echo "<pre>";
// print_r($row);
// // foreach ($row as $r) {
// //   echo $r["name"];
// //   // echo utf8_encode($r["name"]);
// //   // echo mb_convert_encoding($r["name"], 'UTF-8', 'ISO-8859-1');
// //   echo "<br>";
// // }
// echo "</pre>";

$mysqli->close();

function usersGenerate() 
{

}

function usersLatinGenerate() 
{

}

function parseUtf8ToIso88591($string){
  if(!is_null($string)){
    $iso88591_1 = utf8_decode($string);
    // $iso88591_2 = iconv('UTF-8', 'ISO-8859-1', $string);
    $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');       
    return $string;
  }
}

?>