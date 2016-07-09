<?php
/**
 * Registering your view helpers here.
 * Helper(s) can be called in view template directly via view object.
 */

// base url helper
$app->view->register('baseUrl', function() use ($app) {
    return $app->request->getBaseUrl();
});

// get app version
$app->view->register('version', function() use ($app) {
    return $app->getAppVersion();
});