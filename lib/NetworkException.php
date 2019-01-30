<?php
namespace Fonix;

class NetworkException extends \Exception
{
  public $curl_error;
  public $curl_message;

  public function __construct($curl_error, $curl_message)
  {
    $this->curl_error = $curl_error;
    $this->curl_message = $curl_message;

    $message = "Curl Error: " . $curl_error . " Message: " . $curl_message;

    parent::__construct($message, 0, null);
  }

}

?>
