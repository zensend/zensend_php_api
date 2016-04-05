<?php 
require 'config.inc.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  $username = $_SESSION["username"];
  if (!isset(USERS[$username])) {
    /* handle request without session */
    header("Location: /login_fail.php"); 
    exit();
  }
  require 'msisdn_verify_page.inc';
  return;
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $username = $_SESSION["username"];

  if (!isset(USERS[$username])) {
    header("Location: /login_fail.php");
    exit();
  }

  $user = USERS[$username];
  try {
    $verify->verify_response($user["msisdn"]);
    header("Location: /login_success.php");
  } catch (Exception $e) {
    header("Location: /login_fail.php");
  }

} else {
  http_response_code(405);
  exit();
}
?>


