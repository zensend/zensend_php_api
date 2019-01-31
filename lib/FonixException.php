<?php

namespace Fonix;

class FonixException extends \Exception
{
  public $failcode;
  public $parameter;
  public $http_code;

  public function __construct($http_code, $failure)
  {



    $this->failcode = $failure == null ? null : $this->maybe("failcode", $failure);
    $this->parameter = $failure == null ? null : $this->maybe("parameter", $failure);
    $this->http_code = $http_code;

    $message = "Failcode: " . $this->failcode . " Parameter: " . $this->parameter . " (http status: " . $this->http_code . ")";

    parent::__construct($message, 0, null);
  }


  private function maybe($parameter, $params)
  {
    if (array_key_exists($parameter, $params)) {
      return $params[$parameter];
    } else {
      return null;
    }
  }
}

?>
