<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SiteController
 *
 * @author user2
 */

namespace Application\Controllers;

class SiteController {
    private $app;
    
    public function __construct($app) {
        $this->app = $app;
        $this->initRoutines();
    }
    
    private function initRoutines() {
        $app = $this->app;
        $app->get('/', function() use($app) { 
            return $app['twig']->render('index.twig', array(
        'name' => 'Hello, World!'
    ));
            
        });
    }
}
