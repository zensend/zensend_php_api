<?php

namespace Fonix;



class Response {
  public $content_type;
  public $http_code;
  public $body;
}

class Request {

  public $is_post;
  public $post_body;
  public $url;
  public $headers;
}

$stubbed_response = null;

$request_history = array();

function curl_request_history()
{
  global $request_history;
  return $request_history;
}

function curl_init()
{
  $req = new Request();
  $req->is_post = FALSE;
  return $req;
}

function curl_setopt_array($curl, $opts)
{

  if (isset($opts[CURLOPT_POST])) {

    $curl->is_post = ($opts[CURLOPT_POST] == 1);
  }

  if (isset($opts[CURLOPT_POSTFIELDS])) {
    $curl->post_body = $opts[CURLOPT_POSTFIELDS];
  }

  if (isset($opts[CURLOPT_URL])) {
    $curl->url = $opts[CURLOPT_URL];
  }

  if (isset($opts[CURLOPT_HTTPHEADER])) {
    $curl->headers = $opts[CURLOPT_HTTPHEADER];
  }

}

function curl_exec($curl)
{
  global $request_history;
  array_push($request_history, $curl);

  global $stubbed_response;
  return $stubbed_response->body;
}

function curl_getinfo($curl, $param)
{
  global $stubbed_response;

  if ($param == CURLINFO_CONTENT_TYPE) {
    return $stubbed_response->content_type;
  } elseif ($param == CURLINFO_HTTP_CODE) {
    return $stubbed_response->http_code;
  } else {
    throw new Exception("wtf");
  }

}

function curl_close($curl)
{

}

function curl_errno($curl)
{
  global $stubbed_response;
  return $stubbed_response->errno;
}


function curl_error($curl)
{
  global $stubbed_response;
  return $stubbed_response->error;
}

function stub_request($content_type, $http_code, $body)
{
  global $stubbed_response;
  $stubbed_response = new Response();
  $stubbed_response->content_type = $content_type;
  $stubbed_response->http_code = $http_code;
  $stubbed_response->body = $body;

  global $request_history;
  $request_history = array();
}

function stub_error($curl_error, $curl_message)
{

  global $stubbed_response;
  $stubbed_response = new Response();
  $stubbed_response->body = false;
  $stubbed_response->errno = $curl_error;
  $stubbed_response->error = $curl_message;

  global $request_history;
  $request_history = array();
}

class ClientTest extends \PHPUnit_Framework_TestCase
{


  public function testSendMultipleSms()
  {

    stub_request("application/json", 200, <<<EOT
  {
    "success": {
        "txguid": "7CDEB38F-4370-18FD-D7CE-329F21B99209",
        "numbers": "2",
        "smsparts": "1",
        "encoding": "gsm"
    }
  }
EOT
    );

    $client = new Client("API_KEY");
    $request = new SmsRequest();
    $request->body = "TEST HELLO";
    $request->originator = "ORIG";
    $request->numbers = array("447796354848","447796354849");
    $result = $client->send_sms($request);

    $this->assertSame($result->numbers, 2);
    $this->assertSame($result->sms_parts, 1);
    $this->assertSame($result->encoding, "gsm");
    $this->assertSame($result->tx_guid, "7CDEB38F-4370-18FD-D7CE-329F21B99209");

    $history = curl_request_history();

    $this->assertSame(count($history), 1);
    $this->assertSame($history[0]->is_post, true);
    $this->assertSame($history[0]->post_body, "BODY=TEST+HELLO&ORIGINATOR=ORIG&NUMBERS=447796354848%2C447796354849");
    $this->assertSame($history[0]->url, "https://sonar.fonix.io/v2/sendsms");
    $this->assertSame($history[0]->headers, array("X-API-KEY: API_KEY"));
  }


  public function testSendSms()
  {

    stub_request("application/json", 200, <<<EOT
{
    "success": {
        "txguid": "7CDEB38F-4370-18FD-D7CE-329F21B99209",
        "numbers": "1",
        "smsparts": "1",
        "encoding": "gsm"
    }
}
EOT
    );

    $client = new Client("API_KEY");
    $request = new SmsRequest();
    $request->body = "TEST HELLO";
    $request->originator = "ORIG";
    $request->numbers = array("447796354848");

    $result = $client->send_sms($request);

    $this->assertSame($result->numbers, 1);
    $this->assertSame($result->sms_parts, 1);
    $this->assertSame($result->encoding, "gsm");
    $this->assertSame($result->tx_guid, "7CDEB38F-4370-18FD-D7CE-329F21B99209");

    $history = curl_request_history();

    $this->assertSame(count($history), 1);
    $this->assertSame($history[0]->is_post, true);
    $this->assertSame($history[0]->post_body, "BODY=TEST+HELLO&ORIGINATOR=ORIG&NUMBERS=447796354848");
    $this->assertSame($history[0]->url, "https://sonar.fonix.io/v2/sendsms");
    $this->assertSame($history[0]->headers, array("X-API-KEY: API_KEY"));
  }


  public function testSendPoundSms()
  {

    stub_request("application/json", 200, <<<EOT
    {
      "success": {
          "txguid": "7CDEB38F-4370-18FD-D7CE-329F21B99209",
          "numbers": "1",
          "smsparts": "1",
          "encoding": "gsm"
      }
    }
EOT
    );

    $client = new Client("API_KEY");
    $request = new SmsRequest();
    $request->body = "£HELLO";
    $request->originator = "ORIG";
    $request->numbers = array("447796354848");

    $result = $client->send_sms($request);

    $history = curl_request_history();

    $this->assertSame(count($history), 1);
    $this->assertSame($history[0]->is_post, true);
    $this->assertSame($history[0]->post_body, "BODY=%C2%A3HELLO&ORIGINATOR=ORIG&NUMBERS=447796354848");
    $this->assertSame($history[0]->url, "https://sonar.fonix.io/v2/sendsms");
    $this->assertSame($history[0]->headers, array("X-API-KEY: API_KEY"));
  }

