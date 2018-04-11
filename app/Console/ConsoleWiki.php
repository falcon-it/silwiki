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
use Application\Base\ConsoleCommand;
use Application\Wiki\State;
use Application\Wiki\Handler;

class ConsoleWiki implements ServiceProviderInterface, ConsoleCommand {
    private $app;
    private $args;
    
    public function register(Container $app) {
        $app['console.wiki'] = $app->protect(function($args) use($app) {
            $this->app = $app;
            $this->args = $args;
            return $this;
        });
    }
    
    public function run() {
        $s = new State($this->args[0]);
        
        while(true) {
            $exit = false;
            
            //много раз вызываем обработчитк
            Handler::Execute($s, $this->app);
            
            switch ($s->state) {
                //пока не обработчик не установит состояния 
                //когда он сделал всё что мог и дальше работать не будет
                case State::ART_SAVE_FAIL:
                case State::WIKI_NOT_FOUND:
                case State::OK:
                case State::ERROR:
                    $exit = true;
                    break;
            }
            
            if($exit) { break; }
        }
    }
}
