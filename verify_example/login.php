<?php
  require 'config.inc.php';

  if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $error = "";
    require 'login_form.inc';
    return;
  } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST["username"];
    $user = USERS[$username];

    if ($user && $user["password"] == $_POST["password"]) {
      session_start();
      $_SESSION["username"] = $username;
      $options = new ZenSend\VerifyOptions();
      $options->message = "{{token}} is your COMPANY verification code";
      $options->originator = "YOURCOMPANY";
      $verify->create_session($user["msisdn"], $options);
      header("Location: /msisdn_verify.php"); 
    } else {
      $error = "Invalid username or password";
      require 'login_form.inc';
      return;
    }
    
  } else {
    http_response_code(405);
    exit();
  }
?>