  private function request(){
    $request = new SmsRequest();
    $request->body = "TEST HELLO";
    $request->originator = "ORIG";
    $request->numbers = array("447796354848");
    return $request;
  }

  public function testHandleError()
  {

    stub_request("application/json", 400, <<<EOT
    {
      "failure": {
          "failcode": "GENERIC_ERROR"
      }
    }
EOT
    );


    $client = new Client("API_KEY");
    try {
      $client->send_sms($this->request());
    } catch (\Fonix\FonixException $e) {
      $this->assertSame($e->http_code, 400);
      $this->assertSame($e->failcode, "GENERIC_ERROR");
      $this->assertSame($e->parameter, null);
      return;
    }
    $this->fail('An expected exception has not been raised.');


  }

  public function testHandleParameterError()
  {

    stub_request("application/json", 400, <<<EOT
    {
      "failure": {
          "failcode": "IS_EMPTY",
          "parameter": "BODY"

      }
    }
EOT
    );


    $client = new Client("API_KEY");
    try {
      $client->send_sms($this->request());
    } catch (\Fonix\FonixException $e) {
      $this->assertSame($e->http_code, 400);
      $this->assertSame($e->failcode, "IS_EMPTY");
      $this->assertSame($e->parameter, "BODY");
      return;
    }
    $this->fail('An expected exception has not been raised.');


  }


  public function testHandleInvalidJson()
  {

    stub_request("application/json", 400, <<<EOT
    {

    }
EOT
    );


    $client = new Client("API_KEY");
    try {
      $client->send_sms($this->request());
    } catch (\Fonix\FonixException $e) {
      $this->assertSame($e->http_code, 400);
      $this->assertSame($e->failcode, null);
      $this->assertSame($e->parameter, null);
      return;
    }
    $this->fail('An expected exception has not been raised.');


  }


  public function testHandleWrongContentType()
  {

    stub_request("text/plain", 503, "Gateway Timeout");

    $client = new Client("API_KEY");
    try {
      $client->send_sms($this->request());
    } catch (\Fonix\FonixException $e) {
      $this->assertSame($e->http_code, 503);
      $this->assertSame($e->failcode, null);
      $this->assertSame($e->parameter, null);
      return;
    }
    $this->fail('An expected exception has not been raised.');


  }

  public function testHandleConnectionError()
  {

    stub_error(CURLE_COULDNT_CONNECT, "CURL Could not connect");

    $client = new Client("API_KEY");
    try {
      $client->send_sms($this->request());
    } catch (\Fonix\NetworkException $e) {
      $this->assertSame($e->curl_error, CURLE_COULDNT_CONNECT);
      $this->assertSame($e->curl_message, "CURL Could not connect");
      return;
    }
    $this->fail('An expected exception has not been raised.');


  }

  public function testShouldThrowErrorIfCommaInNumbersArray()
  {
    $client = new Client("API_KEY");
    try {
      $request = new SmsRequest();
      $request->body = "TEST HELLO";
      $request->originator = "ORIG";
      $request->numbers = array("44779635,4848");
      $client->send_sms($request);
    } catch (\InvalidArgumentException $e) {
      return;
    }
    $this->fail('An expected exception has not been raised.');
  }


  public function testShouldThrowErrorIfMissingRequiredParameter()
  {
    $client = new Client("API_KEY");
    try {
      $request = new SmsRequest();
      $request->originator = "ORIG";
      $request->numbers = array("447796354848");
      $client->send_sms($request);
    } catch (\InvalidArgumentException $e) {
      return;
    }
    $this->fail('An expected exception has not been raised.');
  }



  public function testShouldBeAbleToSendSmsWithOptionalParameter()
  {
    stub_request("application/json", 200, <<<EOT
    {
      "success": {
        "txguid": "7CDEB38F-4370-18FD-D7CE-329F21B99209",
        "numbers": "1",
        "smsparts": "1",
        "encoding": "gsm"
      }
    }
EOT
    );

    $client = new Client("API_KEY");

    $request = new SmsRequest();
    $request->body = "TEST HELLO";
    $request->originator = "ORIG";
    $request->numbers = array("447796354848");
    $request->originator_type = "alpha";
    $request->encoding = "gsm";
    $request->timetolive_in_minutes = 60;
    $result = $client->send_sms($request);

    $this->assertSame($result->numbers, 1);
    $this->assertSame($result->sms_parts, 1);
    $this->assertSame($result->encoding, "gsm");
    $this->assertSame($result->tx_guid, "7CDEB38F-4370-18FD-D7CE-329F21B99209");

    $history = curl_request_history();

    $this->assertSame(count($history), 1);
    $this->assertSame($history[0]->is_post, true);
    $this->assertSame($history[0]->post_body, "BODY=TEST+HELLO&ORIGINATOR=ORIG&NUMBERS=447796354848&TIMETOLIVE=60&ORIGINATOR_TYPE=alpha&ENCODING=gsm");
    $this->assertSame($history[0]->url, "https://sonar.fonix.io/v2/sendsms");
    $this->assertSame($history[0]->headers, array("X-API-KEY: API_KEY"));
  }



}
