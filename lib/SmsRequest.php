<?php
namespace ZenSend;

class SmsRequest
{
  public $body;
  public $originator;
  public $numbers;

  public $originator_type;
  public $timetolive_in_minutes;
  public $encoding;
}

?>