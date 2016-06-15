<?php
  define("API_KEY", "api_key");
  define("USERS", array("ben" => array("password" => "test", "msisdn" => "441234567890")));
  require '../init.php';
  $verify = new ZenSend\Verify(API_KEY, array(), "http://verify.fonix.dev");
?>
