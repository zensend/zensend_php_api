[![Build Status](https://travis-ci.org/zensend/zensend_php_api.svg?branch=master)](https://travis-ci.org/zensend/zensend_php_api)
# ZenSend PHP bindings

You can sign up for a ZenSend account at https://zensend.io.

## Requirements

PHP 5.3.3 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Add this to your `composer.json`:

    {
      "require": {
        "zensend/zensend-php": "*"
      }
    }

Then install via:

    composer install

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

    require_once('vendor/autoload.php');

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/fonixcode/zensend_php_api/releases). Then, to use the bindings, include the `init.php` file.

    require_once('/path/to/zensend_php_api/init.php');

## Getting Started

Simple usage looks like:

    $client = new ZenSend\Client("api_key");
    $request = new ZenSend\SmsRequest();
    $request->body = "BODY";
    $request->originator = "ORIG";
    $request->numbers = ["447700000000"];
    $result = $client->send_sms($request);
    echo $result->numbers;
    echo $result->sms_parts;
    echo $result->encoding;
    echo $result->tx_guid;

## Documentation

Please see https://zensend.io/docs for up-to-date documentation.

## String Encoding

All strings sent to the API should be UTF-8 encoded.

## Tests

In order to run tests first install [PHPUnit](http://packagist.org/packages/phpunit/phpunit) via [Composer](http://getcomposer.org/):

    composer update --dev

To run the test suite:

    ./vendor/bin/phpunit

## Manual Testing

    ~/.composer/vendor/bin/psysh
    >>> require('./init.php')
    >>> $client = new ZenSend\Client("api_key", "http://127.0.0.1:8084");
    >>> $response = $client->lookup_operator("441234567890");


