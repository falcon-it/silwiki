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

class Bootstrap {
    protected $app;
    
    public function __construct() {
        $this->app = $app = new \Silex\Application();
    }
    
    public function run() {
        $this->app->register(
                new \Silex\Provider\TwigServiceProvider(), 
                array('twig.path' => __DIR__.'/views'));
        new \Application\Controllers\SiteController($this->app);
        $this->app->run();
    }
}
