<?php
// admin controller route mapping
$app->route('/admin(/@controller(/@action(/@id)))', function() {
    return [
        '@directory' => 'admin',
        '@controller' => ':alnum',
        '@action' => ':alnum',
        '@id' => ':digit'
    ];
});

// default controller mapping
$app->route('(/@controller(/@action(/@id)))', function() {
    return [
        '@controller' => ':alnum',
        '@action' => ':alnum',
        '@id' => ':digit'
    ];
});