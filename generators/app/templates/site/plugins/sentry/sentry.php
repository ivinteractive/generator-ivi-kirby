<?php

require_once(kirby()->roots()->index() . DS . '..' . DS . 'bootstrap.php');
Raven_Autoloader::register();

$client = new Raven_Client(EnvHelper::env('SENTRY_DSN'));
$error_handler = new Raven_ErrorHandler($client);
$error_handler->registerExceptionHandler();
$error_handler->registerErrorHandler();
$error_handler->registerShutdownFunction();