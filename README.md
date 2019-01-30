[![Build Status](https://travis-ci.org/Fonix/zensend_php_api.svg?branch=master)](https://travis-ci.org/zensend/zensend_php_api)
# Fonix PHP bindings

You can sign up for a Fonix account at https://zensend.io.

## Requirements

PHP 5.3.3 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Add this to your `composer.json`:

    {
      "require": {
        "Fonix/zensend": "1.0.4"
      }
    }

Then install via:

    composer install

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

    require_once('vendor/autoload.php');

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/Fonix/zensend_php_api/releases). Then, to use the bindings, include the `init.php` file.

    require_once('/path/to/Fonix_php_api/init.php');

## Getting Started

Simple usage looks like:

    $client = new Fonix\Client("api_key");
    $request = new Fonix\SmsRequest();
    $request->body = "BODY";
    $request->originator = "ORIG";
    $request->numbers = ["447700000000"];
    $result = $client->send_sms($request);
    echo $result->numbers;
    echo $result->sms_parts;
    echo $result->encoding;
    echo $result->tx_guid;

## Verify Getting Started

After authenticating the user display the verify iframe using the following code:

    <?php
      session_start();
      $verify = new Fonix\Verify("api_key");
      $verify->create_session("441234567890"); // the number you want to verify
      $verify->write_tags("https://" . $_SERVER['HTTP_HOST'] . "/verify_callback.php");
    ?>

Create another file called verify_callback.php to handle verification:

    <?php
      session_start();
      $verify = new Fonix\Verify("api_key");
      try {
        $verify->verify_response("441234567890");  // the number you want to verify
        // handle verification success
      } catch (Exception $e) {
        // handle verification failed
      }
    ?>

## Documentation

Please see https://Fonix.io/public/docs for up-to-date documentation.

## Certificate Errors

If you receive errors like:

    "SSL certificate problem: unable to get local issuer certificate"

This is likely because your php curl is not set up with a certificate bundle. This can be fixed by following the instructions here: https://support.zend.com/hc/en-us/articles/204159368-PHP-CURL-HTTPS-Error-SSL-certificate-problem-unable-to-get-local-issuer-certificate-

Or alternatively we have included the CA certificates that we require in a bundle which can be used by creating the Fonix Client like:

   $client = Fonix\Client::newWithHardcodedCA("api_key");

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
    >>> $client = new Fonix\Client("api_key", array(), "http://127.0.0.1:8084");
