<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConsoleWiki
 *
 * @author ilya
 */

namespace Application\Console;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConsoleWiki implements ServiceProviderInterface {
    private $app;
    private $args;
    
    public function register(Container $app) {
        $app['console.wiki'] = $app->protect(function($args) use($app) {
            $this->app = $app;
            $this->args = $args;
            return $this;
        });
    }
    
    public function runStep() {
        return false;
    }
    
    public function run() {
        var_dump($this->args);
        while ($this->runStep());
    }
}
