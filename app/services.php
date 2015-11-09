<?php
// application error handling based on the environment
$app->service('error', function($exc) use ($app) {
    $environment = $app->config('environment');
    $httpStatus = $app->response->getHttpStatus();
    
    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        
        $page = ($httpStatus === 404)
        ? $app->view->fetch('errors/404')
        : $app->view->fetch('errors/500');
        
        $app->response->write($page);
        $app->response->render();
    } else {
        error_reporting(-1);
        $exc->render();
    }
    
    return $app;
});

// Monolog setup for application logger
// https://github.com/Seldaek/monolog
$app->service('monolog', function() {
    $logFile = AVENUE_LOG_DIR . '/' . date('Y-m-d'). '.log';
    $logger = new \Monolog\Logger('avenue.log');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile));
    
    return $logger;
});