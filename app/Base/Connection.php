<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Connection
 *
 * @author ilya
 */

namespace Application\Base;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Connection implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['connection'] = $app->protect(function() use($app) {
            require_once __DIR__.'/../../library/adodb5/adodb.inc.php';
            $db = adoNewConnection('mysqli');
            $db->connect(
                    $app['connection.host'], 
                    $app['connection.user'], 
                    $app['connection.pwd'], 
                    $app['connection.db']);
            return $db;
        });
    }
}
