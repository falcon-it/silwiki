<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() use($app) {
    return 'Hello, World!';
    
    # можно вернуть объект ответа, как и в Symfony
    # return new Symfony\Component\HttpFoundation\Response('Hello, world');
});

$app->run();
