<?php
// Registering custom view helpers
// Helper(s) can be called in view template.

$app->view->register('upper', function($input) {
    return strtoupper($input);
});

$app->view->register('lower', function($input) {
    return strtolower($input);
});