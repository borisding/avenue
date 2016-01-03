<?php
/**
 * Registering your view helpers here.
 * Helper(s) can be called in view template directly via view object.
 */

// base url helper
$app->view->register('baseUrl', function() use ($app) {
    return $app->request->getBaseUrl();
});

// upper case helper
$app->view->register('upper', function($input) {
    return strtoupper($input);
});

// lower case helper
$app->view->register('lower', function($input) {
    return strtolower($input);
});