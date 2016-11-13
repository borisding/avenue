<?php
/***********************************************************************
 * View helpers registration.                                          *
 * Helper(s) can be invoked in view template directly via view object. *
 ***********************************************************************/

// get view instance
$view = $app->view();

// base url helper
$view->register('baseUrl', function() use ($app) {
    return $app->request()->getBaseUrl();
});

// get app version
$view->register('version', function() use ($app) {
    return $app->getAppVersion();
});

// translation helper
$view->register('t', function($source, array $value = []) use ($app) {
    return $app->t($source, $value);
});
