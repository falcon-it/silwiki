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

require_once __DIR__.'/../../vendor/adodb/adodb-php/adodb.inc.php';
require_once __DIR__.'/../../vendor/adodb/adodb-php/adodb-active-record.inc.php';

class Connection implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['connection'] = function($app) {
            $db = adoNewConnection('mysqli');
            $db->connect(
                    $app['connection.host'], 
                    $app['connection.user'], 
                    $app['connection.pwd'], 
                    $app['connection.db']);
            $db->setCharset('utf8');
            //$db->debug = true;
            \ADOdb_Active_Record::SetDatabaseAdapter($db);
            \ADODB_Active_Record :: TableHasMany('articles', 'links', 'acticle_id');
            //\ADODB_Active_Record :: ClassHasMany('Application\Models\Article', 'Application\Models\Link', 'acticle_id');
            return $db;
        };
    }
}
