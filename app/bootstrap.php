<?php
$app = new \Avenue\App();

// default route mapping, any other routes should put before default route
$app->route('(/@controller(/@action(/@id)))', [
    '@controller' => ':alnum',
    '@action' => ':alpha',
    '@id' => ':digit'
]);

// application error handling based on the environment
$app->service('error', function() use ($app) {
    $environment = $app->config('environment');

    if ($environment === 'production') {
        // TODO
    } else {
        // TODO
    }
});

// rendering application's output
$app->render();