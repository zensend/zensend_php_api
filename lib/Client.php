<?php

namespace Fonix;



class Client
{
  private $apiKey;
  private $url;
  private $http_options;

  public function __construct($apiKey, $http_options = array(), $url = "https://sonar.fonix.io")
  {
    $this->apiKey = $apiKey;
    $this->url = $url;
    $this->http_options = $http_options;
  }


  public static function newWithHardcodedCA($apiKey)
  {
    return new Client($apiKey, array("ca_file" => Client::ca_file()));
  }

  public static function ca_file()
  {
    return dirname(__FILE__) . '/../data/GeoTrust_Global_CA.pem';
  }

  public function send_sms($sms_request)
  {

    $http_params = array(
      "BODY" => $this->required($sms_request, "body"),
      "ORIGINATOR" => $this->required($sms_request, "originator"),
      "NUMBERS" => implode(",", $this->nocomma($this->required($sms_request, "numbers")))
    );

    if (isset($sms_request->timetolive_in_minutes)) {
      $http_params["TIMETOLIVE"] = $sms_request->timetolive_in_minutes;
    }

    if (isset($sms_request->originator_type)) {
      $http_params["ORIGINATOR_TYPE"] = $sms_request->originator_type;
    }

    if (isset($sms_request->encoding)) {
      $http_params["ENCODING"] = $sms_request->encoding;
    }


    $json = $this->make_request($this->url, true, "/v2/sendsms", $http_params);
    $response = new SmsResponse();
    $response->tx_guid = $json["txguid"];
    $response->numbers = (int)$json["numbers"];
    $response->sms_parts = (int)$json["smsparts"];
    $response->encoding = $json["encoding"];

    return $response;
  }


  private function make_request($url, $is_post, $path, $params)
  {
    $curl = curl_init();
    // we don't use try/finally here for < php 5.5 compat

    $encoded = http_build_query($params);

    $opts = array();
    $opts[CURLOPT_RETURNTRANSFER] = 1;
    $opts[CURLOPT_USERAGENT] = "Fonix PHP";
    $full_url = $url . $path;
    if ($is_post) {
      $opts[CURLOPT_POST] = 1;
      $opts[CURLOPT_POSTFIELDS] = $encoded;
    } else {
      if (count($params) > 0) {
        $full_url = $full_url . "?" . $encoded;
      }
    }

    $opts[CURLOPT_SSL_VERIFYPEER] = TRUE;
    $ca_file = $this->default_value($this->http_options, "ca_file", NULL);

    if ($ca_file != NULL) {
      $opts[CURLOPT_CAINFO] = $ca_file;
    }

    $opts[CURLOPT_URL] = $full_url;

    // these options require php 5.2.3
    $opts[CURLOPT_CONNECTTIMEOUT_MS] = $this->default_value($this->http_options, "connect_timeout_ms", 30 * 1000);
    $opts[CURLOPT_TIMEOUT_MS] = $this->default_value($this->http_options, "timeout_ms", 30 * 1000);

    $opts[CURLOPT_HTTPHEADER] = array("X-API-KEY: " . $this->apiKey);

    curl_setopt_array($curl, $opts);
    $rbody = curl_exec($curl);

    if ($rbody === false) {

      $errno = curl_errno($curl);
      $message = curl_error($curl);
      curl_close($curl);
      throw new NetworkException($errno, $message);
    }

    $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if (strpos($content_type, 'application/json') !== false) {
      $json = json_decode($rbody, true);
      if (array_key_exists("success", $json)) {
        return $json["success"];
      }
      if (array_key_exists("failure", $json)) {
        $failure = $json["failure"];
        throw new FonixException($rcode, $failure);
      } else {
        throw new FonixException($rcode, null);
      }
    } else {
      throw new FonixException($rcode, null);
    }

  }



  private function nocomma($numbers)
  {
    foreach ($numbers as $number) {
      if (strpos($number, ',') !== false) {
        throw new \InvalidArgumentException("numbers cannot contain ','");
      }
    }

    return $numbers;
  }

  private function default_value($params, $parameter, $default_value)
  {
    if (array_key_exists($parameter, $params)) {
      return $params[$parameter];
    } else {
      return $default_value;
    }
  }

  private function required($params, $parameter)
  {
    if (!isset($params->{$parameter})) {
      throw new \InvalidArgumentException("Required: " . $parameter);
    }
    return $params->{$parameter};
  }
}

?>
