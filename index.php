<?php

require("./vendor/autoload.php");
require("./config/index.php");
require('./routes/index.php');

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;

use Application\Core\Dispatcher;
use Application\Core\Middleware\AppHandler;
use Application\Core\Middleware\AuthHandler;
use Application\Core\Middleware\BodyParser;
use Application\Core\Middleware\CorsHandler;
use Application\Core\Middleware\ImgUploader;
use Application\Core\Middleware\QueryParamsParser;

$auth_handler = new AuthHandler;
$app_handler = new AppHandler;
$body_parser = new BodyParser;
$img_uploader = new ImgUploader;
$cors_handler = new CorsHandler;
$query_params_parser = new QueryParamsParser;

$request =  ServerRequest::fromGlobals();
$response = new Response();
$dispatcher = new Dispatcher();

$dispatcher->pipe($auth_handler);
$dispatcher->pipe($query_params_parser);
$dispatcher->pipe($body_parser);
$dispatcher->pipe($img_uploader);
$dispatcher->pipe($cors_handler);
$dispatcher->pipe($app_handler);

$response = $dispatcher->process($request, $response);

send($response);
