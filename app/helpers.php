<?php
// Registering custom view helpers
// Helper(s) can be called in view template.

$view = $app->singleton('view');

$view->register('upper', function($input) {
    return strtoupper($input);
});

$view->register('lower', function($input) {
    return strtolower($input);
});