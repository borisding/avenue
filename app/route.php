<?php
// default route mapping
// any other routes should put before default route
$app->addRoute('(/@controller(/@action(/@id)))', [
    '@controller' => ':alnum',
    '@action' => ':alnum',
    '@id' => ':digit'
]);
