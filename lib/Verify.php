<?php

namespace ZenSend;

class Verify
{
  private $client;

  public function __construct($api_key, $http_options = array(), $verify_url = "https://verify.zensend.io")
  {
    $this->client = new Client($api_key, $http_options, "https://api.zensend.io", $verify_url);
  }

  public function create_session($msisdn)
  {
    $this->assert_session();
    $session = $this->client->create_msisdn_verification($msisdn);
    $_SESSION["zensend_msisdn_verification_session"] = $session;
    return $session;
  }

  public function verify_response($expected_msisdn)
  {
    $this->assert_session();
    $session = $_SESSION["zensend_msisdn_verification_session"];
    $actual_msisdn = $this->client->msisdn_verification_status($session);
    if ($actual_msisdn != $expected_msisdn) {
      throw new VerifyException("msisdn_mismatch");
    }      
  }

  public function write_script_tag()
  {
  ?>
<script type='text/javascript' src='<?php echo $this->client->verify_url . "/assets/msisdn_verification.js"?>'></script>
  <?php
  }


  public function write_div_tag($callback_url)
  {
    $session = $_SESSION["zensend_msisdn_verification_session"];
    $domain = $this->client->verify_url == "https://verify.zensend.io" ? NULL : $this->client->verify_url;

    ?>
<div id="z-msisdn-verification" <?php if ($domain) echo 'data-domain="' . $domain . '" '?> data-callback-url="<?php echo $callback_url ?>" data-session="<?php echo $session ?>"></div>
    <?php
  }

  public function write_tags($callback_url)
  {
    $this->write_script_tag();
    $this->write_div_tag($callback_url);
  }

  private function assert_session()
  {
    if (session_id() == "") {
      throw new VerifyException("session_start() must be called");
    }
  }
}

?>
