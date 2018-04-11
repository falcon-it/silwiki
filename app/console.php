<?php

/**
 * скрипт задля запуска с консоли
 */

require_once __DIR__.'/../vendor/autoload.php';

$bootstrap = new Application\Bootstrap();
$bootstrap->console($argv);

