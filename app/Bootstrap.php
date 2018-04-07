<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bootstrap
 *
 * @author user2
 */

namespace Application;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Application\Console\ConsoleWiki;
use Application\Controllers\SiteController;
use Application\Base\Connection;

class Bootstrap {
    protected $app;
    
    public function __construct() {
        $this->app = new Application();
    }
    
    private function baseConfigure() {
        $this->app->register(
                new Connection(), 
                array(
                    'connection.host' => 'localhost',
                    'connection.user' => 'root',
                    'connection.pwd' => '123456',
                    'connection.db' => 'wiki'
                )
                );
        $this->app->register(new ConsoleWiki());
    }
    
    public function console($argv) {
        $this->baseConfigure();
        
        $command = false;
        $args = array();
        $recordSet = $this->app['connection']()->Execute('select count(*) from articles');
        while (!$recordSet->EOF) {
            echo 'ok: '.$recordSet->fields[0];
            $recordSet->MoveNext();
        }
        
        if(count($argv) > 1) { $command = $argv[1]; }
        if(count($argv) > 2) { $args = array_slice($argv, 2); }
        if(!empty($command)) { $this->app["console.$command"]($args)->run(); }
    }
    
    public function run() {
        $this->baseConfigure();
        $this->app->register(
                new TwigServiceProvider(), 
                array('twig.path' => __DIR__.'/views'));
        $this->app->mount('/', new SiteController());
        $this->app->run();
    }
}
