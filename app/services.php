<?php
/**
 * Application error handling.
 * Error details & stack trace only be displayed in development environment.
 */
$app->container('error', function($exc) use ($app) {
    $environment = $app->getConfig('environment');
    $status = $app->response->getStatus();
    
    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        
        $message = ($status === 404) 
        ? 'Page not found.'
        : 'Sorry! Something went wrong.';
        
        $app->response->write($message);
        $app->response->render();
    } else {
        error_reporting(-1);
        $exc->render();
    }
    
    return $app;
});

/**
 * Monolog service for the application.
 * Default is using the StreamHandler.
 * Details: https://github.com/Seldaek/monolog
 */
$app->container('log', function() use ($app) {
    $logFile = AVENUE_LOG_DIR . '/' . date('Y-m-d'). '.log';
    $monolog = new Monolog\Logger($app->getConfig('logChannel'));
    $monolog->pushHandler(new Monolog\Handler\StreamHandler($logFile));
    
    return $monolog;
});