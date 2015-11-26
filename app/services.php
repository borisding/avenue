<?php
// application error handling based on the environment
$app->addService('error', function($exc) use ($app) {
    $environment = $app->getConfig('environment');
    $httpStatus = $app->response->getHttpStatus();
    
    if ($environment === 'staging' || $environment === 'production') {
        error_reporting(0);
        
        $message = ($httpStatus === 404) 
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