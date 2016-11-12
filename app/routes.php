<?php
/**********************************************************
 * Application routes registration.                       *
 * Routes must be registered with `routers` service name. *
 **********************************************************/

$app->container('routes', function($app) {
    // admin dynamic controller route mapping
    $app->addRoute('/@prefix(/@controller(/@action(/@id)))', [
            '@prefix' => 'admin',
            '@controller' => ':alnum',
            '@action' => ':alnum',
            '@id' => ':digit'
        ]
    );

    // default dynamic controller mapping
    $app->addRoute('(/@controller(/@action(/@id)))', [
            '@controller' => ':alnum',
            '@action' => ':alnum',
            '@id' => ':digit'
        ]
    );
});
