<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Models;

/**
 * Description of Models
 *
 * @author ilya
 */

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Models implements ServiceProviderInterface {
    public function register(Container $app) {
        $app['article'] = function($app) {
            if(empty($app['article.connection'])) {
                throw new \Exception('fail connection');
            }
            
            return function() { return new Article(); };
        };
        
        $app['atom'] = function($app) {
            if(empty($app['article.connection'])) {
                throw new \Exception('fail connection');
            }
            
            return function() { return new Atom(); };
        };
        
        $app['link'] = function($app) {
            if(empty($app['article.connection'])) {
                throw new \Exception('fail connection');
            }
            
            return function() { return new Link(); };
        };
    }
}
