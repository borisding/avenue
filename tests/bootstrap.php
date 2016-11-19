<?php
// include entry script configuration
require_once dirname(__DIR__) . '/config/entry.php';

// set tests namespace at runtime
$autoloader->addPsr4('Avenue\\Tests\\', [__DIR__, __DIR__ . '/src']);
