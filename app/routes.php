<?php
/**
 * Register respective application routes.
 */

$app->container('routes', function($app) {
    // admin dynamic controller route mapping
    $app->addRoute('/admin(/@controller(/@action(/@id)))', function() {
        return [
            '@prefix' => 'admin',
            '@controller' => ':alnum',
            '@action' => ':alnum',
            '@id' => ':digit'
        ];
    });

    // default dynamic controller mapping
    $app->addRoute('(/@controller(/@action(/@id)))', function() {
        return [
            '@controller' => ':alnum',
            '@action' => ':alnum',
            '@id' => ':digit'
        ];
    });
});