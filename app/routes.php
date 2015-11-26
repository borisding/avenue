<?php
// admin controller route mapping
$app->addRoute('/admin(/@controller(/@action(/@id)))', function() {
    return [
        '@directory' => 'admin',
        '@controller' => ':alnum',
        '@action' => ':alnum',
        '@id' => ':digit'
    ];
});

// default controller mapping
$app->addRoute('(/@controller(/@action(/@id)))', function() {
    return [
        '@controller' => ':alnum',
        '@action' => ':alnum',
        '@id' => ':digit'
    ];
});