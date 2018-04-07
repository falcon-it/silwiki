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

class Bootstrap {
    protected $app;
    
    public function __construct() {
        $this->app = new Application();
    }
    
    private function baseCoofigure() {
        $this->app->register(new \Application\Console\ConsoleWiki());
    }
    
    public function console($argv) {
        $this->baseCoofigure();
        
        $command = false;
        $args = array();
        
        if(count($argv) > 1) { $command = $argv[1]; }
        if(count($argv) > 2) { $args = array_slice($argv, 2); }
        if(!empty($command)) { $this->app["console.$command"]($args)->run(); }
    }
    
    public function run() {
        $this->baseCoofigure();
        $this->app->register(
                new \Silex\Provider\TwigServiceProvider(), 
                array('twig.path' => __DIR__.'/views'));
        $this->app->mount('/', new \Application\Controllers\SiteController());
        $this->app->run();
    }
}
